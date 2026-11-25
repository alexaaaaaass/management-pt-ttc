<?php

namespace App\Services\Pracetak;

use App\Models\MasterMaterialPlate;
use App\Models\MasterSpk;
use App\Models\OrderConfirmationItem;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use App\Support\MoneyHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PracetakCostingService
{
    public const DEFAULT_MARGIN_PLATE_CM = 2.0; // Margin potong 2 cm per dimensi plate
    public const DEFAULT_TOLERANSI_RUSAK_PERSEN = 10.0; // 10% cadangan kerusakan/remake

    /**
     * Hitung estimasi kebutuhan material plate & biaya prepress untuk Kalkulator HPP Order Confirmation.
     *
     * @param array $params
     * @return array{
     *   plate_mode: string,
     *   plate_panjang_mm: float,
     *   plate_lebar_mm: float,
     *   plate_luas_1_cm2: float,
     *   plate_luas_total_cm2: float,
     *   plate_luas_sheet_cm2: float,
     *   plate_estimasi_sheet: float,
     *   plate_harga_per_sheet: float,
     *   plate_estimasi_material: float,
     *   plate_estimasi_proses: float,
     *   plate_estimasi_hpp: float,
     *   plate_estimasi_harga_jual: float
     * }
     */
    public function estimatePlateCost(array $params): array
    {
        $plateMode = $params['plate_mode'] ?? 'manual';

        // Mode Repeat Order (pakai plate arsip rak) => biaya plate = 0
        if ($plateMode === 'repeat') {
            return [
                'plate_mode'                => 'repeat',
                'plate_panjang_mm'          => 0.0,
                'plate_lebar_mm'            => 0.0,
                'plate_luas_1_cm2'          => 0.0,
                'plate_luas_total_cm2'      => 0.0,
                'plate_luas_sheet_cm2'      => 0.0,
                'plate_estimasi_sheet'      => 0.0,
                'plate_harga_per_sheet'     => 0.0,
                'plate_estimasi_material'   => 0.0,
                'plate_estimasi_proses'     => 0.0,
                'plate_estimasi_hpp'        => 0.0,
                'plate_estimasi_harga_jual' => 0.0,
            ];
        }

        $marginJualPersen = max(0, (float) ($params['plate_margin_persen'] ?? 0));

        // Mode Manual (kompatibel penuh dengan kalkulator lama)
        if ($plateMode !== 'new') {
            $manualCost = max(0, (float) (MoneyHelper::clean($params['biaya_plate'] ?? 0) ?? 0));
            $hargaJual = round($manualCost * (1 + ($marginJualPersen / 100.0)), 2);

            return [
                'plate_mode'                => 'manual',
                'plate_panjang_mm'          => 0.0,
                'plate_lebar_mm'            => 0.0,
                'plate_luas_1_cm2'          => 0.0,
                'plate_luas_total_cm2'      => 0.0,
                'plate_luas_sheet_cm2'      => 0.0,
                'plate_estimasi_sheet'      => 0.0,
                'plate_harga_per_sheet'     => 0.0,
                'plate_estimasi_material'   => $manualCost,
                'plate_estimasi_proses'     => 0.0,
                'plate_estimasi_hpp'        => $manualCost,
                'plate_estimasi_harga_jual' => $hargaJual,
            ];
        }

        // Mode Plate Baru — hitung dari dimensi layout cylinder & stok Master Material Plate
        $lebarWebCm = max(0, (float) ($params['lebar_bahan'] ?? 0));
        $repeatCylinderCm = max(0, (float) ($params['repeat_cylinder'] ?? 41.28));
        $marginPotongCm = isset($params['plate_margin_potong_cm'])
            ? max(0, (float) $params['plate_margin_potong_cm'])
            : self::DEFAULT_MARGIN_PLATE_CM;
        $jumlahWarna = max(1, (int) ($params['plate_jumlah_warna'] ?? 4));
        $toleransiPersen = isset($params['plate_toleransi_persen'])
            ? max(0, (float) $params['plate_toleransi_persen'])
            : self::DEFAULT_TOLERANSI_RUSAK_PERSEN;

        $platePanjangMm = round(($repeatCylinderCm + $marginPotongCm) * 10.0, 2);
        $plateLebarMm = round(($lebarWebCm + $marginPotongCm) * 10.0, 2);

        $plateId = $params['master_material_plate_id'] ?? null;
        $plate = $plateId ? MasterMaterialPlate::with('stock')->find($plateId) : null;

        $fraction = app(PracetakStockService::class)->calculateSheetFraction(
            $plate,
            $platePanjangMm,
            $plateLebarMm,
            $jumlahWarna
        );

        $baseSheet = (float) $fraction['qty_sheet_terpakai'];
        $estimasiSheet = round($baseSheet * (1 + ($toleransiPersen / 100.0)), 4);

        $hargaPerSheet = 0.0;
        if ($plate) {
            $avgStockPrice = (float) ($plate->stock?->harga_rata_rata ?? 0);
            $hargaPerSheet = $avgStockPrice > 0 ? $avgStockPrice : (float) $plate->harga_beli;
        }

        $biayaMaterial = round($estimasiSheet * $hargaPerSheet, 2);
        $biayaDesign = max(0, (float) (MoneyHelper::clean($params['plate_biaya_design'] ?? 0) ?? 0));
        $biayaProve = max(0, (float) (MoneyHelper::clean($params['plate_biaya_prove'] ?? 0) ?? 0));
        $tarifProsesPerPlate = max(0, (float) (MoneyHelper::clean($params['plate_tarif_proses'] ?? 0) ?? 0));
        $biayaProses = round($biayaDesign + $biayaProve + ($tarifProsesPerPlate * $jumlahWarna), 2);

        $estimasiHpp = round($biayaMaterial + $biayaProses, 2);
        $estimasiHargaJual = round($estimasiHpp * (1 + ($marginJualPersen / 100.0)), 2);

        return [
            'plate_mode'                => 'new',
            'plate_panjang_mm'          => $platePanjangMm,
            'plate_lebar_mm'            => $plateLebarMm,
            'plate_luas_1_cm2'          => $fraction['luas_1_plate_cm2'],
            'plate_luas_total_cm2'      => $fraction['luas_terpakai_cm2'],
            'plate_luas_sheet_cm2'      => $fraction['luas_sheet_cm2'],
            'plate_estimasi_sheet'      => $estimasiSheet,
            'plate_harga_per_sheet'     => round($hargaPerSheet, 2),
            'plate_estimasi_material'   => $biayaMaterial,
            'plate_estimasi_proses'     => $biayaProses,
            'plate_estimasi_hpp'        => $estimasiHpp,
            'plate_estimasi_harga_jual' => $estimasiHargaJual,
        ];
    }

    /**
     * Hitung ulang biaya aktual (Material + Proses + Design + Prove) dan Variance terhadap Estimasi OC.
     */
    public function recalculateProcessCost(PracetakProcess $process): PracetakProcess
    {
        $process->loadMissing(['mesin']);

        $materialCost = (float) $process->materialUsages()
            ->where('status', 'posted')
            ->sum('total_biaya');

        $designCost = max(0, (float) ($process->actual_design_cost ?? 0));
        $proveCost = max(0, (float) ($process->actual_prove_cost ?? 0));
        $processCost = max(0, (float) ($process->actual_process_cost ?? 0));

        // Jika biaya proses CTP belum diisi manual namun mesin memiliki tarif standar per plate, hitung otomatis
        if ($processCost <= 0 && $process->mesin && (float) $process->mesin->tarif_per_plate > 0) {
            $plateCount = max(
                (int) (($process->hasil_baik ?? 0) + ($process->hasil_rusak ?? 0)),
                (int) ($process->jumlah_warna_plate ?? 0)
            );
            if ($plateCount > 0) {
                $processCost = round((float) $process->mesin->tarif_per_plate * $plateCount, 2);
            }
        }

        $totalActual = round($designCost + $proveCost + $materialCost + $processCost, 2);
        $estimasi = (float) ($process->estimasi_biaya_plate ?? 0);
        // Positif = Hemat (Aktual <= Estimasi); Negatif = Overbudget
        $variance = $estimasi > 0 ? round($estimasi - $totalActual, 2) : 0.0;

        $process->updateQuietly([
            'actual_material_cost' => round($materialCost, 2),
            'actual_process_cost'  => round($processCost, 2),
            'actual_total_cost'    => $totalActual,
            'cost_variance'        => $variance,
        ]);

        return $process->fresh();
    }

    /**
     * Daftarkan atau perbarui Arsip Plate Jadi saat Tahap D (Penyimpanan) selesai.
     */
    public function syncPlateArchive(PracetakProcess $process): PracetakPlateArchive
    {
        return DB::transaction(function () use ($process) {
            $process->loadMissing(['spk.salesOrder', 'approvalForm', 'materialUsages']);

            // Jika repeat order yang sudah terhubung ke arsip existing, cukup update lokasi & counter
            if ($process->is_repeat_order && $process->plate_archive_id) {
                $archive = PracetakPlateArchive::find($process->plate_archive_id);
                if ($archive) {
                    $archive->update([
                        'lokasi_rak'             => $process->lokasi_simpan_plate ?: $archive->lokasi_rak,
                        'material_pendukung'     => $process->material_pendukung ?: $archive->material_pendukung,
                        'toleransi_rusak_hilang' => $process->toleransi_rusak_hilang ?: $archive->toleransi_rusak_hilang,
                        'status_kondisi'         => $process->status === 'handed_over' ? 'dipakai_printing' : 'tersimpan',
                        'last_used_at'           => now(),
                    ]);

                    return $archive;
                }
            }

            $fgId = $process->finish_good_item_id;
            $customerId = $process->spk?->salesOrder?->customer_id
                ?? $process->approvalForm?->customer_id;

            if (! $fgId && $process->spk?->salesOrder?->itemable_type === \App\Models\FinishGoodItem::class) {
                $fgId = $process->spk->salesOrder->itemable_id;
            }

            $firstUsage = $process->materialUsages->first();
            $dimensi = ($firstUsage && (float) $firstUsage->panjang_potong_mm > 0 && (float) $firstUsage->lebar_potong_mm > 0)
                ? rtrim(rtrim(number_format((float) $firstUsage->panjang_potong_mm, 2, '.', ''), '0'), '.')
                    . ' × '
                    . rtrim(rtrim(number_format((float) $firstUsage->lebar_potong_mm, 2, '.', ''), '0'), '.') . ' mm'
                : null;

            $qtyTotalPlate = (int) ($process->hasil_baik ?: ($process->jumlah_warna_plate ?: ($process->qty ?: 4)));

            $archive = PracetakPlateArchive::updateOrCreate(
                ['pracetak_process_id' => $process->id],
                [
                    'kode_arsip'             => $process->generatedArchive?->kode_arsip ?: PracetakPlateArchive::generateKode(),
                    'finish_good_item_id'    => $fgId,
                    'customer_id'            => $customerId,
                    'nama_plat'              => $process->nama_plat,
                    'jumlah_warna'           => (int) ($process->jumlah_warna_plate ?: $process->qty ?: 4),
                    'qty_total'              => $qtyTotalPlate,
                    'detail_warna'           => $process->detail_warna_plate,
                    'dimensi_plate_mm'       => $dimensi,
                    'lokasi_rak'             => $process->lokasi_simpan_plate,
                    'material_pendukung'     => $process->material_pendukung,
                    'toleransi_rusak_hilang' => $process->toleransi_rusak_hilang,
                    'status_kondisi'         => $process->status === 'handed_over' ? 'dipakai_printing' : 'tersimpan',
                    'last_used_at'           => now(),
                ]
            );

            if ((int) $process->plate_archive_id !== (int) $archive->id) {
                $process->updateQuietly(['plate_archive_id' => $archive->id]);
            }

            return $archive;
        });
    }

    /**
     * Catat serah terima Plate Jadi ke divisi Printing Flexo beserta tracking jumlah lembar keluar vs sisa di rak.
     */
    public function handOverToPrinting(
        PracetakProcess $process,
        string $penerima,
        ?string $catatan = null,
        ?int $qtyIssued = null
    ): PracetakProcess {
        return DB::transaction(function () use ($process, $penerima, $catatan, $qtyIssued) {
            $archive = $this->syncPlateArchive($process);

            $totalPlate = $archive->effective_qty_total;
            $currentOut = (int) ($archive->qty_dipakai ?? 0);
            $availableInRack = max(0, $totalPlate - $currentOut);

            $qtyOut = $qtyIssued !== null && $qtyIssued > 0
                ? $qtyIssued
                : max(1, $availableInRack ?: $totalPlate);

            if ($availableInRack > 0 && $qtyOut > $availableInRack) {
                throw new \Exception("Jumlah plate diminta ({$qtyOut} lembar) melebihi sisa plate di rak ({$availableInRack} lembar dari total {$totalPlate} lembar).");
            }

            $newOut = min($totalPlate, $currentOut + $qtyOut);

            $process->update([
                'status'         => 'handed_over',
                'handed_over_at' => now(),
                'handed_over_to' => $penerima,
            ]);

            $archive->increment('jumlah_dipakai');
            $archive->update([
                'qty_total'      => $totalPlate,
                'qty_dipakai'    => $newOut,
                'status_kondisi' => $newOut > 0 ? 'dipakai_printing' : 'tersimpan',
                'last_used_at'   => now(),
                'catatan'        => $catatan
                    ? trim(($archive->catatan ? $archive->catatan . "\n" : '') . '[' . now()->format('d/m/Y H:i') . "] Diserahkan {$qtyOut} lembar ke {$penerima} (Sisa di rak: " . max(0, $totalPlate - $newOut) . " lbr): {$catatan}")
                    : trim(($archive->catatan ? $archive->catatan . "\n" : '') . '[' . now()->format('d/m/Y H:i') . "] Diserahkan {$qtyOut} lembar ke {$penerima} (Sisa di rak: " . max(0, $totalPlate - $newOut) . ' lbr)'),
            ]);

            return $process->fresh();
        });
    }

    /**
     * Tarik otomatis estimasi biaya plate, dimensi, warna, dan info Repeat Order dari SPK -> SO -> OrderConfirmation.
     */
    public function resolveEstimatedPlateFromSpk(MasterSpk $spk): array
    {
        $spk->loadMissing(['salesOrder.orderConfirmation.items', 'salesOrder.itemable']);
        $so = $spk->salesOrder;
        $fgId = ($so && $so->itemable_type === \App\Models\FinishGoodItem::class) ? $so->itemable_id : null;
        $productName = $so?->product_name ?: $so?->itemable?->nama_barang ?: "Plate SPK {$spk->no_spk}";

        $result = [
            'approval_form_id'         => $so?->orderConfirmation?->approval_form_id,
            'order_confirmation_id'    => $so?->order_confirmation_id,
            'finish_good_item_id'      => $fgId,
            'nama_plat'                => $productName,
            'jumlah_warna_plate'       => 4,
            'estimasi_biaya_plate'     => 0.0,
            'is_repeat_order'          => false,
            'plate_archive_id'         => null,
            'master_material_plate_id' => null,
            'plate_panjang_mm'         => 432.8,
            'plate_lebar_mm'           => 280.0,
        ];

        // Cek apakah ada warna di SO / FinishGood
        $rawWarna = $so?->warna ?: $so?->itemable?->warna;
        if ($rawWarna && preg_match('/\d+/', (string) $rawWarna, $m)) {
            $result['jumlah_warna_plate'] = max(1, (int) $m[0]);
        }

        // Cari item Order Confirmation yang cocok untuk menarik estimasi plate & layout
        if ($so && $so->orderConfirmation) {
            $ocItems = $so->orderConfirmation->items;
            /** @var OrderConfirmationItem|null $matchedItem */
            $matchedItem = $ocItems->first(function (OrderConfirmationItem $item) use ($fgId, $productName) {
                if ($fgId && (int) $item->finish_good_item_id === (int) $fgId) {
                    return true;
                }

                return $productName && strcasecmp(trim((string) $item->item_name), trim((string) $productName)) === 0;
            }) ?? $ocItems->first();

            if ($matchedItem) {
                $hpp = is_array($matchedItem->kalkulasi_hpp_data) ? $matchedItem->kalkulasi_hpp_data : [];

                if (($hpp['plate_mode'] ?? null) === 'repeat' || $matchedItem->plate_archive_id) {
                    $result['is_repeat_order'] = true;
                    $result['plate_archive_id'] = $matchedItem->plate_archive_id ?: ($hpp['plate_archive_id'] ?? null);
                }

                $estPlate = (float) ($hpp['plate_estimasi_hpp'] ?? $hpp['biaya_plate'] ?? 0);
                if ($estPlate <= 0 && (float) $matchedItem->biaya_plate_terpisah > 0) {
                    $estPlate = (float) $matchedItem->biaya_plate_terpisah;
                }

                $result['estimasi_biaya_plate'] = round($estPlate, 2);
                if (! empty($hpp['plate_jumlah_warna'])) {
                    $result['jumlah_warna_plate'] = (int) $hpp['plate_jumlah_warna'];
                }
                if (! empty($hpp['master_material_plate_id'])) {
                    $result['master_material_plate_id'] = (int) $hpp['master_material_plate_id'];
                }
                if (! empty($hpp['plate_panjang_mm'])) {
                    $result['plate_panjang_mm'] = (float) $hpp['plate_panjang_mm'];
                }
                if (! empty($hpp['plate_lebar_mm'])) {
                    $result['plate_lebar_mm'] = (float) $hpp['plate_lebar_mm'];
                }
            }
        }

        // Jika belum ditandai repeat order di OC, cek apakah FinishGoodItem sudah punya arsip plate aktif
        if (! $result['plate_archive_id'] && $fgId) {
            $activeArchive = PracetakPlateArchive::where('finish_good_item_id', $fgId)
                ->whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])
                ->latest('id')
                ->first();

            if ($activeArchive) {
                $result['plate_archive_id'] = $activeArchive->id;
            }
        }

        return $result;
    }

    /**
     * Daftar Arsip Plate aktif untuk produk atau customer tertentu (dipakai di Approval Form & OC).
     */
    public function findActiveArchives(?int $finishGoodItemId = null, ?int $customerId = null): Collection
    {
        return PracetakPlateArchive::query()
            ->with(['customer', 'finishGoodItem'])
            ->whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])
            ->when($finishGoodItemId, fn ($q) => $q->where('finish_good_item_id', $finishGoodItemId))
            ->when(! $finishGoodItemId && $customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Hitung rincian total plate rusak & total biaya kerusakannya untuk 1 Proses Pra Cetak.
     */
    public function calculateProcessDamageSummary(PracetakProcess $process): array
    {
        $process->loadMissing([
            'spk',
            'approvalForm',
            'materialUsages.masterPlate',
            'generatedArchive',
            'plateArchive',
            'mesin',
        ]);

        $hasilBaik = max(0, (int) ($process->hasil_baik ?? 0));
        $rusakProses = max(
            0,
            (int) ($process->hasil_rusak ?? 0),
            (int) ($process->kerusakan_plate ?? 0)
        );

        $archive = $process->generatedArchive ?: $process->plateArchive;
        $rusakArsip = max(0, (int) ($archive?->qty_rusak ?? 0));
        $totalRusak = $rusakProses + $rusakArsip;

        $jumlahWarna = max(0, (int) ($process->jumlah_warna_plate ?: ($process->qty ?: 0)));
        $totalProduced = $hasilBaik + $totalRusak;
        $wasteRate = $totalProduced > 0 ? round(($totalRusak / $totalProduced) * 100, 1) : 0.0;

        $remakeUsages = $process->materialUsages
            ->filter(fn ($u) => $u->jenis_pemakaian === 'remake_rusak')
            ->values();

        $remakeCost = (float) $remakeUsages->sum('total_biaya');
        $remakePlateQty = (int) $remakeUsages->sum('jumlah_plate');

        $actualTotal = (float) ($process->actual_total_cost ?? 0);
        $actualMaterial = (float) ($process->actual_material_cost ?? 0);
        $estimasiPlate = (float) ($process->estimasi_biaya_plate ?? 0);

        // Tentukan biaya satuan per lembar plate berdasarkan ketersediaan data aktual vs estimasi OC
        $costPerPlate = 0.0;
        $costSourceLabel = 'Belum Ada Data Biaya';
        $costSourceShort = 'Belum Ada Data';
        $formulaNote = 'Biaya aktual maupun estimasi HPP plate masih Rp 0. Posting pemakaian material plate atau lengkapi estimasi HPP plate untuk menghitung biaya kerusakan.';

        $productionUsages = $process->materialUsages->filter(fn ($u) => $u->jenis_pemakaian === 'produksi');
        $prodUsageCost = (float) $productionUsages->sum('total_biaya');
        $prodUsageQty = (int) $productionUsages->sum('jumlah_plate');

        if ($remakeCost > 0 && $remakePlateQty > 0) {
            $costPerPlate = round($remakeCost / $remakePlateQty, 2);
            $costSourceLabel = 'Aktual Pemakaian Remake Plate';
            $costSourceShort = 'Aktual Remake';
            $formulaNote = 'Biaya dihitung dari log pemakaian material plate aktual bertipe Remake / Plate Rusak.';
        } elseif ($actualTotal > 0) {
            $basisQty = max(1, $hasilBaik + $rusakProses, $jumlahWarna);
            $costPerPlate = round($actualTotal / $basisQty, 2);
            $costSourceLabel = 'Biaya Aktual Pra Cetak (Proporsional)';
            $costSourceShort = 'Aktual Pra Cetak';
            $formulaNote = 'Biaya/plate = Total Biaya Aktual Pra Cetak (Rp ' . number_format($actualTotal, 0, ',', '.') . ") ÷ {$basisQty} lembar plate.";
        } elseif ($prodUsageCost > 0 && $prodUsageQty > 0) {
            $costPerPlate = round($prodUsageCost / $prodUsageQty, 2);
            $costSourceLabel = 'Pemakaian Material Plate (Draft/Aktual)';
            $costSourceShort = 'Material Plate';
            $formulaNote = 'Biaya/plate = Total Pemakaian Material Plate (Rp ' . number_format($prodUsageCost, 0, ',', '.') . ") ÷ {$prodUsageQty} lembar plate.";
        } elseif ($estimasiPlate > 0) {
            $basisQty = max(1, $jumlahWarna ?: $hasilBaik);
            $costPerPlate = round($estimasiPlate / $basisQty, 2);
            $costSourceLabel = 'Estimasi HPP Plate (OC)';
            $costSourceShort = 'Estimasi OC';
            $formulaNote = 'Pemakaian material aktual belum diposting — biaya dihitung proporsional dari Estimasi HPP Plate OC (Rp '
                . number_format($estimasiPlate, 0, ',', '.') . ") ÷ {$basisQty} warna/plate = Rp "
                . number_format($costPerPlate, 0, ',', '.') . '/lembar.';
        }

        // Hitung biaya rusak proses & rusak arsip
        if ($remakeCost > 0 && $rusakProses > 0) {
            $extraProsesQty = max(0, $rusakProses - $remakePlateQty);
            $biayaRusakProses = round($remakeCost + ($extraProsesQty * $costPerPlate), 2);
        } else {
            $biayaRusakProses = round($rusakProses * $costPerPlate, 2);
        }

        $biayaRusakArsip = round($rusakArsip * $costPerPlate, 2);
        $totalDamageCost = round($biayaRusakProses + $biayaRusakArsip, 2);

        $catatanList = array_filter([
            trim((string) ($process->catatan_kerusakan_plate ?? '')),
            ($rusakArsip > 0 && $archive?->catatan) ? trim((string) $archive->catatan) : '',
        ]);

        return [
            'hasil_baik'        => $hasilBaik,
            'jumlah_warna'      => $jumlahWarna,
            'rusak_proses'      => $rusakProses,
            'rusak_arsip'       => $rusakArsip,
            'total_rusak'       => $totalRusak,
            'waste_rate'        => $wasteRate,
            'cost_per_plate'    => $costPerPlate,
            'biaya_rusak_proses'=> $biayaRusakProses,
            'biaya_rusak_arsip' => $biayaRusakArsip,
            'total_damage_cost' => $totalDamageCost,
            'cost_source_label' => $costSourceLabel,
            'cost_source_short' => $costSourceShort,
            'formula_note'      => $formulaNote,
            'archive_kode'      => $archive?->kode_arsip,
            'catatan_kerusakan' => implode(' | ', $catatanList),
            'remake_usages'     => $remakeUsages->map(fn ($u) => [
                'material'     => $u->masterPlate?->nama_material ?? '-',
                'jumlah_plate' => (int) $u->jumlah_plate,
                'qty_sheet'    => (float) $u->qty_sheet_terpakai,
                'alasan'       => $u->alasan_kerusakan,
                'total_biaya'  => (float) $u->total_biaya,
            ])->all(),
        ];
    }

    /**
     * Hitung rekap keseluruhan total plate rusak & total biaya kerusakan dari seluruh Proses Pra Cetak.
     */
    public function calculateOverallDamageSummary(): array
    {
        $processes = PracetakProcess::with([
            'spk',
            'approvalForm',
            'materialUsages.masterPlate',
            'generatedArchive',
            'plateArchive',
            'mesin',
        ])->orderByDesc('id')->get();

        $totalBaik = 0;
        $totalRusakProses = 0;
        $totalRusakArsip = 0;
        $totalRusak = 0;
        $totalDamageCost = 0.0;
        $prosesBermasalah = 0;
        $rows = [];

        foreach ($processes as $process) {
            $s = $this->calculateProcessDamageSummary($process);
            $totalBaik += $s['hasil_baik'];
            $totalRusakProses += $s['rusak_proses'];
            $totalRusakArsip += $s['rusak_arsip'];
            $totalRusak += $s['total_rusak'];
            $totalDamageCost += $s['total_damage_cost'];
            if ($s['total_rusak'] > 0) {
                $prosesBermasalah++;
            }

            $rows[] = [
                'id'                => $process->id,
                'ref'               => $process->spk?->no_spk ?: ('PRE-' . ($process->approvalForm?->form_no ?: $process->id)),
                'nama_plat'         => $process->nama_plat ?: '-',
                'hasil_baik'        => $s['hasil_baik'],
                'rusak_proses'      => $s['rusak_proses'],
                'rusak_arsip'       => $s['rusak_arsip'],
                'total_rusak'       => $s['total_rusak'],
                'cost_per_plate'    => $s['cost_per_plate'],
                'total_damage_cost' => $s['total_damage_cost'],
                'cost_source_short' => $s['cost_source_short'],
            ];
        }

        $totalProduced = $totalBaik + $totalRusak;
        $wasteRate = $totalProduced > 0 ? round(($totalRusak / $totalProduced) * 100, 1) : 0.0;

        return [
            'total_proses'       => $processes->count(),
            'proses_bermasalah'  => $prosesBermasalah,
            'total_baik'         => $totalBaik,
            'total_rusak_proses' => $totalRusakProses,
            'total_rusak_arsip'  => $totalRusakArsip,
            'total_rusak'        => $totalRusak,
            'waste_rate'         => $wasteRate,
            'total_damage_cost'  => round($totalDamageCost, 2),
            'rows'               => $rows,
        ];
    }
}
