<?php

namespace App\Services\Production;

use App\Models\Diemaking;
use App\Models\MasterFinishing;
use App\Models\MasterSpk;
use App\Models\Packaging;
use App\Models\PracetakProcess;
use App\Models\Printing;
use App\Models\SalesOrder;
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

            // Salin spesifikasi produk dari FinishGoodItem jika ada
            $fg = $salesOrder->itemable;
            if ($fg instanceof \App\Models\FinishGoodItem) {
                $data['up_satu'] = $data['up_satu'] ?? $fg->up_satu;
                $data['up_dua'] = $data['up_dua'] ?? $fg->up_dua;
                $data['up_tiga'] = $data['up_tiga'] ?? $fg->up_tiga;
                $data['ukuran_potong'] = $data['ukuran_potong'] ?? $fg->ukuran_potong;
                $data['ukuran_cetak'] = $data['ukuran_cetak'] ?? $fg->ukuran_cetak;
                $data['spesifikasi_kertas'] = $data['spesifikasi_kertas'] ?? $fg->spesifikasi_kertas;
            }

            if (empty($data['status'])) {
                $data['status'] = 'ON PROCESS';
            }

            return MasterSpk::create($data);
        });
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

            if ($spk->finishings()->exists() || $spk->printings()->exists() || $spk->pracetaks()->exists()) {
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

            return $packaging;
        });
    }

    /**
     * Hapus packaging dengan proteksi bila stok sudah terkirim.
     */
    public function deletePackaging(Packaging $packaging): void
    {
        DB::transaction(function () use ($packaging) {
            $spk = $packaging->spk;
            if ($spk && $packaging->jenis_transfer !== 'blokir') {
                $currentGood = $spk->total_good_packed;
                $totalShipped = $spk->total_shipped;

                if (($currentGood - $packaging->grand_total) < $totalShipped) {
                    throw ValidationException::withMessages([
                        'packaging' => "Packaging {$packaging->kode_packaging} tidak dapat dihapus karena barang sudah terkirim via Surat Jalan (Total terkirim: {$totalShipped} pcs, sisa stok jika dihapus: " . ($currentGood - $packaging->grand_total) . " pcs).",
                    ]);
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
