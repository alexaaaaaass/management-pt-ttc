<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Surat Jalan - {{ $suratJalan->kode_surat_jalan }}</title>

<style>
@page {
    size: A4 landscape;
    margin: 22px 28px 22px 28px;
}

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
    line-height: 1.35;
    margin: 0;
}

.top-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
}

.top-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.company-title {
    font-size: 13px;
    font-weight: bold;
    color: #000;
    margin-bottom: 2px;
}

.company-desc {
    font-size: 10px;
    font-style: italic;
    color: #111;
    line-height: 1.45;
}

/* TITLE BAR & META LAYOUT MATCHING TTC REFERENCE IMAGE 100% */
.header-box {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
}

.title-bar {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    letter-spacing: 0.5px;
    padding: 7px 6px;
    border: 2px solid #000;
}

.recipient-cell {
    width: 63%;
    padding: 6px 4px 6px 4px;
    border: none;
    vertical-align: top;
}

.meta-cell {
    width: 37%;
    padding: 0;
    border-left: 2px solid #000;
    border-right: 2px solid #000;
    border-bottom: 2px solid #000;
    vertical-align: top;
}

.meta-grid {
    width: 100%;
    border-collapse: collapse;
}

.meta-grid td {
    padding: 3px 8px;
    font-size: 11px;
    vertical-align: middle;
    border: none;
}

.meta-grid td.meta-label {
    width: 34%;
    font-weight: bold;
    border-right: 1.5px solid #000;
}

/* TABLE OF ITEMS MATCHING TTC REFERENCE IMAGE 100% */
.items-table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #000;
    font-size: 10.5px;
    margin-bottom: 4px;
}

.items-table th {
    background-color: #ffffff;
    color: #1f4e78;
    font-weight: bold;
    text-align: center;
    padding: 5px 4px;
    border-left: 1px solid #000;
    border-right: 1px solid #000;
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
    font-size: 10.5px;
}

.items-table tbody td {
    border: 1px solid #000;
    padding: 3.5px 6px;
    height: 18px;
    vertical-align: middle;
    color: #2f5597;
}

.items-table tbody tr.striped td {
    background-color: #d9e1f2;
}

.items-table tfoot td {
    font-weight: bold;
    border-top: 2px solid #000;
    border-bottom: 2px solid #000;
    border-left: 1px solid #000;
    border-right: 1px solid #000;
    padding: 4px 6px;
    font-size: 11px;
}

.items-table tfoot td.total-label-cell {
    background-color: #d9e1f2;
    color: #1f4e78;
    text-align: right;
    padding-right: 18px;
}

.items-table tfoot td.total-value-cell {
    background-color: #ffffff;
    color: #000000;
    text-align: center;
}

/* SIGNATURES SECTION MATCHING TTC REFERENCE IMAGE 100% */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 2px;
}

.footer-table td {
    border: none;
    vertical-align: top;
}

.notice-bold {
    font-weight: bold;
    font-style: italic;
    font-size: 10.5px;
    color: #000;
}

