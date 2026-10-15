<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Surat Jalan - {{ $suratJalan->kode_surat_jalan }}</title>

<style>
@page {
    margin: 25px 30px 25px 30px;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    line-height: 1.3;
}

.top-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.top-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.company-title {
    font-size: 12.5px;
    font-weight: bold;
    color: #000;
    margin-bottom: 2px;
}

.company-desc {
    font-size: 10px;
    font-style: italic;
    color: #222;
    line-height: 1.35;
}

/* OUTLINE BOX FOR SURAT JALAN & RECIPIENT INFO */
.header-box {
    width: 100%;
    border: 2px solid #000;
    border-collapse: collapse;
    margin-bottom: 8px;
}

.title-bar {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    letter-spacing: 0.5px;
    padding: 6px;
    border-bottom: 2px solid #000;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table td {
    padding: 4px 6px;
    vertical-align: top;
    font-size: 11px;
}

.right-meta-table {
    width: 100%;
    border-collapse: collapse;
}

.right-meta-table td {
    padding: 2px 4px;
    font-size: 11px;
    border: none;
}

.right-meta-table tr td:first-child {
    font-weight: bold;
    width: 80px;
}

/* TABLE OF ITEMS */
.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
    margin-bottom: 10px;
}

.items-table th {
    background-color: #2b5fa8;
    color: #ffffff;
    font-weight: bold;
    text-align: center;
    padding: 6px 4px;
    border: 1px solid #000;
    font-size: 10px;
}

.items-table td {
    border: 1px solid #333;
    padding: 4px 5px;
    height: 15px;
    vertical-align: middle;
}

.items-table tr.striped {
    background-color: #e9f1fb;
}

.items-table tfoot td {
    font-weight: bold;
    border: 1px solid #000;
    padding: 5px;
    font-size: 10.5px;
    background-color: #ffffff;
}

/* SIGNATURES SECTION */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.footer-table td {
    border: none;
    vertical-align: top;
}

.notice-bold {
    font-weight: bold;
    font-style: italic;
    font-size: 10px;
}

.notice-sub {
    font-style: italic;
    font-size: 9.5px;
    margin-bottom: 15px;
}

.sig-name {
    font-weight: normal;
    font-size: 11px;
    margin-bottom: 50px;
}

.sig-line {
    font-size: 11px;
    letter-spacing: -1px;
}
</style>
</head>
<body>

<!-- TOP HEADER -->
<table class="top-table">
<tr>
    <td width="70%">
        <div class="company-title">PT Teknografi Tri Cawanaska</div>
        <div class="company-desc">
            Pergudangan Sentral Singosari<br>
            Ardimulyo, Singosari, Malang<br>
            teknografitricawanaska@gmail.com<br>
            +62895 3391 07709
        </div>
    </td>
    <td width="30%" align="right">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" style="max-height: 60px; width: auto;">
        @endif
    </td>
</tr>
</table>

<!-- SURAT JALAN & RECIPIENT BOX -->
<table class="header-box">
<tr>
    <td colspan="2" class="title-bar">
        SURAT JALAN
    </td>
