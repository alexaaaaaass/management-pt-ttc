<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }} - {{ $no_bukti }}</title>
    <style>
        @page { margin: 22px 28px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111827; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .company-name { font-size: 15px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.5px; }
        .company-sub { font-size: 9.5px; color: #4b5563; line-height: 1.35; }
        .doc-title { font-size: 14px; font-weight: bold; text-align: right; color: #111827; }
        .doc-subtitle { font-size: 9px; text-align: right; color: #6b7280; letter-spacing: 0.5px; }

        .meta-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; }
        .meta-table td { padding: 3px 6px; font-size: 10.5px; vertical-align: top; }
        .meta-label { font-weight: bold; width: 130px; color: #374151; }

        .journal-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .journal-table th { background-color: #1e3a8a; color: #ffffff; padding: 6px 8px; font-size: 10px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        .journal-table td { padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10.5px; }
        .journal-table tfoot td { background-color: #f1f5f9; font-weight: bold; border: 1px solid #94a3b8; }

        .terbilang-box { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #1e3a8a; padding: 7px 10px; margin-bottom: 16px; font-size: 10.5px; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .sig-table td { width: 25%; text-align: center; vertical-align: top; border: 1px solid #cbd5e1; padding: 6px; }
        .sig-role { font-size: 9.5px; font-weight: bold; color: #374151; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 48px; }
        .sig-name { font-size: 10px; font-weight: bold; color: #111827; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 65px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 55px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">
                    Offset Printing, Packaging &amp; Label Manufacturing<br>
                    Sistem Pembukuan &amp; Akuntansi Terintegrasi (Double-Entry)
                </div>
            </td>
            <td style="vertical-align: middle;">
                <div class="doc-title">{{ $judul }}</div>
                <div class="doc-subtitle">{{ $subjudul }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="meta-label">No. Bukti Voucher</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $no_bukti }}</strong></td>
            <td class="meta-label">Tanggal Transaksi</td>
            <td style="width: 10px;">:</td>
            <td>{{ $tanggal }}</td>
        </tr>
        <tr>
            <td class="meta-label">{{ $label_pihak }}</td>
            <td>:</td>
            <td>{{ $pihak_terkait }}</td>
            <td class="meta-label">Periode / Unit</td>
            <td>:</td>
            <td>{{ $periode }} / {{ $gudang }}</td>
        </tr>
        <tr>
            <td class="meta-label">Uraian / Memo</td>
            <td>:</td>
            <td colspan="4">{{ $keterangan }}</td>
        </tr>
    </table>

    <table class="journal-table">
        <thead>
            <tr>
                <th style="width: 15%; text-align: left;">Kode Akun</th>
                <th style="width: 30%; text-align: left;">Nama Akun COA</th>
                <th style="width: 27%; text-align: left;">Keterangan Ayat Jurnal</th>
                <th style="width: 14%; text-align: right;">Debit (Rp)</th>
                <th style="width: 14%; text-align: right;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumDebit = 0;
                $sumKredit = 0;
            @endphp
            @foreach ($lines as $line)
                @php
                    $sumDebit += (float) $line['debit'];
                    $sumKredit += (float) $line['kredit'];
                @endphp
                <tr>
                    <td>{{ $line['kode_akun'] }}</td>
                    <td>{{ $line['nama_akun'] }}</td>
                    <td>{{ $line['keterangan'] }}</td>
                    <td style="text-align: right;">{{ (float) $line['debit'] > 0 ? number_format((float) $line['debit'], 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ (float) $line['kredit'] > 0 ? number_format((float) $line['kredit'], 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;">TOTAL MUTASI JURNAL</td>
                <td style="text-align: right;">Rp {{ number_format($sumDebit, 0, ',', '.') }}</td>
                <td style="text-align: right;">Rp {{ number_format($sumKredit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="terbilang-box">
        <strong>Terbilang:</strong> <em># {{ $terbilang }} #</em>
    </div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-role">Dibuat Oleh (Kasir/Adm)</div>
                <div class="sig-name">( {{ $pic_nama }} )</div>
            </td>
            <td>
                <div class="sig-role">Diperiksa Oleh (Accounting)</div>
                <div class="sig-name">( ............................ )</div>
            </td>
            <td>
                <div class="sig-role">Disetujui Oleh (Direksi)</div>
                <div class="sig-name">( ............................ )</div>
            </td>
            <td>
                <div class="sig-role">Diterima / Disetor Oleh</div>
                <div class="sig-name">( ............................ )</div>
            </td>
        </tr>
    </table>
</body>
</html>