.notice-sub {
    font-style: italic;
    font-size: 10px;
    margin-top: 2px;
    margin-bottom: 2px;
    color: #000;
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
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" style="max-height: 65px; width: auto;" alt="Logo TTC">
        @elseif(file_exists(public_path('images/Logo PT Teknografi.png')))
            <img src="{{ public_path('images/Logo PT Teknografi.png') }}" style="max-height: 65px; width: auto;" alt="Logo TTC">
        @elseif(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" style="max-height: 65px; width: auto;" alt="Logo TTC">
        @endif
    </td>
</tr>
</table>

<!-- SURAT JALAN & RECIPIENT BOX -->
@php
    $so = $suratJalan->salesOrder ?? $suratJalan->spk?->salesOrder;
    $customerName = $so?->customer?->nama_customer
                    ?? ($suratJalan->invoice?->customer?->nama_customer ?? 'Customer');

    $noInvoice = $so?->no_sales_order
                 ?: ($suratJalan->invoice?->kode_invoice ?: ($suratJalan->kode_surat_jalan ?: '-'));

    $tanggal = $suratJalan->tanggal_surat_jalan 
               ? date('d M Y', strtotime($suratJalan->tanggal_surat_jalan)) 
               : date('d M Y');

    $ekspedisi = $suratJalan->transportasi ?: ($suratJalan->driver ?: '');
@endphp

<table class="header-box">
<tr>
    <td colspan="2" class="title-bar">
        SURAT JALAN
    </td>
</tr>
<tr>
    <td class="recipient-cell">
        <div style="font-size: 11px;">Yth</div>
        <div style="font-size: 11px; margin: 2px 0;">
            {{ $customerName }}
        </div>
        <div style="font-size: 11px;">
            {{ $suratJalan->alamat_tujuan ?: 'di tempat' }}
        </div>
    </td>
    <td class="meta-cell">
        <table class="meta-grid">
            <tr>
                <td class="meta-label">No Invoice</td>
                <td>{{ $noInvoice }}</td>
            </tr>
            <tr>
                <td class="meta-label">Tanggal</td>
                <td>{{ $tanggal }}</td>
            </tr>
            <tr>
                <td class="meta-label">Ekspedisi</td>
                <td>{{ $ekspedisi }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- TABLE OF ITEMS -->
@php
    $rows = [];
    $totalQty = 0;
    $targetQty = (float) ($suratJalan->qty_pengiriman ?? 0);

    $baseItemName = ($so && !empty($so->product_name) && $so->product_name !== '-')
        ? $so->product_name
        : ($so?->itemable?->nama_barang ?? 'Barang Cetakan');

    $komp = $so && !empty($so->effective_komponen) && $so->effective_komponen !== '-'
            ? ucwords(strtolower(trim($so->effective_komponen)))
            : '';
    $jenis = $so && !empty($so->effective_jenis) && $so->effective_jenis !== '-'
            ? strtoupper(trim($so->effective_jenis))
            : '';
    $isi = $so && !empty($so->effective_isi) && $so->effective_isi !== '-'
            ? trim((string) $so->effective_isi)
            : '';

    // Clean brand name to avoid duplicating komponen/jenis/isi (e.g., "DAFI 16" -> "Etiket Dafi SKT 16")
    $cleanBrand = trim((string) $baseItemName);
    if ($komp !== '' && stripos($cleanBrand, $komp) === 0) {
        $cleanBrand = trim(substr($cleanBrand, strlen($komp)));
    }
    if ($isi !== '' && preg_match('/\s+' . preg_quote($isi, '/') . '$/i', $cleanBrand)) {
        $cleanBrand = trim(preg_replace('/\s+' . preg_quote($isi, '/') . '$/i', '', $cleanBrand));
    }
    if ($jenis !== '' && preg_match('/\b' . preg_quote($jenis, '/') . '\b/i', $cleanBrand)) {
        $cleanBrand = trim(preg_replace('/\b' . preg_quote($jenis, '/') . '\b/i', '', $cleanBrand));
    }
    if ($cleanBrand !== '' && strtoupper($cleanBrand) === $cleanBrand && strlen($cleanBrand) > 2) {
        $cleanBrand = ucwords(strtolower($cleanBrand));
    }

    $parts = array_filter([$komp, $cleanBrand !== '' ? $cleanBrand : $baseItemName, $jenis, $isi], fn($v) => $v !== null && trim((string) $v) !== '');
    $formattedItemName = preg_replace('/\s+/', ' ', implode(' ', $parts));

    // 1. Cek apakah SPK memiliki detail Packaging
    if ($suratJalan->spk && $suratJalan->spk->packagings && $suratJalan->spk->packagings->count() > 0) {
        $remainingToShip = $targetQty > 0 ? $targetQty : null;

        foreach ($suratJalan->spk->packagings as $pkg) {
            if ($pkg->jenis_transfer === 'blokir') {
                continue;
            }

            $qtyPcs = (float) ($pkg->qty_per_satuan_penuh ?: 1);
            $jmlPenuh = (int) $pkg->jumlah_satuan_penuh;

            if ($jmlPenuh > 0 && $qtyPcs > 0) {
                $sub = (float) ($pkg->total_satuan_penuh ?: ($jmlPenuh * $qtyPcs));
                if ($remainingToShip !== null) {
                    if ($remainingToShip <= 0) break;
                    $subShip = min($sub, $remainingToShip);
                    $jmlShip = $qtyPcs > 0 ? (int) ceil($subShip / $qtyPcs) : 1;
                    $rows[] = [
                        'nama_item' => $formattedItemName,
                        'qty_pcs'   => $qtyPcs,
                        'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                        'jumlah'    => $jmlShip,
                        'total'     => $subShip,
                        'keterangan'=> ($suratJalan->keterangan && $suratJalan->keterangan !== '-') ? $suratJalan->keterangan : '',
                    ];
                    $totalQty += $subShip;
                    $remainingToShip -= $subShip;
                } else {
                    $rows[] = [
                        'nama_item' => $formattedItemName,
                        'qty_pcs'   => $qtyPcs,
                        'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                        'jumlah'    => $jmlPenuh,
                        'total'     => $sub,
                        'keterangan'=> ($suratJalan->keterangan && $suratJalan->keterangan !== '-') ? $suratJalan->keterangan : '',
                    ];
                    $totalQty += $sub;
                }
            }

            if ($remainingToShip !== null && $remainingToShip <= 0) {
                break;
            }

            $qtySisa = (float) ($pkg->qty_per_satuan_sisa ?: 1);
            $jmlSisa = (int) $pkg->jumlah_satuan_sisa;
            if ($jmlSisa > 0 && $qtySisa > 0) {
                $sub = (float) ($pkg->total_satuan_sisa ?: ($jmlSisa * $qtySisa));
                if ($remainingToShip !== null) {
                    if ($remainingToShip <= 0) break;
                    $subShip = min($sub, $remainingToShip);
                    $jmlShip = $qtySisa > 0 ? (int) ceil($subShip / $qtySisa) : 1;
                    $rows[] = [
                        'nama_item' => $formattedItemName,
                        'qty_pcs'   => $qtySisa,
                        'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                        'jumlah'    => $jmlShip,
                        'total'     => $subShip,
                        'keterangan'=> '',
                    ];
                    $totalQty += $subShip;
                    $remainingToShip -= $subShip;
                } else {
                    $rows[] = [
                        'nama_item' => $formattedItemName,
                        'qty_pcs'   => $qtySisa,
                        'kemasan'   => $pkg->satuan_transfer ?? 'Box',
                        'jumlah'    => $jmlSisa,
                        'total'     => $sub,
                        'keterangan'=> '',
                    ];
                    $totalQty += $sub;
                }
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

    // 3. Fallback jika SPK tunggal atau material tunggal tanpa packaging
    if (empty($rows)) {
        $sub = (float) ($suratJalan->qty_pengiriman ?: 0);
        $rows[] = [
            'nama_item' => $formattedItemName,
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
            <th width="5%">No</th>
            <th width="29%">Nama Item</th>
            <th width="13%">Q'ty (pcs)</th>
            <th width="13%">Kemasan</th>
            <th width="9%">Jumlah</th>
            <th width="11%">Total</th>
            <th width="20%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        @php
            $rowCount++;
            // Baris ganjil (1, 3, 5, 7, 9) berwarna biru muda persis sesuai referensi TTC
            $isStriped = ($rowCount % 2 == 1);
        @endphp
        <tr class="{{ $isStriped ? 'striped' : '' }}">
            <td align="center">{{ $rowCount }}</td>
            <td align="center">{{ $row['nama_item'] }}</td>
            <td align="center">{{ number_format($row['qty_pcs'], 0, '.', ',') }}</td>
            <td align="center">{{ $row['kemasan'] }}</td>
            <td align="center">{{ number_format($row['jumlah'], 0, '.', ',') }}</td>
            <td align="center">{{ number_format($row['total'], 0, '.', ',') }}</td>
            <td>{{ $row['keterangan'] }}</td>
        </tr>
    @endforeach

    @php
        $minRows = 10;
        $emptyRows = max(0, $minRows - $rowCount);
    @endphp

    @for($i = 0; $i < $emptyRows; $i++)
        @php
            $rowCount++;
            $isStriped = ($rowCount % 2 == 1);
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
            <td colspan="5" class="total-label-cell">Total</td>
            <td class="total-value-cell">{{ number_format($totalQty, 0, '.', ',') }}</td>
            <td style="background-color: #ffffff;">&nbsp;</td>
        </tr>
    </tfoot>
</table>

<!-- BOTTOM NOTICE & SIGNATURE: 5-COLUMN GRID SO PENERIMA & PENGIRIM ALIGN 100% WITH THEIR LINES -->
<table class="footer-table">
<tr>
    <td colspan="5" style="padding-bottom: 2px;">
        <div class="notice-bold">BARANG SUDAH DITERIMA DALAM KEADAAN BAIK DAN SESUAI</div>
        <div class="notice-sub">(tanda tangan dan cap perusahaan)</div>
    </td>
</tr>
<tr>
    <td style="width: 13%;"></td>
    <td style="width: 17%; text-align: center; font-size: 11px; color: #000;">Penerima</td>
    <td style="width: 40%;"></td>
    <td style="width: 17%; text-align: center; font-size: 11px; color: #000;">Pengirim</td>
    <td style="width: 13%;"></td>
</tr>
<tr>
    <td colspan="5" style="height: 46px;">&nbsp;</td>
</tr>
<tr>
    <td style="width: 13%;"></td>
    <td style="width: 17%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 40%;"></td>
    <td style="width: 17%; border-bottom: 1px solid #000;">&nbsp;</td>
    <td style="width: 13%;"></td>
</tr>
</table>

</body>
</html>
