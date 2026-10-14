<?php

namespace App\Services\HR;

use App\Models\BonusKaryawan;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\PengPinjaman;
use App\Models\PotTunjangan;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(
        protected TreasuryService $treasuryService
    ) {}

    /**
     * Generate Kode Slip Gaji: SLIP-Ym-0001
     */
    public function generateKodeSlip(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'SLIP-' . $date->format('Ym') . '-';

        $last = Gaji::where('kode_slip', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/-(\d+)$/', $last->kode_slip, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = Gaji::where('kode_slip', 'like', "{$prefix}%")->count();
            $nextNumber = $count + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Kalkulasi otomatis rincian payroll karyawan untuk periode tertentu
     */
    public function calculateEmployeePayroll(int $karyawanId, ?string $periode = null): array
    {
        $periode = $periode ?? now()->format('Y-m');

        /** @var Karyawan $karyawan */
        $karyawan = Karyawan::with('departemen')->findOrFail($karyawanId);

        $gajiPokok = (float) ($karyawan->gaji_pokok ?? 0);
        $tunjanganJabatan = (float) ($karyawan->tunjangan_jabatan ?? 0);
        $tunjanganKompetensi = (float) ($karyawan->tunjangan_kompetensi ?? 0);
        $tunjanganIntensif = (float) ($karyawan->tunjangan_intensif ?? 0);

        // 1. Kalkulasi Lembur (Sum durasi lembur pada periode)
        $durasiLembur = (float) Lembur::where('karyawan_id', $karyawanId)
            ->where('tanggal_lembur', 'like', "{$periode}%")
            ->sum('durasi_lembur');

        // Rate standar lembur: (1/173) * Gaji Pokok
        $ratePerJam = $gajiPokok > 0 ? round($gajiPokok / 173, 2) : 0.0;
        $totalLembur = round($durasiLembur * $ratePerJam, 2);

        // 2. Kalkulasi Bonus
        $totalBonus = (float) BonusKaryawan::where('karyawan_id', $karyawanId)
            ->where(function ($q) use ($periode) {
                $q->where('periode', $periode)
                    ->orWhere('tanggal_bonus', 'like', "{$periode}%");
            })
            ->sum('nilai_bonus');

        // 3. Kalkulasi Potongan Tunjangan
        $potonganTunjangan = (float) PotTunjangan::where('karyawan_id', $karyawanId)
            ->where('periode_payroll', 'like', "%{$periode}%")
            ->sum('total_potongan');

        // 4. Kalkulasi Cicilan Pinjaman Aktif
        $potonganPinjaman = 0.0;
        $pinjamans = PengPinjaman::where('karyawan_id', $karyawanId)
            ->with('pembayaran')
            ->get();

        foreach ($pinjamans as $pinjaman) {
            $totalTerbayar = (float) $pinjaman->pembayaran->sum('nominal_pembayaran');
            $sisa = (float) $pinjaman->nilai_pinjaman - $totalTerbayar;
            if ($sisa > 0) {
                $cicilan = min((float) $pinjaman->cicilan_per_bulan, $sisa);
                $potonganPinjaman += $cicilan;
            }
        }

        $totalPendapatan = round(
            $gajiPokok + $tunjanganJabatan + $tunjanganKompetensi + $tunjanganIntensif + $totalLembur + $totalBonus,
            2
        );

        $totalPotongan = round(
            $potonganTunjangan + $potonganPinjaman,
            2
        );

        $gajiBersih = max(0.0, round($totalPendapatan - $totalPotongan, 2));

        return [
            'karyawan_id'          => $karyawanId,
            'periode'              => $periode,
            'gaji_pokok'           => $gajiPokok,
            'tunjangan_jabatan'    => $tunjanganJabatan,
            'tunjangan_kompetensi' => $tunjanganKompetensi,
            'tunjangan_intensif'   => $tunjanganIntensif,
            'durasi_lembur'        => $durasiLembur,
            'rate_lembur_per_jam'  => $ratePerJam,
            'total_lembur'         => $totalLembur,
            'total_bonus'          => $totalBonus,
            'total_pendapatan'     => $totalPendapatan,
            'potongan_tunjangan'   => $potonganTunjangan,
            'potongan_kehadiran'   => 0.0,
            'potongan_pinjaman'    => $potonganPinjaman,
            'potongan_bpjs'        => 0.0,
            'total_potongan'       => $totalPotongan,
            'gaji_bersih'          => $gajiBersih,
        ];
    }

    /**
     * Hitung total pendapatan, total potongan, dan gaji bersih dari array data
     */
    public function calculateTotals(array $data): array
    {
        $totalPendapatan = round(
            (float) ($data['gaji_pokok'] ?? 0) +
            (float) ($data['tunjangan_jabatan'] ?? 0) +
            (float) ($data['tunjangan_kompetensi'] ?? 0) +
            (float) ($data['tunjangan_intensif'] ?? 0) +
            (float) ($data['total_lembur'] ?? 0) +
            (float) ($data['total_bonus'] ?? 0),
            2
        );

        $totalPotongan = round(
            (float) ($data['potongan_tunjangan'] ?? 0) +
            (float) ($data['potongan_kehadiran'] ?? 0) +
            (float) ($data['potongan_pinjaman'] ?? 0) +
            (float) ($data['potongan_bpjs'] ?? 0),
            2
        );

        $gajiBersih = max(0.0, round($totalPendapatan - $totalPotongan, 2));

        return [
            'total_pendapatan' => $totalPendapatan,
            'total_potongan'   => $totalPotongan,
            'gaji_bersih'      => $gajiBersih,
        ];
    }

    /**
     * Catat Slip Gaji / Payroll Baru
     */
    public function recordPayroll(array $data): Gaji
    {
        return DB::transaction(function () use ($data) {
            $karyawanId = $data['karyawan_id'] ?? null;
            if (!$karyawanId) {
                throw ValidationException::withMessages([
                    'karyawan_id' => 'Karyawan wajib dipilih.',
                ]);
            }

            $periode = $data['periode'] ?? now()->format('Y-m');

            // Cek pencegahan double payroll untuk karyawan dan periode yang sama
            $exists = Gaji::where('karyawan_id', $karyawanId)
                ->where('periode', $periode)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'periode' => "Karyawan ini sudah memiliki slip gaji untuk periode {$periode}.",
                ]);
            }

            if (empty($data['kode_slip']) || $data['kode_slip'] === 'AUTO') {
                $data['kode_slip'] = $this->generateKodeSlip(
                    isset($data['tanggal_pembayaran']) ? Carbon::parse($data['tanggal_pembayaran']) : null
                );
            }

            if (empty($data['status'])) {
                $data['status'] = 'Draft';
            }

            if (empty($data['tanggal_pembayaran'])) {
                $data['tanggal_pembayaran'] = now()->toDateString();
            }

            // Hitung ulang kalkulasi finansial
            $totals = $this->calculateTotals($data);
            $data['total_pendapatan'] = $totals['total_pendapatan'];
            $data['total_potongan']   = $totals['total_potongan'];
            $data['gaji_bersih']      = $totals['gaji_bersih'];

            /** @var Gaji $gaji */
            $gaji = Gaji::create($data);

            // Jika status langsung Paid, bukukan ke Treasury
            if ($gaji->status === 'Paid') {
                $this->treasuryService->postPayroll($gaji);
            }

            return $gaji;
        });
    }

    /**
     * Update Slip Gaji / Payroll
     */
    public function updatePayroll(Gaji $gaji, array $data): Gaji
    {
        return DB::transaction(function () use ($gaji, $data) {
            $gaji = Gaji::lockForUpdate()->findOrFail($gaji->id);

            // Validasi unik karyawan & periode jika diubah
            if (isset($data['periode']) && $data['periode'] !== $gaji->periode) {
                $exists = Gaji::where('karyawan_id', $data['karyawan_id'] ?? $gaji->karyawan_id)
                    ->where('periode', $data['periode'])
                    ->where('id', '!=', $gaji->id)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'periode' => "Karyawan ini sudah memiliki slip gaji untuk periode {$data['periode']}.",
                    ]);
                }
            }

            $mergedData = array_merge($gaji->toArray(), $data);
            $totals = $this->calculateTotals($mergedData);
            $data['total_pendapatan'] = $totals['total_pendapatan'];
            $data['total_potongan']   = $totals['total_potongan'];
            $data['gaji_bersih']      = $totals['gaji_bersih'];

            $gaji->update($data);

            // Sinkronkan mutasi kas/bank di Treasury
            $this->treasuryService->syncPayroll($gaji);

            return $gaji;
        });
    }

    /**
     * Hapus Slip Gaji
     */
    public function deletePayroll(Gaji $gaji): bool
    {
        return DB::transaction(function () use ($gaji) {
            // Hapus mutasi dari Treasury jika ada
            $this->treasuryService->deletePayroll($gaji);

            return (bool) $gaji->delete();
        });
    }

    /**
     * Bayar Slip Gaji (Ubah status ke Paid dan potong Kas/Bank)
     */
    public function payPayroll(Gaji $gaji, int $accountKasId, ?string $metode = null): Gaji
    {
        return DB::transaction(function () use ($gaji, $accountKasId, $metode) {
            $gaji = Gaji::lockForUpdate()->findOrFail($gaji->id);

            $gaji->update([
                'status'             => 'Paid',
                'account_kas_id'     => $accountKasId,
                'metode_pembayaran'  => $metode ?? $gaji->metode_pembayaran,
                'tanggal_pembayaran' => now()->toDateString(),
            ]);

            $this->treasuryService->syncPayroll($gaji);

            return $gaji;
        });
    }
}
