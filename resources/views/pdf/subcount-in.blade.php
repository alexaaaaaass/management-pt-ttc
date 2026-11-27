<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
body {
    font-family: sans-serif;
    font-size: 12.5px;
    color: #111827;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #334155;
    padding: 7px 8px;
    vertical-align: top;
}
.header-box {
    border: 1.5px solid #1e293b;
    padding: 14px;
    margin-bottom: 16px;
}
.no-border td {
    border: none;
    padding: 3px 4px;
}
.title {
    font-size: 19px;
    font-weight: bold;
    text-align: right;
    color: #047857;
}
.subtitle {
    font-size: 11px;
    text-align: right;
    color: #64748b;
    margin-top: 2px;
}
.signature td {
    border: none;
    text-align: center;
    padding: 4px 8px;
}
thead {
    display: table-header-group;
}
tr {
    page-break-inside: avoid;
}
th {
    background: #1e3a8a;
    color: #ffffff;
    font-size: 11.5px;
}
</style>
</head>
<body>

@php
    $resolvedOut = $subcountIn->resolved_subcount_out;
    $resolvedVendor = $subcountIn->resolved_supplier;
@endphp

<!-- HEADER -->
<div class="header-box">
    <table class="no-border">
        <tr>
            <td width="12%" align="center">
                <img src="{{ public_path('images/logo.png') }}" width="66">
            </td>
            <td width="56%">
                <h2 style="margin:0; font-size: 16px;">PT. TEKNOGRAFI TRI CAWANASKA</h2>
                Pergudangan Sentra Singosari, Ardimulyo, Singosari, Malang<br>
                Email: teknografitricawanaska@gmail.com | Telp: +62895 3391 07709
            </td>
            <td width="32%" style="vertical-align:top">
                <div class="title">SUBCOUNT IN</div>
                <div class="subtitle">Bukti Penerimaan Hasil Subcount / Maklon</div>
            </td>
        </tr>
    </table>
</div>

<!-- INFORMASI -->
<div class="header-box">
    <table class="no-border">
        <tr>
            <td width="22%"><b>No. Subcount In</b></td>
            <td width="3%">:</td>
            <td width="31%">{{ $subcountIn->no_subcount }}</td>
            <td width="18%"><b>Tanggal Terima</b></td>
            <td width="3%">:</td>
            <td width="23%">{{ \Carbon\Carbon::parse($subcountIn->tanggal_subcount)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td><b>Vendor Subcount</b></td>
            <td>:</td>
            <td><b>{{ $resolvedVendor?->nama_supplier ?? '-' }}</b></td>
            <td><b>No. SJ Subcount Out</b></td>
            <td>:</td>
            <td>{{ $resolvedOut?->surat_jalan_subcount ?? '-' }}</td>
        </tr>
        <tr>
            <td><b>No. SJ Vendor</b></td>
            <td>:</td>
            <td>{{ $subcountIn->surat_jalan_pengiriman ?: '-' }}</td>
            <td><b>PIC Penerima</b></td>
            <td>:</td>
            <td>{{ $subcountIn->admin_produksi ?: '-' }}</td>
        </tr>
        @if($resolvedOut?->jenis_proses)
            <tr>
                <td><b>Jenis Proses</b></td>
                <td>:</td>
                <td colspan="4">{{ $resolvedOut->jenis_proses_label }}</td>
            </tr>
        @endif
        @if($subcountIn->keterangan)
            <tr>
                <td><b>Keterangan</b></td>
                <td>:</td>
                <td colspan="4">{{ $subcountIn->keterangan }}</td>
            </tr>
        @endif
    </table>
</div>

<!-- DATA ITEM -->
<div class="header-box">
    <h3 align="center" style="margin-top: 0; margin-bottom: 12px;">DAFTAR ITEM DITERIMA</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="16%">No. Subcount Out</th>
                <th width="21%">Item / SPK Asal</th>
                <th width="10%">Qty Kirim</th>
                <th width="22%">Barang Diterima (Hasil)</th>
                <th width="10%">Qty Terima</th>
                <th width="16%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subcountIn->items as $item)
                @php
                    $originLabel = $item->itemAsal?->nama_master_item
                        ?? ($item->spk
                            ? trim($item->spk->no_spk . ' — ' . ($item->spk->salesOrder?->itemable?->nama_barang ?? $item->spk->salesOrder?->product_name ?? ''), ' —')
                            : '-');

                    if (($item->tipe_hasil ?: 'material') === 'finish_good') {
                        $resultLabel = ($item->finishGoodItem?->nama_barang ?? '-') . ' (Finish Good)';
                    } else {
                        $resultLabel = ($item->itemHasil?->nama_master_item ?? '-') . ' (Material)';
                    }
                @endphp
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->subcountOut?->surat_jalan_subcount ?? $resolvedOut?->surat_jalan_subcount ?? '-' }}</td>
                    <td>{{ $originLabel }}</td>
                    <td align="right">{{ number_format((float) $item->qty_dikirim, 0, ',', '.') }}</td>
                    <td>{{ $resultLabel }}</td>
                    <td align="right"><b>{{ number_format((float) $item->qty_diterima, 0, ',', '.') }}</b></td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br><br>

    <table class="signature">
        <tr>
            <td width="33%">PIC / Admin Produksi,</td>
            <td width="33%">Supervisor,</td>
            <td width="34%">Admin Mainstore,</td>
        </tr>
        <tr>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountIn->admin_produksi ?: '................................' }} )
            </td>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountIn->supervisor ?: '................................' }} )
            </td>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountIn->admin_mainstore ?: '................................' }} )
            </td>
        </tr>
    </table>
</div>

</body>
</html>
