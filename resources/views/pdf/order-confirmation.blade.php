<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Order Confirmation - {{ $orderConfirmation->doc_no }}</title>

<style>
@page {
    margin: 15px 25px 15px 25px;
    size: A4 landscape;
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
    margin-bottom: 8px;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.company-title {
    font-size: 13px;
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
    margin-bottom: 6px;
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

/* TABLE SECTION */
.order-table-container {
    width: 100%;
    border: 2px solid #000;
    border-collapse: collapse;
    margin-bottom: 6px;
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
    padding: 4px 3px;
    border: 1px solid #000;
    font-size: 9.5px;
}

.items-table td {
    border: 1px solid #222;
    padding: 3px 3px;
    height: 15px;
    vertical-align: middle;
}

.items-table tr.striped {
    background-color: #e9f1fb;
}

/* SUMMARY / TOTALS SECTION */
.summary-container {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6px;
}

.summary-container td {
    border: none;
    vertical-align: top;
}

.terms-box {
    font-size: 9px;
    line-height: 1.4;
    padding-right: 15px;
}

.terms-title {
    font-weight: bold;
    font-style: italic;
    font-size: 10px;
    margin-bottom: 3px;
}

.totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.totals-table td {
    padding: 2.5px 6px;
    border-top: 1px solid #888;
    border-bottom: 1px solid #888;
}

.totals-table tr.total-row td {
    font-weight: bold;
    font-size: 10.5px;
    border-top: 2px solid #000;
    border-bottom: 3px double #000;
    background-color: #f7f9fd;
}

/* SIGNATURES */
.signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4px;
}

.signature-table td {
    border: none;
    vertical-align: top;
}

.sig-box {
    width: 200px;
    height: 72px;
    border: 2px solid #000;
    text-align: center;
    position: relative;
    padding: 3px;
}

.sig-title {
    font-weight: bold;
    font-size: 10.5px;
}

.sig-company {
    font-size: 9.5px;
    font-weight: normal;
    margin-top: 1px;
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
                <td>: {{ $orderConfirmation->order_id ?: '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Date</td>
                <td>: {{ $orderConfirmation->tanggal ? date('d M Y', strtotime($orderConfirmation->tanggal)) : date('d M Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust</td>
                <td>: {{ $orderConfirmation->customer?->nama_customer ?? '-' }}</td>
            </tr>
        </table>
    </td>
    <td width="45%">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="meta-label">Doc No</td>
                <td>: {{ $orderConfirmation->doc_no }}</td>
            </tr>
            <tr>
                <td class="meta-label">Lead Time</td>
                <td>: {{ $orderConfirmation->lead_time ?: '21 Hari Kerja' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust ID</td>
                <td>: {{ $orderConfirmation->customer?->kode_customer ?: ('C' . str_pad($orderConfirmation->customer_id, 3, '0', STR_PAD_LEFT)) }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- ORDER CONFIRMATION TABLE -->
<table class="order-table-container">
    <tr>
        <td class="order-title-bar">ORDER CONFIRMATION</td>
    </tr>
    <tr>
        <td style="padding: 0; border: none;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="3%">No</th>
                        <th width="20%">Item</th>
                        <th width="7%">Jenis</th>
                        <th width="9%">Komponen</th>
                        <th width="5%">Isi</th>
                        <th width="10%">Kertas</th>
                        <th width="6%">Warna</th>
                        <th width="19%">Finishing</th>
                        <th width="7%">Qty</th>
                        <th width="7%">Harga</th>
                        <th width="11%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $rowCount = 0; 
                        $designPath = null;
                        $afDesign = $orderConfirmation->approvalForm?->file_design;
                        if (!empty($afDesign)) {
                            if (file_exists(public_path('storage/' . $afDesign))) {
                                $designPath = public_path('storage/' . $afDesign);
                            } elseif (file_exists(storage_path('app/public/' . $afDesign))) {
                                $designPath = storage_path('app/public/' . $afDesign);
                            } elseif (file_exists(public_path($afDesign))) {
                                $designPath = public_path($afDesign);
                            }
                        }
                        $minRows = $designPath ? 4 : 8;
                    @endphp
                    @foreach($orderConfirmation->items as $idx => $item)
                        @php 
                            $rowCount++;
                            $isStriped = ($rowCount % 2 == 0);
                        @endphp
                        <tr class="{{ $isStriped ? 'striped' : '' }}">
                            <td align="center">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold;">{{ $item->item_name }}</td>
                            <td align="center">{{ $item->jenis ?: '-' }}</td>
                            <td align="center">{{ $item->komponen ?: '-' }}</td>
                            <td align="center">{{ $item->isi ?: '-' }}</td>
                            <td>{{ $item->kertas ?: '-' }}</td>
                            <td align="center">{{ $item->warna ?: '-' }}</td>
                            <td>{{ $item->finishing ?: '-' }}</td>
                            <td align="right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td align="right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td align="right">{{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    {{-- Baris kosong untuk menjaga konsistensi tinggi kertas (dipadatkan ke 4 jika ada lampiran desain) --}}
                    @for($i = $rowCount + 1; $i <= max($rowCount, $minRows); $i++)
                        @php $isStriped = ($i % 2 == 0); @endphp
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
        </td>
    </tr>
</table>

<!-- SUMMARY / TOTALS -->
<table class="summary-container">
<tr>
    <td width="60%">
        <div class="terms-box">
            <div class="terms-title">Terms & Conditions</div>
            <div>
                {!! nl2br(e($orderConfirmation->terms_conditions ?: \App\Models\OrderConfirmation::defaultTermsConditions())) !!}
            </div>
        </div>
    </td>
    <td width="40%">
        <table class="totals-table">
            <tr>
                <td width="50%" align="right">Subtotal</td>
                <td width="50%" align="right">{{ number_format($orderConfirmation->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($orderConfirmation->diskon > 0)
            <tr>
                <td align="right">Diskon</td>
                <td align="right">{{ number_format($orderConfirmation->diskon, 0, ',', '.') }}</td>
            </tr>
            @else
            <tr>
                <td align="right">Diskon</td>
                <td align="right">-</td>
            </tr>
            @endif
            <tr class="total-row">
                <td align="right">Total(Rp)</td>
                <td align="right">{{ number_format($orderConfirmation->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- SIGNATURES -->
<table class="signature-table">
<tr>
    <td width="50%" align="center">
        <div class="sig-box" style="margin: 0 auto;">
            <div class="sig-title">Disetujui</div>
            <div style="position: absolute; bottom: 8px; left: 0; right: 0; text-align: center; font-size: 10px;">
                ( {{ $orderConfirmation->disetujui_customer ?: '                           ' }} )
            </div>
        </div>
    </td>
    <td width="50%" align="center">
        <div class="sig-box" style="margin: 0 auto;">
            <div class="sig-title">Diketahui</div>
            <div class="sig-company">{{ $orderConfirmation->diketahui_ttc ?: 'PT. Teknografi Tri Cawanaska' }}</div>
            <div style="position: absolute; bottom: 8px; left: 0; right: 0; text-align: center; font-size: 10px;">
                (                                 )
            </div>
        </div>
    </td>
</tr>
</table>

@if($designPath)
<!-- LAMPIRAN PROVE CETAK & DESAIN PRODUKSI DARI APPROVAL FORM -->
<div style="margin-top: 6px; width: 100%; border: 2px solid #2b5fa8; border-radius: 4px; padding: 4px 6px; text-align: center; background-color: #ffffff;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px;">
        <tr>
            <td style="text-align: left; font-size: 9px; font-weight: bold; color: #2b5fa8; padding: 1px 2px; text-transform: uppercase;">
                Lampiran Desain & Acuan Cetak (Ref Approval Form: {{ $orderConfirmation->approvalForm?->form_no }}):
            </td>
            <td style="text-align: right; font-size: 8px; color: #666; padding: 1px 2px;">
                Acuan Visual Spesifikasi Kemasan Disetujui Customer
            </td>
        </tr>
    </table>
    <div style="text-align: center; padding: 2px;">
        <img src="{{ $designPath }}" style="max-height: 155px; max-width: 98%; height: auto; display: inline-block;">
    </div>
</div>
@endif

</body>
</html>
