<?php

namespace App\Services\HR;

use App\Models\BonusKaryawan;
use App\Models\Gaji;
use App\Models\HariLibur;
use App\Models\HrPayrollSetting;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\PayrollBorongan;
use App\Models\PembPinjaman;
use App\Models\PengPinjaman;
use App\Models\PotTunjangan;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollService
{
    public function __construct(
        protected TreasuryService $treasuryService,
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Generate Kode Slip Gaji: SLIP-Ym-0001 (kebal duplikat).
     */
    public function generateKodeSlip(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'SLIP-' . $date->format('Ym') . '-';

        $last = Gaji::where('kode_slip', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('kode_slip');

        $nextNumber = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        while (Gaji::where('kode_slip', $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT))->exists()) {
            $nextNumber++;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Kalkulasi otomatis rincian payroll karyawan (Bulanan / Harian / Freelance) untuk periode tertentu.
     */
    public function calculateEmployeePayroll(
        int $karyawanId,
        ?string $periode = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null
    ): array {
        $periode = $periode ?? now()->format('Y-m');
        $setting = HrPayrollSetting::current();

        /** @var Karyawan $karyawan */
        $karyawan = Karyawan::with('departemen')->findOrFail($karyawanId);

        if ($tanggalMulai && $tanggalSelesai) {
            $start = Carbon::parse($tanggalMulai)->startOfDay();
            $end = Carbon::parse($tanggalSelesai)->endOfDay();
        } else {
            try {
                $dt = Carbon::createFromFormat('Y-m', $periode);
                $range = $this->attendanceService->getPeriodDateRange($dt->year, $dt->month);
                $start = $range['start'];
                $end = $range['end'];
            } catch (\Throwable) {
                $range = $this->attendanceService->getPeriodDateRange(now()->year, now()->month);
                $start = $range['start'];
                $end = $range['end'];
            }
        }

        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        // 0. Statistik Kehadiran (dari Absensi + Izin + Cuti)
        $att = $this->attendanceService->getEmployeeAttendanceStats($karyawanId, $start, $end);
        $tipePayroll = $karyawan->tipe_gaji ?: 'Bulanan';
        $baseGaji = (float) ($karyawan->gaji_pokok ?? 0);

        // Untuk Harian / Freelance: jika ada log absensi hadir, kalikan tarif harian × hari hadir; jika belum ada scan, tampilkan tarif dasar agar HR bisa sesuaikan jumlah hari
        if (in_array($tipePayroll, ['Harian', 'Freelance'], true) && $att['hadir'] > 0) {
            $gajiPokok = round($baseGaji * $att['hadir'], 2);
            $tunjMakan = round((float) ($karyawan->tunjangan_makan ?? 0) * $att['hadir'], 2);
            $tunjHadir = round((float) ($karyawan->tunjangan_hadir ?? 0) * $att['hadir'], 2);
        } else {
            $gajiPokok = $baseGaji;
            $tunjMakan = (float) ($karyawan->tunjangan_makan ?? 0);
            $tunjHadir = (float) ($karyawan->tunjangan_hadir ?? 0);
        }

        $tunjanganJabatan = (float) ($karyawan->tunjangan_jabatan ?? 0);
        $tunjanganKompetensi = (float) ($karyawan->tunjangan_kompetensi ?? 0);
        $tunjanganIntensif = (float) ($karyawan->tunjangan_intensif ?? 0);
        $tunjanganLain = (float) ($karyawan->tunjangan_lain ?? 0);

        // 1. Kalkulasi Lembur (hanya yang tidak Rejected, bedakan hari kerja vs hari libur)
        $holidays = HariLibur::whereBetween('tanggal_libur', [$startStr, $endStr])
            ->pluck('tanggal_libur')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->flip();

        $lemburRows = Lembur::where('karyawan_id', $karyawanId)
            ->whereBetween('tanggal_lembur', [$startStr, $endStr])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->get();

        if ((float) ($karyawan->tarif_lembur_custom ?? 0) > 0) {
            $ratePerJam = (float) $karyawan->tarif_lembur_custom;
        } elseif ($setting->metode_lembur === 'TARIF_TETAP') {
            $ratePerJam = (float) ($setting->tarif_lembur_tetap_per_jam ?: 15000);
        } else {
            $ratePerJam = $baseGaji > 0 ? round($baseGaji / 173, 2) : 0.0;
        }

        $durasiLembur = 0.0;
        $totalLembur = 0.0;
        $uangMakanLemburRate = (float) ($setting->uang_makan_lembur_nominal ?? 15000);
        $minJamUangMakan = (float) ($setting->min_jam_uang_makan_lembur ?? 2.0);
        $totalUangMakanLembur = 0.0;

        foreach ($lemburRows as $lmb) {
            $jam = (float) ($lmb->durasi_lembur ?? 0);
            $durasiLembur += $jam;
            $tgl = $lmb->tanggal_lembur ? Carbon::parse($lmb->tanggal_lembur) : null;
            $isHoliday = $tgl && ($tgl->isSunday() || $holidays->has($tgl->toDateString()));
            $mult = $isHoliday
                ? (float) ($setting->pengali_lembur_hari_libur ?: 2.0)
                : (float) ($setting->pengali_lembur_hari_kerja ?: 1.0);
            $totalLembur += ($jam * $ratePerJam * $mult);

            // Uang Makan Lembur otomatis bila durasi lembur > 2 jam (sesuai sheet Lembur TTC)
            if ($uangMakanLemburRate > 0 && $jam > $minJamUangMakan) {
                $totalUangMakanLembur += $uangMakanLemburRate;
            }
        }
        $totalLembur = round($totalLembur + $totalUangMakanLembur, 2);

        // 2. Kalkulasi Bonus
        $totalBonus = (float) BonusKaryawan::where('karyawan_id', $karyawanId)
            ->where(function ($q) use ($periode, $startStr, $endStr) {
                $q->where('periode', $periode)
                    ->orWhereBetween('tanggal_bonus', [$startStr, $endStr]);
            })
            ->sum('nilai_bonus');

        // 3. Kalkulasi Potongan Tunjangan
        $potonganTunjangan = (float) PotTunjangan::where('karyawan_id', $karyawanId)
            ->where('periode_payroll', 'like', "%{$periode}%")
            ->sum('total_potongan');

        // 4. Kalkulasi Potongan Kehadiran (Alpha + Scorsing + Sakit Tanpa Surat + Terlambat Flat / Per Jam)
        $potAlphaPerHari = (float) ($setting->potongan_alpha_per_hari ?? 0) > 0
            ? (float) $setting->potongan_alpha_per_hari
            : ($tipePayroll === 'Bulanan' && $baseGaji > 0 ? round($baseGaji / 25, 2) : 0.0);

        $hariPotongPenuh = (int) ($att['alpha'] ?? 0) + (int) ($att['scorsing'] ?? 0);
        if (! empty($setting->potongan_sakit_tanpa_surat)) {
            $hariPotongPenuh += (int) ($att['sakit_tanpa_surat'] ?? 0);
        }

        if (($setting->metode_potongan_telat ?? 'FLAT') === 'PER_JAM' && $baseGaji > 0 && ($att['telat_menit'] ?? 0) > 0) {
            $potTelatTotal = round((($att['telat_menit'] / 60) * ($baseGaji / 173)), 2);
        } else {
            $potTelatPerKali = (float) ($setting->potongan_telat_per_kali ?? 0);
            $potTelatTotal = round(($att['terlambat'] ?? 0) * $potTelatPerKali, 2);
        }

        $potonganKehadiran = round(($hariPotongPenuh * $potAlphaPerHari) + $potTelatTotal, 2);

        // 5. Kalkulasi Cicilan Pinjaman Aktif (hanya pinjaman tidak Rejected yang masih punya sisa)
        $potonganPinjaman = 0.0;
        $pinjamans = PengPinjaman::where('karyawan_id', $karyawanId)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
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
        $potonganPinjaman = round($potonganPinjaman, 2);

        // 6. Kalkulasi Potongan BPJS (Karyawan) & Kontribusi BPJS Perusahaan (3,7%)
        $bpjsManual = round((float) ($karyawan->bpjs_kesehatan_nominal ?? 0) + (float) ($karyawan->bpjs_ketenagakerjaan_nominal ?? 0), 2);
        if ($bpjsManual > 0) {
            $potonganBpjs = $bpjsManual;
        } elseif ($setting->auto_hitung_bpjs && $baseGaji > 0) {
            $pct = (float) $setting->persen_bpjs_kesehatan + (float) $setting->persen_bpjs_ketenagakerjaan;
            $potonganBpjs = round($baseGaji * ($pct / 100), 2);
        } else {
            $potonganBpjs = 0.0;
        }

        $pctBpjsPt = (float) ($setting->persen_bpjs_tk_perusahaan ?? 3.70);
        $bpjsPerusahaan = ($potonganBpjs > 0 && $baseGaji > 0)
            ? round($baseGaji * ($pctBpjsPt / 100), 2)
            : 0.0;

        $totalPendapatan = round(
            $gajiPokok + $tunjanganJabatan + $tunjanganKompetensi + $tunjanganIntensif
            + $tunjMakan + $tunjHadir + $tunjanganLain + $totalLembur + $totalBonus,
            2
        );

        $totalPotongan = round(
            $potonganTunjangan + $potonganKehadiran + $potonganPinjaman + $potonganBpjs,
            2
        );

        $gajiBersih = max(0.0, round($totalPendapatan - $totalPotongan, 2));

        return [
            'karyawan_id'          => $karyawanId,
            'periode'              => $periode,
            'tipe_payroll'         => $tipePayroll,
            'tanggal_mulai'        => $startStr,
            'tanggal_selesai'      => $endStr,
            'jumlah_hari_hadir'    => $att['hadir'],
            'jumlah_alpha'         => ($att['alpha'] ?? 0) + ($att['scorsing'] ?? 0),
            'jumlah_terlambat'     => $att['terlambat'],
            'gaji_pokok'           => $gajiPokok,
            'tunjangan_jabatan'    => $tunjanganJabatan,
            'tunjangan_kompetensi' => $tunjanganKompetensi,
            'tunjangan_intensif'   => $tunjanganIntensif,
            'tunjangan_makan'      => $tunjMakan,
            'tunjangan_hadir'      => $tunjHadir,
            'tunjangan_lain'       => $tunjanganLain,
            'durasi_lembur'        => round($durasiLembur, 2),
            'rate_lembur_per_jam'  => $ratePerJam,
            'total_lembur'         => $totalLembur,
            'total_bonus'          => $totalBonus,
            'total_pendapatan'     => $totalPendapatan,
            'potongan_tunjangan'   => $potonganTunjangan,
            'potongan_kehadiran'   => $potonganKehadiran,
            'potongan_pinjaman'    => $potonganPinjaman,
            'potongan_bpjs'        => $potonganBpjs,
            'bpjs_perusahaan'      => $bpjsPerusahaan,
            'potongan_pph21'       => 0.0,
            'potongan_lain'        => 0.0,
            'total_potongan'       => $totalPotongan,
            'gaji_bersih'          => $gajiBersih,
        ];
    }

    /**
     * Hitung total pendapatan, total potongan, dan gaji bersih dari array data.
     */
    public function calculateTotals(array $data): array
    {
        $totalPendapatan = round(
            (float) ($data['gaji_pokok'] ?? 0) +
            (float) ($data['tunjangan_jabatan'] ?? 0) +
            (float) ($data['tunjangan_kompetensi'] ?? 0) +
            (float) ($data['tunjangan_intensif'] ?? 0) +
            (float) ($data['tunjangan_makan'] ?? 0) +
            (float) ($data['tunjangan_hadir'] ?? 0) +
            (float) ($data['tunjangan_lain'] ?? 0) +
            (float) ($data['total_lembur'] ?? 0) +
            (float) ($data['total_bonus'] ?? 0),
            2
        );

        $totalPotongan = round(
            (float) ($data['potongan_tunjangan'] ?? 0) +
            (float) ($data['potongan_kehadiran'] ?? 0) +
            (float) ($data['potongan_pinjaman'] ?? 0) +
            (float) ($data['potongan_bpjs'] ?? 0) +
            (float) ($data['potongan_pph21'] ?? 0) +
            (float) ($data['potongan_lain'] ?? 0),
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
     * Sinkronisasi otomatis cicilan potongan pinjaman gaji ke tabel pemb_pinjamans (idempotent & bisa rollback).
     */
    public function syncLoanDeduction(Gaji $gaji): void
    {
        // Hapus cicilan otomatis lama milik slip gaji ini agar tidak dobel
        PembPinjaman::where('gaji_id', $gaji->id)->delete();

        $potPinjaman = round((float) ($gaji->potongan_pinjaman ?? 0), 2);
        if ($gaji->status !== 'Paid' || $potPinjaman <= 0) {
            if ($gaji->is_pinjaman_synced) {
                $gaji->updateQuietly(['is_pinjaman_synced' => false]);
            }
            return;
        }

        $remainingToAllocate = $potPinjaman;
        $pinjamans = PengPinjaman::where('karyawan_id', $gaji->karyawan_id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->with('pembayaran')
            ->orderBy('tanggal_pengajuan')
            ->orderBy('id')
            ->get();

        $createdAny = false;
        foreach ($pinjamans as $pinjaman) {
            if ($remainingToAllocate <= 0.01) {
                break;
            }
            $sudahBayar = (float) $pinjaman->pembayaran->sum('nominal_pembayaran');
            $sisa = max(0.0, round((float) $pinjaman->nilai_pinjaman - $sudahBayar, 2));
            if ($sisa <= 0.01) {
                continue;
            }

            $bayar = round(min($remainingToAllocate, $sisa), 2);
            $nextTahap = ((int) $pinjaman->pembayaran->max('tahap_cicilan')) + 1;

            PembPinjaman::create([
                'peng_pinjaman_id'   => $pinjaman->id,
                'karyawan_id'        => $gaji->karyawan_id,
                'gaji_id'            => $gaji->id,
                'tahap_cicilan'      => $nextTahap,
                'tanggal_pembayaran' => $gaji->tanggal_pembayaran ?: now()->toDateString(),
                'nominal_pembayaran' => $bayar,
                'keterangan'         => "Potong Gaji Otomatis {$gaji->kode_slip} (Periode {$gaji->periode})",
            ]);

            $remainingToAllocate = round($remainingToAllocate - $bayar, 2);
            $createdAny = true;
        }

        $gaji->updateQuietly(['is_pinjaman_synced' => $createdAny]);
    }

    /**
     * Catat Slip Gaji / Payroll Baru.
     */
    public function recordPayroll(array $data): Gaji
    {
        return DB::transaction(function () use ($data) {
            $karyawanId = $data['karyawan_id'] ?? null;
            if (! $karyawanId) {
                throw ValidationException::withMessages([
                    'karyawan_id' => 'Karyawan wajib dipilih.',
                ]);
            }

            $periode = $data['periode'] ?? now()->format('Y-m');
            $tipe = $data['tipe_payroll'] ?? 'Bulanan';

            // Cegah double payroll bulanan untuk karyawan + periode sama (untuk Harian/Freelance mingguan diperbolehkan jika tanggal berbeda)
            if ($tipe === 'Bulanan') {
                $exists = Gaji::where('karyawan_id', $karyawanId)
                    ->where('periode', $periode)
                    ->where('tipe_payroll', 'Bulanan')
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'periode' => "Karyawan ini sudah memiliki slip gaji Bulanan untuk periode {$periode}.",
                    ]);
                }
            }

            if (empty($data['kode_slip']) || strtoupper(trim((string) $data['kode_slip'])) === 'AUTO') {
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

            if (! array_key_exists('post_to_ledger', $data)) {
                $data['post_to_ledger'] = false;
            }

            if ($data['status'] === 'Paid' && ! empty($data['post_to_ledger']) && empty($data['account_kas_id'])) {
                throw ValidationException::withMessages([
                    'account_kas_id' => 'Akun Kas/Bank Pembayar wajib dipilih bila status Paid dan Auto-Posting Buku Besar aktif.',
                ]);
            }

            $totals = $this->calculateTotals($data);
            $data['total_pendapatan'] = $totals['total_pendapatan'];
            $data['total_potongan'] = $totals['total_potongan'];
            $data['gaji_bersih'] = $totals['gaji_bersih'];

            /** @var Gaji $gaji */
            $gaji = Gaji::create($data);

            $this->syncLoanDeduction($gaji);

            if ($gaji->status === 'Paid' && $gaji->post_to_ledger) {
                $this->treasuryService->postPayroll($gaji);
            }

            return $gaji;
        });
    }

    /**
     * Update Slip Gaji / Payroll.
     */
    public function updatePayroll(Gaji $gaji, array $data): Gaji
    {
        return DB::transaction(function () use ($gaji, $data) {
            $gaji = Gaji::lockForUpdate()->findOrFail($gaji->id);

            $mergedData = array_merge($gaji->toArray(), $data);
            $tipe = $mergedData['tipe_payroll'] ?? 'Bulanan';

            if ($tipe === 'Bulanan' && isset($data['periode']) && $data['periode'] !== $gaji->periode) {
                $exists = Gaji::where('karyawan_id', $mergedData['karyawan_id'])
                    ->where('periode', $data['periode'])
                    ->where('tipe_payroll', 'Bulanan')
                    ->where('id', '!=', $gaji->id)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'periode' => "Karyawan ini sudah memiliki slip gaji Bulanan untuk periode {$data['periode']}.",
                    ]);
                }
            }

            if (($mergedData['status'] ?? 'Draft') === 'Paid' && ! empty($mergedData['post_to_ledger']) && empty($mergedData['account_kas_id'])) {
                throw ValidationException::withMessages([
                    'account_kas_id' => 'Akun Kas/Bank Pembayar wajib dipilih bila status Paid dan Auto-Posting Buku Besar aktif.',
                ]);
            }

            $totals = $this->calculateTotals($mergedData);
            $data['total_pendapatan'] = $totals['total_pendapatan'];
            $data['total_potongan'] = $totals['total_potongan'];
            $data['gaji_bersih'] = $totals['gaji_bersih'];

            $gaji->update($data);
            $gaji->refresh();

            $this->syncLoanDeduction($gaji);
            $this->treasuryService->syncPayroll($gaji);

            return $gaji;
        });
    }

    /**
     * Hapus Slip Gaji (otomatis rollback cicilan pinjaman & mutasi GL).
     */
    public function deletePayroll(Gaji $gaji): bool
    {
        return DB::transaction(function () use ($gaji) {
            PembPinjaman::where('gaji_id', $gaji->id)->delete();
            $this->treasuryService->deletePayroll($gaji);

            return (bool) $gaji->delete();
        });
    }

    /**
     * Bayar Slip Gaji (ubah ke Paid, potong cicilan pinjaman, opsional posting Kas/Bank).
     */
    public function payPayroll(Gaji $gaji, ?int $accountKasId = null, ?string $metode = null, bool $postToLedger = false): Gaji
    {
        return DB::transaction(function () use ($gaji, $accountKasId, $metode, $postToLedger) {
            $gaji = Gaji::lockForUpdate()->findOrFail($gaji->id);

            $gaji->update([
                'status'             => 'Paid',
                'account_kas_id'     => $accountKasId ?? $gaji->account_kas_id,
                'metode_pembayaran'  => $metode ?? $gaji->metode_pembayaran,
                'tanggal_pembayaran' => now()->toDateString(),
                'post_to_ledger'     => $postToLedger,
            ]);

            $gaji->refresh();
            $this->syncLoanDeduction($gaji);
            $this->treasuryService->syncPayroll($gaji);

            return $gaji;
        });
    }

    /**
     * Hitung ulang total batch Payroll Borongan.
     */
    public function recalculateBorongan(PayrollBorongan $pb): PayrollBorongan
    {
        $pb->load('items');

        $totGaji = 0.0;
        $totTunj = 0.0;
        $totPot = 0.0;
        $grand = 0.0;

        foreach ($pb->items as $it) {
            $g = round((float) $it->protol_gaji + (float) $it->packing_gaji + (float) $it->lain_gaji, 2);
            $t = round((float) $it->tunj_hadir + (float) $it->tunj_makan + (float) $it->tunj_lain, 2);
            $p = round((float) $it->potongan, 2);
            $rowTot = max(0.0, round($g + $t - $p, 2));

            if (abs((float) $it->total - $rowTot) >= 0.01) {
                $it->updateQuietly(['total' => $rowTot]);
            }

            $totGaji += $g;
            $totTunj += $t;
            $totPot += $p;
            $grand += $rowTot;
        }

        $pb->updateQuietly([
            'total_gaji_borongan' => round($totGaji, 2),
            'total_tunjangan'     => round($totTunj, 2),
            'total_potongan'      => round($totPot, 2),
            'grand_total'         => round($grand, 2),
        ]);

        return $pb->refresh();
    }

    public function payBorongan(PayrollBorongan $pb, ?int $accountKasId = null, string $metode = 'Tunai', bool $postToLedger = false): PayrollBorongan
    {
        return DB::transaction(function () use ($pb, $accountKasId, $metode, $postToLedger) {
            $pb = PayrollBorongan::lockForUpdate()->findOrFail($pb->id);
            $this->recalculateBorongan($pb);

            $pb->update([
                'status'            => 'Paid',
                'account_kas_id'    => $accountKasId ?? $pb->account_kas_id,
                'metode_pembayaran' => $metode,
                'post_to_ledger'    => $postToLedger,
            ]);

            $this->treasuryService->syncPayrollBorongan($pb->refresh());

            return $pb;
        });
    }

    public function deleteBorongan(PayrollBorongan $pb): bool
    {
        return DB::transaction(function () use ($pb) {
            $this->treasuryService->deletePayrollBorongan($pb);

            return (bool) $pb->delete();
        });
    }
}
