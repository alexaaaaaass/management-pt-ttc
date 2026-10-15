<?php

namespace App\Services\Finance;

use App\Models\PoBilling;
use App\Models\PoBillPay;
use App\Models\PoBillPayItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoBillPayService
{
    public function __construct(
        protected TreasuryService $treasuryService
    ) {}

    /**
     * Generate Kode Pembayaran Tagihan Supplier (PAY-Ymd-001)
     */
    public function generateKodePembayaran(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $dateStr = $date->format('Ymd');

        $last = PoBillPay::where('kode_pembayaran', 'like', "PAY-{$dateStr}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/PAY-' . $dateStr . '-(\d+)$/', $last->kode_pembayaran, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $count = PoBillPay::whereDate('created_at', $date->toDateString())->count();
            $nextNumber = $count + 1;
        }

        return "PAY-{$dateStr}-" . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Catat pembayaran baru untuk tagihan supplier
     */
    public function recordPayment(array $data): PoBillPay
    {
        return DB::transaction(function () use ($data) {
            $billingId = $data['po_billing_id'] ?? null;
            if (!$billingId) {
                throw ValidationException::withMessages([
                    'po_billing_id' => 'Tagihan PO Billing harus dipilih.',
                ]);
            }

            /** @var PoBilling $billing */
            $billing = PoBilling::lockForUpdate()->findOrFail($billingId);

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw ValidationException::withMessages([
                    'items' => 'Rincian pembayaran tidak boleh kosong.',
                ]);
            }

            $totalBayar = 0.0;
            foreach ($itemsData as $item) {
                $totalBayar += (float) ($item['nominal_pembayaran'] ?? 0);
            }

            if ($totalBayar <= 0) {
                throw ValidationException::withMessages([
                    'total_pembayaran' => 'Total pembayaran harus lebih besar dari 0.',
                ]);
            }

            $sisaTagihan = $billing->sisa_tagihan;
            if ($totalBayar > $sisaTagihan) {
                throw ValidationException::withMessages([
                    'total_pembayaran' => 'Total pembayaran (Rp ' . number_format($totalBayar, 0, ',', '.') .
                        ') melebihi sisa tagihan supplier (Rp ' . number_format($sisaTagihan, 0, ',', '.') . ').',
                ]);
            }

            if (empty($data['kode_pembayaran']) || $data['kode_pembayaran'] === 'AUTO') {
                $data['kode_pembayaran'] = $this->generateKodePembayaran();
            }

            $data['total_tagihan'] = $sisaTagihan;
            $data['total_pembayaran'] = round($totalBayar, 2);
            unset($data['items']);

            /** @var PoBillPay $payment */
            $payment = PoBillPay::create($data);

            foreach ($itemsData as $item) {
                $payment->items()->create($item);
            }

            $payment->load('items');

            // Post mutasi kas/bank ke Treasury
            $this->treasuryService->postPoBillPay($payment);

            return $payment;
        });
    }

    /**
     * Update pembayaran tagihan supplier
     */
    public function updatePayment(PoBillPay $payment, array $data): PoBillPay
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment = PoBillPay::lockForUpdate()->findOrFail($payment->id);
            /** @var PoBilling $billing */
            $billing = PoBilling::lockForUpdate()->findOrFail($payment->po_billing_id);

            $itemsData = $data['items'] ?? null;
            unset($data['items']);

            if ($itemsData !== null) {
                $totalBayar = 0.0;
                foreach ($itemsData as $item) {
                    $totalBayar += (float) ($item['nominal_pembayaran'] ?? 0);
                }

                if ($totalBayar <= 0) {
                    throw ValidationException::withMessages([
                        'total_pembayaran' => 'Total pembayaran harus lebih besar dari 0.',
                    ]);
                }

                $maxAllowed = (float) $billing->sisa_tagihan + (float) $payment->total_pembayaran;
                if ($totalBayar > $maxAllowed) {
                    throw ValidationException::withMessages([
                        'total_pembayaran' => 'Total pembayaran (Rp ' . number_format($totalBayar, 0, ',', '.') .
                            ') melebihi sisa tagihan yang dapat dibayar (Rp ' . number_format($maxAllowed, 0, ',', '.') . ').',
                    ]);
                }

                $data['total_pembayaran'] = round($totalBayar, 2);
                $payment->update($data);

                $payment->items()->delete();
                foreach ($itemsData as $item) {
                    $payment->items()->create($item);
                }
            } else {
                $payment->update($data);
            }

            $payment->load('items');

            // Sinkronkan mutasi kas/bank di Treasury
            $this->treasuryService->syncPoBillPay($payment);

            return $payment;
        });
    }

    /**
     * Hapus pembayaran tagihan supplier
     */
    public function deletePayment(PoBillPay $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            // Hapus mutasi di Treasury
            $this->treasuryService->deletePoBillPay($payment);

            $payment->items()->delete();
            return (bool) $payment->delete();
        });
    }
}
