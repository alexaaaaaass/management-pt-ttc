<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $suratJalan->kode_surat_jalan }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 8mm 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        * { box-sizing: border-box; }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .page {
            width: 100%;
            min-height: 100vh;
            background: #fff;
        }

        .topbar {
            width: 100%;
            margin-bottom: 10px;
        }

        .company {
            width: 60%;
            vertical-align: top;
            padding-top: 4px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.2px;
            margin-bottom: 2px;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.5;
            color: #222;
        }

        .logo-cell {
            width: 40%;
            text-align: right;
            vertical-align: middle;
        }

        .logo-img {
            max-height: 80px;
            width: auto;
            display: inline-block;
        }

        .title-box {
            border: 2px solid #000;
            background: #fff;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 10px 6px;
            margin-bottom: 8px;
        }

        .meta-box {
            border: 1.5px solid #000;
            width: 100%;
        }

        .meta-box td {
            vertical-align: top;
            padding: 6px 8px;
            border: 1px solid #000;
        }

        .meta-left {
            width: 62%;
            font-size: 11px;
        }

        .meta-right {
            width: 38%;
            font-weight: bold;
            font-size: 11px;
        }

        .meta-label {
            font-weight: bold;
            width: 90px;
        }

        .item-table {
            border: 1.5px solid #000;
            margin-top: 8px;
        }

        .item-table th {
            background: #dfe7f5;
            color: #000;
            border: 1px solid #000;
            font-size: 11px;
            font-weight: bold;
            padding: 6px 4px;
            text-align: center;
        }

        .item-table td {
            border: 1px solid #000;
            font-size: 11px;
            padding: 5px 6px;
            background: #fff;
            vertical-align: top;
        }

        .item-table .right {
            text-align: right;
        }

        .item-table .center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background: #eef3ff;
        }

        .summary-row {
            font-size: 13px;
            font-weight: bold;
        }

        .statement {
            margin-top: 14px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            font-style: italic;
        }

        .signature-table {
            width: 100%;
            margin-top: 18px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            font-size: 12px;
            padding: 0 18px;
            vertical-align: bottom;
        }

        .signature-line {
            border-bottom: 1.5px solid #000;
            width: 100%;
            height: 44px;
            margin: 0 auto 6px auto;
        }

        .muted {
            color: #333;
        }
    </style>
</head>
<body>
    @php
        $customer = $suratJalan->salesOrder?->customer;
        $itemable = $suratJalan->salesOrder?->itemable;
        $itemName = $itemable?->nama_barang ?? $itemable?->nama_master_item ?? 'Barang';
        $qty = (float) ($suratJalan->qty_pengiriman ?? 0);
        $amount = (float) ($suratJalan->salesOrder?->harga_pcs ?? 0);
        $total = $qty * $amount;
        $invoiceNo = $suratJalan->invoice?->kode_invoice ?? '-';
        $tanggal = $suratJalan->tanggal_surat_jalan ? \Carbon\Carbon::parse($suratJalan->tanggal_surat_jalan)->format('d/m/Y') : '-';
    @endphp

    <div class="page">
        <table class="topbar">
            <tr>
                <td class="company">
                    <div class="company-name">PT Teknografi Tri Cawanaska</div>
                    <div class="company-info">
                        Pergudangan Sentra Singosari<br>
                        Ardimulyo, Singosari, Malang<br>
                        teknografitricawanaska@gmail.com<br>
                        +62895 3391 07709
                    </div>
                </td>
                <td class="logo-cell">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" alt="Logo TTC" class="logo-img">
                    @else
                        <div style="font-size: 28px; font-weight: bold; color: #1d4ed8;">TTC</div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="title-box">SURAT JALAN</div>

        <table class="meta-box">
            <tr>
                <td class="meta-left">
                    <table style="width:100%;">
                        <tr>
                            <td class="meta-label">Yth</td>
                            <td style="font-weight:bold;">{{ strtoupper($customer?->nama_customer ?? 'Customer') }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{ $customer?->alamat ?? 'di tempat' }}</td>
                        </tr>
                    </table>
                </td>
                <td class="meta-right">
                    <table style="width:100%;">
                        <tr>
                            <td style="width: 45%;">No Invoice</td>
                            <td>: {{ $invoiceNo }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>: {{ $tanggal }}</td>
                        </tr>
                        <tr>
                            <td>Ekspedisi</td>
                            <td>: {{ $suratJalan->transportasi ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 35%;">Nama Item</th>
                    <th style="width: 12%;">Q'ty (pcs)</th>
                    <th style="width: 12%;">Kemasan</th>
                    <th style="width: 12%;">Jumlah</th>
                    <th style="width: 14%;">Total</th>
                    <th style="width: 9%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rows = [
                        [
                            'name' => $itemName,
                            'qty' => $qty,
                            'packing' => 'Box',
                            'amount' => number_format($qty, 0, ',', '.'),
                            'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                            'note' => '-' 
                        ],
                    ];
                @endphp

                @foreach($rows as $i => $row)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td class="right">{{ $row['qty'] }}</td>
                        <td class="center">{{ $row['packing'] }}</td>
                        <td class="right">{{ $row['amount'] }}</td>
                        <td class="right">{{ $row['total'] }}</td>
                        <td class="center">{{ $row['note'] }}</td>
                    </tr>
                @endforeach

                @for($i = count($rows); $i < 4; $i++)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor

                <tr class="total-row summary-row">
                    <td colspan="5" class="right">Total</td>
                    <td class="right">Rp {{ number_format($total, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="statement">BARANG SUDAH DITERIMA DALAM KEADAAN BAIK DAN SESUAI</div>

        <table class="signature-table">
            <tr>
                <td>
                    <div>Penerima</div>
                    <div class="signature-line"></div>
                </td>
                <td>
                    <div>Pengirim</div>
                    <div class="signature-line"></div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
