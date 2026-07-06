<!DOCTYPE html>
<html>
<head>
<style>

body{
    font-family: sans-serif;
    font-size:13px;
}

.header-box{
    border:2px solid black;
    padding:12px;
}

.title{
    background:#dce7f8;
    border-radius:10px;
    padding:12px;
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

<div class="header-box">

<table style="border:none;">
<tr>

<!-- Logo -->
<td width="12%" style="border:none; text-align:center; vertical-align:middle;">

    <img
        src="{{ public_path('images/logo.png') }}"
        width="55">

</td>

<!-- Informasi perusahaan -->
<td width="58%" style="border:none;">

    <h2 style="margin:0; font-size:22px;">
        CV. Indigama Khatulistiwa
    </h2>

    Dsn. Blimbing RT 02 RW 11, Ds. Bulusari, Kec. Gempol,<br>
    Pasuruan, Jawa Timur 67155<br>

    Email : indigama.khatulistiwa01@gmail.com<br>

    Telp : 081703101012

</td>

<!-- Judul -->
<td width="30%" style="border:none;">

    <div class="title">

        <div style="font-size:18px;font-weight:bold;">
            PURCHASE REQUEST
        </div>

        <div style="
            margin-top:8px;
            font-size:20px;
            font-weight:bold;
        ">
            {{ $pr->nomor_pr }}
        </div>

    </div>

</td>

</tr>
</table>

</div>

<br>

<br>

<div class="header-box">

<h3 align="center">
DATA ITEM
</h3>

<table>

<thead>

<tr class="blue">

<th>No</th>
<th>Item</th>
<th>Qty</th>
<th>Satuan</th>
<th>ETA</th>

</tr>

</thead>

<tbody>

@foreach($pr->items as $item)

<tr>

<td>
{{ $loop->iteration }}
</td>

<td>
{{ $item->item->nama_master_item }}
</td>

<td align="center">
{{ number_format($item->qty) }}
</td>

<td align="center">
{{ $item->satuan }}
</td>

<td align="center">
{{ \Carbon\Carbon::parse($item->eta)->format('d-m-Y') }}
</td>

</tr>

@endforeach

</tbody>

</table>

<br>

<b>NOTE :</b>

@foreach($pr->items as $item)

<p>
{{ $loop->iteration }}.
{{ $item->catatan }}
</p>

@endforeach

<br><br><br>

<table border="0">

<tr>

<td align="center">
Dibuat Oleh,
</td>

<td align="center">
Disetujui Oleh,
</td>

</tr>

</table>

</div>

</body>
</html>