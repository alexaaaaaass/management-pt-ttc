<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Approval Form - {{ $approvalForm->form_no }}</title>

<style>
@page {
    margin: 20px 25px 20px 25px;
    size: A4 landscape;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #111;
    line-height: 1.3;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.company-title {
    font-size: 13px;
    font-weight: bold;
    color: #000;
}

.company-info {
    font-size: 10px;
    color: #222;
    line-height: 1.35;
}

.meta-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
    font-size: 11px;
}

.meta-table td {
    border: none;
    padding: 2px 4px;
    vertical-align: top;
}

.meta-label {
    font-weight: bold;
    width: 75px;
}

/* TABLE SECTION */
.order-table-container {
    width: 100%;
    border: 2px solid #000;
    border-collapse: collapse;
    margin-bottom: 8px;
}

.order-title-bar {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    padding: 6px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #000;
    background: #ffffff;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}

.items-table th {
    background-color: #2b5fa8;
    color: #ffffff;
    font-weight: bold;
    text-align: center;
    padding: 5px 3px;
    border: 1px solid #000;
    font-size: 9.5px;
}

.items-table th.vertical-header {
    width: 26px;
    padding: 4px 1px;
    font-size: 8.5px;
}

.items-table td {
    border: 1px solid #222;
    padding: 4px 3px;
    height: 16px;
    vertical-align: middle;
}

.items-table tr.striped {
    background-color: #e9f1fb;
}

.check-cell {
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    color: #1e3a8a;
}

/* FOOTER SECTION */
.footer-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.footer-table td {
    border: none;
    vertical-align: top;
}

.notes-box {
    font-size: 10px;
    line-height: 1.4;
}

.notes-title {
    font-weight: bold;
    font-style: italic;
    font-size: 11px;
    margin-bottom: 4px;
}

.sig-box {
    width: 220px;
    height: 85px;
    border: 2px solid #000;
    text-align: center;
    position: relative;
    padding: 4px;
}

.sig-title {
    font-weight: bold;
    font-size: 11px;
}
</style>
</head>
<body>

<!-- TOP HEADER -->
<table class="header-table">
<tr>
    <td width="70%">
        <div class="company-title">PT Teknografi Tri Cawanaska</div>
        <div class="company-info">
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

<!-- METADATA -->
<table class="meta-table">
<tr>
    <td width="55%">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="meta-label">Order ID</td>
                <td>: {{ $approvalForm->order_id ?: '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Date</td>
                <td>: {{ $approvalForm->tanggal ? date('d M Y', strtotime($approvalForm->tanggal)) : date('d M Y') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust</td>
                <td>: {{ $approvalForm->customer?->nama_customer ?? '-' }}</td>
            </tr>
        </table>
    </td>
    <td width="45%">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td class="meta-label">Form No</td>
                <td>: {{ $approvalForm->form_no }}</td>
            </tr>
            <tr>
                <td class="meta-label">Lead Time</td>
                <td>: {{ $approvalForm->lead_time ?: '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Cust ID</td>
                <td>: {{ $approvalForm->customer?->kode_customer ?: ('C' . str_pad($approvalForm->customer_id, 3, '0', STR_PAD_LEFT)) }}</td>
            </tr>
        </table>
    </td>
</tr>
</table>

<!-- APPROVAL FORM CONTAINER -->
<table class="order-table-container">
    <tr>
        <td class="order-title-bar">APPROVAL FORM</td>
    </tr>
    <tr>
        <td style="padding: 0; border: none;">
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="3%">No</th>
                        <th width="20%">Item</th>
                        <th width="7%">Jenis</th>
                        <th width="10%">Komponen</th>
                        <th width="5%">Isi</th>
                        <th width="11%">Kertas</th>
                        <th width="6%">Warna</th>
                        <th width="22%">Finishing</th>
                        <th width="8%">Qty</th>
                        <th class="vertical-header">Desain</th>
                        <th class="vertical-header">Plate</th>
                        <th class="vertical-header">Produksi</th>
                        <th class="vertical-header">QC</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $rowCount = 0; 
                        $designPath = null;
                        if (!empty($approvalForm->file_design)) {
                            if (file_exists(public_path('storage/' . $approvalForm->file_design))) {
                                $designPath = public_path('storage/' . $approvalForm->file_design);
                            } elseif (file_exists(storage_path('app/public/' . $approvalForm->file_design))) {
                                $designPath = storage_path('app/public/' . $approvalForm->file_design);
                            } elseif (file_exists(public_path($approvalForm->file_design))) {
                                $designPath = public_path($approvalForm->file_design);
                            }
                        }
                        $minRows = $designPath ? 4 : 8;
                        $checkImg = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAfUlEQVRIiWNgGAXDHjBS20A5q67/MPajY2WMVLUA3XAGBir6AJvhDAwMDEzUsgAXoIoPcLmeKhbgM5xiCwgZjmEBMRpIVYsSycgKkQ2gBGC1mZDrSPEpTklchpBiOAMDifmAVMPxWkCt+CDoCmyGE+t6oixAt4QUw0fB4AAAY9FEFIoFW/4AAAAASUVORK5CYII=';
                    @endphp
                    @foreach($approvalForm->items as $idx => $item)
                        @php 
                            $rowCount++;
                            $isStriped = ($rowCount % 2 == 0);
                        @endphp
                        <tr class="{{ $isStriped ? 'striped' : '' }}">
                            <td align="center">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold;">{{ $item->item_name }}</td>
                            <td align="center">{{ $item->jenis ?: '-' }}</td>
                            <td align="center">{{ $item->komponen ?: '-' }}</td>
                            <td align="center">{{ $item->isi ?: '-' }}</td>
                            <td>{{ $item->kertas ?: '-' }}</td>
                            <td align="center">{{ $item->warna ?: '-' }}</td>
                            <td>{{ $item->finishing ?: '-' }}</td>
                            <td align="right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td class="check-cell">{!! $item->cek_desain ? '<img src="' . $checkImg . '" width="11" height="11" style="vertical-align:middle;">' : '' !!}</td>
                            <td class="check-cell">{!! $item->cek_plate ? '<img src="' . $checkImg . '" width="11" height="11" style="vertical-align:middle;">' : '' !!}</td>
                            <td class="check-cell">{!! $item->cek_produksi ? '<img src="' . $checkImg . '" width="11" height="11" style="vertical-align:middle;">' : '' !!}</td>
                            <td class="check-cell">{!! $item->cek_qc ? '<img src="' . $checkImg . '" width="11" height="11" style="vertical-align:middle;">' : '' !!}</td>
                        </tr>
                    @endforeach

                    {{-- Padding baris kosong (jika ada gambar desain dipadatkan ke 4 baris agar pas 1 halaman) --}}
                    @for($i = $rowCount + 1; $i <= max($rowCount, $minRows); $i++)
                        @php $isStriped = ($i % 2 == 0); @endphp
                        <tr class="{{ $isStriped ? 'striped' : '' }}">
                            <td align="center">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </td>
    </tr>
</table>

<!-- FOOTER: CATATAN PRODUKSI & TANDA TANGAN -->
<table class="footer-table">
<tr>
    <td width="55%">
        <div class="notes-box">
            <div class="notes-title">Catatan Produksi:</div>
            <div>
                {!! nl2br(e($approvalForm->catatan_produksi ?: "Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan")) !!}
            </div>
        </div>
    </td>
    <td width="45%" align="right">
        <table style="border-collapse:collapse; display:inline-table;">
            <tr>
                <td style="padding-right: 15px;">
                    <div class="sig-box">
                        <div class="sig-title">Disetujui Marketing</div>
                        <div style="position: absolute; bottom: 8px; left: 0; right: 0; text-align: center; font-size: 10px;">
                            ( {{ $approvalForm->disetujui_marketing ?: '                           ' }} )
                        </div>
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <div class="sig-title">Disetujui Produksi</div>
                        <div style="position: absolute; bottom: 8px; left: 0; right: 0; text-align: center; font-size: 10px;">
                            ( {{ $approvalForm->disetujui_produksi ?: '                           ' }} )
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>

@if($designPath)
<!-- LAMPIRAN PROVE CETAK & DESAIN PRODUKSI (SESUAI KOTAK APPROVAL FORM) -->
<div style="margin-top: 10px; width: 100%; border: 2px solid #2b5fa8; border-radius: 4px; padding: 4px 6px; text-align: center; background-color: #ffffff;">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 3px;">
        <tr>
            <td style="text-align: left; font-size: 9.5px; font-weight: bold; color: #2b5fa8; padding: 1px 2px; text-transform: uppercase;">
                Lampiran Desain & Prove Cetak Etiket:
            </td>
            <td style="text-align: right; font-size: 8.5px; color: #666; padding: 1px 2px;">
                Acuan Visual Spesifikasi Produksi
            </td>
        </tr>
    </table>
    <div style="text-align: center; padding: 2px;">
        <img src="{{ $designPath }}" style="max-height: 190px; max-width: 98%; height: auto; display: inline-block;">
    </div>
</div>
@endif

</body>
</html>