</tr>
<tr>
    <td width="60%" style="padding: 6px 10px; border-right: 1.5px solid #000; vertical-align: top;">
        <div style="font-size: 11px;">Yth</div>
        <div style="font-size: 11.5px; font-weight: bold; margin: 3px 0;">
            {{ $suratJalan->salesOrder?->customer?->nama_customer ?? 'Customer' }}
        </div>
        <div style="font-size: 10.5px; color: #222;">
            di tempat
        </div>
    </td>
    <td width="40%" style="padding: 4px 6px; vertical-align: middle;">
        @php
            $noInvoice = $suratJalan->invoice?->no_invoice 
                         ?: ($suratJalan->salesOrder?->no_sales_order ?? '-');
            $tanggal = $suratJalan->tanggal_surat_jalan 
                       ? date('d M Y', strtotime($suratJalan->tanggal_surat_jalan)) 
                       : date('d M Y');
            $ekspedisi = $suratJalan->transportasi 
                         ? ($suratJalan->transportasi . ($suratJalan->no_polisi ? ' - ' . $suratJalan->no_polisi : '')) 
                         : ($suratJalan->driver ?: '-');
        @endphp
        <table class="right-meta-table">
            <tr>
                <td>No Invoice</td>
                <td>: {{ $noInvoice }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $tanggal }}</td>
            </tr>
            <tr>
                <td>Ekspedisi</td>
                <td>: {{ $ekspedisi }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- TABLE OF ITEMS -->
@php
    $rows = [];
    $totalQty = 0;

    // 1. Cek apakah SPK memiliki detail Packaging
    if ($suratJalan->spk && $suratJalan->spk->packagings()->count() > 0) {
        $itemName = $suratJalan->salesOrder?->itemable?->nama_barang 
                    ?? $suratJalan->salesOrder?->product_name 
                    ?? '-';

        foreach ($suratJalan->spk->packagings as $pkg) {
            if ($pkg->jumlah_satuan_penuh > 0 && $pkg->qty_per_satuan_penuh > 0) {
                $sub = (float) ($pkg->total_satuan_penuh ?: ($pkg->jumlah_satuan_penuh * $pkg->qty_per_satuan_penuh));
                $rows[] = [
                    'nama_item' => $itemName,
                    'qty_pcs'   => (float) $pkg->qty_per_satuan_penuh,
                    'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                    'jumlah'    => (int) $pkg->jumlah_satuan_penuh,
                    'total'     => $sub,
                    'keterangan'=> '',
                ];
                $totalQty += $sub;
            }

            if ($pkg->jumlah_satuan_sisa > 0 && $pkg->qty_per_satuan_sisa > 0) {
                $sub = (float) ($pkg->total_satuan_sisa ?: ($pkg->jumlah_satuan_sisa * $pkg->qty_per_satuan_sisa));
                $rows[] = [
                    'nama_item' => $itemName,
                    'qty_pcs'   => (float) $pkg->qty_per_satuan_sisa,
                    'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                    'jumlah'    => (int) $pkg->jumlah_satuan_sisa,
                    'total'     => $sub,
                    'keterangan'=> '',
                ];
                $totalQty += $sub;
            }
        }
    }

    // 2. Jika tipe MULTI_MATERIAL atau ada items di surat_jalan_items
    if (empty($rows) && $suratJalan->items && $suratJalan->items->count() > 0) {
        foreach ($suratJalan->items as $it) {
            $sub = (float) ($it->qty_kirim ?? 0);
            $satuan = $it->material?->satuan?->nama_satuan ?? 'Pcs';
            $rows[] = [
                'nama_item' => $it->material?->nama_master_item ?? '-',
                'qty_pcs'   => $sub,
                'kemasan'   => $satuan,
                'jumlah'    => 1,
                'total'     => $sub,
                'keterangan'=> ($suratJalan->keterangan && $suratJalan->keterangan !== '-') ? $suratJalan->keterangan : '',
            ];
            $totalQty += $sub;
        }
    }

    // 3. Fallback jika SPK tunggal atau material tunggal tanpa repeater
    if (empty($rows)) {
        $sub = (float) ($suratJalan->qty_pengiriman ?: 0);
        $itemName = $suratJalan->salesOrder?->itemable?->nama_barang 
                    ?? $suratJalan->salesOrder?->product_name 
                    ?? '-';
        $rows[] = [
            'nama_item' => $itemName,
            'qty_pcs'   => $sub,
            'kemasan'   => 'Box',
            'jumlah'    => 1,
            'total'     => $sub,
            'keterangan'=> ($suratJalan->keterangan && $suratJalan->keterangan !== '-') ? $suratJalan->keterangan : '',
        ];
        $totalQty += $sub;
    }

    $rowCount = 0;
@endphp

<table class="items-table">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="30%">Nama Item</th>
            <th width="12%">Q'ty (pcs)</th>
            <th width="11%">Kemasan</th>
            <th width="9%">Jumlah</th>
            <th width="14%">Total</th>
            <th width="20%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        @php
            $rowCount++;
            $isStriped = ($rowCount % 2 == 0);
        @endphp
        <tr class="{{ $isStriped ? 'striped' : '' }}">
            <td align="center">{{ $rowCount }}</td>
            <td>{{ $row['nama_item'] }}</td>
            <td align="center">{{ number_format($row['qty_pcs'], 0, ',', '.') }}</td>
            <td align="center">{{ $row['kemasan'] }}</td>
            <td align="center">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
            <td align="right">{{ number_format($row['total'], 0, ',', '.') }}</td>
            <td>{{ $row['keterangan'] }}</td>
        </tr>
    @endforeach

    {{-- Baris kosong bergaris (zebra stripes) seperti dokumen asli TTC --}}
    @php
        $minRows = 8;
        $emptyRows = max(1, $minRows - $rowCount);
    @endphp

    @for($i = 0; $i < $emptyRows; $i++)
        @php
            $rowCount++;
            $isStriped = ($rowCount % 2 == 0);
        @endphp
        <tr class="{{ $isStriped ? 'striped' : '' }}">
            <td align="center">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    @endfor
    </tbody>

    <tfoot>
        <tr>
            <td colspan="5" align="right" style="padding-right: 15px;">Total</td>
            <td align="right">{{ number_format($totalQty, 0, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
    </tfoot>
</table>

<!-- BOTTOM NOTICE & SIGNATURE -->
<table class="footer-table">
<tr>
    <td width="60%">
        <div class="notice-bold">BARANG SUDAH DITERIMA DALAM KEADAAN BAIK DAN SESUAI</div>
        <div class="notice-sub">(tanda tangan dan cap perusahaan)</div>
        <div class="sig-name">Penerima</div>
        <div class="sig-line">_________________________________</div>
    </td>
    <td width="40%" align="center">
        <div style="height: 28px;">&nbsp;</div>
        <div class="sig-name" style="text-align: center;">Pengirim</div>
        <div class="sig-line" style="text-align: center;">_________________________________</div>
    </td>
</tr>
</table>

</body>
</html>
