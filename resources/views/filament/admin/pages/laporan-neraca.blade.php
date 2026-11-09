<x-filament-panels::page>
    @php
        $data = $this->reportData;
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
        $aktivaBase = max((float) $data['total_aktiva'], 1);
        $lancarPct = round(((float) $data['total_aset_lancar'] / $aktivaBase) * 100, 1);
        $tetapPct = round(((float) $data['total_aset_tetap'] / $aktivaBase) * 100, 1);
        $utangPct = round(((float) $data['total_kewajiban'] / $aktivaBase) * 100, 1);
        $modalPct = round(((float) $data['total_ekuitas'] / $aktivaBase) * 100, 1);
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
            .ttc-bs-wrap {
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
                grid-template-columns: repeat(3, 1fr) !important;
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
            table.ttc-bs-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important;
            }
            table.ttc-bs-table th, table.ttc-bs-table td {
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
            min-width: 180px;
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
            font-size: 23px;
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

        .ttc-bs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 24px;
        }
        .ttc-bs-col {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .ttc-col-head {
            padding: 18px 24px;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .ttc-col-head-aktiva {
            background: #eff6ff;
        }
        .dark .ttc-col-head-aktiva {
            background: rgba(30, 58, 138, 0.35);
            border-bottom-color: #1e3a8a;
        }
        .ttc-col-head-pasiva {
            background: #eef2ff;
        }
        .dark .ttc-col-head-pasiva {
            background: rgba(67, 56, 202, 0.35);
            border-bottom-color: #3730a3;
        }
        .ttc-head-title-aktiva {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dark .ttc-head-title-aktiva {
            color: #93c5fd;
        }
        .ttc-head-sub-aktiva {
            font-size: 12px;
            color: #3b82f6;
            margin-top: 2px;
        }
        .dark .ttc-head-sub-aktiva {
            color: #bfdbfe;
        }
        .ttc-head-title-pasiva {
            font-size: 14px;
            font-weight: 800;
            color: #312e81;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dark .ttc-head-title-pasiva {
            color: #c7d2fe;
        }
        .ttc-head-sub-pasiva {
            font-size: 12px;
            color: #4f46e5;
            margin-top: 2px;
        }
        .dark .ttc-head-sub-pasiva {
            color: #e0e7ff;
        }
        .ttc-balance-ok {
            background: #f0fdf4;
        }
        .dark .ttc-balance-ok {
            background: rgba(5, 150, 105, 0.15) !important;
        }
        .ttc-balance-error {
            background: #fff1f2;
        }
        .dark .ttc-balance-error {
            background: rgba(225, 29, 72, 0.15) !important;
        }
        .ttc-bs-acc-name {
            font-weight: 500;
            color: #0f172a;
        }
        .dark .ttc-bs-acc-name {
            color: #f1f5f9;
        }
        .ttc-col-body {
            padding: 18px 24px;
        }
        table.ttc-bs-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }
        table.ttc-bs-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .dark table.ttc-bs-table tbody td {
            border-bottom-color: #1f2937;
        }
        tr.ttc-bs-sec td {
            background: #f1f5f9;
            font-weight: 800;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #1e3a8a;
            padding: 12px 14px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .dark tr.ttc-bs-sec td {
            background: #1e293b;
            color: #93c5fd;
            border-color: #334155;
        }
        tr.ttc-bs-sub td {
            background: #eff6ff;
            font-weight: 800;
            padding: 12px 14px;
            border-top: 1.5px solid #93c5fd;
            border-bottom: 1.5px solid #93c5fd;
        }
        .dark tr.ttc-bs-sub td {
            background: rgba(30, 58, 138, 0.25);
            border-color: #1e3a8a;
            color: #93c5fd !important;
        }
        .ttc-col-foot {
            padding: 18px 24px;
            color: #ffffff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 15px;
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
                        Laporan Neraca — Posisi Keuangan (Aktiva &amp; Pasiva)
                    </div>
                </div>

                <div class="ttc-btn-group">
                    <a href="{{ route('finance.report.neraca.preview', ['as_of_date' => $this->asOfDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-amber">
                        🖨️ Preview PDF
                    </a>
                    <a href="{{ route('finance.report.neraca.download', ['as_of_date' => $this->asOfDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-emerald">
                        ⬇️ Download PDF
                    </a>
                </div>
            </div>

            <div class="ttc-filter-bar">
                <div class="ttc-field">
                    <label>Per Tanggal Neraca (Cut-Off Date)</label>
                    <input type="date" wire:model.live="asOfDate" class="ttc-input" />
                </div>

                <div class="ttc-preset-group">
                    <span style="font-size: 12px; font-weight: 700; color: #64748b; margin-right: 4px;">Posisi Cepat:</span>
                    <button type="button" wire:click="setPresetDate('today')" class="ttc-preset-btn">Hari Ini</button>
                    <button type="button" wire:click="setPresetDate('end_of_month')" class="ttc-preset-btn">Akhir Bulan Ini</button>
                    <button type="button" wire:click="setPresetDate('last_month')" class="ttc-preset-btn">Akhir Bulan Lalu</button>
                    <button type="button" wire:click="setPresetDate('end_of_year')" class="ttc-preset-btn">Akhir Tahun Ini</button>
                </div>
            </div>
        </div>

        {{-- 4 KPI Cards --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #1d4ed8;">Total Aktiva (Aset)</span>
                    <span class="ttc-kpi-pill">Lancar {{ $lancarPct }}%</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['total_aktiva']) }}</div>
                <div class="ttc-kpi-sub">Aset Tetap: {{ $fmt($data['total_aset_tetap']) }} ({{ $tetapPct }}%)</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #ea580c;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #c2410c;">Total Kewajiban (Utang)</span>
                    <span class="ttc-kpi-pill" style="background: #fff7ed; color: #c2410c;">{{ $utangPct }}% Pasiva</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['total_kewajiban']) }}</div>
                <div class="ttc-kpi-sub">Utang Lancar: {{ $fmt($data['total_kewajiban_lancar']) }}</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #4f46e5;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #4338ca;">Total Ekuitas (Modal)</span>
                    <span class="ttc-kpi-pill" style="background: #eef2ff; color: #4338ca;">{{ $modalPct }}% Pasiva</span>
                </div>
                <div class="ttc-kpi-value">{{ $fmt($data['total_ekuitas']) }}</div>
                <div class="ttc-kpi-sub">
                    Laba Berjalan: <strong>{{ $fmt($data['laba_periode_berjalan'] ?? $data['laba_tahun_berjalan'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="ttc-kpi-card {{ $data['is_balanced'] ? 'ttc-balance-ok' : 'ttc-balance-error' }}" style="border-left-color: {{ $data['is_balanced'] ? '#059669' : '#e11d48' }};">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: {{ $data['is_balanced'] ? '#065f46' : '#9f1239' }};">
                        Status Keseimbangan
                    </span>
                    <span class="ttc-kpi-pill" style="background: {{ $data['is_balanced'] ? '#bbf7d0' : '#fecdd3' }}; color: {{ $data['is_balanced'] ? '#064e3b' : '#881337' }};">
                        Aktiva = Pasiva
                    </span>
                </div>
                <div class="ttc-kpi-value" style="font-size: 20px; color: {{ $data['is_balanced'] ? '#047857' : '#be123c' }};">
                    @if ($data['is_balanced'])
                        🟢 SEIMBANG (BALANCE)
                    @else
                        🔴 SELISIH: {{ $fmt($data['selisih']) }}
                    @endif
                </div>
                <div class="ttc-kpi-sub" style="color: {{ $data['is_balanced'] ? '#065f46' : '#9f1239' }};">
                    Per Tanggal: {{ \Carbon\Carbon::parse($data['as_of_date'])->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>

        {{-- 2 Kolom Bersebelahan: AKTIVA (Kiri) & PASIVA (Kanan) --}}
        <div class="ttc-bs-grid">
            {{-- KOLOM KIRI: AKTIVA --}}
            <div class="ttc-card ttc-bs-col">
                <div>
                    <div class="ttc-col-head ttc-col-head-aktiva">
                        <div>
                            <div class="ttc-head-title-aktiva">
                                AKTIVA (ASET PERUSAHAAN)
                            </div>
                            <div class="ttc-head-sub-aktiva">
                                Kelompok Akun 1000 — Harta &amp; Kekayaan Pabrik
                            </div>
                        </div>
                        <span style="background: #1d4ed8; color: #ffffff; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 8px;">
                            DEBET
                        </span>
                    </div>

                    <div class="ttc-col-body">
                        <table class="ttc-bs-table">
                            <tbody>
                                {{-- I. ASET LANCAR --}}
                                <tr class="ttc-bs-sec">
                                    <td colspan="3">I. ASET LANCAR (CURRENT ASSETS)</td>
                                </tr>
                                @forelse ($data['aset_lancar'] as $item)
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                        <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="font-style: italic; color: #94a3b8;">Belum ada saldo aset lancar</td>
                                    </tr>
                                @endforelse
                                <tr class="ttc-bs-sub">
                                    <td colspan="2">Total Aset Lancar</td>
                                    <td style="text-align: right; font-variant-numeric: tabular-nums; color: #1e3a8a;">{{ $fmt($data['total_aset_lancar']) }}</td>
                                </tr>

                                {{-- II. ASET TETAP --}}
                                <tr class="ttc-bs-sec">
                                    <td colspan="3">II. ASET TETAP (FIXED ASSETS)</td>
                                </tr>
                                @forelse ($data['aset_tetap'] as $item)
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                        <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: {{ $item['nominal'] < 0 ? '#e11d48' : 'inherit' }};">
                                            {{ $fmt($item['nominal']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="font-style: italic; color: #94a3b8;">Belum ada saldo aset tetap</td>
                                    </tr>
                                @endforelse
                                <tr class="ttc-bs-sub">
                                    <td colspan="2">Total Aset Tetap (Nilai Buku Bersih)</td>
                                    <td style="text-align: right; font-variant-numeric: tabular-nums; color: #1e3a8a;">{{ $fmt($data['total_aset_tetap']) }}</td>
                                </tr>

                                {{-- III. ASET LAINNYA --}}
                                @if (!empty($data['aset_lainnya']))
                                    <tr class="ttc-bs-sec">
                                        <td colspan="3">III. ASET LAINNYA</td>
                                    </tr>
                                    @foreach ($data['aset_lainnya'] as $item)
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                            <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                            <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="ttc-bs-sub">
                                        <td colspan="2">Total Aset Lainnya</td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $fmt($data['total_aset_lainnya']) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ttc-col-foot" style="background: #1d4ed8;">
                    <span>TOTAL AKTIVA</span>
                    <span style="font-size: 18px; font-variant-numeric: tabular-nums;">{{ $fmt($data['total_aktiva']) }}</span>
                </div>
            </div>

            {{-- KOLOM KANAN: PASIVA (KEWAJIBAN & EKUITAS) --}}
            <div class="ttc-card ttc-bs-col">
                <div>
                    <div class="ttc-col-head ttc-col-head-pasiva">
                        <div>
                            <div class="ttc-head-title-pasiva">
                                PASIVA (KEWAJIBAN &amp; EKUITAS)
                            </div>
                            <div class="ttc-head-sub-pasiva">
                                Kelompok Akun 2000 &amp; 3000 — Utang &amp; Modal
                            </div>
                        </div>
                        <span style="background: #4338ca; color: #ffffff; font-size: 11px; font-weight: 800; padding: 5px 12px; border-radius: 8px;">
                            KREDIT
                        </span>
                    </div>

                    <div class="ttc-col-body">
                        <table class="ttc-bs-table">
                            <tbody>
                                {{-- I. KEWAJIBAN LANCAR --}}
                                <tr class="ttc-bs-sec">
                                    <td colspan="3">I. KEWAJIBAN JANGKA PENDEK (UTANG LANCAR)</td>
                                </tr>
                                @forelse ($data['kewajiban_lancar'] as $item)
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                        <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="font-style: italic; color: #94a3b8;">Belum ada saldo kewajiban lancar</td>
                                    </tr>
                                @endforelse
                                <tr class="ttc-bs-sub">
                                    <td colspan="2">Total Kewajiban Lancar</td>
                                    <td style="text-align: right; font-variant-numeric: tabular-nums; color: #c2410c;">{{ $fmt($data['total_kewajiban_lancar']) }}</td>
                                </tr>

                                {{-- II. KEWAJIBAN JANGKA PANJANG --}}
                                @if (!empty($data['kewajiban_jangka_panjang']))
                                    <tr class="ttc-bs-sec">
                                        <td colspan="3">II. KEWAJIBAN JANGKA PANJANG</td>
                                    </tr>
                                    @foreach ($data['kewajiban_jangka_panjang'] as $item)
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                            <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                            <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($item['nominal']) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="ttc-bs-sub">
                                        <td colspan="2">Total Kewajiban Jangka Panjang</td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; color: #c2410c;">{{ $fmt($data['total_kewajiban_jangka_panjang']) }}</td>
                                    </tr>
                                @endif

                                <tr style="background: #e2e8f0; font-weight: 800;">
                                    <td colspan="2" style="padding: 12px 14px;">TOTAL KEWAJIBAN (LIABILITIES)</td>
                                    <td style="padding: 12px 14px; text-align: right; font-variant-numeric: tabular-nums;">{{ $fmt($data['total_kewajiban']) }}</td>
                                </tr>

                                {{-- III. EKUITAS / MODAL --}}
                                <tr class="ttc-bs-sec">
                                    <td colspan="3">III. EKUITAS / MODAL (EQUITY)</td>
                                </tr>
                                @forelse ($data['ekuitas_items'] as $item)
                                    <tr>
                                        <td style="font-family: monospace; font-weight: 600; color: #64748b; width: 85px;">{{ $item['kode_akun'] }}</td>
                                        <td style="font-weight: 500;">{{ $item['nama_akun'] }}</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: {{ $item['nominal'] < 0 ? '#e11d48' : 'inherit' }};">
                                            {{ $fmt($item['nominal']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="font-style: italic; color: #94a3b8;">Belum ada saldo ekuitas</td>
                                    </tr>
                                @endforelse
                                <tr class="ttc-bs-sub">
                                    <td colspan="2">TOTAL EKUITAS (MODAL)</td>
                                    <td style="text-align: right; font-variant-numeric: tabular-nums; color: #312e81;">{{ $fmt($data['total_ekuitas']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ttc-col-foot" style="background: #4338ca;">
                    <span>TOTAL PASIVA (KEWAJIBAN + EKUITAS)</span>
                    <span style="font-size: 18px; font-variant-numeric: tabular-nums;">{{ $fmt($data['total_pasiva']) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
