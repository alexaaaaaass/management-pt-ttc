<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans;
            font-size:12px;
            color:#222;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,td{
            border:1px solid #000;
            padding:7px;
        }

        .header-box{
            border:2px solid #000;
            padding:12px;
        }

        .title{
            background:#dce7f8;
            border-radius:10px;
            padding:12px;
            text-align:center;
        }

        .blue{
            background:#2352e5;
            color:#fff;
        }

        .section-title{
            font-size:15px;
            font-weight:bold;
            margin-bottom:10px;
        }

        .text-center{
            text-align:center;
        }

        .no-border td{
            border:none;
        }

    </style>

</head>

<body>

<!-- =======================================================
HEADER
======================================================= -->

<div class="header-box">

<table class="no-border">

<tr>

<td width="12%" class="text-center">

    <img
        src="{{ public_path('images/logo.png') }}"
        width="55">

</td>

<td width="58%">

    <h2 style="margin:0">
        CV. Indigama Khatulistiwa
    </h2>

    Dsn. Blimbing RT 02 RW 11, Ds. Bulusari, Kec. Gempol<br>

    Pasuruan, Jawa Timur 67155<br>

    Email :
    indigama.khatulistiwa01@gmail.com<br>

    Telp :
    081703101012

</td>

<td width="30%">

<div class="title">

<div
style="
margin-top:8px;
font-size:20px;
font-weight:bold;
">

{{ $imr->no_imr }}

</div>

</div>

</td>

</tr>

</table>

</div>

<br>

<!-- =======================================================
INFORMASI IMR
======================================================= -->

<div class="header-box">

<table>

<tr>

<td width="25%">
Tanggal Permintaan
</td>

<td width="25%">
{{ \Carbon\Carbon::parse($imr->tanggal_request)->format('d-m-Y') }}
</td>

<td width="25%">
Departemen
</td>

<td width="25%">
{{ $imr->departemen?->nama_departemen }}
</td>

</tr>

<tr>

<td>
No SPK
</td>

<td>

{{ $imr->spk?->no_spk }}

</td>

<td>
Status
</td>

<td>

{{ $imr->status }}

</td>

</tr>

</table>

</div>

<br>

<!-- =======================================================
DETAIL ITEM
======================================================= -->

<div class="header-box">

<h3 align="center">

DETAIL MATERIAL

</h3>

<table>

<thead>

<tr class="blue">

<th width="5%">
No
</th>

<th width="15%">
Kode Item
</th>

<th>
Nama Material
</th>

<th width="12%">
Qty Request
</th>

<th width="10%">
Satuan
</th>

<th width="28%">
Catatan
</th>

</tr>

</thead>

<tbody>

@foreach($imr->items as $item)

<tr>

<td align="center">

{{ $loop->iteration }}

</td>

<td>

{{ $item->item?->kode_master_item }}

</td>

<td>

{{ $item->item?->nama_master_item }}

</td>

<td align="center">

{{ number_format($item->qty_request,2) }}

</td>

<td align="center">

{{ $item->item?->satuan?->nama_satuan }}

</td>

<td>

{{ $item->catatan ?: '-' }}

</td>

</tr>

@endforeach

</tbody>

</table>

@if($imr->items->whereNotNull('catatan')->count())

<br>

<b>Catatan :</b>

@foreach($imr->items as $item)

@if($item->catatan)

<p>

{{ $loop->iteration }}.
{{ $item->catatan }}

</p>

@endif

@endforeach

@endif

<br><br><br>

<table class="no-border">

<tr>

<td align="center">

Diajukan Oleh

<br><br><br><br>

(.................................)

</td>

<td align="center">

Disetujui Oleh

<br><br><br><br>

(.................................)

</td>

<td align="center">

Gudang

<br><br><br><br>

(.................................)

</td>

</tr>

</table>

</div>

</body>

</html>