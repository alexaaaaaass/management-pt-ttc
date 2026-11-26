<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\MasterCOA;
use App\Models\SuratJalan;
use App\Models\TransKas;
use App\Models\TransKasBank;
use App\Support\MoneyHelper;
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

    public function generateInvoiceCode(?Carbon $date = null): string
    {
        return $this->generateKodeInvoice($date);
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
     * Siapkan nilai otomatis (Subtotal, Ongkir, Potongan/Diskon SO/OC, dan Riwayat Uang Muka/DP)
     * saat Surat Jalan dipilih pada form Invoice.
     */
    public function resolveAutofillFromSuratJalan(SuratJalan $suratJalan, ?int $ignoreInvoiceId = null): array
    {
        $suratJalan->loadMissing([
            'salesOrder.customer',
            'salesOrder.items',
            'salesOrder.orderConfirmation.items',
            'salesOrder.orderConfirmation.salesOrders.items',
            'items.salesOrderItem',
        ]);

        $subtotal = $this->calculateSubtotal($suratJalan);
        $so = $suratJalan->salesOrder;
        $oc = $so?->orderConfirmation;

        $result = [
            'subtotal'               => $subtotal,
            'ongkir'                 => 0.0,
            'ongkir_note'            => null,
            'potongan'               => 0.0,
            'potongan_note'          => null,
            'uang_muka'              => 0.0,
            'uang_muka_note'         => null,
            'last_customer_dp'       => 0.0,
            'last_customer_dp_label' => null,
        ];

        if (! $so) {
            return $result;
        }

        // 1. Ongkos Kirim dari Sales Order (hindari penagihan ganda untuk pengiriman parsial)
        $soOngkir = max(0.0, (float) ($so->harga_kirim ?? 0));
        if ($soOngkir > 0) {
            $claimedOngkir = (float) Invoice::where('sales_order_id', $so->id)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->sum('ongkir');

            $remainingOngkir = max(0.0, round($soOngkir - $claimedOngkir, 0));
            $result['ongkir'] = $remainingOngkir;

            if ($remainingOngkir > 0) {
                $result['ongkir_note'] = sprintf(
                    'Otomatis dari Ongkir SO %s (Rp %s) — dapat diubah manual.',
                    $so->no_sales_order ?: '#' . $so->id,
                    number_format($remainingOngkir, 0, ',', '.')
                );
            } else {
                $result['ongkir_note'] = sprintf(
                    'Ongkir SO %s (Rp %s) sudah ditagihkan pada invoice sebelumnya.',
                    $so->no_sales_order ?: '#' . $so->id,
                    number_format($soOngkir, 0, ',', '.')
                );
            }
        }

        // 2. Diskon / Potongan (Rp) dari Sales Order / Order Confirmation
        $soQty = $so->order_qty > 0 ? (float) $so->order_qty : (float) ($so->qty ?? 0);
        $sjQty = (float) ($suratJalan->qty_pengiriman > 0
            ? $suratJalan->qty_pengiriman
            : $suratJalan->items->sum('qty_kirim'));

        $soDiscount = max(0.0, (float) ($so->diskon ?? 0));
        $ocDiscount = max(0.0, (float) ($oc?->diskon ?? 0));

        $ocItemCount = $oc ? max($oc->items->count(), $oc->salesOrders->count()) : 0;
        $isSharedMultiItemOcDiscount = $oc
            && $ocDiscount > 0
            && $ocItemCount > 1
            && ($soDiscount <= 0.0 || abs($soDiscount - $ocDiscount) < 1.0);

        if ($isSharedMultiItemOcDiscount) {
            $ocSoIds = $oc->salesOrders->pluck('id')->push($so->id)->unique()->values()->all();

            $claimedOcPotongan = (float) Invoice::whereIn('sales_order_id', $ocSoIds)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->sum('potongan');

            $alreadyInvoicedOcSubtotal = (float) Invoice::whereIn('sales_order_id', $ocSoIds)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->sum('subtotal');

            $ocBaseSubtotal = 0.0;
            foreach ($oc->salesOrders as $ocSo) {
                if ($ocSo->mode === 'SPK') {
                    $ocBaseSubtotal += ((float) ($ocSo->qty ?? 0) * (float) ($ocSo->harga_pcs ?? 0));
                } else {
                    $ocBaseSubtotal += (float) $ocSo->items->sum(fn ($it) => (float) ($it->qty ?? 0) * (float) ($it->harga ?? 0));
                }
            }
            if ($ocBaseSubtotal <= 0) {
                $ocBaseSubtotal = (float) ($oc->subtotal ?? 0);
            }

            $remainingOcDiscount = max(0.0, round($ocDiscount - $claimedOcPotongan, 0));
            $ratio = $ocBaseSubtotal > 0 ? min(1.0, $subtotal / $ocBaseSubtotal) : 1.0;

            if ($remainingOcDiscount <= 0) {
                $potongan = 0.0;
                $result['potongan_note'] = sprintf(
                    'Seluruh diskon OC %s (Rp %s) sudah dipakai di invoice sebelumnya.',
                    $oc->doc_no ?: '#' . $oc->id,
                    number_format($ocDiscount, 0, ',', '.')
                );
            } else {
                $isFinalOcShipment = $ocBaseSubtotal > 0
                    && ($alreadyInvoicedOcSubtotal + $subtotal) >= ($ocBaseSubtotal - 1.0);

                $potongan = $isFinalOcShipment
                    ? $remainingOcDiscount
                    : min($remainingOcDiscount, round($ocDiscount * $ratio, 0));
                $potongan = min($potongan, $subtotal);

                $result['potongan'] = $potongan;
                $result['potongan_note'] = sprintf(
                    'Proporsional dari Diskon OC %s (Rp %s untuk %d item; porsi %s%% = Rp %s). Dapat diubah manual.',
                    $oc->doc_no ?: '#' . $oc->id,
                    number_format($ocDiscount, 0, ',', '.'),
                    $ocItemCount,
                    number_format($ratio * 100, 1, ',', '.'),
                    number_format($potongan, 0, ',', '.')
                );
            }
        } else {
            $baseSoDiscount = $soDiscount > 0 ? $soDiscount : ($ocItemCount <= 1 ? $ocDiscount : 0.0);

            if ($baseSoDiscount > 0) {
                $claimedSoPotongan = (float) Invoice::where('sales_order_id', $so->id)
                    ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                    ->sum('potongan');

                $alreadyInvoicedSoSubtotal = (float) Invoice::where('sales_order_id', $so->id)
                    ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                    ->sum('subtotal');

                $soTotalValue = $so->mode === 'SPK'
                    ? ((float) ($so->qty ?? 0) * (float) ($so->harga_pcs ?? 0))
                    : (float) $so->items->sum(fn ($it) => (float) ($it->qty ?? 0) * (float) ($it->harga ?? 0));

                $remainingSoDiscount = max(0.0, round($baseSoDiscount - $claimedSoPotongan, 0));

                if ($remainingSoDiscount <= 0) {
                    $result['potongan'] = 0.0;
                    $result['potongan_note'] = sprintf(
                        'Diskon SO %s (Rp %s) sudah terpakai pada invoice sebelumnya.',
                        $so->no_sales_order ?: '#' . $so->id,
                        number_format($baseSoDiscount, 0, ',', '.')
                    );
                } else {
                    $isPartialShipment = $soQty > 0 && $sjQty > 0 && $sjQty < $soQty
                        && ($soTotalValue <= 0 || ($alreadyInvoicedSoSubtotal + $subtotal) < ($soTotalValue - 1.0));

                    $potongan = $isPartialShipment
                        ? min($remainingSoDiscount, round($baseSoDiscount * ($sjQty / $soQty), 0))
                        : $remainingSoDiscount;
                    $potongan = min($potongan, $subtotal);

                    $result['potongan'] = $potongan;
                    $result['potongan_note'] = $isPartialShipment
                        ? sprintf(
                            'Proporsional pengiriman dari Diskon SO %s (total Rp %s, porsi SJ ini Rp %s). Dapat diubah manual.',
                            $so->no_sales_order ?: '#' . $so->id,
                            number_format($baseSoDiscount, 0, ',', '.'),
                            number_format($potongan, 0, ',', '.')
                        )
                        : sprintf(
                            'Otomatis dari Diskon SO %s (Rp %s). Dapat diubah manual.',
                            $so->no_sales_order ?: '#' . $so->id,
                            number_format($potongan, 0, ',', '.')
                        );
                }
            }
        }

        // 3. Uang Muka (DP) otomatis dari riwayat Kas Masuk / Bank Masuk & riwayat Invoice sebelumnya
        $estimatedNetTotal = max(0.0, $subtotal - $result['potongan'] + $result['ongkir']);
        $dpResolution = $this->resolveCustomerDpHistory($so, $oc, $estimatedNetTotal, $ignoreInvoiceId);
        $result['uang_muka'] = $dpResolution['uang_muka'];
        $result['uang_muka_note'] = $dpResolution['uang_muka_note'];
        $result['last_customer_dp'] = $dpResolution['last_customer_dp'];
        $result['last_customer_dp_label'] = $dpResolution['last_customer_dp_label'];

        return $result;
    }

    /**
     * Deteksi riwayat Uang Muka (DP) yang belum terpakai dari Kas Masuk / Bank Masuk
     * atau riwayat DP invoice sebelumnya untuk customer/order terkait.
     */
    protected function resolveCustomerDpHistory(
        \App\Models\SalesOrder $so,
        ?\App\Models\OrderConfirmation $oc,
        float $maxApplicableTotal,
        ?int $ignoreInvoiceId = null
    ): array {
        $customerId = $so->customer_id;

        $dpCoaIds = MasterCOA::query()
            ->where(function ($q) {
                $q->whereIn('kode_akun', ['201.501', '201.502'])
                    ->orWhere('account_class', 'Current - Deposit')
                    ->orWhere('nama_akun', 'like', '%UANG MUKA PENJUALAN%');
            })
            ->pluck('id')
            ->all();

        $orderTokens = array_values(array_filter(array_unique([
            trim((string) ($so->no_sales_order ?? '')),
            trim((string) ($oc?->order_id ?? '')),
            trim((string) ($oc?->doc_no ?? '')),
            trim((string) ($so->no_po_customer ?? '')),
        ]), fn ($token) => $token !== '' && $token !== '-'));

        $dpRegex = '/\b(dp|uang\s*muka|down\s*payment|panjar)\b/i';
        $matchesOrderToken = function (string $text) use ($orderTokens): bool {
            foreach ($orderTokens as $token) {
                if (stripos($text, $token) !== false) {
                    return true;
                }
            }
            return false;
        };

        $orderSpecificReceipts = collect();
        $customerDpReceipts = collect();

        if ($customerId || ! empty($orderTokens)) {
            $bankRows = TransKasBank::query()
                ->whereIn('tipe_transaksi', ['BANK_MASUK', 'MASUK', 'masuk'])
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereNotIn('status', ['VOID', 'CANCELLED', 'REJECTED']);
                })
                ->where(function ($q) use ($customerId, $orderTokens) {
                    if ($customerId) {
                        $q->where('customer_id', $customerId);
                    }
                    foreach ($orderTokens as $token) {
                        $q->orWhere('keterangan', 'like', '%' . $token . '%');
                    }
                })
                ->orderByDesc('tanggal_transaksi')
                ->orderByDesc('id')
                ->get(['id', 'kode_transaksi', 'tanggal_transaksi', 'account_lawan_id', 'customer_id', 'nominal', 'keterangan']);

            foreach ($bankRows as $row) {
                $ket = (string) ($row->keterangan ?? '');
                $isDpCoa = in_array((int) $row->account_lawan_id, $dpCoaIds, true);
                $isDpText = (bool) preg_match($dpRegex, $ket);
                $isOrderMatch = $matchesOrderToken($ket);

                if (! $isDpCoa && ! $isDpText) {
                    continue;
                }

                $entry = [
                    'kode'    => $row->kode_transaksi ?: ('BANK-' . $row->id),
                    'tanggal' => $row->tanggal_transaksi ? Carbon::parse($row->tanggal_transaksi)->format('d/m/Y') : '-',
                    'nominal' => (float) ($row->nominal ?? 0),
                ];

                if ($isOrderMatch) {
                    $orderSpecificReceipts->push($entry);
                }
                if ($customerId && (int) $row->customer_id === (int) $customerId) {
                    $customerDpReceipts->push($entry);
                }
            }

            $kasRows = TransKas::query()
                ->whereIn('tipe_transaksi', ['KAS_MASUK', 'MASUK', 'masuk'])
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereNotIn('status', ['VOID', 'CANCELLED', 'REJECTED']);
                })
                ->where(function ($q) use ($customerId, $orderTokens) {
                    if ($customerId) {
                        $q->where('customer_id', $customerId);
                    }
                    foreach ($orderTokens as $token) {
                        $q->orWhere('keterangan', 'like', '%' . $token . '%');
                    }
                })
                ->orderByDesc('tanggal_transaksi')
                ->orderByDesc('id')
                ->get(['id', 'kode_transaksi', 'tanggal_transaksi', 'account_bank_id', 'customer_id', 'nominal', 'keterangan']);

            foreach ($kasRows as $row) {
                $ket = (string) ($row->keterangan ?? '');
                $isDpCoa = in_array((int) $row->account_bank_id, $dpCoaIds, true);
                $isDpText = (bool) preg_match($dpRegex, $ket);
                $isOrderMatch = $matchesOrderToken($ket);

                if (! $isDpCoa && ! $isDpText) {
                    continue;
                }

                $entry = [
                    'kode'    => $row->kode_transaksi ?: ('KAS-' . $row->id),
                    'tanggal' => $row->tanggal_transaksi ? Carbon::parse($row->tanggal_transaksi)->format('d/m/Y') : '-',
                    'nominal' => (float) ($row->nominal ?? 0),
                ];

                if ($isOrderMatch) {
                    $orderSpecificReceipts->push($entry);
                }
                if ($customerId && (int) $row->customer_id === (int) $customerId) {
                    $customerDpReceipts->push($entry);
                }
            }
        }

        // Prioritas 1A: DP Kas/Bank yang spesifik menyebut No. SO / OC / PO ini
        if ($orderSpecificReceipts->isNotEmpty()) {
            $ocSoIds = $oc
                ? $oc->salesOrders->pluck('id')->push($so->id)->unique()->values()->all()
                : [$so->id];

            $totalOrderDp = (float) $orderSpecificReceipts->sum('nominal');
            $alreadyAppliedOrderDp = (float) Invoice::whereIn('sales_order_id', $ocSoIds)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->sum('uang_muka');

            $availableDp = max(0.0, round($totalOrderDp - $alreadyAppliedOrderDp, 0));
            $refList = $orderSpecificReceipts->take(2)->map(fn ($r) => "{$r['kode']} ({$r['tanggal']})")->implode(', ');

            if ($availableDp > 0) {
                $applied = $maxApplicableTotal > 0 ? min($availableDp, $maxApplicableTotal) : $availableDp;
                return [
                    'uang_muka'              => $applied,
                    'uang_muka_note'         => sprintf(
                        'Otomatis dari riwayat DP Kas/Bank %s (saldo tersedia Rp %s). Dapat diubah manual.',
                        $refList,
                        number_format($availableDp, 0, ',', '.')
                    ),
                    'last_customer_dp'       => 0.0,
                    'last_customer_dp_label' => null,
                ];
            }
        }

        // Prioritas 1B: Saldo DP Kas/Bank umum milik customer ini yang belum terpakai di invoice
        if ($customerId && $customerDpReceipts->isNotEmpty()) {
            $totalCustDp = (float) $customerDpReceipts->sum('nominal');
            $alreadyAppliedCustDp = (float) Invoice::where('customer_id', $customerId)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->sum('uang_muka');

            $availableDp = max(0.0, round($totalCustDp - $alreadyAppliedCustDp, 0));
            $refList = $customerDpReceipts->take(2)->map(fn ($r) => "{$r['kode']} ({$r['tanggal']})")->implode(', ');

            if ($availableDp > 0) {
                $applied = $maxApplicableTotal > 0 ? min($availableDp, $maxApplicableTotal) : $availableDp;
                return [
                    'uang_muka'              => $applied,
                    'uang_muka_note'         => sprintf(
                        'Otomatis dari saldo DP Kas/Bank customer (%s, sisa Rp %s). Dapat diubah manual.',
                        $refList,
                        number_format($availableDp, 0, ',', '.')
                    ),
                    'last_customer_dp'       => 0.0,
                    'last_customer_dp_label' => null,
                ];
            }
        }

        // Prioritas 2: Cek apakah SO ini sudah pernah memakai DP di invoice sebelumnya (cegah pemotongan ganda)
        $priorSoInvoice = Invoice::where('sales_order_id', $so->id)
            ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
            ->where('uang_muka', '>', 0)
            ->latest('id')
            ->first(['id', 'kode_invoice', 'uang_muka', 'tanggal_invoice']);

        if ($priorSoInvoice) {
            return [
                'uang_muka'              => 0.0,
                'uang_muka_note'         => sprintf(
                    'Uang Muka Rp %s sudah dicatat pada invoice %s untuk SO ini (isi manual jika ada DP tambahan).',
                    number_format((float) $priorSoInvoice->uang_muka, 0, ',', '.'),
                    $priorSoInvoice->kode_invoice
                ),
                'last_customer_dp'       => 0.0,
                'last_customer_dp_label' => null,
            ];
        }

        // Prioritas 3: Cek riwayat DP terakhir dari customer yang sama di invoice sebelumnya (opsi 1-klik)
        if ($customerId) {
            $lastCustomerDpInvoice = Invoice::where('customer_id', $customerId)
                ->when($ignoreInvoiceId, fn ($q) => $q->where('id', '!=', $ignoreInvoiceId))
                ->where('uang_muka', '>', 0)
                ->latest('id')
                ->first(['id', 'kode_invoice', 'uang_muka', 'tanggal_invoice']);

            if ($lastCustomerDpInvoice) {
                $lastNominal = (float) $lastCustomerDpInvoice->uang_muka;
                $tgl = $lastCustomerDpInvoice->tanggal_invoice
                    ? Carbon::parse($lastCustomerDpInvoice->tanggal_invoice)->format('d/m/Y')
                    : '-';

                return [
                    'uang_muka'              => 0.0,
                    'uang_muka_note'         => sprintf(
                        'Riwayat DP terakhir customer ini: Rp %s (%s, %s) — klik tombol di kanan untuk langsung pakai.',
                        number_format($lastNominal, 0, ',', '.'),
                        $lastCustomerDpInvoice->kode_invoice,
                        $tgl
                    ),
                    'last_customer_dp'       => $lastNominal,
                    'last_customer_dp_label' => sprintf(
                        'Ambil DP terakhir: Rp %s (%s)',
                        number_format($lastNominal, 0, ',', '.'),
                        $lastCustomerDpInvoice->kode_invoice
                    ),
                ];
            }
        }

        return [
            'uang_muka'              => 0.0,
            'uang_muka_note'         => 'Belum ada riwayat penerimaan DP Kas/Bank untuk customer/order ini. Isi manual jika ada.',
            'last_customer_dp'       => 0.0,
            'last_customer_dp_label' => null,
        ];
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
        float $paymentsSum = 0.0,
        float $potongan = 0.0
    ): array {
        $diskonPersen = max(0.0, $diskonPersen);
        $potongan = max(0.0, $potongan);
        $ppnPersen = max(0.0, $ppnPersen);
        $ongkir = max(0.0, $ongkir);
        $uangMuka = max(0.0, $uangMuka);
        $paymentsSum = max(0.0, $paymentsSum);

        $diskonNominal = round(($subtotal * $diskonPersen) / 100, 2);
        $totalDiskon = round($diskonNominal + $potongan, 2);
        $ppnNominal = round(($subtotal * $ppnPersen) / 100, 2);

        $grandTotal = max(0.0, round($subtotal - $totalDiskon + $ppnNominal + $ongkir, 2));
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
            'potongan'       => $potongan,
            'total_diskon'   => $totalDiskon,
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
            $suratJalan = SuratJalan::with('salesOrder.orderConfirmation')->find($invoice->surat_jalan_id);
            if ($suratJalan && $suratJalan->salesOrder) {
                $so = $suratJalan->salesOrder;
                $invoice->customer_id = $invoice->customer_id ?: $so->customer_id;
                $invoice->sales_order_id = $invoice->sales_order_id ?: $so->id;
                $invoice->no_po_customer = $invoice->no_po_customer ?: $so->no_po_customer;
                $invoice->order_id = $invoice->order_id
                    ?: ($so->no_sales_order ?: ($so->orderConfirmation?->order_id ?: null));

                if (!isset($invoice->subtotal) || $invoice->subtotal <= 0) {
                    $invoice->subtotal = $this->calculateSubtotal($suratJalan);
                }
            }
        } elseif ($invoice->sales_order_id && empty($invoice->order_id)) {
            $so = \App\Models\SalesOrder::withTrashed()->with('orderConfirmation')->find($invoice->sales_order_id);
            if ($so) {
                $invoice->order_id = $so->no_sales_order ?: ($so->orderConfirmation?->order_id ?: null);
            }
        }

        $paymentsSum = 0.0;
        if ($invoice->exists) {
            $paymentsSum = (float) $invoice->payments()
                ->where('status', 'SUCCESS')
                ->sum('nominal_bayar');
        }

        $totals = $this->calculateTotals(
            subtotal: (float) ($invoice->subtotal ?? 0),
            diskonPersen: (float) ($invoice->diskon ?? 0),
            ppnPersen: (float) ($invoice->ppn ?? 0),
            ongkir: (float) ($invoice->ongkir ?? 0),
            uangMuka: (float) ($invoice->uang_muka ?? 0),
            paymentsSum: $paymentsSum,
            potongan: (float) ($invoice->potongan ?? 0)
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

            $suratJalan = SuratJalan::with('salesOrder.orderConfirmation')->findOrFail($suratJalanId);

            if (empty($data['kode_invoice']) || $data['kode_invoice'] === 'AUTO') {
                $data['kode_invoice'] = $this->generateKodeInvoice();
            }

            // Auto-fill dari Surat Jalan jika kosong
            $so = $suratJalan->salesOrder;
            $data['customer_id'] = $data['customer_id'] ?? $so?->customer_id;
            $data['sales_order_id'] = $data['sales_order_id'] ?? $so?->id;
            $data['no_po_customer'] = $data['no_po_customer'] ?? $so?->no_po_customer;
            $data['order_id'] = ! empty($data['order_id'])
                ? $data['order_id']
                : ($so?->no_sales_order ?: ($so?->orderConfirmation?->order_id ?: null));

            $data['subtotal'] = (float) (MoneyHelper::clean($data['subtotal'] ?? 0) ?? 0);
            $data['potongan'] = (float) (MoneyHelper::clean($data['potongan'] ?? 0) ?? 0);
            $data['ongkir'] = (float) (MoneyHelper::clean($data['ongkir'] ?? 0) ?? 0);
            $data['uang_muka'] = (float) (MoneyHelper::clean($data['uang_muka'] ?? 0) ?? 0);
            $data['diskon'] = (float) ($data['diskon'] ?? 0);
            $data['ppn'] = (float) ($data['ppn'] ?? 0);

            if ($data['subtotal'] <= 0) {
                $data['subtotal'] = $this->calculateSubtotal($suratJalan);
            }

            $totals = $this->calculateTotals(
                subtotal: $data['subtotal'],
                diskonPersen: $data['diskon'],
                ppnPersen: $data['ppn'],
                ongkir: $data['ongkir'],
                uangMuka: $data['uang_muka'],
                paymentsSum: 0.0,
                potongan: $data['potongan']
            );

            $data['grand_total'] = $totals['grand_total'];
            $data['total_payment'] = $totals['total_payment'];
            $data['sisa_tagihan'] = $totals['sisa_tagihan'];
            $data['status'] = $totals['status'];

            $invoice = Invoice::create($data);
            $this->syncDefaultInvoiceItems($invoice);
            app(TreasuryService::class)->syncInvoice($invoice);

            return $invoice;
        });
    }

    /**
     * Update invoice
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            foreach (['subtotal', 'potongan', 'ongkir', 'uang_muka'] as $moneyField) {
                if (array_key_exists($moneyField, $data)) {
                    $data[$moneyField] = (float) (MoneyHelper::clean($data[$moneyField]) ?? 0);
                }
            }

            $invoice->fill($data);
            $this->syncInvoiceFinancials($invoice);
            $invoice->save();
            $this->syncDefaultInvoiceItems($invoice);
            app(TreasuryService::class)->syncInvoice($invoice);

            return $invoice;
        });
    }

    /**
     * Sinkronisasi rincian item invoice otomatis dari Surat Jalan & Sales Order jika belum ada
     */
    public function syncDefaultInvoiceItems(Invoice $invoice): void
    {
        if (! $invoice->exists) {
            return;
        }

        $invoice->loadMissing([
            'items',
            'suratJalan.salesOrder.itemable',
            'suratJalan.salesOrder.orderConfirmation.items',
            'suratJalan.salesOrder.items.material',
            'salesOrder.itemable',
            'salesOrder.orderConfirmation.items',
            'salesOrder.items.material',
        ]);

        $sj = $invoice->suratJalan;
        $so = $invoice->salesOrder ?? $sj?->salesOrder;
        if (! $so) {
            return;
        }

        if ($invoice->items->isEmpty()) {
            if ($so->mode === 'MULTI_MATERIAL' && $so->items->isNotEmpty()) {
                foreach ($so->items as $soItem) {
                    $qty = (float) ($soItem->qty ?? 0);
                    $harga = (float) ($soItem->harga ?? 0);
                    $invoice->items()->create([
                        'item'       => $soItem->material?->nama_master_item ?? 'Material',
                        'jenis'      => $so->effective_jenis ?? '-',
                        'komponen'   => $so->effective_komponen ?? '-',
                        'kertas'     => $so->effective_kertas ?? '-',
                        'finishing'  => $so->effective_finishing ?? '-',
                        'qty'        => $qty,
                        'harga'      => $harga,
                        'total'      => $qty * $harga,
                        'keterangan' => '',
                    ]);
                }
            } else {
                $itemName = $so->product_name;
                if (empty($itemName) || $itemName === '-') {
                    $itemName = $so->itemable?->nama_barang
                        ?? $so->itemable?->nama_master_item
                        ?? $so->kode_material
                        ?? 'Barang Cetakan';
                }

                $qty = (float) ($sj?->qty_pengiriman ?: ($so->qty ?: 1));
                $harga = (float) ($so->harga_pcs ?? 0);
                $subtotal = (float) ($invoice->subtotal ?? 0);
                if ($harga <= 0 && $qty > 0 && $subtotal > 0) {
                    $harga = $subtotal / $qty;
                }
                $total = $subtotal > 0 ? $subtotal : ($qty * $harga);
                $ket = $invoice->catatan ?: ($sj?->keterangan ?: ($so->catatan ?: ''));
                if ($ket === '-') {
                    $ket = '';
                }

                $komponen = $so->effective_komponen ?? ($so->komponen ?? '-');
                if ($komponen !== '-') {
                    $komponen = ucwords(strtolower($komponen));
                }
                $kertas = $so->effective_kertas ?? ($so->kertas ?? '-');
                if ($kertas !== '-') {
                    $kertas = ucwords(strtolower($kertas));
                }

                $invoice->items()->create([
                    'item'       => $itemName,
                    'jenis'      => $so->effective_jenis ?? ($so->jenis ?? '-'),
                    'komponen'   => $komponen,
                    'kertas'     => $kertas,
                    'finishing'  => $so->effective_finishing ?? ($so->finishing ?? '-'),
                    'qty'        => $qty,
                    'harga'      => $harga,
                    'total'      => $total,
                    'keterangan' => $ket,
                ]);
            }
        }
    }

    /**
     * Refresh payment totals on an invoice
     */
    public function refreshInvoicePayment(Invoice $invoice): void
    {
       $paymentsSum = (float) $invoice->payments()
            ->where('status', 'SUCCESS')
            ->sum('nominal_bayar');
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

            app(TreasuryService::class)->deleteInvoice($invoice);

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
