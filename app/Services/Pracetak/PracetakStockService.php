<?php

namespace App\Services\Pracetak;

use App\Models\MasterMaterialPlate;
use App\Models\PracetakMaterialMovement;
use App\Models\PracetakMaterialStock;
use App\Models\PracetakMaterialUsage;
use App\Models\PracetakPenerimaan;
use App\Models\PracetakProcess;
use Illuminate\Support\Facades\DB;

class PracetakStockService
{
    /**
     * Hitung fraksi sheet plate berdasarkan luas potongan (mm) × jumlah plate dibagi luas 1 sheet master (mm).
     *
     * @return array{luas_1_plate_cm2: float, luas_terpakai_cm2: float, luas_sheet_cm2: float, qty_sheet_terpakai: float}
     */
    public function calculateSheetFraction(
        ?MasterMaterialPlate $plate,
        float $panjangPotongMm,
        float $lebarPotongMm,
        int $jumlahPlate = 1
    ): array {
        $jumlahPlate = max(1, $jumlahPlate);
        $panjangPotongMm = max(0, $panjangPotongMm);
        $lebarPotongMm = max(0, $lebarPotongMm);

        $luas1PlateCm2 = ($panjangPotongMm * $lebarPotongMm) / 100.0;
        $luasTerpakaiCm2 = $luas1PlateCm2 * $jumlahPlate;

        $luasSheetCm2 = 0.0;
        if ($plate && (float) $plate->panjang_sheet_mm > 0 && (float) $plate->lebar_sheet_mm > 0) {
            $luasSheetCm2 = ((float) $plate->panjang_sheet_mm * (float) $plate->lebar_sheet_mm) / 100.0;
        }

        $qtySheet = $luasSheetCm2 > 0
            ? round($luasTerpakaiCm2 / $luasSheetCm2, 4)
            : 0.0;

        return [
            'luas_1_plate_cm2'   => round($luas1PlateCm2, 2),
            'luas_terpakai_cm2'  => round($luasTerpakaiCm2, 2),
            'luas_sheet_cm2'     => round($luasSheetCm2, 2),
            'qty_sheet_terpakai' => $qtySheet,
        ];
    }

    /**
     * Konfirmasi / Posting transaksi Penerimaan Material Plate ke Stok & Ledger Prepress (IN).
     * Menggunakan Database Transaction dan pessimistic locking untuk mencegah double-posting.
     *
     * @throws \DomainException
     * @throws \Throwable
     */
    public function postPenerimaan(PracetakPenerimaan $penerimaan, ?int $userId = null): PracetakPenerimaan
    {
        return DB::transaction(function () use ($penerimaan, $userId) {
            $header = DB::table('pracetak_penerimaans')
                ->where('id', $penerimaan->id)
                ->lockForUpdate()
                ->first();

            if (! $header) {
                throw new \DomainException('Transaksi penerimaan tidak ditemukan.');
            }

            if ($header->status !== 'draft') {
                throw new \DomainException("Transaksi {$header->nomor_penerimaan} sudah diposting sebelumnya dan tidak dapat diposting ulang.");
            }

            $items = $penerimaan->items()->with('masterPlate')->get();
            if ($items->isEmpty()) {
                throw new \DomainException('Transaksi tidak memiliki item material plate. Tambahkan minimal 1 item.');
            }

            $totalQty = 0;
            $totalNominal = 0;

            foreach ($items as $item) {
                $qty = (float) $item->qty_sheet;
                $harga = (float) $item->harga_satuan;

                if ($qty <= 0) {
                    throw new \DomainException("Kuantitas plate pada item ID {$item->id} harus lebih dari 0.");
                }
                if ($harga < 0) {
                    throw new \DomainException("Harga satuan pada item ID {$item->id} tidak boleh negatif.");
                }

                $subtotal = round($qty * $harga, 2);
                $totalQty += $qty;
                $totalNominal += $subtotal;

                $stock = PracetakMaterialStock::firstOrCreate(
                    ['master_material_plate_id' => $item->master_material_plate_id],
                    [
                        'stok_sheet_utuh'  => 0,
                        'total_masuk'      => 0,
                        'total_keluar'     => 0,
                        'total_nilai_stok' => 0,
                        'harga_terakhir'   => 0,
                    ]
                );

                $stock = PracetakMaterialStock::where('id', $stock->id)->lockForUpdate()->first();

                $saldoSebelum = (float) $stock->stok_sheet_utuh;
                $saldoSetelah = round($saldoSebelum + $qty, 4);

                $stock->stok_sheet_utuh = $saldoSetelah;
                $stock->total_masuk = round((float) $stock->total_masuk + $qty, 4);
                $stock->total_nilai_stok = round((float) $stock->total_nilai_stok + $subtotal, 2);
                $stock->harga_terakhir = $harga;
                $stock->last_movement_at = now();
                $stock->save();

                PracetakMaterialMovement::create([
                    'master_material_plate_id' => $item->master_material_plate_id,
                    'tanggal'                  => $penerimaan->tanggal_terima,
                    'nomor_transaksi'          => $penerimaan->nomor_penerimaan,
                    'jenis_transaksi'          => 'IN',
                    'referensi_type'           => PracetakPenerimaan::class,
                    'referensi_id'             => $penerimaan->id,
                    'qty_masuk'                => $qty,
                    'qty_keluar'               => 0,
                    'saldo_sebelum'            => $saldoSebelum,
                    'saldo_setelah'            => $saldoSetelah,
                    'harga_satuan'             => $harga,
                    'total_nominal'            => $subtotal,
                    'supplier_id'              => $penerimaan->supplier_id,
                    'user_id'                  => $userId,
                    'catatan'                  => $item->catatan ?: ($penerimaan->catatan ?: 'Penerimaan Material Plate'),
                ]);
            }

            $penerimaan->status = 'posted';
            $penerimaan->total_qty = $totalQty;
            $penerimaan->total_nominal = $totalNominal;
            $penerimaan->posted_by = $userId;
            $penerimaan->posted_at = now();
            $penerimaan->save();

            return $penerimaan;
        });
    }

