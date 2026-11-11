<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi (Mesin) - PT. Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 18px 24px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5px; color: #0f172a; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 10px; }
        .company-name { font-size: 14px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.3px; }
        .company-sub { font-size: 9px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; text-align: right; color: #0f172a; }
        .report-period { font-size: 9px; text-align: right; color: #475569; margin-top: 2px; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 12px -6px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 3.5px solid #1e3a8a; border-radius: 4px; padding: 6px 10px; width: 25%; }
        .kpi-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .kpi-val { font-size: 11.5px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        .section-heading { font-size: 10.5px; font-weight: bold; color: #1e3a8a; background-color: #eff6ff; border-left: 4px solid #1e3a8a; padding: 5px 8px; margin: 10px 0 6px 0; text-transform: uppercase; }

        table.machine-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.machine-table th { background-color: #1e3a8a; color: #ffffff; padding: 6px 6px; font-size: 9px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.machine-table td { padding: 5px 6px; border: 1px solid #cbd5e1; font-size: 9.5px; }
        table.machine-table tbody tr:nth-child(even) td { background-color: #f8fafc; }
        table.machine-table tfoot td { background-color: #e2e8f0; font-weight: bold; border: 1px solid #64748b; font-size: 9.5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-profit { color: #047857; font-weight: bold; }
        .text-loss { color: #be123c; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 60px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 50px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Offset Printing, Packaging &amp; Label Manufacturing</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">REPORT ANALISA TRANSAKSI — LABA RUGI (MESIN)</div>
                <div class="report-period">
                    Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                    @if(!empty($selectedMesin))
                        | Mesin: <strong>{{ $selectedMesin }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan KPI Eksekutif --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-label">Total Output Produksi ({{ $data['grand_spk_count'] }} SPK)</div>
                <div class="kpi-val">{{ number_format($data['grand_hasil_baik'], 0, ',', '.') }} Pcs</div>
            </td>
            <td class="kpi-card" style="border-left-color: #2563eb;">
                <div class="kpi-label">Total Nilai Output (Pendapatan)</div>
                <div class="kpi-val">{{ $fmt($data['grand_pendapatan']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #ea580c;">
                <div class="kpi-label">Total Biaya Bahan &amp; Mesin</div>
                <div class="kpi-val">{{ $fmt($data['grand_total_biaya']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $data['grand_laba'] >= 0 ? '#059669' : '#e11d48' }};">
                <div class="kpi-label">Kontribusi Laba Mesin ({{ $data['grand_margin_persen'] }}%)</div>
                <div class="kpi-val" style="color: {{ $data['grand_laba'] >= 0 ? '#047857' : '#be123c' }};">{{ $fmt($data['grand_laba']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-heading">I. Laba Rugi (Mesin) — Rekapitulasi Per Unit Mesin Cetak</div>

    <table class="machine-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 17%; text-align: left;">Nama Mesin Cetak</th>
                <th style="width: 13%; text-align: left;">Jenis / Proses</th>
                <th style="width: 6%;">SPK</th>
                <th style="width: 10%; text-align: right;">Hasil Baik (Pcs)</th>
                <th style="width: 8%; text-align: right;">Waste (Pcs)</th>
                <th style="width: 11%; text-align: right;">Pendapatan (Rp)</th>
                <th style="width: 11%; text-align: right;">HPP Bahan (Rp)</th>
                <th style="width: 9%; text-align: right;">Biaya Mesin (Rp)</th>
                <th style="width: 11%; text-align: right;">Laba Mesin (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['machines'] as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $row['nama_mesin'] ?? '-' }}</strong> <span style="color: #64748b; font-size: 8.5px;">({{ $row['kode_mesin'] ?? ('MSN-' . str_pad((string) ($row['mesin_id'] ?? $idx + 1), 2, '0', STR_PAD_LEFT)) }})</span></td>
                    <td>{{ $row['jenis_mesin'] ?? '-' }} ({{ $row['proses'] ?? '-' }})</td>
                    <td class="text-center">{{ $row['jumlah_spk'] }}</td>
                    <td class="text-right">{{ number_format($row['hasil_baik'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['hasil_rusak'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $fmt($row['pendapatan']) }}</td>
                    <td class="text-right">{{ $fmt($row['biaya_bahan']) }}</td>
                    <td class="text-right">{{ $fmt($row['biaya_operasional']) }}</td>
                    <td class="text-right {{ $row['laba_mesin'] >= 0 ? 'text-profit' : 'text-loss' }}">
                        {{ $fmt($row['laba_mesin']) }} ({{ $row['margin_persen'] }}%)
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAL SELURUH MESIN CETAK</td>
                <td class="text-center">{{ $data['grand_spk_count'] }}</td>
                <td class="text-right">{{ number_format($data['grand_hasil_baik'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($data['grand_hasil_rusak'], 0, ',', '.') }}</td>
                <td class="text-right">{{ $fmt($data['grand_pendapatan']) }}</td>
                <td class="text-right">{{ $fmt($data['grand_biaya_bahan']) }}</td>
                <td class="text-right">{{ $fmt($data['grand_biaya_operasional']) }}</td>
                <td class="text-right {{ $data['grand_laba'] >= 0 ? 'text-profit' : 'text-loss' }}">
                    {{ $fmt($data['grand_laba']) }} ({{ $data['grand_margin_persen'] }}%)
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="section-heading">II. Laba Rugi (Mesin)-Detail — Rincian Pendapatan &amp; Pengeluaran Per Mesin</div>

    <table class="machine-table">
        <thead>
            <tr>
                <th style="width: 18%; text-align: left;">Mesin Cetak</th>
                <th style="width: 15%; text-align: left;">Kelompok (GroupAcc)</th>
                <th style="width: 34%; text-align: left;">Uraian Rincian Produksi &amp; Biaya</th>
                <th style="width: 11%; text-align: right;">Debet (Rp)</th>
                <th style="width: 11%; text-align: right;">Kredit (Rp)</th>
                <th style="width: 11%; text-align: right;">Saldo Laba (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['machines'] as $m)
                <tr>
                    <td rowspan="3" style="vertical-align: top; background-color: #ffffff;">
                        <strong>{{ $m['nama_mesin'] }}</strong><br>
                        <span style="font-size: 8.5px; color: #475569;">{{ $m['jenis_mesin'] }} ({{ $m['proses'] }})</span>
                    </td>
                    <td><strong>Pendapatan (Detail)</strong></td>
                    <td>Output Produksi: {{ number_format($m['hasil_baik'], 0, ',', '.') }} Pcs ({{ $m['jumlah_spk'] }} SPK)</td>
                    <td class="text-right">{{ $fmt($m['pendapatan']) }}</td>
                    <td class="text-right">Rp 0</td>
                    <td class="text-right">{{ $fmt($m['pendapatan']) }}</td>
                </tr>
                <tr>
                    <td>Pengeluaran (Detail)</td>
                    <td>Pemakaian Bahan Baku Cetak (IMR Printing / Kertas &amp; Tinta)</td>
                    <td class="text-right">Rp 0</td>
                    <td class="text-right">{{ $fmt($m['biaya_bahan']) }}</td>
                    <td class="text-right">{{ $fmt($m['pendapatan'] - $m['biaya_bahan']) }}</td>
                </tr>
                <tr>
                    <td>Pengeluaran (Detail)</td>
                    <td>Biaya Operasional &amp; Perawatan Mesin (Operasional Keluar)</td>
                    <td class="text-right">Rp 0</td>
                    <td class="text-right">{{ $fmt($m['biaya_operasional']) }}</td>
                    <td class="text-right {{ $m['laba_mesin'] >= 0 ? 'text-profit' : 'text-loss' }}">{{ $fmt($m['laba_mesin']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 18px; text-align: center;">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br><br><br><br>
                <strong>( PPIC &amp; Cost Accounting )</strong>
            </td>
            <td style="width: 33%;">
                Diperiksa Oleh,<br><br><br><br>
                <strong>( Manajer Produksi )</strong>
            </td>
            <td style="width: 34%;">
                Disetujui Oleh,<br><br><br><br>
                <strong>( Direksi PT. Teknografi Tri Cawanaska )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
