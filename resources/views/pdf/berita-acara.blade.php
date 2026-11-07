<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara {{ $beritaAcara->no_ba }}</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; font-size: 9px; line-height: 1.35; }
        table { border-collapse: collapse; }
        .letterhead { width: 100%; margin-bottom: 11px; }
        .letterhead td { border: 0; vertical-align: top; padding: 0; }
        .company { font-size: 11px; font-weight: bold; margin-bottom: 2px; }
        .company-details { color: #444; font-size: 8px; line-height: 1.45; }
        .logo { width: 105px; max-height: 65px; }
        .title-box { width: 100%; border: 2px solid #111; margin-bottom: 8px; }
        .title { border-bottom: 2px solid #111; text-align: center; font-size: 14px; font-weight: bold; font-style: italic; padding: 5px; }
        .meta { width: 100%; }
        .meta td { border: 1px solid #111; padding: 3px 5px; }
        .meta-label { width: 18%; font-weight: bold; font-style: italic; }
        .meta-value { width: 32%; font-weight: bold; font-style: italic; }
        .intro { margin: 10px 2px 5px; }
        .quantity-title { width: 45%; margin: 0 auto; border: 1px solid #111; border-bottom: 0; text-align: center; color: #254d81; font-weight: bold; font-style: italic; padding: 2px; }
        .quantity { width: 100%; table-layout: fixed; margin-bottom: 12px; }
        .quantity th { background: #dce4f2; color: #254d81; font-style: italic; text-align: center; }
        .quantity th, .quantity td { border: 1px solid #111; padding: 4px 3px; }
        .quantity td { height: 20px; }
        .quantity .no { width: 8%; text-align: center; }
        .quantity .item { width: 25%; }
        .quantity .qty { width: 14%; text-align: right; }
        .quantity .note { width: 25%; }
        .quantity tfoot td { font-weight: bold; background: #f6f7fa; }
        .summary { width: 100%; margin: 10px 0; }
        .summary td { width: 50%; border: 0; vertical-align: top; }
        .notes { border: 1px solid #111; min-height: 66px; padding: 5px; }
        .notes-title { font-weight: bold; font-style: italic; margin-bottom: 4px; }
        .issuer { padding-left: 16px; }
        .issuer-gap { height: 48px; }
        .signature { margin-top: 4px; font-weight: bold; }
        .section-heading { border-top: 1px solid #111; border-bottom: 1px solid #111; padding: 3px 0; margin: 8px 0 5px; font-weight: bold; font-style: italic; }
        .attachments { width: 100%; table-layout: fixed; }
        .attachments th { text-align: left; font-weight: bold; font-style: italic; padding: 4px; border: 1px solid #111; }
        .attachments td { border: 1px solid #111; padding: 4px; vertical-align: middle; }
        .attachment-row { page-break-inside: avoid; }
        .issue-note { width: 29%; }
        .photo-column { width: 35.5%; text-align: center; }
        .photo { max-width: 100%; height: 105px; }
        .photo-empty { height: 105px; color: #888; }
        .photo-label { text-align: center; margin-top: 2px; }
        .small { color: #555; font-size: 8px; }
    </style>
</head>
<body>
    <table class="letterhead">
        <tr>
            <td width="70%">
                <div class="company">PT Teknografi Tri Cawanaska</div>
                <div class="company-details">
                    Pergudangan Sentral Singosari<br>
                    Ardimulyo, Singosari, Malang<br>
                    teknografitricawanaska@gmail.com<br>
                    +62895 3391 07709
                </div>
            </td>
            <td width="30%" style="text-align:right">
                @if ($logo)<img class="logo" src="{{ $logo }}" alt="Logo PT Teknografi">@endif
            </td>
        </tr>
    </table>

    <div class="title-box">
        <div class="title">BERITA ACARA</div>
        <table class="meta">
            <tr>
                <td class="meta-label">Hal</td>
                <td class="meta-value">{{ $beritaAcara->tipe === 'rapat' ? 'Rapat' : 'Item Rejection' }}</td>
                <td class="meta-label">Doc No</td>
                <td class="meta-value">{{ $beritaAcara->no_ba }}</td>
            </tr>
            <tr>
                <td class="meta-label">Date</td>
                <td class="meta-value">{{ $beritaAcara->tanggal?->translatedFormat('d F Y') ?? '-' }}</td>
                <td class="meta-label">Delivery Date</td>
                <td class="meta-value">{{ $deliveryDate ? \Illuminate\Support\Carbon::parse($deliveryDate)->translatedFormat('d M Y') : '-' }}</td>
            </tr>
        </table>
    </div>

    <p class="intro">
        Sehubungan dengan komplain atas barang yang dikirim
        @if ($customer) dari/untuk {{ $customer->nama_customer }} @endif
        @if ($beritaAcara->no_surat_jalan_invoice) dengan referensi {{ $beritaAcara->no_surat_jalan_invoice }} @endif,
        bersama ini disampaikan rincian sebagai berikut:
    </p>

    <div class="quantity-title">Quantity</div>
    <table class="quantity">
        <thead>
            <tr>
                <th class="no" rowspan="2">No</th>
                <th class="item" rowspan="2">Item</th>
                <th colspan="3">Jumlah</th>
                <th class="note" rowspan="2">Catatan</th>
            </tr>
            <tr>
                <th class="qty">Kedatangan</th>
                <th class="qty">Penerimaan</th>
                <th class="qty">Penolakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td>{{ $item['item_product'] ?: $beritaAcara->item_product ?: '-' }}</td>
                    <td class="qty">{{ $item['qty_kedatangan'] !== null ? number_format((float) $item['qty_kedatangan'], 0, ',', '.') : '-' }}</td>
                    <td class="qty">{{ $item['qty_penerimaan'] !== null ? number_format((float) $item['qty_penerimaan'], 0, ',', '.') : '-' }}</td>
                    <td class="qty">{{ $item['qty_penolakan'] !== null ? number_format((float) $item['qty_penolakan'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $item['catatan_penerimaan'] ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="notes">
                    <div class="notes-title">Catatan:</div>
                    {{ $beritaAcara->catatan ?: ($beritaAcara->tindakan_solusi ?: '-') }}
                </div>
            </td>
            <td class="issuer">
                Malang, {{ $beritaAcara->tanggal?->translatedFormat('d F Y') ?? '-' }}<br>
                Diterbitkan Oleh
                <div class="issuer-gap"></div>
                <div class="signature">PT. Teknografi Tri Cawanaska</div>
                @if ($beritaAcara->ttd_qc || $beritaAcara->ttd_customer)
                    <div class="small">QC: {{ $beritaAcara->ttd_qc ?: '-' }} &nbsp; Customer: {{ $beritaAcara->ttd_customer ?: '-' }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-heading">Lampiran</div>
    <table class="attachments">
        <thead>
            <tr>
                <th class="issue-note">Catatan Penerimaan</th>
                <th class="photo-column">Referensi</th>
                <th class="photo-column">Fisik Datang</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr class="attachment-row">
                    <td class="issue-note">{{ $item['catatan_penerimaan'] ?: '-' }}</td>
                    <td class="photo-column">
                        @if ($item['foto_referensi'])<img class="photo" src="{{ $item['foto_referensi'] }}" alt="Foto referensi">@else<div class="photo-empty">-</div>@endif
                        <div class="photo-label">Referensi</div>
                    </td>
                    <td class="photo-column">
                        @if ($item['foto_fisik'])<img class="photo" src="{{ $item['foto_fisik'] }}" alt="Foto fisik datang">@else<div class="photo-empty">-</div>@endif
                        <div class="photo-label">Fisik Datang</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>