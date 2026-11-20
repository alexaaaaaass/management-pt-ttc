<?php

namespace App\Services\Pracetak;

use App\Models\PracetakMaterialMovement;
use App\Models\PracetakMaterialStock;
use App\Models\PracetakPenerimaan;
use Illuminate\Support\Facades\DB;

class PracetakStockService
{
    /**
     * Konfirmasi / Posting transaksi Penerimaan Material Plate ke Stok & Ledger Prepress.
     * Menggunakan Database Transaction dan pessimistic locking untuk mencegah double-posting.
     *
     * @throws \DomainException
     * @throws \Throwable
     */
    public function postPenerimaan(PracetakPenerimaan $penerimaan, ?int $userId = null): PracetakPenerimaan
    {
        return DB::transaction(function () use ($penerimaan, $userId) {
            // Lock header untuk mencegah concurrent post request
            $header = DB::table('pracetak_penerimaans')
                ->where('id', $penerimaan->id)
                ->lockForUpdate()
                ->first();

            if (!$header) {
                throw new \DomainException('Transaksi penerimaan tidak ditemukan.');
            }

            if ($header->status !== 'draft') {
                throw new \DomainException("Transaksi {$header->nomor_penerimaan} sudah diposting sebelumnya dan tidak dapat diposting ulang.");
            }

            // Muat dan validasi items
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

                // Lock record saldo stok plate untuk update
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

                // Lock baris saldo untuk update
                $stock = PracetakMaterialStock::where('id', $stock->id)->lockForUpdate()->first();

                $saldoSebelum = (float) $stock->stok_sheet_utuh;
                $saldoSetelah = $saldoSebelum + $qty;

                // Update saldo stok plate
                $stock->stok_sheet_utuh = $saldoSetelah;
                $stock->total_masuk = (float) $stock->total_masuk + $qty;
                $stock->total_nilai_stok = (float) $stock->total_nilai_stok + $subtotal;
                $stock->harga_terakhir = $harga;
                $stock->last_movement_at = now();
                $stock->save();

                // Catat mutasi IN pada Ledger Histori Prepress
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

            // Update status transaksi menjadi posted
            $penerimaan->status = 'posted';
            $penerimaan->total_qty = $totalQty;
            $penerimaan->total_nominal = $totalNominal;
            $penerimaan->posted_by = $userId;
            $penerimaan->posted_at = now();
            $penerimaan->save();

            return $penerimaan;
        });
    }
}
