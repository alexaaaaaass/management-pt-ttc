<?php

namespace App\Services\Pracetak;

use App\Models\MasterMaterialPlate;
use App\Models\MasterSpk;
use App\Models\OrderConfirmationItem;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
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
            $manualCost = max(0, (float) ($params['biaya_plate'] ?? 0));
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
        $biayaDesign = max(0, (float) ($params['plate_biaya_design'] ?? 0));
        $biayaProve = max(0, (float) ($params['plate_biaya_prove'] ?? 0));
        $tarifProsesPerPlate = max(0, (float) ($params['plate_tarif_proses'] ?? 0));
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

            $archive = PracetakPlateArchive::updateOrCreate(
                ['pracetak_process_id' => $process->id],
                [
                    'kode_arsip'             => $process->generatedArchive?->kode_arsip ?: PracetakPlateArchive::generateKode(),
                    'finish_good_item_id'    => $fgId,
                    'customer_id'            => $customerId,
                    'nama_plat'              => $process->nama_plat,
                    'jumlah_warna'           => (int) ($process->jumlah_warna_plate ?: $process->qty ?: 4),
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
     * Catat serah terima Plate Jadi ke divisi Printing Flexo.
     */
    public function handOverToPrinting(PracetakProcess $process, string $penerima, ?string $catatan = null): PracetakProcess
    {
        return DB::transaction(function () use ($process, $penerima, $catatan) {
            $process->update([
                'status'         => 'handed_over',
                'handed_over_at' => now(),
                'handed_over_to' => $penerima,
            ]);

            $archive = $this->syncPlateArchive($process);
            $archive->increment('jumlah_dipakai');
            $archive->update([
                'status_kondisi' => 'dipakai_printing',
                'last_used_at'   => now(),
                'catatan'        => $catatan
                    ? trim(($archive->catatan ? $archive->catatan . "\n" : '') . '[' . now()->format('d/m/Y H:i') . "] Diserahkan ke {$penerima}: {$catatan}")
                    : $archive->catatan,
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
}
