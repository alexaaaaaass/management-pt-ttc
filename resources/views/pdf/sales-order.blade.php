<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family:sans-serif;
    font-size:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid black;
    padding:5px;
}

.header-table td{
    border:none;
}

.title{
    text-align:center;
    font-size:22px;
    font-weight:bold;
    border:2px solid black;
    padding:8px;
}

thead{
    background:#dbe3f3;
}

.signature{
    width:250px;
    height:100px;
    border:2px solid black;
    text-align:center;
}

</style>

</head>
<body>

<table class="header-table">

<tr>

<td width="50%">

<b>PT Teknografi Tri Cawanaska</b><br>
Pergudangan Sentra Singosari<br>
Ardimulyo, Singosari, Malang<br>
teknografitricawanaska@gmail.com<br>
+62895339107709

</td>

<td align="right">

<img src="{{ public_path('images/logo.png') }}"
width="120">

</td>

</tr>

</table>

<br>

<table class="header-table">

<tr>

<td width="60%">

<b>Order ID</b>

{{$salesOrder->no_sales_order}}

<br><br>

<b>Date</b>

{{date('d M Y',strtotime($salesOrder->tanggal_po))}}

<br><br>

<b>Cust</b>

{{$salesOrder->customer->nama_customer}}

</td>

<td>

<b>Doc No</b>

SC-{{$salesOrder->no_sales_order}}

<br><br>

<b>Lead Time</b>

32 Hari Kerja

<br><br>

<b>Cust ID</b>

{{$salesOrder->customer->kode_customer}}

</td>

</tr>

</table>

<br>

<div class="title">
ORDER CONFIRMATION
</div>

<table>

<thead>

<tr>

<th>No</th>
<th>Item</th>
<th>Qty</th>
<th>Harga</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<tr>

<td>1</td>

<td>
{{$salesOrder->itemable->nama_barang}}
</td>

<td align="center">
{{number_format($salesOrder->qty)}}
</td>

<td align="right">
{{number_format($salesOrder->harga_pcs)}}
</td>

<td align="right">

{{number_format(
$salesOrder->qty*$salesOrder->harga_pcs
)}}

</td>

</tr>

</tbody>

</table>

<br>

<table class="header-table">

<tr>

<td width="70%">

<h3>Terms & Conditions</h3>

1. Spesifikasi, quantity dan harga telah disepakati.<br><br>

2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.<br><br>

3. Produksi dimulai setelah dokumen disetujui.

</td>

<td>

<table>

<tr>

<td>Subtotal</td>

<td align="right">

Rp
{{number_format(
$salesOrder->qty*$salesOrder->harga_pcs
)}}

</td>

</tr>

<tr>

<td>Total</td>

<td align="right">

Rp
{{number_format(
$salesOrder->qty*$salesOrder->harga_pcs
)}}

</td>

</tr>

</table>

</td>

</tr>

</table>

<br><br>

<table class="header-table">

<tr>

<td align="center">

<div class="signature">

<br>

<b>Disetujui</b>

<br><br><br><br>

(........................)

</div>

</td>

<td align="center">

<div class="signature">

<br>

<b>Diketahui</b>

<br>

PT Teknografi Tri Cawanaska

<br><br><br>

(........................)

</div>

</td>

</tr>

</table>

</body>
</html>