    /**
     * Terima stok Material Plate Mentah secara otomatis dari alur baku PR -> PO -> Penerimaan Barang (LPB).
     * Menambah saldo PracetakMaterialStock, memperbarui Weighted Average Cost berdasarkan harga PO, dan mencatat Ledger IN.
     */
    public function receiveFromLpb(
        MasterMaterialPlate $plate,
        \Illuminate\Database\Eloquent\Model $reference,
        float $qty,
        float $unitPrice,
        ?int $supplierId = null,
        ?int $userId = null,
        ?string $nomorTransaksi = null,
        ?string $tanggal = null,
        ?string $catatan = null
    ): PracetakMaterialStock {
        return DB::transaction(function () use ($plate, $reference, $qty, $unitPrice, $supplierId, $userId, $nomorTransaksi, $tanggal, $catatan) {
            $qty = round(max(0, $qty), 4);
            if ($qty <= 0) {
                throw new \DomainException('Kuantitas penerimaan plate harus lebih dari 0.');
            }

            $harga = $unitPrice > 0 ? round($unitPrice, 2) : round((float) $plate->harga_beli, 2);
            $subtotal = round($qty * $harga, 2);

            $stock = PracetakMaterialStock::firstOrCreate(
                ['master_material_plate_id' => $plate->id],
                [
                    'stok_sheet_utuh'  => 0,
                    'total_masuk'      => 0,
                    'total_keluar'     => 0,
                    'total_nilai_stok' => 0,
                    'harga_terakhir'   => $harga,
                ]
            );

            $stock = PracetakMaterialStock::where('id', $stock->id)->lockForUpdate()->first();

            $saldoSebelum = (float) $stock->stok_sheet_utuh;
            $saldoSetelah = round($saldoSebelum + $qty, 4);

            $stock->stok_sheet_utuh = $saldoSetelah;
            $stock->total_masuk = round((float) $stock->total_masuk + $qty, 4);
            $stock->total_nilai_stok = round((float) $stock->total_nilai_stok + $subtotal, 2);
            $stock->harga_terakhir = $harga;
            $stock->last_movement_at = now();
            $stock->save();

            PracetakMaterialMovement::create([
                'master_material_plate_id' => $plate->id,
                'tanggal'                  => $tanggal ?: now()->toDateString(),
                'nomor_transaksi'          => $nomorTransaksi ?: ('LPB-' . $reference->getKey()),
                'jenis_transaksi'          => 'IN',
                'referensi_type'           => get_class($reference),
                'referensi_id'             => $reference->getKey(),
                'qty_masuk'                => $qty,
                'qty_keluar'               => 0,
                'saldo_sebelum'            => $saldoSebelum,
                'saldo_setelah'            => $saldoSetelah,
                'harga_satuan'             => $harga,
                'total_nominal'            => $subtotal,
                'supplier_id'              => $supplierId ?: $plate->supplier_id,
                'user_id'                  => $userId ?: auth()->id(),
                'catatan'                  => $catatan ?: 'Penerimaan Material Plate via LPB',
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Rollback / pengurangan stok Material Plate Mentah saat edit/hapus LPB atau Retur Eksternal ke supplier.
     */
    public function rollbackFromLpb(
        MasterMaterialPlate $plate,
        \Illuminate\Database\Eloquent\Model $reference,
        float $qty,
        float $unitPrice = 0.0,
        ?int $supplierId = null,
        ?int $userId = null,
        ?string $nomorTransaksi = null,
        ?string $catatan = null,
        string $jenisTransaksi = 'ADJUSTMENT'
    ): PracetakMaterialStock {
        return DB::transaction(function () use ($plate, $reference, $qty, $unitPrice, $supplierId, $userId, $nomorTransaksi, $catatan, $jenisTransaksi) {
            $qty = round(max(0, $qty), 4);
            if ($qty <= 0) {
                throw new \DomainException('Kuantitas pengurangan plate harus lebih dari 0.');
            }

            $stock = PracetakMaterialStock::where('master_material_plate_id', $plate->id)
                ->lockForUpdate()
                ->first();

            if (! $stock || (float) $stock->stok_sheet_utuh < $qty) {
                $avail = $stock ? number_format((float) $stock->stok_sheet_utuh, 4, '.', ',') : '0';
                throw new \DomainException("Saldo stok plate '{$plate->nama_material}' ({$avail} sheet) tidak mencukupi untuk dikurangi {$qty} sheet.");
            }

            $harga = $unitPrice > 0
                ? round($unitPrice, 2)
                : ((float) $stock->harga_rata_rata > 0 ? round((float) $stock->harga_rata_rata, 2) : round((float) $plate->harga_beli, 2));
            $subtotal = round($qty * $harga, 2);

            $saldoSebelum = (float) $stock->stok_sheet_utuh;
            $saldoSetelah = max(0, round($saldoSebelum - $qty, 4));
            $nilaiSetelah = $saldoSetelah <= 0.0001
                ? 0.0
                : max(0, round((float) $stock->total_nilai_stok - $subtotal, 2));

            $stock->stok_sheet_utuh = $saldoSetelah;
            if ($jenisTransaksi === 'ADJUSTMENT') {
                $stock->total_masuk = max(0, round((float) $stock->total_masuk - $qty, 4));
            } else {
                $stock->total_keluar = round((float) $stock->total_keluar + $qty, 4);
            }
            $stock->total_nilai_stok = $nilaiSetelah;
            $stock->last_movement_at = now();
            $stock->save();

            PracetakMaterialMovement::create([
                'master_material_plate_id' => $plate->id,
                'tanggal'                  => now()->toDateString(),
                'nomor_transaksi'          => $nomorTransaksi ?: ('REV-LPB-' . $reference->getKey()),
                'jenis_transaksi'          => $jenisTransaksi,
                'referensi_type'           => get_class($reference),
                'referensi_id'             => $reference->getKey(),
                'qty_masuk'                => 0,
                'qty_keluar'               => $qty,
                'saldo_sebelum'            => $saldoSebelum,
                'saldo_setelah'            => $saldoSetelah,
                'harga_satuan'             => $harga,
                'total_nominal'            => $subtotal,
                'supplier_id'              => $supplierId ?: $plate->supplier_id,
                'user_id'                  => $userId ?: auth()->id(),
                'catatan'                  => $catatan ?: 'Rollback Penerimaan Plate via LPB',
            ]);

            return $stock->fresh();
        });
    }

    /**
     * Posting pemakaian bahan plate (Tahap C Plate Making / Remake Rusak / Prove) — Mutasi OUT.
     * Mendukung pemotongan fraksi sheet desimal (misal 0.4500 sheet) maupun sheet utuh (1.0000 sheet).
     *
     * @throws \DomainException
     * @throws \Throwable
     */
    public function postMaterialUsage(PracetakMaterialUsage $usage, ?int $userId = null): PracetakMaterialUsage
    {
        return DB::transaction(function () use ($usage, $userId) {
            $lockedUsage = PracetakMaterialUsage::where('id', $usage->id)
                ->with(['masterPlate', 'pracetakProcess.spk'])
                ->lockForUpdate()
                ->first();

            if (! $lockedUsage) {
                throw new \DomainException('Data pemakaian material plate tidak ditemukan.');
            }

            if ($lockedUsage->status !== 'draft') {
                throw new \DomainException("Pemakaian material ID #{$lockedUsage->id} berstatus {$lockedUsage->status} dan tidak dapat diposting ulang.");
            }

            $qtyOut = round((float) $lockedUsage->qty_sheet_terpakai, 4);
            if ($qtyOut <= 0) {
                throw new \DomainException('Kuantitas pemakaian sheet plate harus lebih dari 0.');
            }

            $stock = PracetakMaterialStock::where('master_material_plate_id', $lockedUsage->master_material_plate_id)
                ->lockForUpdate()
                ->first();

            $plateName = $lockedUsage->masterPlate?->nama_material ?? "Plate #{$lockedUsage->master_material_plate_id}";

            if (! $stock || (float) $stock->stok_sheet_utuh < $qtyOut) {
                $available = $stock ? number_format((float) $stock->stok_sheet_utuh, 4, '.', ',') : '0';
                throw new \DomainException(
                    "Stok plate '{$plateName}' tidak mencukupi. Tersedia: {$available} sheet, dibutuhkan: "
                    . number_format($qtyOut, 4, '.', ',') . ' sheet.'
                );
            }

            $unitPrice = (float) $stock->harga_rata_rata;
            if ($unitPrice <= 0) {
                $unitPrice = (float) ($lockedUsage->masterPlate?->harga_beli ?? 0);
            }

            $totalCost = round($qtyOut * $unitPrice, 2);
            $saldoSebelum = (float) $stock->stok_sheet_utuh;
            $saldoSetelah = max(0, round($saldoSebelum - $qtyOut, 4));
            $nilaiStokSetelah = $saldoSetelah <= 0.0001
                ? 0.0
                : max(0, round((float) $stock->total_nilai_stok - $totalCost, 2));

            $stock->stok_sheet_utuh = $saldoSetelah;
            $stock->total_keluar = round((float) $stock->total_keluar + $qtyOut, 4);
            $stock->total_nilai_stok = $nilaiStokSetelah;
            $stock->last_movement_at = now();
            $stock->save();

            $process = $lockedUsage->pracetakProcess;
            $spkRef = $process?->spk?->no_spk ?: ('PRE-' . ($process?->id ?? $lockedUsage->id));
            $nomorTransaksi = "OUT-{$spkRef}-U{$lockedUsage->id}";

            $jenisLabel = $lockedUsage->jenis_label;
            $catatan = "{$jenisLabel} ({$lockedUsage->jumlah_plate} plat · {$process?->nama_plat})";
            if (! empty($lockedUsage->alasan_kerusakan)) {
                $catatan .= " | Alasan: {$lockedUsage->alasan_kerusakan}";
            }

            PracetakMaterialMovement::create([
                'master_material_plate_id' => $lockedUsage->master_material_plate_id,
                'tanggal'                  => now()->toDateString(),
                'nomor_transaksi'          => $nomorTransaksi,
                'jenis_transaksi'          => 'OUT',
                'referensi_type'           => PracetakProcess::class,
                'referensi_id'             => $lockedUsage->pracetak_process_id,
                'qty_masuk'                => 0,
                'qty_keluar'               => $qtyOut,
                'saldo_sebelum'            => $saldoSebelum,
                'saldo_setelah'            => $saldoSetelah,
                'harga_satuan'             => $unitPrice,
                'total_nominal'            => $totalCost,
                'supplier_id'              => $lockedUsage->masterPlate?->supplier_id,
                'user_id'                  => $userId,
                'catatan'                  => $catatan,
            ]);

            $lockedUsage->harga_satuan_snapshot = $unitPrice;
            $lockedUsage->total_biaya = $totalCost;
            $lockedUsage->status = 'posted';
            $lockedUsage->posted_by = $userId;
            $lockedUsage->posted_at = now();
            $lockedUsage->save();

            if ($process) {
                $process->material_posted_at = now();
                $process->save();

                app(PracetakCostingService::class)->recalculateProcessCost($process);
            }

            return $lockedUsage;
        });
    }

    /**
     * Posting seluruh baris pemakaian plate berstatus 'draft' milik sebuah Proses Pra Cetak sekaligus.
     *
     * @throws \DomainException
     * @throws \Throwable
     */
    public function postAllDraftUsages(PracetakProcess $process, ?int $userId = null): int
    {
        return DB::transaction(function () use ($process, $userId) {
            $drafts = $process->materialUsages()
                ->where('status', 'draft')
                ->orderBy('id')
                ->get();

            if ($drafts->isEmpty()) {
                throw new \DomainException('Tidak ada baris pemakaian material plate berstatus Draft yang perlu diposting.');
            }

            $postedCount = 0;
            foreach ($drafts as $usage) {
                $this->postMaterialUsage($usage, $userId);
                $postedCount++;
            }

            return $postedCount;
        });
    }

    /**
     * Membalikkan (Reversal / Adjustment IN) pemakaian plate yang sudah terposting jika terjadi kesalahan input.
     * Menjaga audit trail Ledger tetap immutable.
     *
     * @throws \DomainException
     * @throws \Throwable
     */
    public function reverseMaterialUsage(PracetakMaterialUsage $usage, ?int $userId = null, ?string $reason = null): PracetakMaterialUsage
    {
        return DB::transaction(function () use ($usage, $userId, $reason) {
            $lockedUsage = PracetakMaterialUsage::where('id', $usage->id)
                ->with(['masterPlate', 'pracetakProcess.spk'])
                ->lockForUpdate()
                ->first();

            if (! $lockedUsage || $lockedUsage->status !== 'posted') {
                throw new \DomainException('Hanya pemakaian material berstatus Posted yang dapat dilakukan reversal.');
            }

            $qtyBack = round((float) $lockedUsage->qty_sheet_terpakai, 4);
            $unitPrice = (float) $lockedUsage->harga_satuan_snapshot;
            $totalCost = round((float) $lockedUsage->total_biaya, 2);

            $stock = PracetakMaterialStock::firstOrCreate(
                ['master_material_plate_id' => $lockedUsage->master_material_plate_id],
                [
                    'stok_sheet_utuh'  => 0,
                    'total_masuk'      => 0,
                    'total_keluar'     => 0,
                    'total_nilai_stok' => 0,
                    'harga_terakhir'   => $unitPrice,
                ]
            );

            $stock = PracetakMaterialStock::where('id', $stock->id)->lockForUpdate()->first();

            $saldoSebelum = (float) $stock->stok_sheet_utuh;
            $saldoSetelah = round($saldoSebelum + $qtyBack, 4);

            $stock->stok_sheet_utuh = $saldoSetelah;
            $stock->total_keluar = max(0, round((float) $stock->total_keluar - $qtyBack, 4));
            $stock->total_nilai_stok = round((float) $stock->total_nilai_stok + $totalCost, 2);
            $stock->last_movement_at = now();
            $stock->save();

            $process = $lockedUsage->pracetakProcess;
            $spkRef = $process?->spk?->no_spk ?: ('PRE-' . ($process?->id ?? $lockedUsage->id));

            PracetakMaterialMovement::create([
                'master_material_plate_id' => $lockedUsage->master_material_plate_id,
                'tanggal'                  => now()->toDateString(),
                'nomor_transaksi'          => "REV-{$spkRef}-U{$lockedUsage->id}",
                'jenis_transaksi'          => 'ADJUSTMENT',
                'referensi_type'           => PracetakProcess::class,
                'referensi_id'             => $lockedUsage->pracetak_process_id,
                'qty_masuk'                => $qtyBack,
                'qty_keluar'               => 0,
                'saldo_sebelum'            => $saldoSebelum,
                'saldo_setelah'            => $saldoSetelah,
                'harga_satuan'             => $unitPrice,
                'total_nominal'            => $totalCost,
                'supplier_id'              => $lockedUsage->masterPlate?->supplier_id,
                'user_id'                  => $userId,
                'catatan'                  => 'Reversal Pemakaian Plate: ' . ($reason ?: 'Koreksi input operator'),
            ]);

            $lockedUsage->status = 'reversed';
            $lockedUsage->save();

            if ($process) {
                app(PracetakCostingService::class)->recalculateProcessCost($process);
            }

            return $lockedUsage;
        });
    }
}
