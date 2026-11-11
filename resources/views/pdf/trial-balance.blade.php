<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Trial Balance (Neraca Saldo) - PT. Teknografi Tri Cawanaska</title>
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

        table.tb-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.tb-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 5px 4px;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #1e3a8a;
            font-weight: bold;
        }
        table.tb-table td {
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
            vertical-align: middle;
        }
        table.tb-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        table.tb-table tfoot td {
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
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
                <div class="company-sub">Ringkasan Neraca Saldo (Trial Balance) — Chart of Accounts</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">LAPORAN NERACA SALDO (TRIAL BALANCE)</div>
                <div class="report-period">
                    Per Tanggal: {{ $data['as_of_date'] }}
                    @if(!empty($data['selected_group']))
                        | Kelompok Akun: <strong>{{ $data['selected_group'] }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Ringkasan KPI Eksekutif --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="border-left-color: #2563eb;">
                <div class="kpi-label">Total Mutasi Debit</div>
                <div class="kpi-val text-debit">{{ $fmt($data['grand_mut_debit']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #7c3aed;">
                <div class="kpi-label">Total Mutasi Kredit</div>
                <div class="kpi-val text-kredit">{{ $fmt($data['grand_mut_kredit']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #059669;">
                <div class="kpi-label">Total Saldo Akhir (Debit = Kredit)</div>
                <div class="kpi-val text-saldo">{{ $fmt($data['grand_end_debit']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $data['is_balanced'] ? '#059669' : '#d97706' }};">
                <div class="kpi-label">Status Neraca Saldo</div>
                <div class="kpi-val" style="color: {{ $data['is_balanced'] ? '#047857' : '#b45309' }};">
                    {{ $data['is_balanced'] ? 'SEIMBANG (BALANCE)' : 'FILTER KELOMPOK' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="tb-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: left;">Kode Akun</th>
                <th style="width: 25%; text-align: left;">Nama Akun (Chart of Accounts)</th>
                <th style="width: 15%; text-align: center;">Kelompok Akun</th>
                <th style="width: 8%; text-align: center;">Laporan</th>
                <th style="width: 6%; text-align: center;">Jml. Mutasi</th>
                <th style="width: 9%; text-align: right;">Mutasi Debit (Rp)</th>
                <th style="width: 9%; text-align: right;">Mutasi Kredit (Rp)</th>
                <th style="width: 10%; text-align: right;">Saldo Akhir Debit</th>
                <th style="width: 10%; text-align: right;">Saldo Akhir Kredit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="nowrap"><span class="code-pill">{{ $row['kode_akun'] }}</span></td>
                    <td>{{ $row['nama_akun'] }}</td>
                    <td class="text-center nowrap">{{ $row['group_label'] }}</td>
                    <td class="text-center nowrap">{{ $row['stmt'] }}</td>
                    <td class="text-center nowrap">{{ $row['tx_count'] }}x</td>
                    <td class="text-right nowrap {{ $row['mut_debit'] > 0 ? 'text-debit' : '' }}">
                        {{ $row['mut_debit'] > 0 ? $fmt($row['mut_debit']) : '-' }}
                    </td>
                    <td class="text-right nowrap {{ $row['mut_kredit'] > 0 ? 'text-kredit' : '' }}">
                        {{ $row['mut_kredit'] > 0 ? $fmt($row['mut_kredit']) : '-' }}
                    </td>
                    <td class="text-right nowrap {{ $row['end_debit'] > 0 ? 'text-debit' : '' }}">
                        {{ $row['end_debit'] > 0 ? $fmt($row['end_debit']) : '-' }}
                    </td>
                    <td class="text-right nowrap {{ $row['end_kredit'] > 0 ? 'text-kredit' : '' }}">
                        {{ $row['end_kredit'] > 0 ? $fmt($row['end_kredit']) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #64748b;">
                        Tidak ada akun COA yang sesuai kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: left;">TOTAL SUMMARY ({{ number_format($data['total_rows'], 0, ',', '.') }} AKUN COA)</td>
                <td class="text-right text-debit">{{ $fmt($data['grand_mut_debit']) }}</td>
                <td class="text-right text-kredit">{{ $fmt($data['grand_mut_kredit']) }}</td>
                <td class="text-right text-debit">{{ $fmt($data['grand_end_debit']) }}</td>
                <td class="text-right text-kredit">{{ $fmt($data['grand_end_kredit']) }}</td>
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

