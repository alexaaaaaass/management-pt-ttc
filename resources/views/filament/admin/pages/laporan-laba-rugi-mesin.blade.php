<x-filament-panels::page>
    @php
        $data = $this->reportData;
        $mesinOptions = $this->mesinList;
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
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
                size: A4 landscape;
                margin: 8mm 8mm;
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
                font-size: 10px !important;
            }
            table.ttc-pl-table th, table.ttc-pl-table td {
                padding: 5px 8px !important;
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
            min-width: 155px;
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
        .dark .ttc-kpi-sub { color: #9ca3af; }

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
            color: #0f172a;
        }
        .dark .ttc-section-title { color: #f8fafc; }
        .ttc-section-desc {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 3px;
        }

        .ttc-table-wrap {
            padding: 20px 26px 26px 26px;
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
        table.ttc-table {
            width: 100%;
            min-width: 1100px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }
        table.ttc-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        .dark table.ttc-table thead th {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #334155;
        }
        table.ttc-table tbody td {
            padding: 15px 16px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            line-height: 1.5;
        }
        .dark table.ttc-table tbody td {
            border-bottom-color: #1f2937;
        }
        table.ttc-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .dark table.ttc-table tbody tr:nth-child(even) td {
            background: rgba(30, 41, 59, 0.35);
        }
        table.ttc-table tbody tr:hover td {
            background: #eff6ff;
        }
        .dark table.ttc-table tbody tr:hover td {
            background: rgba(30, 58, 138, 0.25);
        }
        table.ttc-table tfoot td {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            font-size: 14px;
            padding: 16px 16px;
            border-top: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
            white-space: nowrap;
        }
        .dark table.ttc-table tfoot td {
            background: #1e293b;
            color: #f8fafc;
            border-color: #475569;
        }

        .ttc-code-badge {
            display: inline-block;
            background: #1e3a8a;
            color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
        }
        .ttc-type-badge {
            display: inline-block;
            background: #e2e8f0;
            color: #1e293b;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .dark .ttc-type-badge {
            background: #334155;
            color: #e2e8f0;
        }
        .ttc-margin-badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .ttc-detail-box {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .dark .ttc-detail-box { border-color: #334155; }
        .ttc-detail-head {
            background: #f1f5f9;
            padding: 14px 20px;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .dark .ttc-detail-head {
            background: #1e293b;
            border-bottom-color: #334155;
        }
    </style>

    <div class="ttc-rpt-wrap">
        @include('filament.admin.partials.finance-report-tabs')

        {{-- Top Hero & Control Filter Card --}}
        <div class="ttc-card">
            <div class="ttc-hero">
                <div>
                    <div>
                        <span class="ttc-badge-amber">Report Analisa Transaksi</span>
                        <span class="ttc-hero-sub">PT. Teknografi Tri Cawanaska</span>
                    </div>
                    <div class="ttc-hero-title">
                        Laba Rugi (Mesin) &amp; Laba Rugi (Mesin)-Detail
                    </div>
                </div>

                <div class="ttc-btn-group">
                    <a href="{{ route('finance.report.laba-rugi-mesin.preview', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'mesin' => $this->selectedMesin]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-amber">
                        🖨️ Preview PDF
                    </a>
                    <a href="{{ route('finance.report.laba-rugi-mesin.download', ['start_date' => $this->startDate, 'end_date' => $this->endDate, 'mesin' => $this->selectedMesin]) }}"
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

                    <div class="ttc-field">
                        <label>Pilih Unit Mesin</label>
                        <select wire:model.live="selectedMesin" class="ttc-input" style="min-width: 210px;">
                            <option value="">-- Semua Unit Mesin (Flexo) --</option>
                            @foreach ($mesinOptions as $kode => $nama)
                                <option value="{{ $nama }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ttc-field">
                        <label>Mode Tampilan Laporan</label>
                        <select wire:model.live="viewMode" class="ttc-input" style="min-width: 230px;">
                            <option value="ALL">Laba Rugi (Mesin) + Detail</option>
                            <option value="SUMMARY">Laba Rugi (Mesin) Saja</option>
                            <option value="DETAIL">Laba Rugi (Mesin)-Detail Saja</option>
                        </select>
                    </div>
                </div>

                <div class="ttc-preset-group">
                    <span style="font-size: 12px; font-weight: 700; color: #64748b; margin-right: 4px;">Filter Cepat:</span>
                    <button type="button" wire:click="setPreset('this_month')" class="ttc-preset-btn">Bulan Ini</button>
                    <button type="button" wire:click="setPreset('last_month')" class="ttc-preset-btn">Bulan Lalu</button>
                    <button type="button" wire:click="setPreset('this_year')" class="ttc-preset-btn">Tahun Ini</button>
                </div>
            </div>
        </div>

        {{-- 4 KPI Cards dengan Ruang Lega --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #1d4ed8;">Output Produksi</span>
                    <span class="ttc-kpi-pill">{{ $data['grand_spk_count'] }} SPK</span>
                </div>
                <div class="ttc-kpi-value">
                    {{ number_format($data['grand_hasil_baik'], 0, ',', '.') }} <span style="font-size: 14px; font-weight: 600; color: #64748b;">Pcs</span>
                </div>
                <div class="ttc-kpi-sub">
                    Waste / Rusak: <strong>{{ number_format($data['grand_hasil_rusak'], 0, ',', '.') }} Pcs</strong>
                </div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #4f46e5;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #4338ca;">Nilai Output (Pendapatan)</span>
                    <span class="ttc-kpi-pill" style="background: #eef2ff; color: #4338ca;">Revenue</span>
                </div>
                <div class="ttc-kpi-value">
                    {{ $fmt($data['grand_pendapatan']) }}
                </div>
                <div class="ttc-kpi-sub">
                    Berdasarkan nilai SO / Output SPK selesai
                </div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #ea580c;">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label" style="color: #c2410c;">Total Biaya Produksi</span>
                    <span class="ttc-kpi-pill" style="background: #fff7ed; color: #c2410c;">Bahan + Mesin</span>
                </div>
                <div class="ttc-kpi-value">
                    {{ $fmt($data['grand_total_biaya']) }}
                </div>
                <div class="ttc-kpi-sub">
                    Bahan: {{ $fmt($data['grand_biaya_bahan']) }} &bull; Mesin: {{ $fmt($data['grand_biaya_operasional']) }}
                </div>
            </div>

            <div class="ttc-kpi-card {{ $data['grand_laba'] >= 0 ? 'ttc-kpi-profit' : 'ttc-kpi-loss' }}">
                <div class="ttc-kpi-top">
                    <span class="ttc-kpi-label">
                        Kontribusi Laba Mesin
                    </span>
                    <span class="ttc-kpi-pill">
                        Margin {{ $data['grand_margin_persen'] }}%
                    </span>
                </div>
                <div class="ttc-kpi-value">
                    {{ $fmt($data['grand_laba']) }}
                </div>
                <div class="ttc-kpi-sub">
                    {{ $this->selectedMesin ? 'Filter Mesin: ' . $this->selectedMesin : 'Gabungan Seluruh Unit Mesin (Flexo)' }}
                </div>
            </div>
        </div>

        {{-- TABEL 1: LABA RUGI (MESIN) - REKAPITULASI PER UNIT MESIN CETAK --}}
        @if ($this->viewMode === 'ALL' || $this->viewMode === 'SUMMARY')
            <div class="ttc-card">
                <div class="ttc-section-head">
                    <div>
                        <div class="ttc-section-title">
                            I. Laba Rugi (Mesin) — Rekapitulasi Profitabilitas per Unit Mesin (Flexo)
                        </div>
                        <div class="ttc-section-desc">
                            Periode Laporan: {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($data['end_date'])->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="ttc-kpi-pill" style="font-size: 12px; padding: 5px 14px;">
                        {{ count($data['machines']) }} Unit Mesin Aktif
                    </span>
                </div>

                <div class="ttc-table-wrap">
                    <table class="ttc-table">
                        <thead>
                            <tr>
                                <th style="text-align: left; min-width: 170px;">Unit Mesin Cetak</th>
                                <th style="text-align: left; min-width: 140px;">Jenis / Proses</th>
                                <th style="text-align: center; width: 70px;">Jml SPK</th>
                                <th style="text-align: right; min-width: 130px;">Hasil Baik (Pcs)</th>
                                <th style="text-align: right; min-width: 120px;">Waste (Pcs)</th>
                                <th style="text-align: right; min-width: 155px;">Pendapatan (Rp)</th>
                                <th style="text-align: right; min-width: 155px;">HPP Bahan (Rp)</th>
                                <th style="text-align: right; min-width: 145px;">Biaya Mesin (Rp)</th>
                                <th style="text-align: right; min-width: 155px;">Laba Mesin (Rp)</th>
                                <th style="text-align: right; width: 95px;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['machines'] as $idx => $row)
                                @php
                                    $kodeMesin = $row['kode_mesin'] ?? ('MSN-' . str_pad((string) ($row['mesin_id'] ?? $idx + 1), 2, '0', STR_PAD_LEFT));
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 800; font-size: 14px;">{{ $row['nama_mesin'] ?? '-' }}</div>
                                        <div style="margin-top: 4px;">
                                            <span class="ttc-code-badge">{{ $kodeMesin }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ttc-type-badge">
                                            {{ trim((string) ($row['jenis_mesin'] ?? '-')) }} &bull; {{ trim((string) ($row['proses'] ?? '-')) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; font-variant-numeric: tabular-nums;">
                                        {{ $row['jumlah_spk'] ?? 0 }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">
                                        {{ number_format($row['hasil_baik'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-variant-numeric: tabular-nums; color: #e11d48; font-weight: 600;">
                                        {{ number_format($row['hasil_rusak'] ?? 0, 0, ',', '.') }}
                                        <span style="font-size: 11.5px; color: #64748b; font-weight: 500;">({{ $row['waste_persen'] ?? 0 }}%)</span>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: #1d4ed8;">
                                        {{ $fmt($row['pendapatan']) }}
                                    </td>
                                    <td style="text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; color: #c2410c;">
                                        {{ $fmt($row['biaya_bahan']) }}
                                    </td>
                                    <td style="text-align: right; font-weight: 600; font-variant-numeric: tabular-nums; color: #7e22ce;">
                                        {{ $fmt($row['biaya_operasional']) }}
                                    </td>
                                    <td style="text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; color: {{ $row['laba_mesin'] >= 0 ? '#047857' : '#be123c' }};">
                                        {{ $fmt($row['laba_mesin']) }}
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="ttc-margin-badge" style="background: {{ $row['margin_persen'] >= 0 ? '#d1fae5' : '#ffe4e6' }}; color: {{ $row['margin_persen'] >= 0 ? '#065f46' : '#9f1239' }};">
                                            {{ $row['margin_persen'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 32px; color: #64748b; font-style: italic;">
                                        Tidak ada data mesin cetak yang ditemukan pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">TOTAL KESELURUHAN MESIN</td>
                                <td style="text-align: center; font-variant-numeric: tabular-nums;">{{ $data['grand_spk_count'] }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ number_format($data['grand_hasil_baik'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; color: #e11d48;">{{ number_format($data['grand_hasil_rusak'], 0, ',', '.') }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; color: #1d4ed8;">{{ $fmt($data['grand_pendapatan']) }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; color: #c2410c;">{{ $fmt($data['grand_biaya_bahan']) }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; color: #7e22ce;">{{ $fmt($data['grand_biaya_operasional']) }}</td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; color: {{ $data['grand_laba'] >= 0 ? '#047857' : '#be123c' }};">
                                    {{ $fmt($data['grand_laba']) }}
                                </td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $data['grand_margin_persen'] }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- TABEL 2: LABA RUGI (MESIN)-DETAIL --}}
        @if ($this->viewMode === 'ALL' || $this->viewMode === 'DETAIL')
            <div class="ttc-card">
                <div class="ttc-section-head">
                    <div>
                        <div class="ttc-section-title">
                            II. Laba Rugi (Mesin)-Detail — Rincian Pendapatan &amp; Pengeluaran Per Mesin
                        </div>
                        <div class="ttc-section-desc">
                            Menampilkan rincian komponen Pendapatan (Detail) dan Pengeluaran (Detail) per unit mesin cetak &amp; finishing
                        </div>
                    </div>
                </div>

                <div class="ttc-table-wrap">
                    @foreach ($data['machines'] as $idx => $machine)
                        @php
                            $kodeM = $machine['kode_mesin'] ?? ('MSN-' . str_pad((string) ($machine['mesin_id'] ?? $idx + 1), 2, '0', STR_PAD_LEFT));
                        @endphp
                        <div class="ttc-detail-box">
                            <div class="ttc-detail-head">
                                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                    <span class="ttc-code-badge">{{ $kodeM }}</span>
                                    <span style="font-size: 15px; font-weight: 800;">{{ $machine['nama_mesin'] ?? '-' }}</span>
                                    <span style="font-size: 12.5px; color: #64748b;">
                                        ({{ trim((string) ($machine['jenis_mesin'] ?? '-')) }} &bull; {{ trim((string) ($machine['proses'] ?? '-')) }})
                                    </span>
                                </div>
                                <div style="font-size: 13.5px; font-weight: 800; color: {{ $machine['laba_mesin'] >= 0 ? '#047857' : '#be123c' }};">
                                    Saldo Laba Mesin: {{ $fmt($machine['laba_mesin']) }} (Margin {{ $machine['margin_persen'] }}%)
                                </div>
                            </div>

                            <table class="ttc-table">
                                <thead>
                                    <tr>
                                        <th style="text-align: left; width: 210px;">Kelompok (GroupAcc)</th>
                                        <th style="text-align: left;">Uraian Rincian Produksi &amp; Biaya Mesin</th>
                                        <th style="text-align: right; width: 190px;">Debet (Pendapatan)</th>
                                        <th style="text-align: right; width: 190px;">Kredit (Pengeluaran)</th>
                                        <th style="text-align: right; width: 190px;">Saldo Bersih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-weight: 700; color: #1d4ed8;">Pendapatan (Detail)</td>
                                        <td>
                                            Output Produksi Cetak: <strong>{{ number_format($machine['hasil_baik'], 0, ',', '.') }} Pcs</strong> dari <strong>{{ $machine['jumlah_spk'] }} SPK</strong>
                                            <span style="color: #64748b; margin-left: 6px;">(Waste: {{ number_format($machine['hasil_rusak'], 0, ',', '.') }} Pcs / {{ $machine['waste_persen'] ?? 0 }}%)</span>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: #1d4ed8;">{{ $fmt($machine['pendapatan']) }}</td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; color: #94a3b8;">Rp 0</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($machine['pendapatan']) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: #c2410c;">Pengeluaran (Detail)</td>
                                        <td>
                                            Pemakaian Bahan Baku Cetak (IMR Printing / Kertas / Tinta / Plate)
                                        </td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; color: #94a3b8;">Rp 0</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: #c2410c;">{{ $fmt($machine['biaya_bahan']) }}</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums;">{{ $fmt($machine['pendapatan'] - $machine['biaya_bahan']) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 700; color: #7e22ce;">Pengeluaran (Detail)</td>
                                        <td>
                                            Biaya Operasional, Suku Cadang &amp; Perawatan Mesin (Operasional Keluar Terkait Mesin)
                                        </td>
                                        <td style="text-align: right; font-variant-numeric: tabular-nums; color: #94a3b8;">Rp 0</td>
                                        <td style="text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: #7e22ce;">{{ $fmt($machine['biaya_operasional']) }}</td>
                                        <td style="text-align: right; font-weight: 800; font-variant-numeric: tabular-nums; color: {{ $machine['laba_mesin'] >= 0 ? '#047857' : '#be123c' }};">
                                            {{ $fmt($machine['laba_mesin']) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
