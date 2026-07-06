```html
<!DOCTYPE html>
<html>
<head>
<style>

    .header-box{
    border:2px solid black;
    padding:12px;
    margin-bottom:15px;
}

.page-break{
    page-break-inside:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

tr{
    page-break-inside:avoid;
    page-break-after:auto;
}

thead{
    display:table-header-group;
}

tfoot{
    display:table-footer-group;
}

body{
    font-family: sans-serif;
    font-size:13px;
}

.header-box{
    border:2px solid black;
    padding:12px;
    margin-bottom:15px;
}

.title{
    background:#dce7f8;
    border-radius:10px;
    padding:12px;
    text-align:center;
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

.no-border td{
    border:none;
    padding:4px;
}

.signature td{
    border:none;
    text-align:center;
}

</style>
</head>
<body>

<!-- HEADER -->
<div class="header-box">

<table class="no-border">

<tr>

<td width="12%" style="text-align:center; vertical-align:middle">

    <img
        src="{{ public_path('images/logo.png') }}"
        width="55">

</td>

<td width="58%">

    <h2 style="margin:0">
        CV. Indigama Khatulistiwa
    </h2>

    Dsn. Blimbing RT 02 RW 11, Ds. Bulusari, Kec. Gempol,<br>
    Pasuruan, Jawa Timur 67155<br>

    Email : indigama.khatulistiwa01@gmail.com<br>

    Telp : 081703101012

</td>

<td width="30%">

<div class="title">

<div style="font-size:18px;font-weight:bold">
PURCHASE ORDER
</div>

<div style="margin-top:10px;font-size:18px;font-weight:bold">
{{ $po->no_po }}
</div>

</div>

</td>

</tr>

</table>

</div>


<!-- INFORMASI PO -->
<div class="header-box">

<table>

<tr>
<td width="25%">No PO</td>
<td width="5%">:</td>
<td>{{ $po->no_po }}</td>
</tr>

<tr>
<td>No PR</td>
<td>:</td>
<td>{{ $po->purchaseRequest->nomor_pr }}</td>
</tr>

<tr>
<td>Tanggal PO</td>
<td>:</td>
<td>{{ \Carbon\Carbon::parse($po->po_date)->format('d-m-Y') }}</td>
</tr>

<tr>
<td>Supplier</td>
<td>:</td>
<td>{{ $po->supplier->nama_supplier }}</td>
</tr>

<tr>
<td>ETA</td>
<td>:</td>
<td>
{{ $po->eta ? \Carbon\Carbon::parse($po->eta)->format('d-m-Y') : '-' }}
</td>
</tr>

<tr>
<td>Currency</td>
<td>:</td>
<td>{{ $po->currency }}</td>
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

<th>No</th>
<th>Item</th>
<th>Qty</th>
<th>Satuan</th>
<th>Harga</th>
<th>Diskon</th>
<th>Total</th>

</tr>

</thead>

<tbody>

@foreach($po->items as $item)

<tr>

<td align="center">
{{ $loop->iteration }}
</td>

<td>
{{ $item->item->nama_master_item }}
</td>

<td align="center">
{{ number_format($item->qty_po) }}
</td>

<td align="center">
{{ $item->satuan }}
</td>

<td align="right">
     Rp {{ number_format($item->price,0,',','.') }}
</td>

<td align="center">
{{ $item->discount }} %
</td>

<td align="right">
Rp {{ number_format($item->total,0,',','.') }}
</td>

</tr>

@endforeach

</tbody>

</table>


@php

$subtotal = $po->items->sum('total');

$grandTotal =
$subtotal
+ ($po->ppn ?? 0)
+ ($po->ongkir ?? 0)
- ($po->dp ?? 0);

@endphp

<br>

<table>

<tr>
<td width="80%" align="right">
<b>Subtotal</b>
</td>

<td align="right">
Rp {{ number_format($subtotal,0,',','.') }}
</td>
</tr>

<tr>
<td align="right">
<b>PPN</b>
</td>

<td align="right">
Rp {{ number_format($po->ppn,0,',','.') }}
</td>
</tr>

<tr>
<td align="right">
<b>Ongkir</b>
</td>

<td align="right">
Rp {{ number_format($po->ongkir,0,',','.') }}
</td>
</tr>

<tr>
<td align="right">
<b>DP</b>
</td>

<td align="right">
Rp {{ number_format($po->dp,0,',','.') }}
</td>
</tr>

<tr>

<td align="right">

<b>
GRAND TOTAL
</b>

</td>

<td align="right">

<b>
Rp {{ number_format($grandTotal,0,',','.') }}
</b>

</td>

</tr>

</table>

<br>



</table>

</div>

<div class="">

    <b>NOTE :</b>

@foreach($po->items as $item)

@if($item->catatan)

<p>
{{ $loop->iteration }}.
{{ $item->catatan }}
</p>

@endif

@endforeach

<br><br><br>

<table class="signature">

<tr>

<td>
Dibuat Oleh
</td>

<td>
Diperiksa Oleh
</td>

<td>
Disetujui Oleh
</td>

</tr>

<tr>

<td style="height:80px">




(.....................)

</td>

<td>




(.....................)

</td>

<td>




(.....................)

</td>

</tr>
</div>

</body>
</html>
```
