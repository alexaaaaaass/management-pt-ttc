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
    color: #1e3a8a;
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
                <div class="title">SUBCOUNT OUT</div>
                <div class="subtitle">Surat Jalan Pengiriman Maklon / Subcount</div>
            </td>
        </tr>
    </table>
</div>

<!-- INFORMASI -->
<div class="header-box">
    <table class="no-border">
        <tr>
            <td width="20%"><b>No. Surat Jalan</b></td>
            <td width="3%">:</td>
            <td width="32%">{{ $subcountOut->surat_jalan_subcount }}</td>
            <td width="18%"><b>Tanggal Kirim</b></td>
            <td width="3%">:</td>
            <td width="24%">{{ \Carbon\Carbon::parse($subcountOut->tanggal_subcount)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td><b>Vendor Subcount</b></td>
            <td>:</td>
            <td><b>{{ $subcountOut->supplier?->nama_supplier ?? '-' }}</b></td>
            <td><b>Target Selesai</b></td>
            <td>:</td>
            <td>{{ $subcountOut->tanggal_target_selesai ? \Carbon\Carbon::parse($subcountOut->tanggal_target_selesai)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td><b>Jenis Proses</b></td>
            <td>:</td>
            <td>{{ $subcountOut->jenis_proses_label }}</td>
            <td><b>PIC / Produksi</b></td>
            <td>:</td>
            <td>{{ $subcountOut->admin_produksi ?: '-' }}</td>
        </tr>
        @if($subcountOut->keterangan)
            <tr>
                <td><b>Keterangan</b></td>
                <td>:</td>
                <td colspan="4">{{ $subcountOut->keterangan }}</td>
            </tr>
        @endif
    </table>
</div>

<!-- DATA ITEM -->
<div class="header-box">
    <h3 align="center" style="margin-top: 0; margin-bottom: 12px;">DAFTAR ITEM DIKIRIM</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Sumber</th>
                <th width="20%">No SPK / Kode Material</th>
                <th>Nama Produk / Material</th>
                <th width="11%">Qty</th>
                <th width="10%">Satuan</th>
                <th width="18%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subcountOut->items as $item)
                <tr>
                    <td align="center">{{ $loop->iteration }}</td>
                    <td align="center">{{ $item->sumber_item === 'spk' ? 'SPK' : 'Material' }}</td>
                    <td>{{ $item->spk?->no_spk ?? $item->item?->kode_master_item ?? '-' }}</td>
                    <td>
                        @if($item->sumber_item === 'material')
                            {{ $item->item?->nama_master_item ?? $item->nama_produk ?? '-' }}
                        @else
                            {{ $item->nama_produk ?: ($item->spk?->salesOrder?->itemable?->nama_barang ?? $item->spk?->salesOrder?->product_name ?? '-') }}
                        @endif
                    </td>
                    <td align="right">{{ number_format((float) $item->qty, 0, ',', '.') }}</td>
                    <td align="center">{{ $item->satuan?->nama_satuan ?? '-' }}</td>
                    <td>{{ $item->catatan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br><br>

    <table class="signature">
        <tr>
            <td width="33%">PIC / Admin Produksi,</td>
            <td width="33%">Supervisor,</td>
            <td width="34%">Admin Mainstore / Vendor,</td>
        </tr>
        <tr>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountOut->admin_produksi ?: '................................' }} )
            </td>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountOut->supervisor ?: '................................' }} )
            </td>
            <td height="75" style="vertical-align: bottom;">
                ( {{ $subcountOut->admin_mainstore ?: '................................' }} )
            </td>
        </tr>
    </table>
</div>

</body>
</html>
