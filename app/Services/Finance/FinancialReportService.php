<?php

namespace App\Services\Finance;

use App\Models\CoaMovement;
use App\Models\MasterCOA;
use App\Models\MasterPrinting;
use App\Models\OperasionalPay;
use App\Models\Printing;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Hitung Laporan Laba Rugi (Income Statement) Multi-Step untuk rentang tanggal
     */
    public function getIncomeStatement(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->toDateString() : now()->startOfMonth()->toDateString();
        $end = $endDate ? Carbon::parse($endDate)->toDateString() : now()->endOfMonth()->toDateString();

        $coas = MasterCOA::orderBy('kode_akun')->get();

        $movementsByCoa = CoaMovement::query()
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->selectRaw('coa_id, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $pendapatanUsaha = [];
        $returDiskon = [];
        $hppItems = [];
        $bebanPenjualan = [];
        $bebanAdminUmum = [];
        $bebanTenagaKerja = [];
        $bebanPajakK3Lain = [];
        $pendapatanLain = [];
        $bebanNonOperasional = [];

        foreach ($coas as $coa) {
            if (strtoupper((string) $coa->posting) === 'NO' || (int) $coa->level <= 2) {
                // Tetap proses jika akun header ternyata memiliki mutasi langsung
                $hasDirect = $movementsByCoa->has($coa->id);
                if (! $hasDirect) {
                    continue;
                }
            }

            $code = trim((string) $coa->kode_akun);
            $codeInt = (int) preg_replace('/\D/', '', substr($code, 0, 4));
            if ($codeInt < 4000 || $codeInt >= 8000) {
                continue;
            }

            $mov = $movementsByCoa->get($coa->id);
            $debit = (float) ($mov->total_debit ?? 0);
            $kredit = (float) ($mov->total_kredit ?? 0);

            if (str_starts_with($code, '4')) {
                if (str_starts_with($code, '44')) {
                    // Retur & Diskon Penjualan (mengurangi pendapatan)
                    $nilai = $debit - $kredit;
                    if (abs($nilai) > 0.001) {
                        $returDiskon[] = [
                            'kode_akun' => $code,
                            'nama_akun' => $coa->nama_akun,
                            'nominal'   => $nilai,
                        ];
                    }
                } elseif (str_starts_with($code, '45')) {
                    // Pendapatan Lain-lain
                    $nilai = $kredit - $debit;
                    if (abs($nilai) > 0.001) {
                        $pendapatanLain[] = [
                            'kode_akun' => $code,
                            'nama_akun' => $coa->nama_akun,
                            'nominal'   => $nilai,
                        ];
                    }
                } else {
                    // Pendapatan Usaha Utama (41xx, 42xx, 43xx)
                    $nilai = $kredit - $debit;
                    if (abs($nilai) > 0.001) {
                        $pendapatanUsaha[] = [
                            'kode_akun' => $code,
                            'nama_akun' => $coa->nama_akun,
                            'nominal'   => $nilai,
                        ];
                    }
                }
            } elseif (str_starts_with($code, '5')) {
                $nilai = $debit - $kredit;
                if (abs($nilai) > 0.001) {
                    $hppItems[] = [
                        'kode_akun' => $code,
                        'nama_akun' => $coa->nama_akun,
                        'nominal'   => $nilai,
                    ];
                }
            } elseif (str_starts_with($code, '6')) {
                $nilai = $debit - $kredit;
                if (abs($nilai) > 0.001) {
                    $row = [
                        'kode_akun' => $code,
                        'nama_akun' => $coa->nama_akun,
                        'nominal'   => $nilai,
                    ];
                    if (str_starts_with($code, '61')) {
                        $bebanPenjualan[] = $row;
                    } elseif (str_starts_with($code, '62')) {
                        $bebanAdminUmum[] = $row;
                    } elseif (str_starts_with($code, '63')) {
                        $bebanTenagaKerja[] = $row;
                    } else {
                        $bebanPajakK3Lain[] = $row;
                    }
                }
            } elseif (str_starts_with($code, '7')) {
                $nilai = $debit - $kredit;
                if (abs($nilai) > 0.001) {
                    $bebanNonOperasional[] = [
                        'kode_akun' => $code,
                        'nama_akun' => $coa->nama_akun,
                        'nominal'   => $nilai,
                    ];
                }
            }
        }

        $totalPendapatanKotor = round(collect($pendapatanUsaha)->sum('nominal'), 2);
        $totalReturDiskon = round(collect($returDiskon)->sum('nominal'), 2);
        $totalPendapatanBersih = round($totalPendapatanKotor - $totalReturDiskon, 2);

        $totalHpp = round(collect($hppItems)->sum('nominal'), 2);
        $labaKotor = round($totalPendapatanBersih - $totalHpp, 2);

        $totalBebanPenjualan = round(collect($bebanPenjualan)->sum('nominal'), 2);
        $totalBebanAdminUmum = round(collect($bebanAdminUmum)->sum('nominal'), 2);
        $totalBebanTenagaKerja = round(collect($bebanTenagaKerja)->sum('nominal'), 2);
        $totalBebanPajakK3Lain = round(collect($bebanPajakK3Lain)->sum('nominal'), 2);

        $totalBebanOperasional = round(
            $totalBebanPenjualan + $totalBebanAdminUmum + $totalBebanTenagaKerja + $totalBebanPajakK3Lain,
            2
        );

        $labaOperasional = round($labaKotor - $totalBebanOperasional, 2);

        $totalPendapatanLain = round(collect($pendapatanLain)->sum('nominal'), 2);
        $totalBebanNonOperasional = round(collect($bebanNonOperasional)->sum('nominal'), 2);
        $netLainLain = round($totalPendapatanLain - $totalBebanNonOperasional, 2);

        $labaBersih = round($labaOperasional + $netLainLain, 2);
        $marginBersih = $totalPendapatanBersih > 0
            ? round(($labaBersih / $totalPendapatanBersih) * 100, 2)
            : 0.0;

        return [
            'start_date'               => $start,
            'end_date'                 => $end,
            'pendapatan_usaha'         => $pendapatanUsaha,
            'retur_diskon'             => $returDiskon,
            'total_pendapatan_kotor'   => $totalPendapatanKotor,
            'total_retur_diskon'       => $totalReturDiskon,
            'total_pendapatan_bersih'  => $totalPendapatanBersih,
            'hpp_items'                => $hppItems,
            'total_hpp'                => $totalHpp,
            'laba_kotor'               => $labaKotor,
            'beban_penjualan'          => $bebanPenjualan,
            'total_beban_penjualan'    => $totalBebanPenjualan,
            'beban_admin_umum'         => $bebanAdminUmum,
            'total_beban_admin_umum'   => $totalBebanAdminUmum,
            'beban_tenaga_kerja'       => $bebanTenagaKerja,
            'total_beban_tenaga_kerja' => $totalBebanTenagaKerja,
            'beban_pajak_k3_lain'      => $bebanPajakK3Lain,
            'total_beban_pajak_k3_lain'=> $totalBebanPajakK3Lain,
            'total_beban_operasional'  => $totalBebanOperasional,
            'laba_operasional'         => $labaOperasional,
            'pendapatan_lain'          => $pendapatanLain,
            'total_pendapatan_lain'    => $totalPendapatanLain,
            'beban_non_operasional'    => $bebanNonOperasional,
            'total_beban_non_op'       => $totalBebanNonOperasional,
            'net_lain_lain'            => $netLainLain,
            'laba_bersih'              => $labaBersih,
            'margin_bersih_persen'     => $marginBersih,
        ];
    }

    /**
     * Hitung Laporan Laba Rugi per Mesin Cetak (Adopsi RAnalisaAccounting / Laba Rugi Mesin Indigama)
     */
    public function getMachineProfitabilityReport(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->toDateString() : now()->startOfMonth()->toDateString();
        $end = $endDate ? Carbon::parse($endDate)->toDateString() : now()->endOfMonth()->toDateString();

        $machines = MasterPrinting::orderBy('id')->get();

        $printings = Printing::with([
            'spk.salesOrder.orderConfirmation.items',
            'spk.suratJalans.invoice',
            'mesin',
            'operator',
        ])
            ->whereDate('tanggal_entri', '>=', $start)
            ->whereDate('tanggal_entri', '<=', $end)
            ->get();

        $operasionalPays = OperasionalPay::query()
            ->where('status', 'ACTIVE')
            ->whereDate('tanggal_transaksi', '>=', $start)
            ->whereDate('tanggal_transaksi', '<=', $end)
            ->get();

        $rows = [];
        $grandPendapatan = 0.0;
        $grandBiayaBahan = 0.0;
        $grandBiayaOperasional = 0.0;
        $grandHasilBaik = 0;
        $grandHasilRusak = 0;
        $grandSpkCount = 0;

        foreach ($machines as $machine) {
            $machinePrintings = $printings->where('mesin_id', $machine->id);

            $spkIds = $machinePrintings->pluck('spk_id')->filter()->unique();
            $jumlahSpk = $spkIds->count();
            $hasilBaik = (int) $machinePrintings->sum('hasil_baik');
            $hasilRusak = (int) $machinePrintings->sum(fn ($p) => (int) ($p->hasil_rusak ?? 0) + (int) ($p->semi_waste ?? 0));

            $nilaiPendapatan = 0.0;
            $biayaBahan = 0.0;

            foreach ($machinePrintings as $p) {
                $qtyGood = (float) ($p->hasil_baik ?? 0);
                $qtyBad = (float) ($p->hasil_rusak ?? 0) + (float) ($p->semi_waste ?? 0);
                $so = $p->spk?->salesOrder;

                $matchedOcItem = null;
                if ($so?->orderConfirmation && $so->orderConfirmation->items->isNotEmpty()) {
                    $ket = (string) ($p->keterangan_spk ?? '');
                    if (preg_match('/#ITEM:(\d+)/', $ket, $m)) {
                        $matchedOcItem = $so->orderConfirmation->items->firstWhere('id', (int) $m[1]);
                    }
                    if (! $matchedOcItem) {
                        $matchedOcItem = $so->orderConfirmation->items->first(function ($it) use ($so, $ket) {
                            if ($ket !== '' && stripos($ket, (string) $it->item_name) !== false && stripos($ket, (string) $it->komponen) !== false) {
                                return true;
                            }
                            return ($so->itemable_id && (int) $it->finish_good_item_id === (int) $so->itemable_id)
                                || ($so->product_name && strcasecmp(trim((string) $it->item_name), trim((string) $so->product_name)) === 0);
                        }) ?? $so->orderConfirmation->items->first();
                    }
                }

                $hargaPcs = (float) ($matchedOcItem?->harga ?? $so?->harga_pcs ?? 0);
                $hppPcs = (float) ($matchedOcItem?->hpp_per_pcs ?? 0);
                if ($hppPcs <= 0 && $hargaPcs > 0) {
                    $hppPcs = round($hargaPcs * 0.85, 4); // Standar HPP 85% sesuai Bismillah MACRO.xlsm
                }

                $nilaiPendapatan += ($qtyGood * $hargaPcs);
                $biayaBahan += ($qtyGood * $hppPcs);
            }

            // Biaya operasional langsung yang mencantumkan nama mesin ini
            $biayaOp = (float) $operasionalPays->filter(function ($op) use ($machine) {
                if (empty($op->mesin)) {
                    return false;
                }
                return stripos($op->mesin, $machine->nama_mesin) !== false
                    || stripos($machine->nama_mesin, $op->mesin) !== false;
            })->sum('nominal');

            $labaKotorMesin = round($nilaiPendapatan - $biayaBahan - $biayaOp, 2);
            $margin = $nilaiPendapatan > 0 ? round(($labaKotorMesin / $nilaiPendapatan) * 100, 1) : 0.0;
            $totalOut = $hasilBaik + $hasilRusak;
            $wastePersen = $totalOut > 0 ? round(($hasilRusak / $totalOut) * 100, 1) : 0.0;

            $rows[] = [
                'mesin_id'          => $machine->id,
                'kode_mesin'        => $machine->kode_mesin ?? ('MSN-' . str_pad((string) $machine->id, 2, '0', STR_PAD_LEFT)),
                'nama_mesin'        => $machine->nama_mesin,
                'jenis_mesin'       => $machine->jenis_mesin ?? '-',
                'proses'            => $machine->proses ?? '-',
                'jumlah_spk'        => $jumlahSpk,
                'hasil_baik'        => $hasilBaik,
                'hasil_rusak'       => $hasilRusak,
                'waste_persen'      => $wastePersen,
                'pendapatan'        => round($nilaiPendapatan, 2),
                'biaya_bahan'       => round($biayaBahan, 2),
                'biaya_operasional' => round($biayaOp, 2),
                'total_biaya'       => round($biayaBahan + $biayaOp, 2),
                'laba_mesin'        => $labaKotorMesin,
                'margin_persen'     => $margin,
            ];

            $grandPendapatan += $nilaiPendapatan;
            $grandBiayaBahan += $biayaBahan;
            $grandBiayaOperasional += $biayaOp;
            $grandHasilBaik += $hasilBaik;
            $grandHasilRusak += $hasilRusak;
            $grandSpkCount += $jumlahSpk;
        }

        $grandTotalBiaya = round($grandBiayaBahan + $grandBiayaOperasional, 2);
        $grandLaba = round($grandPendapatan - $grandTotalBiaya, 2);
        $grandMargin = $grandPendapatan > 0 ? round(($grandLaba / $grandPendapatan) * 100, 1) : 0.0;

        return [
            'start_date'              => $start,
            'end_date'                => $end,
            'machines'                => $rows,
            'grand_spk_count'         => $grandSpkCount,
            'grand_hasil_baik'        => $grandHasilBaik,
            'grand_hasil_rusak'       => $grandHasilRusak,
            'grand_pendapatan'        => round($grandPendapatan, 2),
            'grand_biaya_bahan'       => round($grandBiayaBahan, 2),
            'grand_biaya_operasional' => round($grandBiayaOperasional, 2),
            'grand_total_biaya'       => $grandTotalBiaya,
            'grand_laba'              => $grandLaba,
            'grand_margin_persen'     => $grandMargin,
        ];
    }

    /**
     * Hitung Laporan Neraca Keuangan (Balance Sheet) per Tanggal Akhir Periode
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $end = $asOfDate ? Carbon::parse($asOfDate)->toDateString() : now()->toDateString();

        $coas = MasterCOA::orderBy('kode_akun')->get();

        $movementsByCoa = CoaMovement::query()
            ->whereDate('tanggal', '<=', $end)
            ->selectRaw('coa_id, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $asetLancar = [];
        $asetTetap = [];
        $asetLainnya = [];

        $kewajibanLancar = [];
        $kewajibanJangkaPanjang = [];
        $ekuitasItems = [];

        $labaBerjalanPendapatan = 0.0;
        $labaBerjalanBeban = 0.0;
        $totalOpeningDebitAll = 0.0;
        $totalOpeningKreditAll = 0.0;

        foreach ($coas as $coa) {
            $code = trim((string) $coa->kode_akun);
            $mov = $movementsByCoa->get($coa->id);

            $openDebit = (float) ($coa->saldo_debit ?? 0);
            $openKredit = (float) ($coa->saldo_kredit ?? 0);
            $mutDebit = (float) ($mov->total_debit ?? 0);
            $mutKredit = (float) ($mov->total_kredit ?? 0);

            $totalOpeningDebitAll += $openDebit;
            $totalOpeningKreditAll += $openKredit;

            $totDebit = $openDebit + $mutDebit;
            $totKredit = $openKredit + $mutKredit;

            if (str_starts_with($code, '1') || str_starts_with($code, '8')) {
                // Kelompok Aset & Penyesuaian Neraca: saldo bersih normal di sisi Debit
                $saldo = round($totDebit - $totKredit, 2);
                if (abs($saldo) < 0.001) {
                    continue;
                }
                $row = [
                    'kode_akun' => $code,
                    'nama_akun' => $coa->nama_akun,
                    'nominal'   => $saldo,
                ];
                if (str_starts_with($code, '11') || str_starts_with($code, '10')) {
                    $asetLancar[] = $row;
                } elseif (str_starts_with($code, '12')) {
                    $asetTetap[] = $row;
                } else {
                    $asetLainnya[] = $row;
                }
            } elseif (str_starts_with($code, '2')) {
                // Kelompok Kewajiban: saldo bersih normal di sisi Kredit
                $saldo = round($totKredit - $totDebit, 2);
                if (abs($saldo) < 0.001) {
                    continue;
                }
                $row = [
                    'kode_akun' => $code,
                    'nama_akun' => $coa->nama_akun,
                    'nominal'   => $saldo,
                ];
                if (str_starts_with($code, '21')) {
                    $kewajibanLancar[] = $row;
                } else {
                    $kewajibanJangkaPanjang[] = $row;
                }
            } elseif (str_starts_with($code, '3')) {
                // Kelompok Modal / Ekuitas: saldo bersih normal di sisi Kredit (Prive mengurangi modal)
                $saldo = round($totKredit - $totDebit, 2);
                if (abs($saldo) < 0.001) {
                    continue;
                }
                $ekuitasItems[] = [
                    'kode_akun' => $code,
                    'nama_akun' => $coa->nama_akun,
                    'nominal'   => $saldo,
                ];
            } elseif (str_starts_with($code, '4')) {
                $labaBerjalanPendapatan += ($totKredit - $totDebit);
            } elseif (str_starts_with($code, '5') || str_starts_with($code, '6') || str_starts_with($code, '7')) {
                $labaBerjalanBeban += ($totDebit - $totKredit);
            }
        }

        // Penyeimbang Saldo Awal Otomatis (Akun 3400 - Ekuitas Saldo Awal) bila saldo awal master_coas belum seimbang
        $selisihSaldoAwal = round($totalOpeningDebitAll - $totalOpeningKreditAll, 2);
        if (abs($selisihSaldoAwal) >= 0.01) {
            $ekuitasItems[] = [
                'kode_akun' => '3400',
                'nama_akun' => 'Ekuitas Saldo Awal (Penyeimbang Saldo Awal COA)',
                'nominal'   => $selisihSaldoAwal,
            ];
        }

        $labaTahunBerjalan = round($labaBerjalanPendapatan - $labaBerjalanBeban, 2);

        if (abs($labaTahunBerjalan) >= 0.01) {
            $ekuitasItems[] = [
                'kode_akun' => '3300-AUTO',
                'nama_akun' => 'Laba / (Rugi) Bersih Periode Berjalan',
                'nominal'   => $labaTahunBerjalan,
            ];
        }

        $totalAsetLancar = round(collect($asetLancar)->sum('nominal'), 2);
        $totalAsetTetap = round(collect($asetTetap)->sum('nominal'), 2);
        $totalAsetLainnya = round(collect($asetLainnya)->sum('nominal'), 2);
        $totalAktiva = round($totalAsetLancar + $totalAsetTetap + $totalAsetLainnya, 2);

        $totalKewajibanLancar = round(collect($kewajibanLancar)->sum('nominal'), 2);
        $totalKewajibanJangkaPanjang = round(collect($kewajibanJangkaPanjang)->sum('nominal'), 2);
        $totalKewajiban = round($totalKewajibanLancar + $totalKewajibanJangkaPanjang, 2);

        $totalEkuitas = round(collect($ekuitasItems)->sum('nominal'), 2);
        $totalPasiva = round($totalKewajiban + $totalEkuitas, 2);

        $selisih = round($totalAktiva - $totalPasiva, 2);
        $isBalanced = abs($selisih) < 1.0;

        return [
            'as_of_date'                     => $end,
            'aset_lancar'                    => $asetLancar,
            'total_aset_lancar'              => $totalAsetLancar,
            'aset_tetap'                     => $asetTetap,
            'total_aset_tetap'               => $totalAsetTetap,
            'aset_lainnya'                   => $asetLainnya,
            'total_aset_lainnya'             => $totalAsetLainnya,
            'total_aktiva'                   => $totalAktiva,
            'kewajiban_lancar'               => $kewajibanLancar,
            'total_kewajiban_lancar'         => $totalKewajibanLancar,
            'kewajiban_jangka_panjang'       => $kewajibanJangkaPanjang,
            'total_kewajiban_jangka_panjang' => $totalKewajibanJangkaPanjang,
            'total_kewajiban'                => $totalKewajiban,
            'ekuitas_items'                  => $ekuitasItems,
            'laba_tahun_berjalan'            => $labaTahunBerjalan,
            'laba_periode_berjalan'          => $labaTahunBerjalan,
            'total_ekuitas'                  => $totalEkuitas,
            'total_pasiva'                   => $totalPasiva,
            'selisih'                        => $selisih,
            'is_balanced'                    => $isBalanced,
        ];
    }
}

