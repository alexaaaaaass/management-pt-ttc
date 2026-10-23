<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->kode_invoice }}</title>
    <style>
        @page { size: A4 landscape; margin: 18px 20px 22px 20px; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #111;
            margin: 0;
        }
        table { border-collapse: collapse; width: 100%; }
        .header td { vertical-align: top; }
        .company { line-height: 1.45; font-size: 10px; }
        .logo { text-align: right; }
        .logo img { width: 105px; height: 70px; object-fit: contain; }
        .double-line {
            border-top: 3px double #111;
            height: 5px;
            margin-top: 10px;
        }
        .meta td { padding: 1px 3px; vertical-align: top; }
        .meta-label { width: 52px; font-weight: bold; }
        .meta-value { font-weight: normal; }
        .order-label { font-weight: bold; width: 75px; }
        .party { margin-top: 5px; margin-bottom: 14px; }
        .party td { border: 1px solid #111; padding: 3px 5px; }
        .party .label { width: 70px; font-weight: bold; }
        .invoice-title {
            border: 1px solid #111;
            border-bottom: 0;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            padding: 9px 0;
        }
        .items th, .items td {
            border: 1px solid #111;
            padding: 3px 4px;
            height: 17px;
        }
        .items th {
            background: #31558f;
            color: #fff;
            text-align: center;
            font-weight: bold;
            white-space: nowrap;
        }
        .items td { vertical-align: middle; }
        .items tbody tr:nth-child(even) { background: #dce3f3; }
        .center { text-align: center; }
        .right { text-align: right; white-space: nowrap; }
        .bottom { margin-top: 5px; }
        .bottom td { vertical-align: top; }
        .terms-title { font-weight: bold; font-style: italic; margin-bottom: 4px; }
        .terms { line-height: 1.5; }
        .totals { width: 100%; }
        .totals td { padding: 2px 3px; }
        .total-label { width: 65%; }
        .total-value { text-align: right; white-space: nowrap; }
        .grand td { border-top: 1px solid #111; font-weight: bold; }
        .bank {
            border: 1px solid #111;
            padding: 5px;
            margin-top: 5px;
            min-height: 48px;
            line-height: 1.5;
            font-size: 8px;
        }
        .signatures { margin-top: 22px; }
        .signatures td { text-align: center; width: 50%; }
        .sign-space { height: 38px; }
        .muted { color: #444; }
    </style>
</head>
<body>
@php
    $customer = $invoice->customer
        ?? $invoice->suratJalan?->salesOrder?->customer
        ?? $invoice->salesOrder?->customer;

    $customerName = $customer->nama_customer
        ?? $invoice->kepada
        ?? '-';

    $orderId = $invoice->order_id
        ?? $invoice->salesOrder?->kode_sales_order
        ?? $invoice->suratJalan?->salesOrder?->kode_sales_order
        ?? '-';

    $fmt = fn ($value) => 'IDR ' . number_format((float) ($value ?? 0), 0, ',', '.');

    $subtotal = (float) ($invoice->subtotal ?? 0);
    $ongkir = (float) ($invoice->ongkir ?? 0);
    $potongan = (float) ($invoice->potongan ?? (($subtotal * (float) ($invoice->diskon ?? 0)) / 100));
    $dp = (float) ($invoice->uang_muka ?? 0);
    $grandTotal = (float) ($invoice->grand_total ?? ($subtotal - $potongan + $ongkir));
    $totalAkhir = $grandTotal - $dp;

    $items = $invoice->items ?? collect();
    $emptyRows = max(0, 8 - $items->count());

    $tanggalInvoice = $invoice->tanggal_invoice
        ? \Carbon\Carbon::parse($invoice->tanggal_invoice)->translatedFormat('d M Y')
        : '-';
    $tanggalTempo = $invoice->tanggal_jatuh_tempo
        ? \Carbon\Carbon::parse($invoice->tanggal_jatuh_tempo)->translatedFormat('d M Y')
        : '-';

    $terms = $invoice->terms_conditions
        ?? "1. Pembayaran dilakukan maksimal 15 hari setelah barang diterima.\n2. Barang dianggap diterima dengan baik jika tidak ada komplain dalam 3 hari.\n3. Transfer hanya melalui rekening a/n PT Teknografi Tri Cawanaska dan disertai bukti transfer.";
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
                    <td>{{ $orderId }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="party">
    <tr>
        <td style="width:41%; font-weight:bold">
            Dibuat Oleh: {{ $invoice->dibuat_oleh ?: 'PT TEKNOGRAFI TRI CAWANASKA' }}
        </td>
        <td class="label">Kepada</td>
        <td style="font-weight:bold">{{ $customerName }}</td>
    </tr>
</table>

<div class="invoice-title">INVOICE</div>

<table class="items">
    <colgroup>
        <col style="width:4%">
        <col style="width:15%">
        <col style="width:6%">
        <col style="width:8%">
        <col style="width:8%">
        <col style="width:21%">
        <col style="width:8%">
        <col style="width:9%">
        <col style="width:13%">
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
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->item ?? '-' }}</td>
                <td class="center">{{ $item->jenis ?? '-' }}</td>
                <td>{{ $item->komponen ?? '-' }}</td>
                <td>{{ $item->kertas ?? '-' }}</td>
                <td>{{ $item->finishing ?? '-' }}</td>
                <td class="right">{{ number_format((float) ($item->qty ?? 0), 0, ',', '.') }}</td>
                <td class="right">{{ number_format((float) ($item->harga ?? 0), 0, ',', '.') }}</td>
                <td class="right">{{ number_format((float) ($item->total ?? 0), 0, ',', '.') }}</td>
                <td>{{ $item->keterangan ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="center">Belum ada rincian item invoice.</td>
            </tr>
        @endforelse

        @for($i = 0; $i < $emptyRows; $i++)
            <tr>
                @for($j = 0; $j < 10; $j++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @endfor
    </tbody>
</table>

<table class="bottom">
    <tr>
        <td style="width:60%; padding-right:18px">
            <div class="terms-title">Terms &amp; Conditions</div>
            <div class="terms">
                {!! nl2br(e($terms)) !!}
            </div>

            <div style="margin-top:6px; font-weight:bold">Terbilang</div>
            <div style="border:1px solid #111; min-height:48px; padding:5px; margin-top:2px">
                {{ $invoice->terbilang_formatted ?? '-' }}
            </div>

            <div class="bank">
                <strong>{{ $invoice->bank_accounts ?: "BRI PT TEKNOGRAFI TRI CAWANASKA 0583 0100 1711 567\nBCA PT TEKNOGRAFI TRI CAWANASKA 617 186 1010" }}</strong>
            </div>
        </td>

        <td style="width:40%">
            <table class="totals">
                <tr>
                    <td class="total-label">Subtotal</td>
                    <td class="total-value">{{ $fmt($subtotal) }}</td>
                </tr>
                <tr>
                    <td class="total-label">Pengiriman</td>
                    <td class="total-value">{{ $fmt($ongkir) }}</td>
                </tr>
                <tr>
                    <td class="total-label">Potongan</td>
                    <td class="total-value">{{ $fmt($potongan) }}</td>
                </tr>
                <tr>
                    <td class="total-label">DP</td>
                    <td class="total-value">{{ $fmt($dp) }}</td>
                </tr>
                <tr class="grand">
                    <td class="total-label">Total</td>
                    <td class="total-value">{{ $fmt($totalAkhir) }}</td>
                </tr>
            </table>

            <table class="signatures">
                <tr>
                    <td>(</td>
                    <td>(</td>
                </tr>
                <tr>
                    <td class="sign-space"></td>
                    <td class="sign-space"></td>
                </tr>
                <tr>
                    <td>)</td>
                    <td>)</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
