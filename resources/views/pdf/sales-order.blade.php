<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Order Confirmation - {{ $salesOrder->no_sales_order }}</title>

<style>
@page {
    margin: 20px 25px 20px 25px;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #111;
    line-height: 1.3;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.company-title {
    font-size: 12px;
    font-weight: bold;
    color: #000;
}

.company-info {
    font-size: 10px;
    color: #222;
    line-height: 1.35;
}

.meta-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
    font-size: 11px;
}

.meta-table td {
    border: none;
    padding: 2px 4px;
    vertical-align: top;
}

.meta-label {
    font-weight: bold;
    width: 75px;
}

/* ORDER CONFIRMATION TABLE */
.order-table-container {
    width: 100%;
    border: 2px solid #000;
    border-collapse: collapse;
    margin-bottom: 8px;
}

.order-title-bar {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    padding: 5px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #000;
    background: #ffffff;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}

.items-table th {
    background-color: #2b5fa8;
    color: #ffffff;
    font-weight: bold;
    text-align: center;
    padding: 5px 3px;
    border: 1px solid #000;
    font-size: 9.5px;
}

.items-table td {
    border: 1px solid #222;
    padding: 3px 3px;
    height: 14px;
    vertical-align: middle;
}

.items-table tr.striped {
    background-color: #e9f1fb;
}

/* SUMMARY / TOTALS SECTION */
.summary-container {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.summary-container td {
    border: none;
    vertical-align: top;
}

.terms-box {
    font-size: 9.5px;
    line-height: 1.45;
    padding-right: 15px;
}

.terms-title {
    font-weight: bold;
    font-style: italic;
    font-size: 10.5px;
    margin-bottom: 4px;
}

.totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10.5px;
}

.totals-table td {
    padding: 3px 6px;
    border-top: 1px solid #888;
    border-bottom: 1px solid #888;
}

.totals-table tr.total-row td {
    font-weight: bold;
    font-size: 11px;
    border-top: 2px solid #000;
    border-bottom: 3px double #000;
    background-color: #f7f9fd;
}

/* SIGNATURES */
.signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
}

.signature-table td {
    border: none;
    vertical-align: top;
}

.sig-box {
    width: 220px;
    height: 90px;
    border: 2px solid #000;
    text-align: center;
    position: relative;
    padding: 4px;
}

.sig-title {
    font-weight: bold;
    font-size: 11px;
}

.sig-company {
    font-size: 10px;
    font-weight: normal;
    margin-top: 1px;
}

.sig-line {
    position: absolute;
    bottom: 6px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 10px;
}
</style>
</head>
<body>

<!-- TOP HEADER -->
<table class="header-table">
<tr>
    <td width="70%">
        <div class="company-title">PT Teknografi Tri Cawanaska</div>
        <div class="company-info">
            Pergudangan Sentral Singosari<br>
            Ardimulyo, Singosari, Malang<br>
            teknografitricawanaska@gmail.com<br>
            +6281233390799
        </div>
    </td>
    <td width="30%" align="right">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" style="max-height: 60px; width: auto;">
        @endif
    </td>
</tr>
</table>

