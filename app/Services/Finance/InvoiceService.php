<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\SuratJalan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    /**
     * Generate format kode invoice berikutnya: 000001/INV/mY
     */
    public function generateKodeInvoice(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $period = $date->format('mY');

        $latest = Invoice::where('kode_invoice', 'like', "%/INV/{$period}")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($latest && preg_match('/^(\d+)\/INV\//', $latest->kode_invoice, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = Invoice::count() + 1;
        }

        return str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT) . '/INV/' . $period;
    }

    /**
     * Hitung subtotal invoice berdasarkan Surat Jalan dan Sales Order
     */
    public function calculateSubtotal(SuratJalan $suratJalan): float
    {
        $suratJalan->loadMissing([
            'salesOrder.customer',
            'salesOrder.items',
            'items.salesOrderItem',
        ]);

        $so = $suratJalan->salesOrder;
        if (!$so) {
            return 0.0;
        }

        if ($so->mode === 'SPK') {
            return (float) ($suratJalan->qty_pengiriman ?? 0) * (float) ($so->harga_pcs ?? 0);
        }

        if ($so->mode === 'SINGLE_MATERIAL') {
            $item = $so->items->first();
            return (float) ($suratJalan->qty_pengiriman ?? 0) * (float) ($item->harga ?? 0);
        }

        // MULTI_MATERIAL
        $subtotal = 0.0;
        foreach ($suratJalan->items as $detail) {
            $qty = (float) ($detail->qty_kirim ?? 0);
            $harga = (float) ($detail->salesOrderItem?->harga ?? 0);
            $subtotal += ($qty * $harga);
        }

        return $subtotal;
    }

    /**
     * Hitung seluruh rincian nominal dan status invoice
     */
    public function calculateTotals(
        float $subtotal,
        float $diskonPersen = 0.0,
        float $ppnPersen = 0.0,
        float $ongkir = 0.0,
        float $uangMuka = 0.0,
        float $paymentsSum = 0.0
    ): array {
        $diskonPersen = max(0.0, $diskonPersen);
        $ppnPersen = max(0.0, $ppnPersen);
        $ongkir = max(0.0, $ongkir);
        $uangMuka = max(0.0, $uangMuka);
        $paymentsSum = max(0.0, $paymentsSum);

        $diskonNominal = round(($subtotal * $diskonPersen) / 100, 2);
        $ppnNominal = round(($subtotal * $ppnPersen) / 100, 2);

        $grandTotal = round($subtotal - $diskonNominal + $ppnNominal + $ongkir, 2);
        $totalPayment = round($uangMuka + $paymentsSum, 2);
        $sisaTagihan = max(0.0, round($grandTotal - $totalPayment, 2));

        if ($sisaTagihan <= 0.0 && $grandTotal > 0.0) {
            $status = 'Paid';
        } elseif ($totalPayment > 0.0) {
            $status = 'Partial';
        } else {
            $status = 'Unpaid';
        }

        return [
            'subtotal'       => $subtotal,
            'diskon'         => $diskonPersen,
            'diskon_nominal' => $diskonNominal,
            'ppn'            => $ppnPersen,
            'ppn_nominal'    => $ppnNominal,
            'ongkir'         => $ongkir,
            'uang_muka'      => $uangMuka,
            'grand_total'    => $grandTotal,
            'total_payment'  => $totalPayment,
            'sisa_tagihan'   => $sisaTagihan,
            'status'         => $status,
        ];
    }

    /**
     * Sinkronisasi data dan finansial Invoice (digunakan oleh Observer dan Service)
     */
    public function syncInvoiceFinancials(Invoice $invoice): void
    {
        // Hubungkan data dari Surat Jalan & Sales Order jika belum ada
        if ($invoice->surat_jalan_id) {
            $suratJalan = SuratJalan::with('salesOrder')->find($invoice->surat_jalan_id);
            if ($suratJalan && $suratJalan->salesOrder) {
                $invoice->customer_id = $invoice->customer_id ?: $suratJalan->salesOrder->customer_id;
                $invoice->sales_order_id = $invoice->sales_order_id ?: $suratJalan->salesOrder->id;
                $invoice->no_po_customer = $invoice->no_po_customer ?: $suratJalan->salesOrder->no_po_customer;

                if (!isset($invoice->subtotal) || $invoice->subtotal <= 0) {
                    $invoice->subtotal = $this->calculateSubtotal($suratJalan);
                }
            }
        }

        $paymentsSum = 0.0;
        if ($invoice->exists) {
            $paymentsSum = (float) $invoice->payments()->sum('nominal_bayar');
        }

        $totals = $this->calculateTotals(
            subtotal: (float) ($invoice->subtotal ?? 0),
            diskonPersen: (float) ($invoice->diskon ?? 0),
            ppnPersen: (float) ($invoice->ppn ?? 0),
            ongkir: (float) ($invoice->ongkir ?? 0),
            uangMuka: (float) ($invoice->uang_muka ?? 0),
            paymentsSum: $paymentsSum
        );

        $invoice->grand_total = $totals['grand_total'];
        $invoice->total_payment = $totals['total_payment'];
        $invoice->sisa_tagihan = $totals['sisa_tagihan'];
        $invoice->status = $totals['status'];
    }

    /**
     * Buat Invoice baru
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $suratJalanId = $data['surat_jalan_id'] ?? null;
            if (!$suratJalanId) {
                throw ValidationException::withMessages([
                    'surat_jalan_id' => 'Surat Jalan harus dipilih.',
                ]);
            }

            // Cegah duplikasi Invoice untuk 1 Surat Jalan
            $exists = Invoice::where('surat_jalan_id', $suratJalanId)->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'surat_jalan_id' => 'Surat Jalan ini sudah memiliki Invoice terkait.',
                ]);
            }

            $suratJalan = SuratJalan::with('salesOrder')->findOrFail($suratJalanId);

            if (empty($data['kode_invoice']) || $data['kode_invoice'] === 'AUTO') {
                $data['kode_invoice'] = $this->generateKodeInvoice();
            }

            // Auto-fill dari Surat Jalan jika kosong
            $data['customer_id'] = $data['customer_id'] ?? $suratJalan->salesOrder?->customer_id;
            $data['sales_order_id'] = $data['sales_order_id'] ?? $suratJalan->salesOrder?->id;
            $data['no_po_customer'] = $data['no_po_customer'] ?? $suratJalan->salesOrder?->no_po_customer;

            if (!isset($data['subtotal']) || $data['subtotal'] <= 0) {
                $data['subtotal'] = $this->calculateSubtotal($suratJalan);
            }

            $totals = $this->calculateTotals(
                subtotal: (float) ($data['subtotal'] ?? 0),
                diskonPersen: (float) ($data['diskon'] ?? 0),
                ppnPersen: (float) ($data['ppn'] ?? 0),
                ongkir: (float) ($data['ongkir'] ?? 0),
                uangMuka: (float) ($data['uang_muka'] ?? 0),
                paymentsSum: 0.0
            );

            $data['grand_total'] = $totals['grand_total'];
            $data['total_payment'] = $totals['total_payment'];
            $data['sisa_tagihan'] = $totals['sisa_tagihan'];
            $data['status'] = $totals['status'];

            return Invoice::create($data);
        });
    }

    /**
     * Update invoice
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice->fill($data);
            $this->syncInvoiceFinancials($invoice);
            $invoice->save();

            return $invoice;
        });
    }

    /**
     * Refresh payment totals on an invoice
     */
    public function refreshInvoicePayment(Invoice $invoice): void
    {
        $paymentsSum = (float) $invoice->payments()->sum('nominal_bayar');
        $uangMuka = (float) ($invoice->uang_muka ?? 0);
        $totalPayment = round($uangMuka + $paymentsSum, 2);
        $grandTotal = (float) $invoice->grand_total;
        $sisaTagihan = max(0.0, round($grandTotal - $totalPayment, 2));

        $invoice->total_payment = $totalPayment;
        $invoice->sisa_tagihan = $sisaTagihan;

        if ($sisaTagihan <= 0.0 && $grandTotal > 0.0) {
            $invoice->status = 'Paid';
        } elseif ($totalPayment > 0.0) {
            $invoice->status = 'Partial';
        } else {
            $invoice->status = 'Unpaid';
        }

        $invoice->saveQuietly();
    }

    /**
     * Hapus invoice dengan proteksi pembayaran
     */
    public function deleteInvoice(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            if ($invoice->payments()->exists()) {
                throw new \Exception('Tidak dapat menghapus Invoice karena sudah memiliki riwayat pembayaran. Hapus riwayat pembayaran terlebih dahulu.');
            }

            return (bool) $invoice->delete();
        });
    }

    /**
     * Cek apakah invoice boleh dihapus
     */
    public function canDelete(Invoice $invoice): bool
    {
        return !$invoice->payments()->exists();
    }
}
