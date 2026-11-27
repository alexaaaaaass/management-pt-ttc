<?php

namespace App\Filament\Admin\Pages;

use App\Models\Cuti;
use App\Models\HrPayrollSetting;
use App\Models\HrShift;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\KaryawanDisiplin;
use App\Models\Lembur;
use App\Models\PengPinjaman;
use App\Services\HR\AttendanceService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class HrdDashboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Dashboard HRD';
    protected static ?string $title = 'Dashboard HRD — Kepegawaian, Absensi & Kedisiplinan';
    protected static string $view = 'filament.admin.pages.hrd-dashboard-page';

    public int $bulan;
    public int $tahun;
    public int $kontrakFilterHari;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(): void
    {
        $this->bulan = (int) request()->query('bulan', now()->month);
        $this->tahun = (int) request()->query('tahun', now()->year);
        $this->kontrakFilterHari = (int) (HrPayrollSetting::current()->alert_kontrak_hari ?: 0);
    }

    public function getDashboardDataProperty(): array
    {
        $karyawans = Karyawan::with(['departemen', 'shift'])->orderBy('nama')->get();
        $aktif = $karyawans->where('status_pegawai', 'Aktif');
        $nonaktif = $karyawans->where('status_pegawai', '!=', 'Aktif');

        // 1. Breakdown Skema Gaji
        $bySkema = [
            'Bulanan'   => $aktif->where('tipe_gaji', 'Bulanan')->count(),
            'Harian'    => $aktif->where('tipe_gaji', 'Harian')->count(),
            'Freelance' => $aktif->where('tipe_gaji', 'Freelance')->count(),
            'Borongan'  => $aktif->filter(fn ($k) => $k->tipe_gaji === 'Borongan' || $k->is_borongan)->count(),
        ];

        // 2. Breakdown Departemen
        $byDept = $aktif->groupBy(fn ($k) => $k->departemen?->nama_departemen ?: 'Belum Diatur')
            ->map(fn ($grp, $name) => [
                'nama'  => $name,
                'count' => $grp->count(),
                'pct'   => $aktif->count() > 0 ? round(($grp->count() / $aktif->count()) * 100, 1) : 0,
            ])
            ->sortByDesc('count')
            ->values()
            ->all();

        // 3. Breakdown Shift
        $byShift = HrShift::withCount(['karyawans' => fn ($q) => $q->where('status_pegawai', 'Aktif')])
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn ($s) => [
                'kode'  => $s->kode,
                'nama'  => $s->nama,
                'jam'   => "{$s->jam_masuk}–{$s->jam_pulang}",
                'count' => (int) $s->karyawans_count,
            ])
            ->all();

        // 4. Monitoring Masa Kontrak (fleksibel: 0 = semua kontrak aktif, atau H-7 / H-14 / H-30 / H-90 / H-365)
        $today = now()->startOfDay();
        $filterHari = (int) $this->kontrakFilterHari;
        $alertSettingHari = (int) (HrPayrollSetting::current()->alert_kontrak_hari ?: 30);

        $contractAlerts = $aktif->filter(function (Karyawan $k) use ($today, $filterHari) {
            if ($k->masa_kontrak === 'TETAP' || ! $k->tangal_akhir_kontrak) {
                return false;
            }
            if ($filterHari <= 0) {
                return true;
            }
            $diff = $today->diffInDays(Carbon::parse($k->tangal_akhir_kontrak)->startOfDay(), false);

            return $diff <= $filterHari;
        })->map(function (Karyawan $k) use ($today, $alertSettingHari) {
            $exp = Carbon::parse($k->tangal_akhir_kontrak)->startOfDay();
            $diff = $today->diffInDays($exp, false);

            return [
                'id'           => $k->id,
                'nip'          => $k->nip,
                'nama'         => $k->nama,
                'departemen'   => $k->departemen?->nama_departemen ?? '-',
                'masa_kontrak' => Karyawan::MASA_KONTRAK_SHORT[$k->masa_kontrak] ?? ($k->masa_kontrak ?: 'Manual'),
                'tanggal'      => $exp->format('d/m/Y'),
                'sisa_hari'    => $diff,
                'is_expired'   => $diff < 0,
                'is_soon'      => $diff >= 0 && $diff <= $alertSettingHari,
            ];
        })->sortBy('sisa_hari')->values()->all();

        // 5. Alert Kelengkapan Data Karyawan Aktif (PIN Finger / Rekening Bank / BPJS)
        $incompleteProfiles = $aktif->map(function (Karyawan $k) {
            $missing = [];
            if (blank($k->pin_finger)) {
                $missing[] = 'PIN Finger';
            }
            if (blank($k->no_rekening)) {
                $missing[] = 'Rekening Bank';
            }
            if (blank($k->bpjs_ketenagakerjaan) && blank($k->bpjs_kesehatan)) {
                $missing[] = 'No. BPJS';
            }
            if (blank($k->nik)) {
                $missing[] = 'NIK/KTP';
            }
            return [
                'id'      => $k->id,
                'nip'     => $k->nip,
                'nama'    => $k->nama,
                'jabatan' => $k->jabatan ?: '-',
                'missing' => $missing,
            ];
        })->filter(fn ($row) => ! empty($row['missing']))->values()->all();

        // 6. Pending Approvals
        $pending = [
            'cuti'     => Cuti::where('status', 'Pending')->count(),
            'izin'     => Izin::where('status', 'Pending')->count(),
            'lembur'   => Lembur::where('status', 'Pending')->count(),
            'pinjaman' => PengPinjaman::where('status', 'Pending')->count(),
        ];
        $pending['total'] = array_sum($pending);

        // 7. Kehadiran & Lembur Bulan Berjalan (Cut-off 26-25)
        $attendanceRecap = app(AttendanceService::class)->getMonthlyRecap($this->tahun, $this->bulan);

        // 8. Catatan Kedisiplinan Aktif (SP1 / SP2 / SP3 / Scorsing)
        $activeDisiplins = KaryawanDisiplin::with('karyawan')
            ->where('status', 'Aktif')
            ->orderByDesc('tanggal_surat')
            ->limit(10)
            ->get()
            ->map(fn (KaryawanDisiplin $d) => [
                'karyawan_id' => $d->karyawan_id,
                'nama'        => $d->karyawan?->nama ?? '-',
                'nomor_surat' => $d->nomor_surat,
                'jenis'       => $d->jenis,
                'jenis_label' => KaryawanDisiplin::JENIS_OPTIONS[$d->jenis] ?? $d->jenis,
                'tanggal'     => $d->tanggal_surat?->format('d/m/Y') ?? '-',
                'hari'        => (int) $d->jumlah_hari_scorsing,
                'pelanggaran' => $d->pelanggaran,
            ])
            ->all();

        return [
            'total_karyawan'      => $karyawans->count(),
            'total_aktif'         => $aktif->count(),
            'total_nonaktif'      => $nonaktif->count(),
            'by_skema'            => $bySkema,
            'by_dept'             => $byDept,
            'by_shift'            => $byShift,
            'contract_alerts'     => $contractAlerts,
            'incomplete_profiles' => $incompleteProfiles,
            'pending'             => $pending,
            'attendance'          => $attendanceRecap,
            'active_disiplins'    => $activeDisiplins,
        ];
    }
}
