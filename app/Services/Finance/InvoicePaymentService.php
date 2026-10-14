<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\InvPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePaymentService
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected TreasuryService $treasuryService
    ) {}

    /**
     * Catat pembayaran baru untuk Invoice
     */
    public function recordPayment(array $data): InvPayment
    {
        return DB::transaction(function () use ($data) {
            $invoiceId = $data['invoice_id'] ?? null;
            if (!$invoiceId) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Invoice harus dipilih.',
                ]);
            }

            /** @var Invoice $invoice */
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            $nominal = (float) ($data['nominal_bayar'] ?? 0);
            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'nominal_bayar' => 'Nominal pembayaran harus lebih besar dari 0.',
                ]);
            }

            if ($nominal > $invoice->sisa_tagihan) {
                throw ValidationException::withMessages([
                    'nominal_bayar' => 'Nominal pembayaran (Rp ' . number_format($nominal, 0, ',', '.') .
                        ') melebihi sisa tagihan (Rp ' . number_format($invoice->sisa_tagihan, 0, ',', '.') . ').',
                ]);
            }

            if (empty($data['status'])) {
                $data['status'] = 'SUCCESS';
            }

            $payment = InvPayment::create($data);

            // Refresh invoice status & balance
            $this->invoiceService->refreshInvoicePayment($invoice);

            // Post mutasi kas/bank ke Treasury
            $this->treasuryService->postInvPayment($payment);

            return $payment;
        });
    }

    /**
     * Update pembayaran yang sudah ada
     */
    public function updatePayment(InvPayment $payment, array $data): InvPayment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment = InvPayment::lockForUpdate()->findOrFail($payment->id);
            $invoice = Invoice::lockForUpdate()->findOrFail($payment->invoice_id);

            if (isset($data['nominal_bayar'])) {
                $newNominal = (float) $data['nominal_bayar'];
                if ($newNominal <= 0) {
                    throw ValidationException::withMessages([
                        'nominal_bayar' => 'Nominal pembayaran harus lebih besar dari 0.',
                    ]);
                }

                // Sisa tagihan saat ini + nominal pembayaran yang lama adalah batas maksimal baru
                $maxAllowed = (float) $invoice->sisa_tagihan + (float) $payment->nominal_bayar;
                if ($newNominal > $maxAllowed) {
                    throw ValidationException::withMessages([
                        'nominal_bayar' => 'Nominal pembayaran (Rp ' . number_format($newNominal, 0, ',', '.') .
                            ') melebihi sisa tagihan yang dapat dibayar (Rp ' . number_format($maxAllowed, 0, ',', '.') . ').',
                    ]);
                }
            }

            $payment->update($data);

            // Refresh invoice status & balance
            $this->invoiceService->refreshInvoicePayment($invoice);

            // Sinkronkan mutasi kas/bank di Treasury
            $this->treasuryService->syncInvPayment($payment);

            return $payment;
        });
    }

    /**
     * Hapus pembayaran dan hitung ulang status invoice
     */
    public function deletePayment(InvPayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $invoice = Invoice::lockForUpdate()->find($payment->invoice_id);

            // Hapus mutasi di Treasury
            $this->treasuryService->deleteInvPayment($payment);

            $deleted = $payment->delete();

            if ($invoice) {
                $this->invoiceService->refreshInvoicePayment($invoice);
            }

            return (bool) $deleted;
        });
    }
}
