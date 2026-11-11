<x-filament-panels::page>
    @php
        $data = $this->reportData;
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
        $rev = max((float) $data['total_pendapatan_bersih'], 1);
        $hppPct = min(max(round(((float) $data['total_hpp'] / $rev) * 100, 1), 0), 100);
        $opPct = min(max(round(((float) $data['total_beban_operasional'] / $rev) * 100, 1), 0), 100);
        $grossPct = $data['total_pendapatan_bersih'] > 0 ? round(((float) $data['laba_kotor'] / $rev) * 100, 1) : 0;
    @endphp

    <style>
        .ttc-rpt-wrap {
            display: flex;
            flex-direction: column;
            gap: 24px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .dark .ttc-rpt-wrap { color: #f1f5f9; }

        .ttc-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .dark .ttc-card {
            background: #111827;
            border-color: #1f2937;
        }

        .ttc-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1e1b4b 100%);
            color: #ffffff;
            padding: 22px 28px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .ttc-badge-amber {
            display: inline-block;
            background: #f59e0b;
            color: #0f172a;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 4px 12px;
            border-radius: 999px;
        }
        .ttc-hero-sub {
            font-size: 13px;
            color: #bfdbfe;
            font-weight: 500;
            margin-left: 8px;
        }
        .ttc-hero-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-top: 8px;
            letter-spacing: -0.01em;
        }

        .ttc-btn-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .ttc-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-decoration: none;
            transition: all 0.15s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        }
        .ttc-btn-amber { background: #f59e0b; color: #0f172a; }
        .ttc-btn-amber:hover { background: #fbbf24; }
        .ttc-btn-emerald { background: #10b981; color: #052e16; }
        .ttc-btn-emerald:hover { background: #34d399; }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm;
            }
            *, .dark *, .dark {
                color: #0f172a !important;
                background-color: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
            html, body, .fi-body, .fi-layout, .fi-main-ctn, .fi-main, .fi-page, .fi-page-content-wrapper, .fi-section, main {
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                overflow: visible !important;
                position: static !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #0f172a !important;
            }
            .fi-sidebar, 
            .fi-topbar, 
            .fi-breadcrumbs, 
            .fi-header, 
            .fi-footer,
            aside, 
            nav, 
            header,
            .ttc-finance-tabs-nav, 
            .ttc-btn-group, 
            .ttc-filter-bar, 
            .ttc-preset-group,
            .ttc-hide-print {
                display: none !important;
            }
            .ttc-rpt-wrap {
                display: block !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .ttc-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                overflow: visible !important;
                display: block !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                margin-bottom: 12px !important;
            }
            .ttc-hero {
                background: #1e3a8a !important;
                color: #ffffff !important;
                padding: 12px 16px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .ttc-hero-title {
                color: #ffffff !important;
                font-size: 15px !important;
            }
            .ttc-hero-sub {
                color: #dbeafe !important;
            }
            .ttc-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px !important;
            }
            .ttc-kpi-card {
                padding: 8px 10px !important;
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid !important;
            }
            .ttc-table-wrap {
                overflow: visible !important;
                display: block !important;
                padding: 8px 12px !important;
            }
            table.ttc-pl-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
            }
            table.ttc-pl-table th, table.ttc-pl-table td {
                padding: 6px 10px !important;
            }
            thead {
                display: table-header-group !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .ttc-filter-bar {
            padding: 22px 28px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
        }
        .dark .ttc-filter-bar {
            background: #1f2937;
            border-top-color: #374151;
        }
        .ttc-filter-inputs {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 18px;
        }
        .ttc-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 8px;
        }
        .dark .ttc-field label { color: #9ca3af; }
        .ttc-input {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13.5px;
            font-weight: 500;
            min-width: 165px;
        }
        select.ttc-input {
            padding-right: 38px !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 16px 16px !important;
        }
        .dark .ttc-input {
            background-color: #111827;
            border-color: #374151;
            color: #f9fafb;
        }
        .ttc-preset-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .ttc-preset-btn {
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s;
        }
        .ttc-preset-btn:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        .dark .ttc-preset-btn {
            background: #111827;
            border-color: #374151;
            color: #e5e7eb;
        }

        .ttc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .ttc-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #2563eb;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .dark .ttc-kpi-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ttc-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .ttc-kpi-label {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
        }
        .dark .ttc-kpi-label { color: #9ca3af; }
        .ttc-kpi-pill {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .ttc-kpi-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
            font-variant-numeric: tabular-nums;
        }
        .dark .ttc-kpi-value { color: #f8fafc; }
        .ttc-kpi-sub {
            margin-top: 8px;
            font-size: 12.5px;
            color: #64748b;
        }

        .ttc-kpi-profit {
            background: #f0fdf4;
            border-left-color: #059669 !important;
        }
        .ttc-kpi-profit .ttc-kpi-label { color: #065f46; }
        .ttc-kpi-profit .ttc-kpi-value { color: #047857; }
        .ttc-kpi-profit .ttc-kpi-sub { color: #065f46; }
        .ttc-kpi-profit .ttc-kpi-pill { background: #bbf7d0; color: #064e3b; }

        .dark .ttc-kpi-profit {
            background: rgba(6, 78, 59, 0.25) !important;
            border-color: #065f46 !important;
            border-left-color: #10b981 !important;
        }
        .dark .ttc-kpi-profit .ttc-kpi-label { color: #6ee7b7 !important; }
        .dark .ttc-kpi-profit .ttc-kpi-value { color: #34d399 !important; }
        .dark .ttc-kpi-profit .ttc-kpi-sub { color: #a7f3d0 !important; }
        .dark .ttc-kpi-profit .ttc-kpi-pill { background: rgba(16, 185, 129, 0.25) !important; color: #6ee7b7 !important; border: 1px solid #059669; }

        .ttc-kpi-loss {
            background: #fff1f2;
            border-left-color: #e11d48 !important;
        }
        .ttc-kpi-loss .ttc-kpi-label { color: #9f1239; }
        .ttc-kpi-loss .ttc-kpi-value { color: #be123c; }
        .ttc-kpi-loss .ttc-kpi-sub { color: #9f1239; }
        .ttc-kpi-loss .ttc-kpi-pill { background: #fecdd3; color: #881337; }

        .dark .ttc-kpi-loss {
            background: rgba(159, 18, 57, 0.25) !important;
            border-color: #9f1239 !important;
            border-left-color: #f43f5e !important;
        }
        .dark .ttc-kpi-loss .ttc-kpi-label { color: #fda4af !important; }
        .dark .ttc-kpi-loss .ttc-kpi-value { color: #fb7185 !important; }
        .dark .ttc-kpi-loss .ttc-kpi-sub { color: #fecdd3 !important; }
        .dark .ttc-kpi-loss .ttc-kpi-pill { background: rgba(225, 29, 72, 0.25) !important; color: #fda4af !important; border: 1px solid #e11d48; }

        .ttc-section-head {
            padding: 18px 28px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .dark .ttc-section-head {
            background: #1f2937;
            border-bottom-color: #374151;
        }
        .ttc-section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .ttc-section-desc {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 3px;
        }

        .ttc-table-wrap {
            padding: 20px 28px 28px 28px;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #3b82f6 #1e293b;
        }
        .ttc-table-wrap::-webkit-scrollbar {
            height: 10px;
        }
        .ttc-table-wrap::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 6px;
        }
        .dark .ttc-table-wrap::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .ttc-table-wrap::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 6px;
        }
        .ttc-table-wrap::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }
        table.ttc-pl-table {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
        }
        table.ttc-pl-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 18px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }
        .dark table.ttc-pl-table thead th {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #334155;
        }
        table.ttc-pl-table tbody td {
            padding: 13px 18px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .dark table.ttc-pl-table tbody td {
            border-bottom-color: #1f2937;
        }
        table.ttc-pl-table tbody tr.ttc-item-row:hover td {
            background: #f8fafc;
        }
        .dark table.ttc-pl-table tbody tr.ttc-item-row:hover td {
            background: rgba(30, 41, 59, 0.4);
        }
        tr.ttc-group-row td {
            background: #eff6ff;
            color: #1e3a8a;
            font-weight: 800;
            font-size: 13.5px;
            padding: 14px 18px;
            border-top: 1px solid #bfdbfe;
            border-bottom: 1px solid #bfdbfe;
        }
        .dark tr.ttc-group-row td {
            background: rgba(30, 58, 138, 0.3);
            color: #93c5fd;
            border-color: #1e3a8a;
        }
        tr.ttc-subtotal-row td {
            background: #f1f5f9;
            font-weight: 800;
            padding: 14px 18px;
            border-top: 1.5px solid #94a3b8;
            border-bottom: 1.5px solid #94a3b8;
        }
        .dark tr.ttc-subtotal-row td {
            background: #1e293b;
            border-color: #475569;
        }
        tr.ttc-major-row td {
            background: #dbeafe;
            color: #1e3a8a;
            font-weight: 800;
            font-size: 15px;
            padding: 16px 18px;
            border-top: 2px solid #2563eb;
            border-bottom: 2px solid #2563eb;
        }
        .dark tr.ttc-major-row td {
            background: rgba(37, 99, 235, 0.25);
            color: #bfdbfe;
        }
        .ttc-code-pill {
            display: inline-block;
            background: #1e3a8a;
            color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
        }
    </style>

    <div class="ttc-rpt-wrap">
        @include('filament.admin.partials.finance-report-tabs')

        {{-- Top Hero & Filter Control Bar --}}
        <div class="ttc-card">
            <div class="ttc-hero">
                <div>
                    <div>
                        <span class="ttc-badge-amber">Report Analisa Transaksi</span>
                        <span class="ttc-hero-sub">PT. Teknografi Tri Cawanaska</span>
                    </div>
                    <div class="ttc-hero-title">
                        Laporan Laba Rugi (Global) — Multi-Step Income Statement
                    </div>
                </div>

                <div class="ttc-btn-group">
                    <a href="{{ route('finance.report.laba-rugi.preview', ['start_date' => $this->startDate, 'end_date' => $this->endDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-amber">
                        🖨️ Preview PDF
                    </a>
                    <a href="{{ route('finance.report.laba-rugi.download', ['start_date' => $this->startDate, 'end_date' => $this->endDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-emerald">
                        ⬇️ Download PDF
                    </a>
                </div>
            </div>

            <div class="ttc-filter-bar">
                <div class="ttc-filter-inputs">
                    <div class="ttc-field">
                        <label>Periode Mulai</label>
                        <input type="date" wire:model.live="startDate" class="ttc-input" />
                    </div>
                    <div style="padding-bottom: 10px; font-weight: 700; color: #64748b;">s/d</div>
                    <div class="ttc-field">
                        <label>Periode Akhir</label>
                        <input type="date" wire:model.live="endDate" class="ttc-input" />
                    </div>
                </div>

                <div class="ttc-preset-group">
                    <span style="font-size: 12px; font-weight: 700; color: #64748b; margin-right: 4px;">Filter Cepat:</span>
                    <button type="button" wire:click="setPreset('this_month')" class="ttc-preset-btn">Bulan Ini</button>
                    <button type="button" wire:click="setPreset('last_month')" class="ttc-preset-btn">Bulan Lalu</button>
                    <button type="button" wire:click="setPreset('this_quarter')" class="ttc-preset-btn">Kuartal Ini</button>
                    <button type="button" wire:click="setPreset('this_year')" class="ttc-preset-btn">Tahun Ini</button>
                </div>
            </div>
        </div>

        {{-- 4 Executive KPI Cards --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #1d4ed8;">Pendapatan Bersih</span>
                    <span class="ttc-kpi-pill">100% Basis</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['total_pendapatan_bersih']) }}</div>
                <div class="ttc-kpi-sub">Pendapatan Kotor: <strong>{{ $fmt($data['total_pendapatan_kotor']) }}</strong></div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #ea580c;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #c2410c;">Harga Pokok (HPP)</span>
                    <span class="ttc-kpi-pill" style="background: #fff7ed; color: #c2410c;">{{ $hppPct }}% Revenue</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['total_hpp']) }}</div>
                <div class="ttc-kpi-sub">Beban Operasional: {{ $fmt($data['total_beban_operasional']) }} ({{ $opPct }}%)</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #4f46e5;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #4338ca;">Laba Kotor (Gross)</span>
                    <span class="ttc-kpi-pill" style="background: #eef2ff; color: #4338ca;">Margin {{ $grossPct }}%</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['laba_kotor']) }}</div>
                <div class="ttc-kpi-sub">Laba Operasional (EBIT): <strong>{{ $fmt($data['laba_operasional']) }}</strong></div>
            </div>

            <div class="ttc-kpi-card {{ $data['laba_bersih'] >= 0 ? 'ttc-kpi-profit' : 'ttc-kpi-loss' }}">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label">
                        {{ $data['laba_bersih'] >= 0 ? 'Laba Bersih (Net Profit)' : 'Rugi Bersih (Net Loss)' }}
                    </span>
                    <span class="ttc-kpi-pill">
                        {{ $data['margin_bersih_persen'] }}%
                    </span>
                </div>
                <div class="ttc-kpi-value">
                    {{ $fmt($data['laba_bersih']) }}
                </div>
                <div class="ttc-kpi-sub">
                    Periode {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                </div>
            </div>
        </div>

        {{-- Tabel Laporan Laba Rugi Multi-Step --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">Rincian Komponen Laba Rugi (Global)</div>
                    <div class="ttc-section-desc">
                        Periode Akuntansi: {{ \Carbon\Carbon::parse($data['start_date'])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($data['end_date'])->translatedFormat('d F Y') }}
                    </div>
                </div>
                <div style="font-size: 12.5px; color: #475569; font-weight: 600;">
                    Mata Uang: <strong>Rupiah (IDR)</strong>
                </div>
            </div>

            <div class="ttc-table-wrap">
                <table class="ttc-pl-table">
                    <thead>
                        <tr>
                            <th style="text-align: left; width: 150px;">Kode Akun</th>
                            <th style="text-align: left;">Uraian Akun / Kelompok Laba Rugi</th>
                            <th style="text-align: right; width: 240px;">Nominal (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- I. PENDAPATAN USAHA --}}
                        <tr class="ttc-group-row">
                            <td><span class="ttc-code-pill">4000</span></td>
                            <td colspan="2">I. PENDAPATAN USAHA (REVENUE)</td>
                        </tr>
                        @forelse ($data['pendapatan_usaha'] as $item)
                            <tr class="ttc-item-row">
                                <td style="font-family: monospace; font-weight: 600; color: #64748b;">{{ $item['kode_akun'] }}</td>
                                <td style="padding-left: 34px; font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="color: #94a3b8;">-</td>
                                <td style="padding-left: 34px; font-style: italic; color: #94a3b8;">Belum ada mutasi pendapatan penjualan pada periode ini</td>
                                <td style="text-align: right; color: #94a3b8;">Rp 0</td>
                            </tr>
                        @endforelse

                        @if (!empty($data['retur_diskon']))
                            @foreach ($data['retur_diskon'] as $item)
                                <tr class="ttc-item-row" style="color: #e11d48;">
                                    <td style="font-family: monospace; font-weight: 600;">{{ $item['kode_akun'] }}</td>
                                    <td style="padding-left: 34px; font-weight: 600;">(-) {{ $item['nama_akun'] }}</td>
                                    <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">({{ $fmt($item['nominal']) }})</td>
                                </tr>
                            @endforeach
                        @endif

                        <tr class="ttc-subtotal-row">
                            <td></td>
                            <td>TOTAL PENDAPATAN USAHA BERSIH</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; color: #1d4ed8;">{{ $fmt($data['total_pendapatan_bersih']) }}</td>
                        </tr>

                        {{-- II. HARGA POKOK PENJUALAN (HPP) --}}
                        <tr class="ttc-group-row">
                            <td><span class="ttc-code-pill" style="background: #c2410c;">5000</span></td>
                            <td colspan="2">II. HARGA POKOK PENJUALAN (HPP / COST OF GOODS SOLD)</td>
                        </tr>
                        @forelse ($data['hpp_items'] as $item)
                            <tr class="ttc-item-row">
                                <td style="font-family: monospace; font-weight: 600; color: #64748b;">{{ $item['kode_akun'] }}</td>
                                <td style="padding-left: 34px; font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="color: #94a3b8;">-</td>
                                <td style="padding-left: 34px; font-style: italic; color: #94a3b8;">Belum ada mutasi HPP pada periode ini</td>
                                <td style="text-align: right; color: #94a3b8;">Rp 0</td>
                            </tr>
                        @endforelse
                        <tr class="ttc-subtotal-row">
                            <td></td>
                            <td>TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; color: #c2410c;">({{ $fmt($data['total_hpp']) }})</td>
                        </tr>

                        {{-- LABA KOTOR --}}
                        <tr class="ttc-major-row">
                            <td></td>
                            <td>LABA KOTOR (GROSS PROFIT)</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $fmt($data['laba_kotor']) }}</td>
                        </tr>

                        {{-- III. BEBAN OPERASIONAL --}}
                        <tr class="ttc-group-row">
                            <td><span class="ttc-code-pill" style="background: #6d28d9;">6000</span></td>
                            <td colspan="2">III. BEBAN OPERASIONAL (OPERATING EXPENSES)</td>
                        </tr>
                        @php
                            $allOpExpenses = array_merge(
                                $data['beban_penjualan'],
                                $data['beban_admin_umum'],
                                $data['beban_tenaga_kerja'],
                                $data['beban_pajak_k3_lain']
                            );
                        @endphp
                        @forelse ($allOpExpenses as $item)
                            <tr class="ttc-item-row">
                                <td style="font-family: monospace; font-weight: 600; color: #64748b;">{{ $item['kode_akun'] }}</td>
                                <td style="padding-left: 34px; font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td style="color: #94a3b8;">-</td>
                                <td style="padding-left: 34px; font-style: italic; color: #94a3b8;">Belum ada mutasi beban operasional pada periode ini</td>
                                <td style="text-align: right; color: #94a3b8;">Rp 0</td>
                            </tr>
                        @endforelse
                        <tr class="ttc-subtotal-row">
                            <td></td>
                            <td>TOTAL BEBAN OPERASIONAL</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; color: #e11d48;">({{ $fmt($data['total_beban_operasional']) }})</td>
                        </tr>

                        {{-- LABA OPERASIONAL --}}
                        <tr class="ttc-major-row">
                            <td></td>
                            <td>LABA OPERASIONAL USAHA (EBIT)</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $fmt($data['laba_operasional']) }}</td>
                        </tr>

                        {{-- IV. PENDAPATAN & BEBAN LAIN-LAIN --}}
                        <tr class="ttc-group-row">
                            <td><span class="ttc-code-pill" style="background: #334155;">7000</span></td>
                            <td colspan="2">IV. PENDAPATAN &amp; BEBAN NON-OPERASIONAL</td>
                        </tr>
                        @foreach ($data['pendapatan_lain'] as $item)
                            <tr class="ttc-item-row" style="color: #047857;">
                                <td style="font-family: monospace; font-weight: 600;">{{ $item['kode_akun'] }}</td>
                                <td style="padding-left: 34px; font-weight: 600;">(+) {{ $item['nama_akun'] }}</td>
                                <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                            </tr>
                        @endforeach
                        @foreach ($data['beban_non_operasional'] as $item)
                            <tr class="ttc-item-row" style="color: #e11d48;">
                                <td style="font-family: monospace; font-weight: 600;">{{ $item['kode_akun'] }}</td>
                                <td style="padding-left: 34px; font-weight: 600;">(-) {{ $item['nama_akun'] }}</td>
                                <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">({{ $fmt($item['nominal']) }})</td>
                            </tr>
                        @endforeach
                        @if (empty($data['pendapatan_lain']) && empty($data['beban_non_operasional']))
                            <tr>
                                <td style="color: #94a3b8;">-</td>
                                <td style="padding-left: 34px; font-style: italic; color: #94a3b8;">Tidak ada pendapatan/beban non-operasional pada periode ini</td>
                                <td style="text-align: right; color: #94a3b8;">Rp 0</td>
                            </tr>
                        @endif
                        <tr class="ttc-subtotal-row">
                            <td></td>
                            <td>TOTAL PENDAPATAN / (BEBAN) LAIN-LAIN BERSIH</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $fmt($data['net_lain_lain']) }}</td>
                        </tr>

                        {{-- LABA / RUGI BERSIH --}}
                        <tr style="background: {{ $data['laba_bersih'] >= 0 ? '#d1fae5' : '#ffe4e6' }}; color: {{ $data['laba_bersih'] >= 0 ? '#064e3b' : '#881337' }}; font-weight: 900; font-size: 16px;">
                            <td style="padding: 18px; border-top: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }}; border-bottom: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }};"></td>
                            <td style="padding: 18px; border-top: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }}; border-bottom: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }};">
                                LABA / (RUGI) BERSIH PERIODE BERJALAN (Margin: {{ $data['margin_bersih_persen'] }}%)
                            </td>
                            <td style="padding: 18px; text-align: right; font-variant-numeric: tabular-nums; border-top: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }}; border-bottom: 2.5px solid {{ $data['laba_bersih'] >= 0 ? '#059669' : '#e11d48' }};">
                                {{ $fmt($data['laba_bersih']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
