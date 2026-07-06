<!DOCTYPE html>
<html>
<head>
<style>

body{
    font-family:sans-serif;
    font-size:13px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid black;
    padding:6px;
    vertical-align:top;
}

.header-box{
    border:2px solid black;
    padding:15px;
    margin-bottom:20px;
}

.no-border td{
    border:none;
    padding:2px;
}

.title{
    font-size:20px;
    font-weight:bold;
    text-align:right;
}

.signature td{
    border:none;
    text-align:center;
}

thead{
    display:table-header-group;
}

tr{
    page-break-inside:avoid;
}
.blue{
    background:#2352e5;
    color:white;
}

th{
    background:#2352e5;
    color:white;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid black;
    padding:8px;
}

</style>
</head>
<body>

<!-- HEADER -->
<div class="header-box">

<table class="no-border">

<tr>

<td width="12%" align="center">

<img
    src="{{ public_path('images/logo.png') }}"
    width="70">

</td>

<td width="58%">

<h2 style="margin:0">
CV. Indigama Khatulistiwa
</h2>

Dsn. Blimbing RT 02 RW 11, Ds. Bulusari, Kec. Gempol,<br>
Pasuruan, Jawa Timur 67155<br>

Email: indigama.khatulistiwa01@gmail.com<br>

Telp: 081703101012

</td>

<td width="30%" style="vertical-align:top">

<div class="title">
SUBCOUNT IN
</div>

</td>

</tr>

</table>

</div>

<!-- INFORMASI -->
<div class="header-box">

<table class="no-border">

<tr>

<td width="25%">
<b>No. Subcount In</b>
</td>

<td width="5%">
:
</td>

<td width="30%">
{{ $subcountIn->no_subcount }}
</td>

<td width="15%">
<b>Tanggal</b>
</td>

<td width="5%">
:
</td>

<td>
{{ \Carbon\Carbon::parse($subcountIn->tanggal_subcount)->format('d-m-Y') }}
</td>

</tr>

<tr>
<td><b>No. Surat Jalan</b></td>
<td>:</td>
<td>{{ $subcountIn->surat_jalan_pengiriman }}</td>
</tr>

<tr>
<td><b>Admin Produksi</b></td>
<td>:</td>
<td>{{ $subcountIn->admin_produksi }}</td>
</tr>

<tr>
<td><b>Supervisor</b></td>
<td>:</td>
<td>{{ $subcountIn->supervisor }}</td>
</tr>

<tr>
<td><b>Admin Mainstore</b></td>
<td>:</td>
<td>{{ $subcountIn->admin_mainstore }}</td>
</tr>

<tr>
<td><b>Keterangan</b></td>
<td>:</td>
<td colspan="4">
{{ $subcountIn->keterangan }}
</td>
</tr>

</table>

</div>


<!-- DATA ITEM -->
<div class="header-box">

<h3 align="center">
DATA ITEM
</h3>

<table>

<thead>

<tr>

<th width="5%">
No
</th>

<th width="16%">
No. Subcount Out
</th>

<th>
Item
</th>

<th width="8%">
Qty
</th>

<th width="15%">
Konversi Ke
</th>

<th width="10%">
Qty Konversi
</th>

<th width="18%">
Keterangan
</th>

</tr>

</thead>

<tbody>

@foreach($subcountIn->items as $item)

<tr>

<td>
{{ $loop->iteration }}
</td>

<td>
    {{ optional($item->subcountOut)->surat_jalan_subcount }}
</td>

<td>

{{ $item->itemHasil?->nama_master_item ?? '-' }}

</td>

<td align="center">

{{ number_format($item->qty_dikirim) }}

</td>

<td align="center">

{{ $item->itemHasil?->nama_master_item ?? '-' }}

</td>

<td align="center">

{{ number_format($item->qty_diterima) }}

</td>

<td>

{{ $item->keterangan }}

</td>

</tr>

@endforeach

</tbody>

</table>

<br><br><br><br>

<table class="signature">

<tr>

<td>
Admin Produksi,
</td>

<td>
Supervisor,
</td>

<td>
Admin Mainstore,
</td>

</tr>

<tr>

<td height="100">

<br><br><br>

( ................................ )

</td>

<td>

<br><br><br>

( ................................ )

</td>

<td>

<br><br><br>

( ................................ )

</td>

</tr>

</table>

</div>

</body>
</html>