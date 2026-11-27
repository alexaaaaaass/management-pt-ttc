<?php

namespace App\Services\HR;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\HariLibur;
use App\Models\HrPayrollSetting;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\KaryawanDisiplin;
use App\Models\Lembur;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AttendanceService
{
    /**
     * Tentukan rentang tanggal cut-off periode payroll (default PT TTC: tgl 26 bulan lalu s/d tgl 25 bulan berjalan).
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getPeriodDateRange(int $year, int $month): array
    {
        $setting = HrPayrollSetting::current();
        $useCutoff = (bool) ($setting->pakai_cutoff_bulanan ?? true);
        $cutStart = (int) ($setting->cutoff_tanggal_mulai ?: 26);
        $cutEnd = (int) ($setting->cutoff_tanggal_selesai ?: 25);

        $anchor = Carbon::create($year, $month, 1);

        if ($useCutoff && $cutStart > $cutEnd) {
            $prevMonth = $anchor->copy()->subMonth();
            $startDay = min($cutStart, $prevMonth->daysInMonth);
            $endDay = min($cutEnd, $anchor->daysInMonth);

            $start = Carbon::create($prevMonth->year, $prevMonth->month, $startDay)->startOfDay();
            $end = Carbon::create($year, $month, $endDay)->endOfDay();
        } else {
            $start = $anchor->copy()->startOfMonth()->startOfDay();
            $end = $anchor->copy()->endOfMonth()->endOfDay();
        }

        return [
            'start' => $start,
            'end'   => $end,
            'label' => $start->format('d M Y') . ' – ' . $end->format('d M Y'),
        ];
    }

    /**
     * Ambil aturan shift spesifik karyawan (Multi-Shift) atau fallback ke HrPayrollSetting.
     */
    public function resolveEmployeeShiftRules(?Karyawan $karyawan, ?HrPayrollSetting $setting = null): array
    {
        $setting = $setting ?? HrPayrollSetting::current();
        $shift = $karyawan?->shift;

        $jamMasuk = $shift?->jam_masuk ?: ($setting->jam_masuk_standar ?: '08:00');
        $jamPulang = $shift?->jam_pulang ?: ($setting->jam_pulang_standar ?: '17:00');
        $toleransi = (int) ($shift ? $shift->toleransi_telat_menit : ($setting->toleransi_telat_menit ?? 10));
        $isOvernight = (bool) ($shift?->is_overnight ?? false);

        return [
            'shift_nama'         => $shift?->nama ?? 'Default Kantor',
            'masuk_menit'        => $this->parseTimeMinutes($jamMasuk),
            'masuk_cutoff_menit' => $this->parseTimeMinutes($jamMasuk) + $toleransi,
            'pulang_menit'       => $this->parseTimeMinutes($jamPulang),
            'is_overnight'       => $isOvernight,
        ];
    }

    /**
     * Hitung jumlah hari kerja efektif (mengecualikan Hari Minggu dan daftar HariLibur).
     */
    public function countEffectiveWorkDays(Carbon $start, Carbon $end): int
    {
        if ($end->lessThan($start)) {
            return 0;
        }

        $holidays = HariLibur::whereBetween('tanggal_libur', [$start->toDateString(), $end->toDateString()])
            ->pluck('tanggal_libur')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->flip();

        $days = 0;
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $day) {
            if ($day->isSunday()) {
                continue;
            }
            if ($holidays->has($day->toDateString())) {
                continue;
            }
            $days++;
        }

        return $days;
    }

    /**
     * Hitung sisa kuota cuti tahunan karyawan pada tahun tertentu (tidak menghitung cuti berstatus Rejected).
     */
    public function getRemainingAnnualLeave(int $karyawanId, int $year, ?int $ignoreCutiId = null): array
    {
        $kuota = (int) (HrPayrollSetting::current()->kuota_cuti_tahunan ?: 12);

        $used = (int) Cuti::query()
            ->where('karyawan_id', $karyawanId)
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->whereYear('tanggal_mulai', $year)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->when($ignoreCutiId, fn ($q) => $q->where('id', '!=', $ignoreCutiId))
            ->sum('jumlah_hari');

        return [
            'kuota'     => $kuota,
            'digunakan' => $used,
            'sisa'      => max(0, $kuota - $used),
        ];
    }

    /**
     * Rekap kehadiran satu karyawan pada rentang tanggal (digunakan oleh PayrollService).
     * Mendukung Multi-Shift, keterlambatan per menit, dan Scorsing (dari Izin maupun Catatan Kedisiplinan).
     */
    public function getEmployeeAttendanceStats(int $karyawanId, Carbon $start, Carbon $end): array
    {
        $setting = HrPayrollSetting::current();
        $karyawan = Karyawan::with('shift')->find($karyawanId);
        $shiftRules = $this->resolveEmployeeShiftRules($karyawan, $setting);

        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $scans = Absensi::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal_scan', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('tanggal_scan')
            ->get();

        $hadirDays = [];
        $telatFromScan = 0;
        $telatMenitTotal = 0;

        $scansByDate = $scans->groupBy(fn ($s) => Carbon::parse($s->tanggal_scan)->toDateString());
        foreach ($scansByDate as $tgl => $dayScans) {
            $hadirDays[$tgl] = true;
            $firstIn = $dayScans->first(fn ($s) => $s->io === 'Masuk') ?? $dayScans->first();
            if ($firstIn) {
                $scanTime = Carbon::parse($firstIn->tanggal_scan);
                $scanMinutes = ($scanTime->hour * 60) + $scanTime->minute;

                // Handle shift malam (misal masuk 23:00, scan 00:15 dianggap +1440 menit)
                $evalMinutes = $scanMinutes;
                if ($shiftRules['is_overnight'] && $scanMinutes < 360) {
                    $evalMinutes += 1440;
                }

                if ($evalMinutes > $shiftRules['masuk_cutoff_menit']) {
                    $telatFromScan++;
                    $telatMenitTotal += max(0, $evalMinutes - $shiftRules['masuk_menit']);
                }
            }
        }

        $izins = Izin::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal_izin', [$startStr, $endStr])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->get();

        $alphaFromIzin = $izins->where('jenis_izin', 'alpha')->count();
        $scorsingFromIzin = $izins->where('jenis_izin', 'scorsing')->count();
        $telatFromIzin = $izins->where('jenis_izin', 'terlambat')->count();
        $sakitTanpaSurat = $izins->where('jenis_izin', 'sakit')->count();
        $sakitCount = $izins->whereIn('jenis_izin', ['sakit', 'sakit_surat_dokter'])->count();
        $izinLain = $izins->whereIn('jenis_izin', ['meninggalkan_kantor', 'pulang_awal', 'dinas_luar'])->count();

        // Hitung juga hari scorsing dari tabel karyawan_disiplins yang jatuh di rentang periode
        $scorsingFromDisiplin = (int) KaryawanDisiplin::where('karyawan_id', $karyawanId)
            ->where('jenis', 'SCORSING')
            ->where('status', '!=', 'Dicabut')
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('tanggal_mulai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_selesai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_surat', [$startStr, $endStr]);
            })
            ->sum('jumlah_hari_scorsing');

        $scorsingDays = max($scorsingFromIzin, $scorsingFromDisiplin);

        $cutiHari = (int) Cuti::where('karyawan_id', $karyawanId)
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('tanggal_mulai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_selesai', [$startStr, $endStr]);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->sum('jumlah_hari');

        return [
            'hari_kerja'        => $this->countEffectiveWorkDays($start, $end),
            'hadir'             => count($hadirDays),
            'terlambat'         => max($telatFromScan, $telatFromIzin),
            'telat_menit'       => $telatMenitTotal,
            'alpha'             => $alphaFromIzin,
            'scorsing'          => $scorsingDays,
            'sakit'             => $sakitCount,
            'sakit_tanpa_surat' => $sakitTanpaSurat,
            'izin'              => $izinLain,
            'cuti'              => $cutiHari,
            'shift_nama'        => $shiftRules['shift_nama'],
        ];
    }

    /**
     * Rekap absensi bulanan seluruh karyawan aktif (mendukung Cut-off 26-25, Multi-Shift & Scorsing).
     */
    public function getMonthlyRecap(int $year, int $month, ?int $departemenId = null, ?string $tipeGaji = null): array
    {
        $range = $this->getPeriodDateRange($year, $month);
        $start = $range['start'];
        $end = $range['end'];
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $setting = HrPayrollSetting::current();
        $hariKerjaEfektif = $this->countEffectiveWorkDays($start, $end);
        $anchorMonth = Carbon::create($year, $month, 1);

        $karyawans = Karyawan::with(['departemen', 'shift'])
            ->where('status_pegawai', 'Aktif')
            ->when($departemenId, fn ($q) => $q->where('departemen_id', $departemenId))
            ->when($tipeGaji, fn ($q) => $q->where('tipe_gaji', $tipeGaji))
            ->orderBy('nama')
            ->get();

        $ids = $karyawans->pluck('id')->all();
        if (empty($ids)) {
            return [
                'periode'          => $anchorMonth->format('Y-m'),
                'periode_label'    => $anchorMonth->translatedFormat('F Y'),
                'cutoff_label'     => $range['label'],
                'hari_kerja'       => $hariKerjaEfektif,
                'rows'             => [],
                'summary'          => ['karyawan' => 0, 'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0, 'alpha' => 0, 'scorsing' => 0, 'lembur' => 0],
            ];
        }

        $absensis = Absensi::whereIn('karyawan_id', $ids)
            ->whereBetween('tanggal_scan', [$start, $end])
            ->orderBy('tanggal_scan')
            ->get()
            ->groupBy('karyawan_id');

        $izins = Izin::whereIn('karyawan_id', $ids)
            ->whereBetween('tanggal_izin', [$startStr, $endStr])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->get()
            ->groupBy('karyawan_id');

        $disiplins = KaryawanDisiplin::whereIn('karyawan_id', $ids)
            ->where('jenis', 'SCORSING')
            ->where('status', '!=', 'Dicabut')
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('tanggal_mulai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_selesai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_surat', [$startStr, $endStr]);
            })
            ->get()
            ->groupBy('karyawan_id');

        $cutis = Cuti::whereIn('karyawan_id', $ids)
            ->where(function ($q) use ($startStr, $endStr) {
                $q->whereBetween('tanggal_mulai', [$startStr, $endStr])
                    ->orWhereBetween('tanggal_selesai', [$startStr, $endStr]);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->get()
            ->groupBy('karyawan_id');

        $lemburs = Lembur::whereIn('karyawan_id', $ids)
            ->whereBetween('tanggal_lembur', [$startStr, $endStr])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->get()
            ->groupBy('karyawan_id');

        $rows = [];
        $summary = ['karyawan' => count($ids), 'hadir' => 0, 'telat' => 0, 'izin' => 0, 'sakit' => 0, 'cuti' => 0, 'alpha' => 0, 'scorsing' => 0, 'lembur' => 0.0];

        foreach ($karyawans as $k) {
            $shiftRules = $this->resolveEmployeeShiftRules($k, $setting);

            /** @var Collection $kScans */
            $kScans = $absensis->get($k->id, collect());
            $byDate = $kScans->groupBy(fn ($s) => Carbon::parse($s->tanggal_scan)->toDateString());
            $hadir = $byDate->count();
            $telatScan = 0;
            $pulangCepat = 0;

            foreach ($byDate as $dayScans) {
                $firstIn = $dayScans->first(fn ($s) => $s->io === 'Masuk') ?? $dayScans->first();
                if ($firstIn) {
                    $t = Carbon::parse($firstIn->tanggal_scan);
                    $evalMin = ($t->hour * 60 + $t->minute);
                    if ($shiftRules['is_overnight'] && $evalMin < 360) {
                        $evalMin += 1440;
                    }
                    if ($evalMin > $shiftRules['masuk_cutoff_menit']) {
                        $telatScan++;
                    }
                }
                $lastOut = $dayScans->last(fn ($s) => $s->io === 'Keluar');
                if ($lastOut && ! $shiftRules['is_overnight']) {
                    $tOut = Carbon::parse($lastOut->tanggal_scan);
                    if (($tOut->hour * 60 + $tOut->minute) < $shiftRules['pulang_menit']) {
                        $pulangCepat++;
                    }
                }
            }

            /** @var Collection $kIzins */
            $kIzins = $izins->get($k->id, collect());
            $telat = max($telatScan, $kIzins->where('jenis_izin', 'terlambat')->count());
            $pulangCepat = max($pulangCepat, $kIzins->where('jenis_izin', 'pulang_awal')->count());
            $izin = $kIzins->whereIn('jenis_izin', ['meninggalkan_kantor', 'dinas_luar'])->count();
            $sakit = $kIzins->whereIn('jenis_izin', ['sakit', 'sakit_surat_dokter'])->count();
            $alpha = $kIzins->where('jenis_izin', 'alpha')->count();
            $scorsing = max(
                $kIzins->where('jenis_izin', 'scorsing')->count(),
                (int) $disiplins->get($k->id, collect())->sum('jumlah_hari_scorsing')
            );

            $cuti = (int) $cutis->get($k->id, collect())->sum('jumlah_hari');
            $lemburJam = round((float) $lemburs->get($k->id, collect())->sum('durasi_lembur'), 2);

            $rows[] = [
                'karyawan_id'  => $k->id,
                'nip'          => $k->nip,
                'nama'         => $k->nama,
                'departemen'   => $k->departemen?->nama_departemen ?? '-',
                'jabatan'      => $k->jabatan ?? '-',
                'shift'        => $shiftRules['shift_nama'],
                'tipe_gaji'    => $k->tipe_gaji ?? 'Bulanan',
                'hari_kerja'   => $hariKerjaEfektif,
                'hadir'        => $hadir,
                'telat'        => $telat,
                'pulang_cepat' => $pulangCepat,
                'izin'         => $izin,
                'sakit'        => $sakit,
                'cuti'         => $cuti,
                'alpha'        => $alpha,
                'scorsing'     => $scorsing,
                'lembur_jam'   => $lemburJam,
            ];

            $summary['hadir'] += $hadir;
            $summary['telat'] += $telat;
            $summary['izin'] += $izin;
            $summary['sakit'] += $sakit;
            $summary['cuti'] += $cuti;
            $summary['alpha'] += $alpha;
            $summary['scorsing'] += $scorsing;
            $summary['lembur'] = round($summary['lembur'] + $lemburJam, 2);
        }

        return [
            'periode'       => $anchorMonth->format('Y-m'),
            'periode_label' => $anchorMonth->translatedFormat('F Y'),
            'cutoff_label'  => $range['label'],
            'hari_kerja'    => $hariKerjaEfektif,
            'rows'          => $rows,
            'summary'       => $summary,
        ];
    }

    /**
     * Import log absensi fingerprint dari file Excel (.xlsx/.xls) atau CSV/TSV.
     * Format kolom yang dikenali otomatis: PIN/NIP, Nama, Tanggal Scan (atau Tanggal + Jam), IO (Masuk/Keluar/0/1/C/In/C/Out), Mesin, SN, Workcode.
     */
    public function importFromFile(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            throw new \RuntimeException('File absensi tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($absolutePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        if (count($rawRows) < 2) {
            return ['imported' => 0, 'skipped' => 0, 'unmatched' => 0];
        }

        // Petakan header kolom secara fleksibel
        $headerRow = array_map(fn ($h) => strtolower(trim((string) $h)), $rawRows[0]);
        $colMap = [
            'pin'      => $this->findCol($headerRow, ['pin', 'pin_finger', 'finger', 'id finger', 'user id', 'ac-no', 'ac no']),
            'nip'      => $this->findCol($headerRow, ['nip', 'nik', 'kode karyawan']),
            'nama'     => $this->findCol($headerRow, ['nama', 'name', 'nama karyawan', 'karyawan']),
            'datetime' => $this->findCol($headerRow, ['tanggal_scan', 'tanggal scan', 'datetime', 'waktu', 'scan', 'time', 'date/time']),
            'date'     => $this->findCol($headerRow, ['tanggal', 'date', 'tgl']),
            'time'     => $this->findCol($headerRow, ['jam', 'clock', 'jam_scan']),
            'io'       => $this->findCol($headerRow, ['io', 'i/o', 'status', 'in/out', 'state', 'tipe', 'keterangan']),
            'mesin'    => $this->findCol($headerRow, ['mesin', 'machine', 'device']),
            'sn'       => $this->findCol($headerRow, ['sn', 'serial']),
            'workcode' => $this->findCol($headerRow, ['workcode', 'work code']),
        ];

        $karyawans = Karyawan::with('departemen')->get();
        $byPin = $karyawans->filter(fn ($k) => filled($k->pin_finger))->keyBy(fn ($k) => trim((string) $k->pin_finger));
        $byNip = $karyawans->filter(fn ($k) => filled($k->nip))->keyBy(fn ($k) => trim((string) $k->nip));
        $byNama = $karyawans->keyBy(fn ($k) => strtolower(trim((string) $k->nama)));

        $imported = 0;
        $skipped = 0;
        $unmatched = 0;

        for ($i = 1; $i < count($rawRows); $i++) {
            $r = $rawRows[$i];
            if (empty(array_filter($r, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                continue;
            }

            $pin = $colMap['pin'] !== null ? trim((string) ($r[$colMap['pin']] ?? '')) : '';
            $nip = $colMap['nip'] !== null ? trim((string) ($r[$colMap['nip']] ?? '')) : '';
            $nama = $colMap['nama'] !== null ? trim((string) ($r[$colMap['nama']] ?? '')) : '';

            $karyawan = null;
            if ($pin !== '' && $byPin->has($pin)) {
                $karyawan = $byPin->get($pin);
            } elseif ($nip !== '' && $byNip->has($nip)) {
                $karyawan = $byNip->get($nip);
            } elseif ($pin !== '' && $byNip->has($pin)) {
                $karyawan = $byNip->get($pin);
            } elseif ($nama !== '' && $byNama->has(strtolower($nama))) {
                $karyawan = $byNama->get(strtolower($nama));
            }

            if (! $karyawan) {
                $unmatched++;
                continue;
            }

            $scanDt = $this->parseScanDateTime($r, $colMap);
            if (! $scanDt) {
                $skipped++;
                continue;
            }

            $ioRaw = $colMap['io'] !== null ? strtolower(trim((string) ($r[$colMap['io']] ?? ''))) : '';
            $io = match (true) {
                in_array($ioRaw, ['keluar', 'out', 'c/out', 'check out', 'checkout', '1', 'pulang'], true) => 'Keluar',
                in_array($ioRaw, ['masuk', 'in', 'c/in', 'check in', 'checkin', '0', 'datang'], true)     => 'Masuk',
                default => $scanDt->hour >= 13 ? 'Keluar' : 'Masuk',
            };

            $exists = Absensi::where('karyawan_id', $karyawan->id)
                ->where('tanggal_scan', $scanDt->format('Y-m-d H:i:s'))
                ->where('io', $io)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Absensi::create([
                'karyawan_id'  => $karyawan->id,
                'nip'          => $karyawan->nip,
                'pin'          => $pin ?: ($karyawan->pin_finger ?: $karyawan->nip),
                'jabatan'      => $karyawan->jabatan,
                'departemen'   => $karyawan->departemen?->nama_departemen,
                'kantor'       => $karyawan->kantor,
                'tanggal_scan' => $scanDt->format('Y-m-d H:i:s'),
                'sn'           => $colMap['sn'] !== null ? trim((string) ($r[$colMap['sn']] ?? '')) : null,
                'mesin'        => $colMap['mesin'] !== null ? trim((string) ($r[$colMap['mesin']] ?? '')) : 'Import Fingerprint',
                'workcode'     => $colMap['workcode'] !== null ? trim((string) ($r[$colMap['workcode']] ?? '')) : null,
                'io'           => $io,
            ]);

            $imported++;
        }

        return [
            'imported'  => $imported,
            'skipped'   => $skipped,
            'unmatched' => $unmatched,
        ];
    }

    protected function findCol(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $cand) {
            foreach ($headers as $idx => $h) {
                if ($h === $cand || str_contains($h, $cand)) {
                    return $idx;
                }
            }
        }

        return null;
    }

    protected function parseScanDateTime(array $row, array $colMap): ?Carbon
    {
        $raw = $colMap['datetime'] !== null ? ($row[$colMap['datetime']] ?? null) : null;
        if ($raw === null || trim((string) $raw) === '') {
            $d = $colMap['date'] !== null ? ($row[$colMap['date']] ?? null) : null;
            $t = $colMap['time'] !== null ? ($row[$colMap['time']] ?? null) : null;
            if ($d === null || trim((string) $d) === '') {
                return null;
            }
            if (is_numeric($d)) {
                $dStr = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $d))->toDateString();
            } else {
                $dStr = trim((string) $d);
            }
            $raw = trim($dStr . ' ' . trim((string) ($t ?? '08:00:00')));
        } elseif (is_numeric($raw)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw));
        }

        try {
            return Carbon::parse(str_replace('/', '-', trim((string) $raw)));
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseTimeMinutes(string $hhmm): int
    {
        $parts = explode(':', trim($hhmm));

        return ((int) ($parts[0] ?? 8) * 60) + (int) ($parts[1] ?? 0);
    }
}