<!-- METADATA -->
<table class="meta-table">
<tr>
    <td width="55%">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="meta-label">Order ID</td>
                <td>: {{ $salesOrder->no_sales_order }}</td>
            </tr>
            <tr>
                <td class="meta-label">Date</td>
                <td>: {{ $salesOrder->tanggal_po ? date('d M Y', strtotime($salesOrder->tanggal_po)) : date('d M Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust</td>
                <td>: {{ $salesOrder->customer?->nama_customer ?? '-' }}</td>
            </tr>
        </table>
    </td>
    <td width="45%">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="meta-label">Doc No</td>
                <td>: SC-{{ $salesOrder->no_sales_order }}</td>
            </tr>
            <tr>
                <td class="meta-label">Lead Time</td>
                <td>: {{ $salesOrder->lead_time ?: '21 Hari Kerja' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust ID</td>
                <td>: {{ $salesOrder->customer?->kode_customer ?? '-' }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ORDER CONFIRMATION TABLE WRAPPER -->
<div style="border: 2px solid #000; margin-bottom: 5px;">
    <div class="order-title-bar">
        ORDER CONFIRMATION
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="19%">Item</th>
                <th width="7%">Jenis</th>
                <th width="9%">Komponen</th>
                <th width="5%">Isi</th>
                <th width="10%">Kertas</th>
                <th width="5%">Warna</th>
                <th width="14%">Finishing</th>
                <th width="8%">Qty</th>
                <th width="8%">Harga</th>
                <th width="12%">Total</th>
            </tr>
        </thead>
        <tbody>
        @php
            $subtotal = 0;
            $rowCount = 0;
        @endphp

        @if($salesOrder->order_type === 'MATERIAL' || ($salesOrder->items && $salesOrder->items->count() > 0))
            @foreach($salesOrder->items as $index => $item)
                @php
                    $itemQty = (float) ($item->qty ?? 0);
                    $itemHarga = (float) ($item->harga ?? 0);
                    $itemTotal = $itemQty * $itemHarga;
                    $subtotal += $itemTotal;
                    $rowCount++;
                    $isStriped = ($rowCount % 2 == 0);
                @endphp
                <tr class="{{ $isStriped ? 'striped' : '' }}">
                    <td align="center">{{ $rowCount }}</td>
                    <td>{{ $item->material?->nama_master_item ?? '-' }}</td>
                    <td align="center">-</td>
                    <td align="center">Material</td>
                    <td align="center">-</td>
                    <td>{{ $item->material?->nama_master_item ?? '-' }}</td>
                    <td align="center">-</td>
                    <td>-</td>
                    <td align="center">{{ number_format($itemQty, 0, ',', '.') }}</td>
                    <td align="right">{{ number_format($itemHarga, 0, ',', '.') }}</td>
                    <td align="right">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        @else
            @php
                $itemQty = (float) ($salesOrder->qty ?? 0);
                $itemHarga = (float) ($salesOrder->harga_pcs ?? 0);
                $itemTotal = $itemQty * $itemHarga;
                $subtotal = $itemTotal;
                $rowCount++;

                $namaItem = $salesOrder->itemable?->nama_barang ?? $salesOrder->product_name ?? $salesOrder->kode_material ?? '-';
                $jenis = $salesOrder->effective_jenis;
                $komponen = $salesOrder->effective_komponen;
                $isi = $salesOrder->effective_isi;
                $kertas = $salesOrder->effective_kertas;
                $warna = $salesOrder->effective_warna;
                $finishing = $salesOrder->effective_finishing;
            @endphp
            <tr>
                <td align="center">1</td>
                <td>{{ $namaItem }}</td>
                <td align="center">{{ $jenis }}</td>
                <td align="center">{{ $komponen }}</td>
                <td align="center">{{ $isi }}</td>
                <td>{{ $kertas }}</td>
                <td align="center">{{ $warna }}</td>
                <td>{{ $finishing }}</td>
                <td align="center">{{ number_format($itemQty, 0, ',', '.') }}</td>
                <td align="right">{{ number_format($itemHarga, 0, ',', '.') }}</td>
                <td align="right">{{ number_format($itemTotal, 0, ',', '.') }}</td>
            </tr>
        @endif

        {{-- Baris bergaris (zebra stripes) seperti dokumen resmi asli TTC --}}
        @php
            $minRows = 8;
            $emptyRows = max(1, $minRows - $rowCount);
        @endphp

        @for($i = 0; $i < $emptyRows; $i++)
            @php
                $rowCount++;
                $isStriped = ($rowCount % 2 == 0);
            @endphp
            <tr class="{{ $isStriped ? 'striped' : '' }}">
                <td align="center">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        @endfor

        </tbody>
    </table>
</div>

<!-- TOTALS & TERMS SECTION -->
@php
    $diskon = (float) ($salesOrder->diskon ?? 0);
    $ongkir = (float) ($salesOrder->harga_kirim ?? 0);
    $grandTotal = max(0, $subtotal - $diskon + $ongkir);
@endphp

<table class="summary-container">
<tr>
    <td width="60%">
        <div class="terms-box">
            <div class="terms-title">Terms & Conditions</div>
            1. Spesifikasi, quantity, dan harga telah disepakati.<br>
            2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.<br>
            3. Produksi dimulai setelah dokumen ini disetujui.
        </div>
    </td>
    <td width="40%">
        <table class="totals-table">
            <tr>
                <td align="right" width="40%">Subtotal</td>
                <td align="right" width="60%">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td align="right">Diskon</td>
                <td align="right">
                    {{ $diskon > 0 ? number_format($diskon, 0, ',', '.') : '' }}
                </td>
            </tr>
            @if($ongkir > 0)
            <tr>
                <td align="right">Biaya Kirim</td>
                <td align="right">{{ number_format($ongkir, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td align="right">Total(Rp)</td>
                <td align="right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- SIGNATURE BOXES -->
<table class="signature-table">
<tr>
    <td width="45%" align="left">
        <div class="sig-box" style="margin-left: 15px;">
            <div class="sig-title">Disetujui</div>
            <div class="sig-line">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
        </div>
    </td>
    <td width="10%">&nbsp;</td>
    <td width="45%" align="right">
        <div class="sig-box" style="margin-right: 0;">
            <div class="sig-title">Diketahui</div>
            <div class="sig-company">PT. Teknografi Tri Cawanaska</div>
            <div class="sig-line">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
        </div>
    </td>
</tr>
</table>

</body>
</html>