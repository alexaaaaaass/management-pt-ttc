<?php

namespace App\Services\Finance;

use App\Models\PenerimaanBarang;
use App\Models\PoBilling;
use App\Models\PoBillingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoBillingService
{
    /**
     * Generate Kode Tagihan Supplier (BILL-Ymd-001)
     */
    public function generateKodeTagihan(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $dateStr = $date->format('Ymd');

        $last = PoBilling::where('kode_tagihan', 'like', "BILL-{$dateStr}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/BILL-' . $dateStr . '-(\d+)$/', $last->kode_tagihan, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = PoBilling::whereDate('created_at', $date->toDateString())->count();
            $nextNumber = $count + 1;
        }

        return "BILL-{$dateStr}-" . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Kalkulasi total barang dan grand total tagihan
     */
    public function calculateTotals(
        array $items,
        float $ppn = 0.0,
        float $ongkir = 0.0,
        float $dp = 0.0
    ): array {
        $totalBarang = 0.0;

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $harga = (float) ($item['harga'] ?? 0);
            $diskon = (float) ($item['diskon'] ?? 0);

            $subtotal = ($qty * $harga) - (($qty * $harga) * $diskon / 100);
            $totalBarang += $subtotal;
        }

        $grandTotal = max(0.0, round($totalBarang + $ppn + $ongkir - $dp, 2));

        return [
            'total_barang' => round($totalBarang, 2),
            'ppn'          => round($ppn, 2),
            'ongkir'       => round($ongkir, 2),
            'dp'           => round($dp, 2),
            'grand_total'  => $grandTotal,
        ];
    }

    /**
     * Buat Tagihan PO Billing baru dari LPB (Penerimaan Barang)
     */
    public function createBilling(array $data): PoBilling
    {
        return DB::transaction(function () use ($data) {
            $lpbId = $data['penerimaan_barang_id'] ?? null;
            if (!$lpbId) {
                throw ValidationException::withMessages([
                    'penerimaan_barang_id' => 'Penerimaan Barang (LPB) harus dipilih.',
                ]);
            }

            // Cegah duplikasi tagihan untuk 1 LPB
            $exists = PoBilling::where('penerimaan_barang_id', $lpbId)->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'penerimaan_barang_id' => 'Penerimaan Barang ini sudah memiliki tagihan PO Billing.',
                ]);
            }

            $lpb = PenerimaanBarang::with('purchaseOrder')->findOrFail($lpbId);

            if (empty($data['kode_tagihan']) || $data['kode_tagihan'] === 'AUTO') {
                $data['kode_tagihan'] = $this->generateKodeTagihan();
            }

            $data['purchase_order_id'] = $data['purchase_order_id'] ?? $lpb->purchase_order_id;
            $data['supplier_id'] = $data['supplier_id'] ?? $lpb->purchaseOrder?->supplier_id;

            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $totals = $this->calculateTotals(
                items: $itemsData,
                ppn: (float) ($data['ppn'] ?? 0),
                ongkir: (float) ($data['ongkir'] ?? 0),
                dp: (float) ($data['dp'] ?? 0)
            );

            $data['total_barang'] = $totals['total_barang'];
            $data['grand_total'] = $totals['grand_total'];

            /** @var PoBilling $billing */
            $billing = PoBilling::create($data);

            foreach ($itemsData as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga'] ?? 0);
                $diskon = (float) ($item['diskon'] ?? 0);
                $subtotal = ($qty * $harga) - (($qty * $harga) * $diskon / 100);

                $item['subtotal'] = round($subtotal, 2);
                $billing->items()->create($item);
            }

            return $billing->load('items');
        });
    }

    /**
     * Update data tagihan supplier
     */
    public function updateBilling(PoBilling $billing, array $data): PoBilling
    {
        return DB::transaction(function () use ($billing, $data) {
            $itemsData = $data['items'] ?? null;
            unset($data['items']);

            if ($itemsData !== null) {
                $totals = $this->calculateTotals(
                    items: $itemsData,
                    ppn: (float) ($data['ppn'] ?? $billing->ppn),
                    ongkir: (float) ($data['ongkir'] ?? $billing->ongkir),
                    dp: (float) ($data['dp'] ?? $billing->dp)
                );

                $data['total_barang'] = $totals['total_barang'];
                $data['grand_total'] = $totals['grand_total'];

                $billing->update($data);

                $billing->items()->delete();
                foreach ($itemsData as $item) {
                    $qty = (float) ($item['qty'] ?? 0);
                    $harga = (float) ($item['harga'] ?? 0);
                    $diskon = (float) ($item['diskon'] ?? 0);
                    $subtotal = ($qty * $harga) - (($qty * $harga) * $diskon / 100);

                    $item['subtotal'] = round($subtotal, 2);
                    $billing->items()->create($item);
                }
            } else {
                $billing->update($data);
            }

            return $billing->load('items');
        });
    }

    /**
     * Hapus tagihan dengan proteksi riwayat pembayaran
     */
    public function deleteBilling(PoBilling $billing): bool
    {
        return DB::transaction(function () use ($billing) {
            if ($billing->payments()->exists()) {
                throw new \Exception('Tidak dapat menghapus Tagihan Supplier karena sudah memiliki riwayat pembayaran.');
            }

            $billing->items()->delete();
            return (bool) $billing->delete();
        });
    }

    /**
     * Cek apakah billing dapat dihapus
     */
    public function canDelete(PoBilling $billing): bool
    {
        return ! $billing->payments()->exists();
    }
}
