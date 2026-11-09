<x-filament-panels::page>
    @php
        $data = $this->ledgerData;
        $fmt = fn ($val, $dec = 0) => 'Rp ' . number_format((float) $val, $dec, ',', '.');
    @endphp

    <style>
        .ttc-gl-wrap {
            display: flex;
            flex-direction: column;
            gap: 22px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .dark .ttc-gl-wrap { color: #f1f5f9; }

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
            gap: 10px;
            flex-wrap: wrap;
        }
        .ttc-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-decoration: none;
            white-space: nowrap;
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
            .ttc-gl-wrap {
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
            table.ttc-gl-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10px !important;
            }
            table.ttc-gl-table th, table.ttc-gl-table td {
                padding: 5px 6px !important;
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
            padding: 20px 26px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
        }
        .dark .ttc-filter-bar {
            background: #1f2937;
            border-top-color: #374151;
        }
        .ttc-filter-inputs {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 14px;
        }
        .ttc-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 6px;
        }
        .dark .ttc-field label { color: #9ca3af; }
        .ttc-input {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 500;
            min-width: 145px;
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
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .ttc-preset-btn:hover {
            background-color: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        .dark .ttc-preset-btn {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }
        .dark .ttc-preset-btn:hover {
            background-color: #334155;
            border-color: #60a5fa;
            color: #93c5fd;
        }
        .ttc-gl-account-name {
            font-weight: 700;
            color: #0f172a;
        }
        .dark .ttc-gl-account-name {
            color: #f8fafc;
        }
        .ttc-gl-date {
            font-weight: 600;
            color: #334155;
        }
        .dark .ttc-gl-date {
            color: #cbd5e1;
        }
        .ttc-gl-remarks {
            white-space: normal;
            min-width: 240px;
            max-width: 380px;
            color: #334155;
            line-height: 1.35;
        }
        .dark .ttc-gl-remarks {
            color: #cbd5e1;
        }
        .ttc-row-active-filter {
            background: #eff6ff;
        }
        .dark .ttc-row-active-filter {
            background: rgba(30, 58, 138, 0.3) !important;
        }
        .ttc-gl-filter-btn {
            padding: 5px 11px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            transition: all 0.15s ease;
        }
        .ttc-gl-filter-btn:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-color: #93c5fd;
        }
        .dark .ttc-gl-filter-btn {
            border-color: #374151;
            background-color: #1e293b;
            color: #e2e8f0;
        }
        .dark .ttc-gl-filter-btn:hover {
            background-color: #334155;
            color: #93c5fd;
            border-color: #60a5fa;
        }
        .ttc-gl-filter-btn-selected {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
            border-color: #1d4ed8 !important;
        }

        .ttc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
        }
        .ttc-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #2563eb;
            border-radius: 14px;
            padding: 18px 22px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .dark .ttc-kpi-card {
            background: #111827;
            border-color: #1f2937;
        }
        .ttc-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .ttc-kpi-value {
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 6px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .dark .ttc-kpi-value { color: #f8fafc; }
        .ttc-kpi-sub {
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
            white-space: nowrap;
        }

        .ttc-section-head {
            padding: 16px 24px;
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
            font-size: 13.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f172a;
        }
        .dark .ttc-section-title { color: #f8fafc; }
        .ttc-section-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .ttc-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        table.ttc-gl-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            font-variant-numeric: tabular-nums;
        }
        table.ttc-gl-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 14px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        .dark table.ttc-gl-table thead th {
            background: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        table.ttc-gl-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            white-space: nowrap;
        }
        .dark table.ttc-gl-table tbody td {
            border-bottom-color: #1f2937;
        }
        table.ttc-gl-table tbody tr:hover td {
            background: #f8fafc;
        }
        .dark table.ttc-gl-table tbody tr:hover td {
            background: rgba(30, 41, 59, 0.4);
        }
        table.ttc-gl-table tfoot td {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            font-size: 13px;
            padding: 14px 14px;
            border-top: 2px solid #94a3b8;
            white-space: nowrap;
        }
        .dark table.ttc-gl-table tfoot td {
            background: #1e293b;
            color: #f8fafc;
            border-top-color: #475569;
        }
        .ttc-code-pill {
            display: inline-block;
            background: #1e3a8a;
            color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
        }
    </style>

    <div class="ttc-gl-wrap">
        @include('filament.admin.partials.finance-report-tabs')

        {{-- Hero & Filter Control Card --}}
        <div class="ttc-card">
            <div class="ttc-hero">
                <div>
                    <div>
                        <span class="ttc-badge-amber">Buku Besar &amp; Jurnal Umum — Double-Entry Engine</span>
                        <span class="ttc-hero-sub">PT. Teknografi Tri Cawanaska</span>
                    </div>
                    <div class="ttc-hero-title">
                        General Ledger (Buku Besar Akun &amp; Mutasi Jurnal Umum)
                    </div>
                </div>
                <div class="ttc-btn-group">
                    <button type="button" onclick="window.print()" class="ttc-btn" style="background: #3b82f6; color: #ffffff; cursor: pointer;">
                        🖨️ Cetak / Simpan PDF
                    </button>
                    <a href="{{ url('/admin/jurnal-memorials/create') }}" class="ttc-btn ttc-btn-emerald">
                        + Input Jurnal Memorial
                    </a>
                    <a href="{{ url('/admin/report-finaces') }}" class="ttc-btn ttc-btn-amber">
                        Neraca Saldo
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
                        <label>Filter Akun Buku Besar (COA)</label>
                        <select wire:model.live="selectedCoaId" class="ttc-input" style="min-width: 240px;">
                            <option value="">-- Semua Akun COA ({{ $data['active_coa_count'] }} Akun) --</option>
                            @foreach($data['coa_options'] as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ttc-field">
                        <label>Sumber Transaksi</label>
                        <select wire:model.live="selectedRefType" class="ttc-input" style="min-width: 200px;">
                            <option value="">-- Semua Sumber --</option>
                            <option value="INVOICE">Invoice Penjualan</option>
                            <option value="INV_PAYMENT">Pembayaran Bon/Invoice</option>
                            <option value="OPERASIONAL_PAY">Operasional / Komisi Sales</option>
                            <option value="JOURNAL_MEMORIAL">Jurnal Memorial / HPP</option>
                            <option value="TRANS_KAS">Kas Masuk / Keluar</option>
                            <option value="TRANS_BANK">Bank Masuk / Keluar</option>
                        </select>
                    </div>

                    <div class="ttc-field">
                        <label>Cari Referensi / Uraian</label>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Contoh: INV-26, COM-26, Barbara..." class="ttc-input" style="min-width: 210px;" />
                    </div>
                </div>

                <div class="ttc-preset-group">
                    <button type="button" wire:click="setPreset('this_month')" class="ttc-preset-btn">Bulan Ini</button>
                    <button type="button" wire:click="setPreset('last_month')" class="ttc-preset-btn">Bulan Lalu</button>
                    <button type="button" wire:click="setPreset('this_year')" class="ttc-preset-btn">Tahun 2026</button>
                    @if($this->selectedCoaId !== '' || $this->selectedRefType !== '' || $this->searchQuery !== '')
                        <button type="button" wire:click="resetFilters" class="ttc-preset-btn" style="background-color: #fee2e2; color: #b91c1c; border-color: #fca5a5;">
                            Reset Filter
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- 4 KPI Summary Cards --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-label">Total Mutasi Debit</div>
                <div class="ttc-kpi-value" style="color: #1d4ed8;">
                    {{ $fmt($data['total_debit'], 2) }}
                </div>
                <div class="ttc-kpi-sub">{{ $data['debit_count'] }} Baris Ayat Jurnal Debit</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #7c3aed;">
                <div class="ttc-kpi-label">Total Mutasi Kredit</div>
                <div class="ttc-kpi-value" style="color: #6d28d9;">
                    {{ $fmt($data['total_kredit'], 2) }}
                </div>
                <div class="ttc-kpi-sub">{{ $data['kredit_count'] }} Baris Ayat Jurnal Kredit</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: {{ $data['is_balanced'] ? '#059669' : '#dc2626' }};">
                <div class="ttc-kpi-label">Status Double-Entry</div>
                <div class="ttc-kpi-value" style="color: {{ $data['is_balanced'] ? '#059669' : '#dc2626' }};">
                    {{ $data['is_balanced'] ? 'SEIMBANG (BALANCE)' : ($this->selectedCoaId !== '' ? 'FILTER PER AKUN' : 'SELISIH') }}
                </div>
                <div class="ttc-kpi-sub">
                    @if($this->selectedCoaId !== '')
                        Menampilkan mutasi 1 akun spesifik
                    @else
                        Selisih Debit vs Kredit: {{ $fmt($data['selisih'], 2) }}
                    @endif
                </div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #d97706;">
                <div class="ttc-kpi-label">Total Ayat Jurnal &amp; Akun</div>
                <div class="ttc-kpi-value" style="color: #b45309;">
                    {{ number_format($data['total_rows'], 0, ',', '.') }} Baris Mutasi
                </div>
                <div class="ttc-kpi-sub">Melibatkan {{ $data['active_coa_count'] }} Akun COA Aktif</div>
            </div>
        </div>

        {{-- Ringkasan Buku Besar per Akun COA (Klik untuk Filter) --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">1. Ringkasan Buku Besar per Akun COA Aktif</div>
                    <div class="ttc-section-desc">Klik tombol <strong>Filter Buku Besar</strong> pada salah satu akun untuk melihat rincian mutasi khusus akun tersebut di bawah.</div>
                </div>
                @if($this->selectedCoaId !== '')
                    <button type="button" wire:click="resetFilters" class="ttc-preset-btn" style="background-color: #eff6ff; color: #1d4ed8; border-color: #93c5fd;">
                        Tampilkan Semua Akun Kembali
                    </button>
                @endif
            </div>
            <div class="ttc-table-wrap">
                <table class="ttc-gl-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Kode Akun</th>
                            <th style="text-align: left;">Nama Akun Buku Besar (COA)</th>
                            <th style="text-align: center;">Saldo Normal</th>
                            <th style="text-align: center;">Jml. Mutasi</th>
                            <th style="text-align: right;">Total Mutasi Debit</th>
                            <th style="text-align: right;">Total Mutasi Kredit</th>
                            <th style="text-align: right;">Saldo Akhir Akun</th>
                            <th style="text-align: center;" class="ttc-hide-print">Aksi Filter</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['account_summaries'] as $acc)
                            @php
                                $isSelected = (string) $this->selectedCoaId === (string) $acc['coa_id'];
                            @endphp
                            <tr class="{{ $isSelected ? 'ttc-row-active-filter' : '' }}">
                                <td>
                                    <span class="ttc-code-pill">{{ $acc['kode_akun'] }}</span>
                                </td>
                                <td class="ttc-gl-account-name">
                                    {{ $acc['nama_akun'] }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="padding: 3px 9px; border-radius: 6px; font-size: 10.5px; font-weight: 800; background: {{ $acc['posisi'] === 'DEBIT' ? '#dbeafe' : '#f3e8ff' }}; color: {{ $acc['posisi'] === 'DEBIT' ? '#1e40af' : '#6b21a8' }};">
                                        {{ $acc['posisi'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #475569;">
                                    {{ $acc['count'] }}x
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #1d4ed8;">
                                    {{ $fmt($acc['debit'], 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #6d28d9;">
                                    {{ $fmt($acc['kredit'], 2) }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #059669;">
                                    {{ $fmt($acc['saldo_akhir'], 2) }}
                                </td>
                                <td style="text-align: center;" class="ttc-hide-print">
                                    <button type="button"
                                            wire:click="filterByAccount({{ $acc['coa_id'] }})"
                                            class="ttc-gl-filter-btn {{ $isSelected ? 'ttc-gl-filter-btn-selected' : '' }}">
                                        {{ $isSelected ? '✓ Sedang Difilter' : 'Lihat Mutasi Akun' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabel Detail Mutasi Jurnal Umum & Buku Besar --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">2. Rincian Ayat Jurnal Umum &amp; Mutasi Buku Besar (Kronologis)</div>
                    <div class="ttc-section-desc">Seluruh pencatatan akuntansi berpasangan (Double-Entry) dari Invoice Penjualan, Pembayaran DP/Pelunasan, Beban HPP (85%), dan Komisi Sales (2%).</div>
                </div>
                <div style="font-size: 12px; font-weight: 700; color: #1e40af;">
                    Menampilkan {{ count($data['rows']) }} Baris Jurnal
                </div>
            </div>
            <div class="ttc-table-wrap">
                <table class="ttc-gl-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 48px;">No</th>
                            <th style="text-align: center;">Tanggal</th>
                            <th style="text-align: left;">Kode &amp; Nama Akun COA</th>
                            <th style="text-align: center;">Sumber Transaksi</th>
                            <th style="text-align: left;">No. Referensi</th>
                            <th style="text-align: left;">Keterangan / Uraian Transaksi</th>
                            <th style="text-align: right;">Debit (Rp)</th>
                            <th style="text-align: right;">Kredit (Rp)</th>
                            <th style="text-align: right;">Saldo Berjalan Akun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['rows'] as $idx => $row)
                            <tr>
                                <td style="text-align: center; color: #64748b; font-weight: 600;">
                                    {{ $idx + 1 }}
                                </td>
                                <td style="text-align: center;" class="ttc-gl-date">
                                    {{ $row['tanggal'] }}
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span class="ttc-code-pill">{{ $row['kode_akun'] }}</span>
                                        <span class="ttc-gl-account-name">{{ $row['nama_akun'] }}</span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 3px 9px; border-radius: 6px; font-size: 10.5px; font-weight: 800; background: {{ $row['ref_bg'] }}; color: {{ $row['ref_fg'] }};">
                                        {{ $row['ref_label'] }}
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: #3b82f6;">
                                    {{ $row['reference_no'] }}
                                </td>
                                <td class="ttc-gl-remarks">
                                    {{ $row['remarks'] }}
                                </td>
                                <td style="text-align: right; font-weight: {{ $row['debit'] > 0 ? '800' : '500' }}; color: {{ $row['debit'] > 0 ? '#1d4ed8' : '#cbd5e1' }};">
                                    {{ $row['debit'] > 0 ? $fmt($row['debit'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: {{ $row['kredit'] > 0 ? '800' : '500' }}; color: {{ $row['kredit'] > 0 ? '#6d28d9' : '#cbd5e1' }};">
                                    {{ $row['kredit'] > 0 ? $fmt($row['kredit'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #059669;">
                                    {{ $fmt($row['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 28px; color: #64748b;">
                                    Tidak ada mutasi jurnal yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align: left;">TOTAL MUTASI JURNAL UMUM ({{ count($data['rows']) }} BARIS)</td>
                            <td style="text-align: right; color: #1d4ed8;">{{ $fmt($data['total_debit'], 2) }}</td>
                            <td style="text-align: right; color: #6d28d9;">{{ $fmt($data['total_kredit'], 2) }}</td>
                            <td style="text-align: right; color: #059669;">
                                {{ $data['is_balanced'] ? '✓ BALANCE' : 'FILTER AKUN' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

