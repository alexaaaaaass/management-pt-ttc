<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->kode_invoice }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 18px 22px 18px 22px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111;
            margin: 0;
            line-height: 1.3;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .header td {
            vertical-align: top;
        }
        .company {
            line-height: 1.45;
            font-size: 10px;
        }
        .logo {
            text-align: right;
        }
        .logo img {
            max-height: 65px;
            width: auto;
            object-fit: contain;
        }
        .double-line {
            border-top: 3px double #000;
            height: 4px;
            margin-top: 8px;
            margin-bottom: 6px;
        }
        .meta td {
            padding: 1.5px 3px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .meta-label {
            width: 55px;
            font-weight: bold;
        }
        .meta-value {
            font-weight: normal;
        }
        .order-label {
            font-weight: bold;
            width: 65px;
        }
        .invoice-title {
            border: 2px solid #000;
            border-bottom: 1.5px solid #000;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            padding: 6px 0;
            letter-spacing: 0.5px;
        }
        .items {
            border: 2px solid #000;
            border-top: 0;
        }
        .items th, .items td {
            border: 1px solid #000;
            padding: 3px 4px;
            height: 17px;
            font-size: 9px;
        }
        .items th {
            background-color: #2f5597;
            color: #fff;
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
        }
        .items td {
            vertical-align: middle;
            color: #2f5597;
        }
        .items tbody tr.striped td {
            background-color: #d9e1f2;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
            white-space: nowrap;
        }
        .bottom {
            margin-top: 4px;
            width: 100%;
            border-collapse: collapse;
        }
        .terms-title {
            font-weight: bold;
            font-style: italic;
            margin-bottom: 3px;
            font-size: 9.5px;
        }
        .terms {
            line-height: 1.45;
            font-size: 9px;
        }
        /* UNIFIED TERBILANG & BANK ACCOUNT BOX MATCHING TTC REFERENCE IMAGE 100% */
        .terbilang-bank-box {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-top: 2px;
        }
        .terbilang-bank-box td.terbilang-cell {
            height: 36px;
            padding: 4px 6px;
            font-size: 9px;
            line-height: 1.4;
            border-bottom: 1.5px solid #000;
            vertical-align: middle;
        }
        .terbilang-bank-box td.bank-cell {
            padding: 4px 6px;
            font-weight: bold;
            font-size: 8.5px;
            line-height: 1.5;
            vertical-align: middle;
        }
        .totals {
            width: 82%;
            margin-left: 2%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 2px 4px;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .total-label {
            width: 48%;
            text-align: right;
            padding-right: 12px;
        }
        .total-curr {
            width: 12%;
            text-align: left;
            white-space: nowrap;
        }
        .total-value {
            width: 40%;
            text-align: right;
            white-space: nowrap;
        }
        .grand td {
            border-top: 3px double #000;
            font-weight: bold;
            padding-top: 3px;
        }
    </style>
</head>
<body>
@php
    $sj = $invoice->suratJalan;
    $so = $invoice->salesOrder
        ?? $sj?->salesOrder
        ?? $sj?->spk?->salesOrder;

    $customer = $invoice->customer
        ?? $so?->customer
        ?? $sj?->salesOrder?->customer;

    if (! $so && $customer) {
        $so = \App\Models\SalesOrder::withTrashed()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->first();
    }

    $customerName = $customer->nama_customer
        ?? $invoice->kepada
        ?? '-';

    $orderId = $invoice->order_id
        ?: ($so?->no_sales_order
        ?: ($sj?->salesOrder?->no_sales_order
        ?: ($so?->kode_sales_order ?: '-')));

    $subtotal = (float) ($invoice->subtotal ?? 0);
    $ongkir = (float) ($invoice->ongkir ?? 0);
    $potongan = (float) ($invoice->potongan ?? (($subtotal * (float) ($invoice->diskon ?? 0)) / 100));
    $grandTotal = (float) ($invoice->grand_total ?? ($subtotal - $potongan + $ongkir));

    // Uang Muka (DP) resmi saat Order Confirmation
    $uangMuka = (float) ($invoice->uang_muka ?? 0);

    // Total akumulasi pembayaran yang sudah diterima
    $totalPaid = (float) (
        $invoice->total_payment > 0
            ? $invoice->total_payment
            : ($uangMuka + ($invoice->payments ? $invoice->payments->sum('nominal_bayar') : 0))
    );
    $sisaTagihan = (float) ($invoice->sisa_tagihan ?? max(0, $grandTotal - $totalPaid));
    $isPaid = (strtolower((string) ($invoice->status ?? '')) === 'paid') || ($totalPaid >= $grandTotal && $grandTotal > 0);

    // Nilai DP yang memotong nilai faktur: hanya uang_muka yang tercatat di awal
    $dp = $uangMuka;

    // Nilai Total faktur: Grand Total dikurangi DP awal jika ada, namun jika tidak ada DP maka Total adalah Grand Total
    $displayTotal = ($dp > 0 && $grandTotal > $dp) ? ($grandTotal - $dp) : $grandTotal;

    $resolvedSoProductName = null;
    if ($so) {
        $resolvedSoProductName = ($so->product_name && $so->product_name !== '-')
            ? $so->product_name
            : ($so->itemable?->nama_barang
                ?? $so->itemable?->nama_master_item
                ?? $so->kode_material
                ?? 'Barang Cetakan');
    }

    $items = collect();
    if ($invoice->items && $invoice->items->isNotEmpty()) {
        foreach ($invoice->items as $invItem) {
            $rawItemName = trim((string) ($invItem->item ?? ''));
            if (($rawItemName === '' || $rawItemName === '-' || strtoupper($rawItemName) === 'KERTAS IVORY') && !empty($resolvedSoProductName)) {
                $rawItemName = $resolvedSoProductName;
            }
            $komp = $invItem->komponen ?? ($so?->effective_komponen ?? '-');
            if ($komp !== '-' && strtoupper($komp) === $komp) {
                $komp = ucwords(strtolower($komp));
            }
            $kertas = $invItem->kertas ?? ($so?->effective_kertas ?? '-');
            if ($kertas !== '-' && strtoupper($kertas) === $kertas) {
                $kertas = ucwords(strtolower($kertas));
            }
            $items->push((object)[
                'item'       => $rawItemName ?: '-',
                'jenis'      => $invItem->jenis ?: ($so?->effective_jenis ?? '-'),
                'komponen'   => $komp ?: '-',
                'kertas'     => $kertas ?: '-',
                'finishing'  => $invItem->finishing ?: ($so?->effective_finishing ?? '-'),
                'qty'        => (float) ($invItem->qty ?? 0),
                'harga'      => (float) ($invItem->harga ?? 0),
                'total'      => (float) ($invItem->total ?? 0),
                'keterangan' => ($invItem->keterangan && $invItem->keterangan !== '-') ? $invItem->keterangan : '',
            ]);
        }
    } elseif ($so) {
        if ($so->mode === 'MULTI_MATERIAL' && $so->items && $so->items->isNotEmpty()) {
            foreach ($so->items as $soItem) {
                $qty = (float) ($soItem->qty ?? 0);
                $harga = (float) ($soItem->harga ?? 0);
                $total = (float) ($soItem->subtotal ?? ($qty * $harga));
                $items->push((object)[
                    'item' => $soItem->material?->nama_master_item ?? 'Material',
                    'jenis' => $so->effective_jenis ?? '-',
                    'komponen' => $so->effective_komponen ?? '-',
                    'kertas' => $so->effective_kertas ?? '-',
                    'finishing' => $so->effective_finishing ?? '-',
                    'qty' => $qty,
                    'harga' => $harga,
                    'total' => $total,
                    'keterangan' => '',
                ]);
            }
        } else {
            $itemName = $resolvedSoProductName ?: 'Barang Cetakan';

            $qty = (float) ($sj?->qty_pengiriman ?: ($so->qty ?: 1));
            $harga = (float) ($so->harga_pcs ?? 0);
            if ($harga <= 0 && $qty > 0) {
                $harga = $subtotal / $qty;
            }
            $total = $subtotal > 0 ? $subtotal : ($qty * $harga);

            $ket = $invoice->catatan ?: ($sj?->keterangan ?: ($so->catatan ?: ''));
            if ($ket === '-') $ket = '';

            $komp = $so->effective_komponen ?? ($so->komponen ?? '-');
            if ($komp !== '-' && strtoupper($komp) === $komp) {
                $komp = ucwords(strtolower($komp));
            }
            $kertas = $so->effective_kertas ?? ($so->kertas ?? '-');
            if ($kertas !== '-' && strtoupper($kertas) === $kertas) {
                $kertas = ucwords(strtolower($kertas));
            }

            $items->push((object)[
                'item' => $itemName,
                'jenis' => $so->effective_jenis ?? ($so->jenis ?? '-'),
                'komponen' => $komp,
                'kertas' => $kertas,
                'finishing' => $so->effective_finishing ?? ($so->finishing ?? '-'),
                'qty' => $qty,
                'harga' => $harga,
                'total' => $total,
                'keterangan' => $ket,
            ]);
        }
    } elseif ($sj || $subtotal > 0) {
        $fallbackName = 'Barang Cetakan';
        $fallbackJenis = '-';
        $fallbackKomp = '-';
        $fallbackKertas = '-';
        $fallbackFin = '-';
        $fallbackQty = (float) ($sj?->qty_pengiriman ?: 1);
        $fallbackHarga = $fallbackQty > 0 ? ($subtotal / $fallbackQty) : $subtotal;

        if ($customer) {
            $ocItem = \App\Models\OrderConfirmationItem::whereHas('orderConfirmation', fn($q) => $q->where('customer_id', $customer->id))->latest('id')->first();
            $afItem = \App\Models\ApprovalFormItem::whereHas('approvalForm', fn($q) => $q->where('customer_id', $customer->id))->latest('id')->first();
            $fgItem = \App\Models\FinishGoodItem::where('customer_id', $customer->id)->latest('id')->first();

            $fallbackName = $ocItem?->item_name ?: ($afItem?->item_name ?: ($fgItem?->nama_barang ?: 'Barang Cetakan'));
            $fallbackJenis = $ocItem?->jenis ?: ($afItem?->jenis ?: ($fgItem?->jenis ?: '-'));
            $fallbackKomp = $ocItem?->komponen ?: ($afItem?->komponen ?: ($fgItem?->komponen ?: '-'));
            $fallbackKertas = $ocItem?->kertas ?: ($afItem?->kertas ?: ($fgItem?->spesifikasi_kertas ?: '-'));
            $fallbackFin = $ocItem?->finishing ?: ($afItem?->finishing ?: ($fgItem?->finishing ?: '-'));
            if ($ocItem && $ocItem->harga > 0 && $fallbackQty <= 1 && $subtotal > 0) {
                $fallbackHarga = (float) $ocItem->harga;
                $fallbackQty = round($subtotal / $fallbackHarga);
            }
        }

        $items->push((object)[
            'item'       => $fallbackName,
            'jenis'      => $fallbackJenis,
            'komponen'   => $fallbackKomp !== '-' ? ucwords(strtolower($fallbackKomp)) : '-',
            'kertas'     => $fallbackKertas !== '-' ? ucwords(strtolower($fallbackKertas)) : '-',
            'finishing'  => $fallbackFin,
            'qty'        => $fallbackQty,
            'harga'      => $fallbackHarga,
            'total'      => $subtotal,
            'keterangan' => ($invoice->catatan && $invoice->catatan !== '-') ? $invoice->catatan : '',
        ]);
    }

    $minRows = 9;
    $emptyRows = max(0, $minRows - $items->count());

    $tanggalInvoice = $invoice->tanggal_invoice
        ? \Carbon\Carbon::parse($invoice->tanggal_invoice)->translatedFormat('d M Y')
        : '-';
    $tanggalTempo = $invoice->tanggal_jatuh_tempo
        ? \Carbon\Carbon::parse($invoice->tanggal_jatuh_tempo)->translatedFormat('d M Y')
        : '-';

    $terms = $invoice->terms_conditions
        ?? "1. Pembayaran dilakukan maksimal 15 hari setelah barang diterima.\n2. Barang dianggap diterima dengan baik jika tidak ada komplain dalam 3 hari.\n3. Transfer hanya melalui rekening a/n PT Teknografi Tri Cawanaska\n   dan disertai bukti transfer.";

    $terbilangTarget = $displayTotal > 0 ? $displayTotal : ($grandTotal > 0 ? $grandTotal : $subtotal);
    if (!empty($invoice->terbilang_override)) {
        $terbilangText = $invoice->terbilang_override;
    } elseif ($terbilangTarget > 0) {
        $terbilangText = trim(\App\Models\Invoice::penyebut($terbilangTarget)) . ' Rupiah';
        $terbilangText = preg_replace('/\s+/', ' ', $terbilangText);
    } else {
        $terbilangText = '';
    }

    $bankLines = [];
    if (!empty($invoice->bank_accounts)) {
        $bankLines = array_filter(array_map('trim', explode("\n", $invoice->bank_accounts)));
    }
    if (empty($bankLines)) {
        $bankLines = [
            'BRI PT TEKNOGRAFI TRI CAWANASKA 0583 0100 1711 567',
            'BCA PT TEKNOGRAFI TRI CAWANASKA 617 186 1010',
        ];
    }
@endphp

<table class="header">
    <tr>
        <td style="width:70%">
            <div class="company">
                <strong>PT Teknografi Tri Cawanaska</strong><br>
                Pergudangan Sentra Singosari<br>
                Ardimulyo, Singosari, Malang<br>
                teknografitricawanaska@gmail.com<br>
                +62895 3391 07709
            </div>
        </td>
        <td class="logo" style="width:30%">
            @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo TTC">
            @elseif(file_exists(public_path('images/Logo PT Teknografi.png')))
                <img src="{{ public_path('images/Logo PT Teknografi.png') }}" alt="Logo TTC">
            @elseif(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Logo TTC">
            @endif
        </td>
    </tr>
</table>

<div class="double-line"></div>

<table class="meta">
    <tr>
        <td style="width:60%">
            <table>
                <tr>
                    <td class="meta-label">Invoice</td>
                    <td class="meta-value">{{ $invoice->kode_invoice ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Date</td>
                    <td class="meta-value">{{ $tanggalInvoice }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Due</td>
                    <td class="meta-value">{{ $tanggalTempo }}</td>
                </tr>
            </table>
        </td>
        <td style="width:40%">
            <table>
                <tr>
                    <td class="order-label">Order ID</td>
                    <td style="font-weight:normal">{{ $orderId }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- PARTY BOXES: EXACTLY MATCHING ORIGINAL TTC REFERENCE (KEPADA CUSTOMER BOX CONNECTS TO INVOICE HEADER) -->
<table style="width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 0;">
    <tr>
        <td style="width: 39%; border: 1.5px solid #000; padding: 3px 6px; font-weight: bold; font-size: 9.5px; vertical-align: middle;">
            Dibuat Oleh: {{ strtoupper($invoice->dibuat_oleh ?: 'PT TEKNOGRAFI TRI CAWANASKA') }}
        </td>
        <td style="width: 21%; border: none;"></td>
        <td style="width: 7%; border: 1.5px solid #000; padding: 3px 6px; font-weight: bold; font-size: 9.5px; vertical-align: middle;">
            Kepada
        </td>
        <td rowspan="2" style="width: 33%; border-top: 1.5px solid #000; border-right: 2px solid #000; border-left: 1.5px solid #000; border-bottom: 0; padding: 3px 8px; font-weight: bold; font-size: 9.5px; vertical-align: top;">
            {{ strtoupper($customerName) }}
        </td>
    </tr>
    <tr>
        <td style="height: 16px; border: none;"></td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
    </tr>
</table>

<div class="invoice-title">INVOICE</div>

<table class="items">
    <colgroup>
        <col style="width:4%">
        <col style="width:16%">
        <col style="width:6%">
        <col style="width:8%">
        <col style="width:8%">
        <col style="width:22%">
        <col style="width:8%">
        <col style="width:9%">
        <col style="width:11%">
        <col style="width:8%">
    </colgroup>
    <thead>
        <tr>
            <th>No</th>
            <th>Item</th>
            <th>Jenis</th>
            <th>Komponen</th>
            <th>Kertas</th>
            <th>Finishing</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Total</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $index => $item)
            @php
                // Baris ganjil (1, 3, 5, 7, 9) berwarna biru muda persis sesuai referensi TTC
                $isStriped = (($index + 1) % 2 == 1);
            @endphp
            <tr class="{{ $isStriped ? 'striped' : '' }}">
                <td class="center">{{ $index + 1 }}</td>
                <td class="center">{{ $item->item ?? '-' }}</td>
                <td class="center">{{ $item->jenis ?? '-' }}</td>
                <td class="center">{{ $item->komponen ?? '-' }}</td>
                <td class="center">{{ $item->kertas ?? '-' }}</td>
                <td class="center">{{ $item->finishing ?? '-' }}</td>
                <td class="center">{{ number_format((float) ($item->qty ?? 0), 0, '.', ',') }}</td>
                <td class="center">IDR {{ number_format((float) ($item->harga ?? 0), 0, '.', ',') }}</td>
                <td class="center">IDR {{ number_format((float) ($item->total ?? 0), 0, '.', ',') }}</td>
                <td>{{ $item->keterangan ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center">Belum ada rincian item invoice.</td>
            </tr>
        @endforelse

        @for($i = 0; $i < $emptyRows; $i++)
            @php $isStriped = (($items->count() + $i + 1) % 2 == 1); @endphp
            <tr class="{{ $isStriped ? 'striped' : '' }}">
                @for($j = 0; $j < 10; $j++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @endfor
    </tbody>
</table>

<!-- BOTTOM SECTION: 2-ROW TABLE SO TTD BRACKETS ALIGN EXACTLY ON THE SAME BASELINE AS THE BANK BOX -->
<table class="bottom">
    <tr>
        <td style="width: 60%; padding-right: 16px; vertical-align: top;">
            <div class="terms-title" style="margin-top: 8px;">Terms &amp; Conditions</div>
            <div class="terms">
                {!! nl2br(e($terms)) !!}
            </div>
        </td>
        <td style="width: 40%; vertical-align: top;">
            <table class="totals">
                <tr>
                    <td class="total-label">Subtotal</td>
                    <td class="total-curr">IDR</td>
                    <td class="total-value">{{ number_format($subtotal, 0, '.', ',') }}</td>
                </tr>
                <tr>
                    <td class="total-label">Pengiriman</td>
                    <td class="total-curr">{{ $ongkir > 0 ? 'IDR' : '' }}</td>
                    <td class="total-value">{{ $ongkir > 0 ? number_format($ongkir, 0, '.', ',') : '' }}</td>
                </tr>
                <tr>
                    <td class="total-label">Potongan</td>
                    <td class="total-curr">{{ $potongan > 0 ? 'IDR' : '' }}</td>
                    <td class="total-value">{{ $potongan > 0 ? number_format($potongan, 0, '.', ',') : '' }}</td>
                </tr>
                <tr>
                    <td class="total-label">DP</td>
                    <td class="total-curr">{{ $dp > 0 ? 'IDR' : '' }}</td>
                    <td class="total-value">{{ $dp > 0 ? number_format($dp, 0, '.', ',') : '' }}</td>
                </tr>
                <tr class="grand">
                    <td class="total-label">Total</td>
                    <td class="total-curr">IDR</td>
                    <td class="total-value">{{ number_format($displayTotal, 0, '.', ',') }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width: 60%; padding-right: 16px; vertical-align: bottom;">
            <div style="margin-top: 5px; font-weight: bold; font-size: 9px;">Terbilang</div>
            <table class="terbilang-bank-box">
                <tr>
                    <td class="terbilang-cell">
                        {{ $terbilangText }}
                    </td>
                </tr>
                <tr>
                    <td class="bank-cell">
                        @foreach($bankLines as $idx => $line)
                            <div>{{ $line }}</div>
                        @endforeach
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 40%; vertical-align: bottom; padding-bottom: 5px;">
            <table style="width: 90%; margin-left: 3%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: bottom; font-size: 9.5px; white-space: nowrap;">
                        (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: bottom; font-size: 9.5px; white-space: nowrap;">
                        (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
