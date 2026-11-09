<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi (Global) - PT. Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 22px 28px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #0f172a; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .company-name { font-size: 14.5px; font-weight: bold; color: #1e3a8a; }
        .company-sub { font-size: 9px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; text-align: right; color: #0f172a; }
        .report-period { font-size: 9.5px; text-align: right; color: #475569; margin-top: 2px; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 14px -6px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 3.5px solid #1e3a8a; border-radius: 4px; padding: 6px 10px; width: 25%; }
        .kpi-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .kpi-val { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        table.report { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.report th { background-color: #1e3a8a; color: #ffffff; padding: 6px 8px; font-size: 9.5px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.report td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; }
        .sec-header td { background-color: #eff6ff; font-weight: bold; color: #1e3a8a; border-top: 1px solid #bfdbfe; border-bottom: 1px solid #bfdbfe; }
        .sec-subtotal td { background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #94a3b8; border-bottom: 1px solid #94a3b8; }
        .sec-highlight td { background-color: #dbeafe; font-weight: bold; font-size: 10.5px; color: #1e3a8a; border-top: 1.5px solid #1e3a8a; border-bottom: 1.5px solid #1e3a8a; }
        .sec-net td { background-color: #ecfdf5; font-weight: bold; font-size: 11px; color: #065f46; border-top: 2px solid #059669; border-bottom: 2px solid #059669; }
        .text-right { text-align: right; }
        .pl-indent { padding-left: 20px !important; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 65px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 52px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Offset Printing, Packaging &amp; Label Manufacturing</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">REPORT ANALISA TRANSAKSI — LABA RUGI (GLOBAL)</div>
                <div class="report-period">
                    Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-label">Pendapatan Usaha Bersih</div>
                <div class="kpi-val">{{ $fmt($data['total_pendapatan_bersih']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #ea580c;">
                <div class="kpi-label">Harga Pokok Penjualan (HPP)</div>
                <div class="kpi-val">{{ $fmt($data['total_hpp']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #4f46e5;">
                <div class="kpi-label">Laba Kotor (Gross Profit)</div>
                <div class="kpi-val">{{ $fmt($data['laba_kotor']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }};">
                <div class="kpi-label">Laba / (Rugi) Bersih ({{ $data['margin_bersih_persen'] }}%)</div>
                <div class="kpi-val" style="color: {{ $data['laba_bersih'] >= 0 ? '#047857' : '#be123c' }};">{{ $fmt($data['laba_bersih']) }}</div>
            </td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th style="width: 15%; text-align: left;">Kode Akun</th>
                <th style="width: 60%; text-align: left;">Uraian Komponen Laba Rugi (Global)</th>
                <th style="width: 25%; text-align: right;">Nominal (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="sec-header">
                <td>4000</td>
                <td colspan="2">I. PENDAPATAN USAHA</td>
            </tr>
            @forelse ($data['pendapatan_usaha'] as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">{{ $item['nama_akun'] }}</td>
                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                </tr>
            @empty
                <tr>
                    <td>-</td>
                    <td class="pl-indent"><em>Tidak ada mutasi pendapatan usaha pada periode ini</em></td>
                    <td class="text-right">Rp 0</td>
                </tr>
            @endforelse
            @foreach ($data['retur_diskon'] as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">(-) {{ $item['nama_akun'] }}</td>
                    <td class="text-right">({{ $fmt($item['nominal']) }})</td>
                </tr>
            @endforeach
            <tr class="sec-subtotal">
                <td></td>
                <td>TOTAL PENDAPATAN USAHA BERSIH</td>
                <td class="text-right">{{ $fmt($data['total_pendapatan_bersih']) }}</td>
            </tr>

            <tr class="sec-header">
                <td>5000</td>
                <td colspan="2">II. HARGA POKOK PENJUALAN (HPP / COGS)</td>
            </tr>
            @forelse ($data['hpp_items'] as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">{{ $item['nama_akun'] }}</td>
                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                </tr>
            @empty
                <tr>
                    <td>-</td>
                    <td class="pl-indent"><em>Tidak ada mutasi HPP pada periode ini</em></td>
                    <td class="text-right">Rp 0</td>
                </tr>
            @endforelse
            <tr class="sec-subtotal">
                <td></td>
                <td>TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                <td class="text-right">({{ $fmt($data['total_hpp']) }})</td>
            </tr>

            <tr class="sec-highlight">
                <td></td>
                <td>LABA KOTOR (GROSS PROFIT)</td>
                <td class="text-right">{{ $fmt($data['laba_kotor']) }}</td>
            </tr>

            <tr class="sec-header">
                <td>6000</td>
                <td colspan="2">III. BEBAN OPERASIONAL</td>
            </tr>
            @php
                $allOp = array_merge(
                    $data['beban_penjualan'],
                    $data['beban_admin_umum'],
                    $data['beban_tenaga_kerja'],
                    $data['beban_pajak_k3_lain']
                );
            @endphp
            @forelse ($allOp as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">{{ $item['nama_akun'] }}</td>
                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                </tr>
            @empty
                <tr>
                    <td>-</td>
                    <td class="pl-indent"><em>Tidak ada mutasi beban operasional pada periode ini</em></td>
                    <td class="text-right">Rp 0</td>
                </tr>
            @endforelse
            <tr class="sec-subtotal">
                <td></td>
                <td>TOTAL BEBAN OPERASIONAL</td>
                <td class="text-right">({{ $fmt($data['total_beban_operasional']) }})</td>
            </tr>

            <tr class="sec-highlight">
                <td></td>
                <td>LABA OPERASIONAL (EBIT)</td>
                <td class="text-right">{{ $fmt($data['laba_operasional']) }}</td>
            </tr>

            <tr class="sec-header">
                <td>7000</td>
                <td colspan="2">IV. PENDAPATAN &amp; BEBAN LAIN-LAIN (NON-OPERASIONAL)</td>
            </tr>
            @foreach ($data['pendapatan_lain'] as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">(+) {{ $item['nama_akun'] }}</td>
                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                </tr>
            @endforeach
            @foreach ($data['beban_non_operasional'] as $item)
                <tr>
                    <td>{{ $item['kode_akun'] }}</td>
                    <td class="pl-indent">(-) {{ $item['nama_akun'] }}</td>
                    <td class="text-right">({{ $fmt($item['nominal']) }})</td>
                </tr>
            @endforeach
            <tr class="sec-subtotal">
                <td></td>
                <td>TOTAL PENDAPATAN / (BEBAN) LAIN-LAIN BERSIH</td>
                <td class="text-right">{{ $fmt($data['net_lain_lain']) }}</td>
            </tr>

            <tr class="sec-net">
                <td></td>
                <td>LABA / (RUGI) BERSIH PERIODE BERJALAN (Margin: {{ $data['margin_bersih_persen'] }}%)</td>
                <td class="text-right">{{ $fmt($data['laba_bersih']) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 24px; text-align: center;">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br><br><br><br>
                <strong>( Accounting / Finance )</strong>
            </td>
            <td style="width: 33%;">
                Diperiksa Oleh,<br><br><br><br>
                <strong>( Kepala Bagian Keuangan )</strong>
            </td>
            <td style="width: 34%;">
                Disetujui Oleh,<br><br><br><br>
                <strong>( Direksi PT. Teknografi Tri Cawanaska )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
