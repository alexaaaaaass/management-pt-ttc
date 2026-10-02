<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>

        
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .border {
            border: 1px solid #000;
        }

        td,
        th {
            padding: 6px;
            vertical-align: top;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
        }

        .section {
            border: 1px solid #000;
            margin-top: 12px;
        }

        .no-border td {
            border: none;
            padding: 4px;
        }

        .item-table th,
     .item-table td{
    border:1px solid #000;
}

       .item-table th {
    text-align: center;
    background: #80a9ec; /* Biru */
    color: #fff;
    font-weight: bold;
}


        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .signature {
            margin-top: 60px;
        }

        .signature td {
            border: none;
            text-align: center;
            padding-top: 70px;
        }

        footer {
            position: fixed;
            bottom: -5px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }

        .pagenum:before {
            content: counter(page);
        }

        .pagecount:before {
            content: counter(pages);
        }
        .total-box{
    margin-top:12px;
    width:40%;
    float:right;
}

.total-box td{
    border:1px solid #000;
    padding:6px;
}

.total-title{
    background:#1f4e78;
    color:#fff;
    font-weight:bold;
}

.text-bold{
    font-weight:bold;
}
    </style>

</head>

<body>

    {{-- HEADER --}}

    <table class="border">

        <tr>

            <td width="12%" style="border:none">

                <img src="{{ public_path('images/logo.png') }}" width="70">

            </td>

            <td width="53%" style="border:none">

                <div class="company-name">
                    CV. Indigama Khatulistiwa
                </div>

                Dsn. Blimbing RT 02 RW 11, Ds. Bulusari<br>
                Kec. Gempol, Pasuruan - Jawa Timur 67155<br>

                Email : indigama.khatulistiwa01@gmail.com<br>

                Telp : 081703101012

            </td>

            <td width="35%" style="border:none">

                <div class="title">

                    LAPORAN PENERIMAAN BARANG

                </div>

            </td>

        </tr>

    </table>


    {{-- INFORMASI --}}

    <table class="section no-border">

        <tr>

            <td width="23%"><b>No.Penerimaan Barang</b></td>

            <td width="27%">: {{ $pb->no_penerimaan }}</td>

            <td width="20%"><b>Referensi (No.PO)</b></td>

            <td>: {{ $pb->purchaseOrder->no_po }}</td>

        </tr>

        <tr>

            <td><b>Tgl.Penerimaan</b></td>

            <td>: {{ \Carbon\Carbon::parse($pb->tanggal_terima)->format('d-m-Y') }}</td>

            <td></td>

            <td></td>

        </tr>

        <tr>

            <td><b>Supplier</b></td>

            <td>: {{ $pb->purchaseOrder->supplier->nama_supplier ?? '-' }}</td>

            <td></td>

            <td></td>

        </tr>

        <tr>

            <td><b>No.Surat Jalan</b></td>

            <td>: {{ $pb->no_surat_jalan }}</td>

            <td></td>

            <td></td>

        </tr>

        <tr>

            <td><b>No.Pol / Nama Pengirim</b></td>

            <td>

                : {{ $pb->nopol_kendaraan ?: '-' }}

                @if($pb->nama_pengirim)

                    / {{ $pb->nama_pengirim }}

                @endif

            </td>

            <td></td>

            <td></td>

        </tr>

        <tr>

            <td><b>Keterangan</b></td>

            <td colspan="3">

                : {{ $pb->catatan_pengiriman ?: '-' }}

            </td>

        </tr>

    </table>


    {{-- JUDUL ITEM --}}

    <table class="border" style="margin-top:10px;">

        <tr>

            <th style="text-align:center">

                DATA ITEM

            </th>

        </tr>

    </table>


    {{-- TABEL ITEM --}}

   <table class="item-table">

    <thead>

    <tr>

        <th width="5%">No</th>

        <th width="12%">Kode</th>

        <th>Nama Item</th>

        <th width="8%">Qty PO</th>

        <th width="10%">Diterima Sebelum</th>

        <th width="10%">Diterima</th>

        <th width="8%">Sisa</th>

        <th width="8%">Satuan</th>

        <th width="10%">LOT</th>

        <th width="10%">EXP</th>

    </tr>

    </thead>

    <tbody>

@forelse($pb->items as $index => $row)

@php

$poItem = $row->purchaseOrderItem;

$qtyPO = $poItem->qty_po;

$qtyTerima = $row->qty_terima;

// qty_received sekarang sudah termasuk penerimaan ini,
// jadi kurangi agar mendapatkan penerimaan sebelumnya.
$qtySebelumnya = max(0, $poItem->qty_received - $qtyTerima);

$sisa = $qtyPO - $poItem->qty_received;

@endphp

<tr>

<td align="center">
{{ $index+1 }}
</td>

<td>
{{ $poItem->item->kode_material }}
</td>

<td>
{{ $poItem->item->nama_master_item }}
</td>

<td align="right">
{{ number_format($qtyPO,2) }}
</td>

<td align="right">
{{ number_format($qtySebelumnya,2) }}
</td>

<td align="right">
<strong>{{ number_format($qtyTerima,2) }}</strong>
</td>

<td align="right">
{{ number_format($sisa,2) }}
</td>

<td align="center">
{{ $poItem->item->satuan->nama_satuan }}
</td>

<td align="center">
{{ $row->no_lot ?: '-' }}
</td>

<td align="center">
{{ $row->tgl_exp ? \Carbon\Carbon::parse($row->tgl_exp)->format('d-m-Y') : '-' }}
</td>

</tr>

@empty

<tr>

<td colspan="10" align="center">

Tidak ada data item.

</td>

</tr>

@endforelse

</tbody>

</table>


    {{-- TANDA TANGAN --}}

    <table class="signature">

        <tr>

            <td>

                Pengirim,

            </td>

            <td>

                Penerima,

            </td>

        </tr>

        <tr>

            <td>

                (........................................)

            </td>

            <td>

                (........................................)

            </td>

        </tr>

    </table>


    {{-- FOOTER --}}

    <footer>

        Halaman

        <span class="pagenum"></span>

        /

        <span class="pagecount"></span>

    </footer>

</body>

</html>