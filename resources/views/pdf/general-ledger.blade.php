<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>General Ledger (Buku Besar & Jurnal Umum) - PT. Teknografi Tri Cawanaska</title>
    <style>
        @page {
            margin: 16px 20px 20px 20px;
            size: A4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.3px;
        }
        .company-sub {
            font-size: 8px;
            color: #475569;
            margin-top: 1px;
        }
        .report-title {
            font-size: 11.5px;
            font-weight: bold;
            text-align: right;
            color: #0f172a;
        }
        .report-period {
            font-size: 8px;
            text-align: right;
            color: #475569;
            margin-top: 2px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin: 0 -5px 8px -5px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #1e3a8a;
            padding: 5px 8px;
            width: 25%;
        }
        .kpi-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
        }
        .kpi-val {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        table.gl-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.gl-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 5px 4px;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #1e3a8a;
            font-weight: bold;
        }
        table.gl-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            vertical-align: top;
        }
        table.gl-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        table.gl-table tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
            border: 1px solid #64748b;
            font-size: 8.5px;
            padding: 5px 4px;
        }

        .code-pill {
            font-weight: bold;
            color: #1e3a8a;
        }
        .badge-ref {
            font-size: 7.5px;
            font-weight: bold;
            color: #1e40af;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-debit { color: #1d4ed8; font-weight: bold; }
        .text-kredit { color: #6d28d9; font-weight: bold; }
        .text-saldo { color: #047857; font-weight: bold; }
        .nowrap { white-space: nowrap; }

        .footer-note {
            width: 100%;
            font-size: 7.5px;
            color: #64748b;
            margin-top: 6px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 2, ',', '.');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 50px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 42px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Buku Besar &amp; Jurnal Umum — Double-Entry Accounting Engine</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">LAPORAN BUKU BESAR (GENERAL LEDGER)</div>
                <div class="report-period">
                    Periode: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                    @if(!empty($data['selected_account']))
                        | Akun: <strong>{{ $data['selected_account']->kode_akun }} - {{ $data['selected_account']->nama_akun }}</strong>
                    @endif
                    @if(!empty($data['selected_ref_type']))
                        | Modul: <strong>{{ $data['selected_ref_type'] }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan KPI Eksekutif --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="border-left-color: #2563eb;">
                <div class="kpi-label">Total Baris Mutasi</div>
                <div class="kpi-val">{{ number_format($data['total_rows'], 0, ',', '.') }} Baris Jurnal</div>
            </td>
            <td class="kpi-card" style="border-left-color: #1d4ed8;">
                <div class="kpi-label">Total Mutasi Debit</div>
                <div class="kpi-val text-debit">{{ $fmt($data['total_debit']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #7c3aed;">
                <div class="kpi-label">Total Mutasi Kredit</div>
                <div class="kpi-val text-kredit">{{ $fmt($data['total_kredit']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $data['is_balanced'] ? '#059669' : '#e11d48' }};">
                <div class="kpi-label">Status Neraca (Balance)</div>
                <div class="kpi-val" style="color: {{ $data['is_balanced'] ? '#047857' : '#be123c' }};">
                    {{ $data['is_balanced'] ? 'SEIMBANG (BALANCE)' : 'SELISIH ' . $fmt($data['selisih']) }}
                </div>
            </td>
        </tr>
    </table>

    <table class="gl-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">No</th>
                <th style="width: 7%; text-align: center;">Tanggal</th>
                <th style="width: 19%; text-align: left;">Kode &amp; Nama Akun COA</th>
                <th style="width: 12%; text-align: center;">Sumber Transaksi</th>
                <th style="width: 12%; text-align: left;">No. Referensi</th>
                <th style="width: 23%; text-align: left;">Keterangan / Uraian Transaksi</th>
                <th style="width: 8%; text-align: right;">Debit (Rp)</th>
                <th style="width: 8%; text-align: right;">Kredit (Rp)</th>
                <th style="width: 8%; text-align: right;">Saldo Berjalan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $idx => $row)
                <tr>
                    <td class="text-center nowrap">{{ $idx + 1 }}</td>
                    <td class="text-center nowrap">{{ $row['tanggal'] }}</td>
                    <td>
                        <span class="code-pill">{{ $row['kode_akun'] }}</span>
                        <span>{{ $row['nama_akun'] }}</span>
                    </td>
                    <td class="text-center badge-ref">{{ $row['ref_label'] }}</td>
                    <td class="nowrap text-bold" style="color: #2563eb;">{{ $row['reference_no'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                    <td class="text-right nowrap {{ $row['debit'] > 0 ? 'text-debit' : '' }}">
                        {{ $row['debit'] > 0 ? $fmt($row['debit']) : '-' }}
                    </td>
                    <td class="text-right nowrap {{ $row['kredit'] > 0 ? 'text-kredit' : '' }}">
                        {{ $row['kredit'] > 0 ? $fmt($row['kredit']) : '-' }}
                    </td>
                    <td class="text-right nowrap text-saldo">
                        {{ $fmt($row['running_balance']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada data transaksi jurnal umum pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: left;">TOTAL MUTASI JURNAL ({{ number_format($data['total_rows'], 0, ',', '.') }} BARIS)</td>
                <td class="text-right text-debit">{{ $fmt($data['total_debit']) }}</td>
                <td class="text-right text-kredit">{{ $fmt($data['total_kredit']) }}</td>
                <td class="text-right text-saldo">{{ $data['is_balanced'] ? 'BALANCE' : 'FILTER AKUN' }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-note">
        <tr>
            <td style="text-align: left;">
                Dokumen ini dicetak otomatis dari Sistem ERP Keuangan &amp; Akuntansi PT. Teknografi Tri Cawanaska pada {{ now()->format('d/m/Y H:i:s') }}.
            </td>
            <td style="text-align: right;">
                Status: Resmi &amp; Terverifikasi Double-Entry
            </td>
        </tr>
    </table>
</body>
</html>

