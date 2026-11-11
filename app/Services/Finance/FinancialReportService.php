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
     * Hitung Laporan Laba Rugi per Unit Mesin Produksi
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

    /**
     * Hitung Data Buku Besar & Jurnal Umum (General Ledger) untuk PDF dan Export
     */
    public function getGeneralLedgerReport(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $coaId = null,
        ?string $refType = null
    ): array {
        $start = $startDate ?: '2026-01-01';
        $end = $endDate ?: '2026-12-31';

        $allMovementsInPeriod = CoaMovement::with(['coa', 'user'])
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningByCoa = [];
        foreach ($allMovementsInPeriod->groupBy('coa_id') as $cId => $group) {
            $coa = $group->first()?->coa;
            $code = trim((string) ($coa?->kode_akun ?? ''));
            $isCreditNormal = str_starts_with($code, '2') || str_starts_with($code, '3') || str_starts_with($code, '4');

            $openDebit = (float) ($coa?->saldo_debit ?? 0);
            $openKredit = (float) ($coa?->saldo_kredit ?? 0);

            $prior = CoaMovement::where('coa_id', $cId)
                ->whereDate('tanggal', '<', $start)
                ->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(kredit),0) as k')
                ->first();

            $running = $isCreditNormal
                ? (($openKredit - $openDebit) + ((float) ($prior->k ?? 0) - (float) ($prior->d ?? 0)))
                : (($openDebit - $openKredit) + ((float) ($prior->d ?? 0) - (float) ($prior->k ?? 0)));

            foreach ($group as $m) {
                $d = (float) $m->debit;
                $k = (float) $m->kredit;
                $running += $isCreditNormal ? ($k - $d) : ($d - $k);
                $runningByCoa[$m->id] = round($running, 2);
            }
        }

        $filtered = $allMovementsInPeriod->filter(function (CoaMovement $m) use ($coaId, $refType) {
            if ($coaId !== null && $coaId !== '' && (string) $m->coa_id !== (string) $coaId) {
                return false;
            }
            if ($refType !== null && $refType !== '' && (string) $m->reference_type !== (string) $refType) {
                return false;
            }
            return true;
        })->values();

        $rows = [];
        foreach ($filtered as $m) {
            $code = trim((string) ($m->coa?->kode_akun ?? '-'));
            $refMeta = match ($m->reference_type) {
                'INVOICE'          => ['label' => 'Invoice Penjualan'],
                'INV_PAYMENT'      => ['label' => 'Pembayaran Bon/Invoice'],
                'OPERASIONAL_PAY'  => ['label' => 'Operasional / Komisi'],
                'JOURNAL_MEMORIAL' => ['label' => 'Jurnal Memorial / HPP'],
                'TRANS_KAS'        => ['label' => 'Kas Masuk/Keluar'],
                'TRANS_BANK'       => ['label' => 'Bank Masuk/Keluar'],
                'PO_BILLING'       => ['label' => 'Tagihan PO'],
                'PO_BILL_PAY'      => ['label' => 'Pembayaran PO'],
                default            => ['label' => $m->reference_type ?: '-'],
            };

            $rows[] = [
                'id'              => $m->id,
                'tanggal'         => Carbon::parse($m->tanggal)->format('d/m/Y'),
                'coa_id'          => $m->coa_id,
                'kode_akun'       => $code,
                'nama_akun'       => $m->coa?->nama_akun ?? '-',
                'ref_type'        => $m->reference_type,
                'ref_label'       => $refMeta['label'],
                'reference_no'    => $m->reference_no ?: '-',
                'remarks'         => $m->remarks ?: '-',
                'debit'           => round((float) $m->debit, 2),
                'kredit'          => round((float) $m->kredit, 2),
                'running_balance' => $runningByCoa[$m->id] ?? 0.0,
            ];
        }

        $totalDebit = round(collect($rows)->sum('debit'), 2);
        $totalKredit = round(collect($rows)->sum('kredit'), 2);
        $selisih = round(abs($totalDebit - $totalKredit), 2);

        $selectedAccount = null;
        if ($coaId) {
            $selectedAccount = MasterCOA::find($coaId);
        }

        return [
            'start_date'        => $start,
            'end_date'          => $end,
            'rows'              => $rows,
            'total_debit'       => $totalDebit,
            'total_kredit'      => $totalKredit,
            'selisih'           => $selisih,
            'is_balanced'       => $selisih < 1.0,
            'total_rows'        => count($rows),
            'selected_account'  => $selectedAccount,
            'selected_ref_type' => $refType,
        ];
    }

    /**
     * Hitung Data Neraca Saldo (Trial Balance) untuk PDF dan Export
     */
    public function getTrialBalanceReport(?string $selectedGroup = null, bool $onlyActive = true): array
    {
        $movementsByCoa = CoaMovement::query()
            ->selectRaw('coa_id, COALESCE(SUM(debit),0) as total_debit, COALESCE(SUM(kredit),0) as total_kredit, COUNT(*) as tx_count')
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');

        $coas = MasterCOA::orderBy('kode_akun')->get();
        $rows = [];

        $grandMutDebit = 0.0;
        $grandMutKredit = 0.0;
        $grandEndDebit = 0.0;
        $grandEndKredit = 0.0;

        foreach ($coas as $coa) {
            $code = trim((string) $coa->kode_akun);
            $mov = $movementsByCoa->get($coa->id);

            $openDebit = round((float) ($coa->saldo_debit ?? 0), 2);
            $openKredit = round((float) ($coa->saldo_kredit ?? 0), 2);
            $mutDebit = round((float) ($mov->total_debit ?? 0), 2);
            $mutKredit = round((float) ($mov->total_kredit ?? 0), 2);
            $txCount = (int) ($mov->tx_count ?? 0);

            $hasActivity = ($openDebit > 0 || $openKredit > 0 || $mutDebit > 0 || $mutKredit > 0);
            if ($onlyActive && ! $hasActivity) {
                continue;
            }

            if ($selectedGroup !== null && $selectedGroup !== '' && ! str_starts_with($code, $selectedGroup)) {
                continue;
            }

            $net = round(($openDebit + $mutDebit) - ($openKredit + $mutKredit), 2);
            $endDebit = $net > 0 ? $net : 0.0;
            $endKredit = $net < 0 ? abs($net) : 0.0;

            $firstDigit = substr($code, 0, 1);
            $groupMeta = match ($firstDigit) {
                '1' => ['label' => '1 - Aset / Aktiva',      'stmt' => 'NERACA'],
                '2' => ['label' => '2 - Kewajiban / Utang',  'stmt' => 'NERACA'],
                '3' => ['label' => '3 - Ekuitas / Modal',    'stmt' => 'NERACA'],
                '4' => ['label' => '4 - Pendapatan Usaha',   'stmt' => 'LABA RUGI'],
                '5' => ['label' => '5 - Harga Pokok (HPP)',  'stmt' => 'LABA RUGI'],
                '6' => ['label' => '6 - Beban Operasional',  'stmt' => 'LABA RUGI'],
                default => ['label' => 'Lainnya',            'stmt' => 'LAINNYA'],
            };

            $rows[] = [
                'id'          => $coa->id,
                'kode_akun'   => $code,
                'nama_akun'   => $coa->nama_akun,
                'group_label' => $groupMeta['label'],
                'stmt'        => $groupMeta['stmt'],
                'tx_count'    => $txCount,
                'open_debit'  => $openDebit,
                'open_kredit' => $openKredit,
                'mut_debit'   => $mutDebit,
                'mut_kredit'  => $mutKredit,
                'end_debit'   => $endDebit,
                'end_kredit'  => $endKredit,
            ];

            $grandMutDebit += $mutDebit;
            $grandMutKredit += $mutKredit;
            $grandEndDebit += $endDebit;
            $grandEndKredit += $endKredit;
        }

        $isBalanced = round(abs($grandEndDebit - $grandEndKredit), 2) < 1.0;

        return [
            'rows'              => $rows,
            'grand_mut_debit'   => round($grandMutDebit, 2),
            'grand_mut_kredit'  => round($grandMutKredit, 2),
            'grand_end_debit'   => round($grandEndDebit, 2),
            'grand_end_kredit'  => round($grandEndKredit, 2),
            'is_balanced'       => $isBalanced,
            'total_rows'        => count($rows),
            'selected_group'    => $selectedGroup,
            'as_of_date'        => now()->format('d/m/Y'),
        ];
    }
}


