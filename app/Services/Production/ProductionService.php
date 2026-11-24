<?php

namespace App\Services\Production;

use App\Models\DieMaking;
use App\Models\FinishGoodItem;
use App\Models\MasterFinishing;
use App\Models\MasterSpk;
use App\Models\Packaging;
use App\Models\PracetakProcess;
use App\Models\Printing;
use App\Models\SalesOrder;
use App\Services\Inventory\FinishGoodStockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    /**
     * Generate format penomoran resmi SPK: {count}/SPK-IK{kode_ik}/{mY}
     */
    public function generateNoSpk(?string $kodeIk = '01'): string
    {
        $count = MasterSpk::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;
        $bulanTahun = Carbon::now()->format('mY');

        return str_pad($count, 4, '0', STR_PAD_LEFT)
            . '/SPK-IK' . ($kodeIk ?: '01')
            . '/' . $bulanTahun;
    }

    /**
     * Generate nomor kode packaging: PKG-YYYYMMDD-XXX
     */
    public function generateKodePackaging(): string
    {
        $tanggal = Carbon::now()->format('Ymd');
        $count = Packaging::whereDate('created_at', today())->count() + 1;

        return 'PKG-' . $tanggal . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Buat SPK baru dari Sales Order yang valid.
     */
    public function createSpk(array $data): MasterSpk
    {
        return DB::transaction(function () use ($data) {
            $salesOrderId = $data['sales_order_id'] ?? null;
            if (!$salesOrderId) {
                throw ValidationException::withMessages([
                    'sales_order_id' => 'Sales Order wajib dipilih untuk membuat SPK.',
                ]);
            }

            $salesOrder = SalesOrder::with(['customer', 'itemable'])->findOrFail($salesOrderId);

            // Validasi order_type wajib 'SPK'
            if ($salesOrder->order_type !== 'SPK' && $salesOrder->mode !== 'SPK') {
                throw ValidationException::withMessages([
                    'sales_order_id' => 'Sales Order yang dipilih bukan bertipe pesanan produksi (SPK).',
                ]);
            }

            // Validasi pencegahan double SPK untuk 1 Sales Order
            $existingSpk = MasterSpk::where('sales_order_id', $salesOrder->id)->first();
            if ($existingSpk) {
                throw ValidationException::withMessages([
                    'sales_order_id' => "Sales Order {$salesOrder->no_sales_order} sudah memiliki SPK produksi ({$existingSpk->no_spk}).",
                ]);
            }

            // Generate nomor SPK jika belum ada
            $kodeIk = $data['kode_ik'] ?? '01';
            if (empty($data['no_spk'])) {
                $data['no_spk'] = $this->generateNoSpk($kodeIk);
            }

            // Sync tanggal PO dari Sales Order jika tidak diisi manual
            if (empty($data['tanggal_po'])) {
                $data['tanggal_po'] = $salesOrder->tanggal_po;
            }

            // Tarik spesifikasi teknis cetak (Prioritas 1: Kalkulasi HPP Order Confirmation, Fallback: FinishGoodItem)
            $specs = $this->resolveTechnicalSpecsFromSalesOrder($salesOrder);
            $data['up_satu'] = filled($data['up_satu'] ?? null) ? $data['up_satu'] : $specs['up_satu'];
            $data['up_dua'] = filled($data['up_dua'] ?? null) ? $data['up_dua'] : $specs['up_dua'];
            $data['up_tiga'] = filled($data['up_tiga'] ?? null) ? $data['up_tiga'] : $specs['up_tiga'];
            $data['ukuran_potong'] = filled($data['ukuran_potong'] ?? null) ? $data['ukuran_potong'] : $specs['ukuran_potong'];
            $data['ukuran_cetak'] = filled($data['ukuran_cetak'] ?? null) ? $data['ukuran_cetak'] : $specs['ukuran_cetak'];
            $data['spesifikasi_kertas'] = filled($data['spesifikasi_kertas'] ?? null) ? $data['spesifikasi_kertas'] : $specs['spesifikasi_kertas'];

            if (empty($data['status'])) {
                $data['status'] = 'ON PROCESS';
            }

            return MasterSpk::create($data);
        });
    }

    /**
     * Resolusi spesifikasi teknis cetak SPK (UP 1-3, Ukuran Potong, Ukuran Cetak, Spesifikasi Kertas).
     * Prioritas 1: Kalkulasi HPP Flexo pada item Order Confirmation terkait.
     * Prioritas 2 (Fallback): Data master FinishGoodItem.
     */
    public function resolveTechnicalSpecsFromSalesOrder(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing(['orderConfirmation.items', 'itemable']);

        $fg = $salesOrder->itemable instanceof FinishGoodItem ? $salesOrder->itemable : null;
        $oc = $salesOrder->orderConfirmation;
        $matchedOcItem = null;

        if ($oc && $oc->items->isNotEmpty()) {
            $fgId = $fg?->id;
            $productName = trim((string) ($salesOrder->product_name ?: ($fg?->nama_barang ?? '')));

            $matchedOcItem = $oc->items->first(function ($item) use ($fgId, $productName) {
                if ($fgId && (int) $item->finish_good_item_id === (int) $fgId) {
                    return true;
                }

                return $productName !== '' && strcasecmp(trim((string) $item->item_name), $productName) === 0;
            }) ?? $oc->items->first();
        }

        $hpp = [];
        if ($matchedOcItem) {
            $rawHpp = $matchedOcItem->kalkulasi_hpp_data;
            if (is_array($rawHpp)) {
                $hpp = $rawHpp;
            } elseif (is_string($rawHpp) && trim($rawHpp) !== '') {
                $decoded = json_decode($rawHpp, true);
                if (is_array($decoded)) {
                    $hpp = $decoded;
                }
            }
        }

        $fmtMm = static function (float $cm): string {
            $mm = round($cm * 10.0, 2);

            return rtrim(rtrim(number_format($mm, 2, '.', ''), '0'), '.');
        };

        $cleanDuplicateTags = static function (?string $text): ?string {
            if ($text === null || trim($text) === '') {
                return $text;
            }

            return trim((string) preg_replace('/(\[[^\]]+\])(?:\s*\1)+/i', '$1', $text));
        };

        // 1) UP 1 (Total UP/Repeat), UP 2 (Arah Repeat), UP 3 (Arah Lebar/Lane)
        $upRepeat = ! empty($hpp['jumlah_etiket_sepanjang_repeat']) ? (int) $hpp['jumlah_etiket_sepanjang_repeat'] : null;
        $upLebar = ! empty($hpp['jumlah_etiket_sepanjang_lebar']) ? (int) $hpp['jumlah_etiket_sepanjang_lebar'] : null;
        $upTotal = ! empty($hpp['etiket_per_repeat'])
            ? (int) $hpp['etiket_per_repeat']
            : (($upRepeat && $upLebar) ? ($upRepeat * $upLebar) : null);

        $upSatu = $upTotal ?? $fg?->up_satu;
        $upDua = $upRepeat ?? $fg?->up_dua;
        $upTiga = $upLebar ?? $fg?->up_tiga;

        // 2) Ukuran Potong / Jadi Etiket (Die-Cut) dalam mm
        $ukuranPotong = null;
        $panjangCm = (float) ($hpp['panjang_etiket_jadi'] ?? 0);
        $lebarCm = (float) ($hpp['lebar_etiket_jadi'] ?? 0);
        if ($panjangCm > 50) {
            $panjangCm /= 10.0;
        }
        if ($lebarCm > 50) {
            $lebarCm /= 10.0;
        }
        if ($panjangCm > 0 && $lebarCm > 0) {
            $ukuranPotong = "{$fmtMm($panjangCm)} × {$fmtMm($lebarCm)} mm";
        } else {
            $ukuranPotong = $fg?->ukuran_potong;
        }

        // 3) Ukuran Cetak (Lebar Web × Repeat Cylinder) dalam mm
        $ukuranCetak = null;
        $lebarWebCm = (float) ($hpp['lebar_bahan'] ?? 0);
        $repeatCm = (float) ($hpp['repeat_cylinder'] ?? 0);
        if ($lebarWebCm > 100) {
            $lebarWebCm /= 10.0;
        }
        if ($repeatCm > 100) {
            $repeatCm /= 10.0;
        }
        if ($lebarWebCm > 0 && $repeatCm > 0) {
            $ukuranCetak = "{$fmtMm($lebarWebCm)} × {$fmtMm($repeatCm)} mm";
        } else {
            $ukuranCetak = $fg?->ukuran_cetak;
        }

        // 4) Spesifikasi Kertas / Material
        $baseKertas = $cleanDuplicateTags(
            $matchedOcItem?->kertas
                ?: $salesOrder->kertas
                ?: $fg?->spesifikasi_kertas
        );

        $specParts = [];
        if (! empty($baseKertas)) {
            $gramatur = (float) ($hpp['gramatur_kertas'] ?? 0);
            if ($gramatur > 0 && ! str_contains(strtolower($baseKertas), 'gsm')) {
                $specParts[] = $baseKertas . ' (' . rtrim(rtrim(number_format($gramatur, 2, '.', ''), '0'), '.') . ' GSM)';
            } else {
                $specParts[] = $baseKertas;
            }
        } elseif (! empty($hpp['gramatur_kertas'])) {
            $specParts[] = 'Kertas ' . rtrim(rtrim(number_format((float) $hpp['gramatur_kertas'], 2, '.', ''), '0'), '.') . ' GSM';
        }

        if ($lebarWebCm > 0) {
            $specParts[] = 'Lebar Web: ' . rtrim(rtrim(number_format($lebarWebCm, 2, '.', ''), '0'), '.') . ' cm';
        }

        $panjangRoll = (float) ($hpp['total_panjang_bahan'] ?? $matchedOcItem?->kebutuhan_kertas_meter ?? 0);
        $beratRoll = (float) ($hpp['berat_bahan_teoritis'] ?? $matchedOcItem?->kebutuhan_kertas_kg ?? 0);
        if ($panjangRoll > 0 || $beratRoll > 0) {
            $estBits = [];
            if ($panjangRoll > 0) {
                $estBits[] = number_format($panjangRoll, 1, '.', ',') . ' m';
            }
            if ($beratRoll > 0) {
                $estBits[] = number_format($beratRoll, 1, '.', ',') . ' kg';
            }
            $specParts[] = 'Est. Kebutuhan: ' . implode(' / ', $estBits);
        }

        $spesifikasiKertas = ! empty($specParts) ? implode(' | ', $specParts) : null;
        $fromOc = ! empty($hpp) && ($upTotal !== null || ($panjangCm > 0 && $lebarCm > 0) || ($lebarWebCm > 0 && $repeatCm > 0));

        return [
            'up_satu'            => $upSatu,
            'up_dua'             => $upDua,
            'up_tiga'            => $upTiga,
            'ukuran_potong'      => $ukuranPotong,
            'ukuran_cetak'       => $ukuranCetak,
            'spesifikasi_kertas' => $spesifikasiKertas,
            'source'             => $fromOc ? 'order_confirmation' : ($fg ? 'finish_good' : 'none'),
            'oc_doc_no'          => $oc?->doc_no,
        ];
    }

    /**
     * Perbarui SPK dengan proteksi integritas.
     */
    public function updateSpk(MasterSpk $spk, array $data): MasterSpk
    {
        return DB::transaction(function () use ($spk, $data) {
            // Jika sales_order_id diganti, validasi SO baru belum memiliki SPK lain
            if (isset($data['sales_order_id']) && $data['sales_order_id'] != $spk->sales_order_id) {
                $exists = MasterSpk::where('sales_order_id', $data['sales_order_id'])
                    ->where('id', '!=', $spk->id)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'sales_order_id' => 'Sales Order tujuan sudah terikat dengan SPK lain.',
                    ]);
                }
            }

            $spk->update($data);
            return $spk->fresh();
        });
    }

    /**
     * Hapus SPK dengan proteksi transaksi hilir.
     */
    public function deleteSpk(MasterSpk $spk): void
    {
        DB::transaction(function () use ($spk) {
            if ($spk->suratJalans()->exists()) {
                throw ValidationException::withMessages([
                    'spk' => "SPK {$spk->no_spk} tidak dapat dihapus karena sudah memiliki riwayat Surat Jalan pengiriman.",
                ]);
            }

            if ($spk->packagings()->exists()) {
                throw ValidationException::withMessages([
                    'spk' => "SPK {$spk->no_spk} tidak dapat dihapus karena sudah ada hasil produksi packaging.",
                ]);
            }

            if ($spk->finishings()->exists() || $spk->diemakings()->exists() || $spk->printings()->exists() || $spk->pracetaks()->exists()) {
                throw ValidationException::withMessages([
                    'spk' => "SPK {$spk->no_spk} tidak dapat dihapus karena sudah masuk antrean proses kerja lantai pabrik.",
                ]);
            }

            $spk->delete();
        });
    }

    /**
     * Catat proses Pra-Cetak (pembuatan plat).
     */
    public function recordPracetak(array $data): PracetakProcess
    {
        return DB::transaction(function () use ($data) {
            $spkId = $data['spk_id'] ?? null;
            if (!$spkId) {
                throw ValidationException::withMessages([
                    'spk_id' => 'SPK wajib ditentukan untuk proses Pra-Cetak.',
                ]);
            }

            if (empty($data['tgl_entry'])) {
                $data['tgl_entry'] = Carbon::now()->toDateString();
            }

            if (empty($data['status'])) {
                $data['status'] = 'plat_making';
            }

            return PracetakProcess::create($data);
        });
    }

    /**
     * Catat proses Printing (mesin cetak).
     */
    public function recordPrinting(array $data): Printing
    {
        return DB::transaction(function () use ($data) {
            $spkId = $data['spk_id'] ?? null;
            if (!$spkId) {
                throw ValidationException::withMessages([
                    'spk_id' => 'SPK wajib ditentukan untuk proses Printing.',
                ]);
            }

            if (empty($data['tanggal_entri'])) {
                $data['tanggal_entri'] = Carbon::now()->toDateString();
            }

            if (empty($data['keterangan_spk'])) {
                $data['keterangan_spk'] = 'reguler';
            }

            return Printing::create($data);
        });
    }

    /**
     * Catat proses Finishing (protol, sorter, lem, dsb).
     */
    public function recordFinishing(array $data): MasterFinishing
    {
        return DB::transaction(function () use ($data) {
            $spkId = $data['spk_id'] ?? null;
            if (!$spkId) {
                throw ValidationException::withMessages([
                    'spk_id' => 'SPK wajib ditentukan untuk proses Finishing.',
                ]);
            }

            if (empty($data['tanggal_entri'])) {
                $data['tanggal_entri'] = Carbon::now()->toDateString();
            }

            if (empty($data['keterangan_spk'])) {
                $data['keterangan_spk'] = 'reguler';
            }

            return MasterFinishing::create($data);
        });
    }

    /**
     * Catat hasil Packaging (pengepakan barang jadi).
     */
    public function recordPackaging(array $data): Packaging
    {
        return DB::transaction(function () use ($data) {
            $spkId = $data['spk_id'] ?? null;
            if (!$spkId) {
                throw ValidationException::withMessages([
                    'spk_id' => 'SPK wajib ditentukan untuk hasil Packaging.',
                ]);
            }

            $spk = MasterSpk::with('salesOrder')->findOrFail($spkId);

            if (empty($data['kode_packaging'])) {
                $data['kode_packaging'] = $this->generateKodePackaging();
            }

            if (empty($data['tgl_transfer'])) {
                $data['tgl_transfer'] = Carbon::now()->toDateString();
            }

            // Hitung total satuan penuh & sisa
            $jmlPenuh = (int) ($data['jumlah_satuan_penuh'] ?? 0);
            $qtyPenuh = (int) ($data['qty_per_satuan_penuh'] ?? 0);
            $jmlSisa = (int) ($data['jumlah_satuan_sisa'] ?? 0);
            $qtySisa = (int) ($data['qty_per_satuan_sisa'] ?? 0);

            $data['total_satuan_penuh'] = $jmlPenuh * $qtyPenuh;
            $data['total_satuan_sisa'] = $jmlSisa * $qtySisa;
            $data['grand_total'] = $data['total_satuan_penuh'] + $data['total_satuan_sisa'];

            if ($data['grand_total'] <= 0) {
                throw ValidationException::withMessages([
                    'grand_total' => 'Total barang hasil packaging harus lebih besar dari 0.',
                ]);
            }

            $packaging = Packaging::create($data);

            // Otomatis tambahkan ke FinishGoodStock
            $fgId = $spk->salesOrder?->itemable_id;
            if ($fgId && (! $spk->salesOrder?->itemable_type || $spk->salesOrder?->itemable_type === FinishGoodItem::class)) {
                $fgStockService = app(FinishGoodStockService::class);
                if ($packaging->jenis_transfer === 'blokir') {
                    $fgStockService->increaseBlockedStock(
                        finishGoodItemId: (int) $fgId,
                        qty: (float) $packaging->grand_total,
                        reference: $packaging,
                        spkId: $spk->id,
                        remarks: 'Hasil Packaging (Blokir) - ' . $packaging->kode_packaging
                    );
                } else {
                    $fgStockService->increaseStock(
                        finishGoodItemId: (int) $fgId,
                        qty: (float) $packaging->grand_total,
                        reference: $packaging,
                        source: 'PACKAGING',
                        spkId: $spk->id,
                        remarks: 'Hasil Packaging Produksi - ' . $packaging->kode_packaging
                    );
                }
            }

            return $packaging;
        });
    }

    /**
     * Update data Packaging dan sinkronkan mutasi FinishGoodStock.
     */
    public function updatePackaging(Packaging $packaging, array $data): Packaging
    {
        return DB::transaction(function () use ($packaging, $data) {
            $oldSpk = $packaging->spk()->with('salesOrder')->first();
            $oldFgId = $oldSpk?->salesOrder?->itemable_id;
            $oldGrandTotal = (float) $packaging->grand_total;
            $oldJenis = $packaging->jenis_transfer;

            $fgStockService = app(FinishGoodStockService::class);

            if ($oldFgId && $oldGrandTotal > 0) {
                if ($oldJenis === 'blokir') {
                    $fgStockService->decreaseBlockedStock((int) $oldFgId, $oldGrandTotal, $packaging, $packaging->spk_id);
                } else {
                    $fgStockService->decreaseStock(
                        finishGoodItemId: (int) $oldFgId,
                        qty: $oldGrandTotal,
                        reference: $packaging,
                        source: 'PACKAGING',
                        spkId: $packaging->spk_id,
                        remarks: 'Rollback Update Packaging - ' . $packaging->kode_packaging,
                        allowNegative: true
                    );
                }
            }

            $packaging->update($data);
            $packaging->refresh();

            $newSpk = $packaging->spk()->with('salesOrder')->first();
            $newFgId = $newSpk?->salesOrder?->itemable_id;
            $newGrandTotal = (float) $packaging->grand_total;

            if ($newFgId && $newGrandTotal > 0) {
                if ($packaging->jenis_transfer === 'blokir') {
                    $fgStockService->increaseBlockedStock(
                        finishGoodItemId: (int) $newFgId,
                        qty: $newGrandTotal,
                        reference: $packaging,
                        spkId: $packaging->spk_id,
                        remarks: 'Update Packaging (Blokir) - ' . $packaging->kode_packaging
                    );
                } else {
                    $fgStockService->increaseStock(
                        finishGoodItemId: (int) $newFgId,
                        qty: $newGrandTotal,
                        reference: $packaging,
                        source: 'PACKAGING',
                        spkId: $packaging->spk_id,
                        remarks: 'Update Packaging Produksi - ' . $packaging->kode_packaging
                    );
                }
            }

            return $packaging;
        });
    }

    /**
     * Hapus packaging dengan proteksi bila stok sudah terkirim.
     */
    public function deletePackaging(Packaging $packaging): void
    {
        DB::transaction(function () use ($packaging) {
            $spk = $packaging->spk()->with('salesOrder')->first();
            if ($spk && $packaging->jenis_transfer !== 'blokir') {
                $currentGood = $spk->total_good_packed;
                $totalShipped = $spk->total_shipped;

                if (($currentGood - $packaging->grand_total) < $totalShipped) {
                    throw ValidationException::withMessages([
                        'packaging' => "Packaging {$packaging->kode_packaging} tidak dapat dihapus karena barang sudah terkirim via Surat Jalan (Total terkirim: {$totalShipped} pcs, sisa stok jika dihapus: " . ($currentGood - $packaging->grand_total) . " pcs).",
                    ]);
                }
            }

            $fgId = $spk?->salesOrder?->itemable_id;
            if ($fgId && (float) $packaging->grand_total > 0) {
                $fgStockService = app(FinishGoodStockService::class);
                if ($packaging->jenis_transfer === 'blokir') {
                    $fgStockService->decreaseBlockedStock((int) $fgId, (float) $packaging->grand_total, $packaging, $packaging->spk_id);
                } else {
                    $fgStockService->decreaseStock(
                        finishGoodItemId: (int) $fgId,
                        qty: (float) $packaging->grand_total,
                        reference: $packaging,
                        source: 'PACKAGING',
                        spkId: $packaging->spk_id,
                        remarks: 'Rollback Hapus Packaging - ' . $packaging->kode_packaging,
                        allowNegative: true
                    );
                }
            }

            $packaging->delete();
        });
    }

    /**
     * Ambil ringkasan progres produksi SPK dari seluruh stasiun kerja.
     */
    public function getProductionProgress(MasterSpk $spk): array
    {
        $spk->loadMissing([
            'salesOrder.customer',
            'salesOrder.itemable',
            'pracetaks',
            'printings',
            'diemakings',
            'finishings',
            'packagings',
            'suratJalans',
        ]);

        $orderQty = (int) ($spk->salesOrder?->qty ?? 0);
        $totalGoodPacked = (int) $spk->packagings
            ->where('jenis_transfer', '!=', 'blokir')
            ->sum('grand_total');

        $totalBlockedPacked = (int) $spk->packagings
            ->where('jenis_transfer', 'blokir')
            ->sum('grand_total');

        $totalShipped = (int) $spk->suratJalans->sum('qty_pengiriman');
        $onHandStock = max(0, $totalGoodPacked - $totalShipped);

        // Agregasi Printing
        $totalPrintingGood = (int) $spk->printings->sum('hasil_baik');
        $totalPrintingWaste = (int) $spk->printings->sum('hasil_rusak') + (int) $spk->printings->sum('semi_waste');

        // Agregasi Die Making
        $totalDiemakingGood = (int) $spk->diemakings->sum('hasil_baik');
        $totalDiemakingWaste = (int) $spk->diemakings->sum('hasil_rusak') + (int) $spk->diemakings->sum('semi_waste');

        // Agregasi Finishing
        $totalFinishingGood = (int) $spk->finishings->sum('hasil_baik');
        $totalFinishingWaste = (int) $spk->finishings->sum('hasil_rusak') + (int) $spk->finishings->sum('semi_waste');

        // Status Pra-Cetak
        $pracetakCount = $spk->pracetaks->count();
        $pracetakStatus = $pracetakCount > 0 ? 'SELESAI' : 'BELUM PROSES';

        $completionRate = $orderQty > 0
            ? round(min(100.0, ($totalGoodPacked / $orderQty) * 100), 1)
            : 0.0;

        $fulfillmentRate = $orderQty > 0
            ? round(min(100.0, ($totalShipped / $orderQty) * 100), 1)
            : 0.0;

        return [
            'no_spk'               => $spk->no_spk,
            'status_spk'           => $spk->status,
            'customer_name'        => $spk->salesOrder?->customer?->nama_customer ?? '-',
            'product_name'         => $spk->salesOrder?->itemable?->nama_barang ?? '-',
            'order_qty'            => $orderQty,
            'pracetak_status'      => $pracetakStatus,
            'pracetak_count'       => $pracetakCount,
            'printing_good'        => $totalPrintingGood,
            'printing_waste'       => $totalPrintingWaste,
            'diemaking_good'       => $totalDiemakingGood,
            'diemaking_waste'      => $totalDiemakingWaste,
            'finishing_good'       => $totalFinishingGood,
            'finishing_waste'      => $totalFinishingWaste,
            'packed_good'          => $totalGoodPacked,
            'packed_blocked'       => $totalBlockedPacked,
            'total_shipped'        => $totalShipped,
            'on_hand_stock'        => $onHandStock,
            'completion_rate'      => $completionRate,
            'fulfillment_rate'     => $fulfillmentRate,
            'is_ready_to_ship'     => $onHandStock > 0,
        ];
    }

    /**
     * Validasi apakah SPK dapat ditandai 'FINISH'.
     */
    public function canCompleteSpk(MasterSpk $spk): bool
    {
        // Minimal sudah ada packaging hasil baik
        return $spk->total_good_packed > 0;
    }

    /**
     * Tandai status SPK menjadi 'FINISH'.
     */
    public function completeSpk(MasterSpk $spk): MasterSpk
    {
        return DB::transaction(function () use ($spk) {
            if (!$this->canCompleteSpk($spk)) {
                throw ValidationException::withMessages([
                    'status' => "SPK {$spk->no_spk} belum dapat diselesaikan karena belum memiliki riwayat packaging hasil baik.",
                ]);
            }

            $spk->update(['status' => 'FINISH']);
            return $spk->fresh();
        });
    }

    /**
     * Kembalikan status SPK menjadi 'ON PROCESS'.
     */
    public function reopenSpk(MasterSpk $spk): MasterSpk
    {
        return DB::transaction(function () use ($spk) {
            $spk->update(['status' => 'ON PROCESS']);
            return $spk->fresh();
        });
    }
}
