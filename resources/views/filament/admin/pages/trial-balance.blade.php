<x-filament-panels::page>
    @php
        $data = $this->trialBalanceData;
        $fmt = fn ($val, $dec = 2) => 'Rp ' . number_format((float) $val, $dec, ',', '.');
    @endphp

    <style>
        .ttc-tb-wrap {
            display: flex;
            flex-direction: column;
            gap: 22px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .dark .ttc-tb-wrap { color: #f1f5f9; }

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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        }
        .ttc-btn-amber { background: #f59e0b; color: #0f172a; }
        .ttc-btn-emerald { background: #10b981; color: #052e16; }

        @media print {
            .fi-sidebar, .fi-topbar, .ttc-finance-tabs-nav, .ttc-btn-group, .ttc-filter-bar, .ttc-preset-group, .fi-breadcrumbs {
                display: none !important;
            }
            .ttc-hero {
                background: #1e3a8a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ttc-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
            }
            body {
                background: #ffffff !important;
            }
        }

        .ttc-filter-bar {
            padding: 18px 26px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
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
        .ttc-input {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 500;
            min-width: 180px;
        }
        select.ttc-input {
            padding-right: 38px !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 16px 16px !important;
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
        .ttc-kpi-sub {
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
            white-space: nowrap;
        }

        .ttc-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        table.ttc-tb-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            font-variant-numeric: tabular-nums;
        }
        table.ttc-tb-table thead th {
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
        table.ttc-tb-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            white-space: nowrap;
        }
        table.ttc-tb-table tbody tr:hover td {
            background: #f8fafc;
        }
        table.ttc-tb-table tfoot td {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            font-size: 13px;
            padding: 14px 14px;
            border-top: 2px solid #94a3b8;
            white-space: nowrap;
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

    <div class="ttc-tb-wrap">
        @include('filament.admin.partials.finance-report-tabs')

        {{-- Hero & Filter Control Bar --}}
        <div class="ttc-card">
            <div class="ttc-hero">
                <div>
                    <div>
                        <span class="ttc-badge-amber">Summary Account — Indigama RSummaryAccount</span>
                        <span class="ttc-hero-sub">PT. Teknografi Tri Cawanaska</span>
                    </div>
                    <div class="ttc-hero-title">
                        Trial Balance (Neraca Saldo &amp; Rekapitulasi Mutasi COA)
                    </div>
                </div>
                <div class="ttc-btn-group">
                    <button type="button" onclick="window.print()" class="ttc-btn" style="background: #3b82f6; color: #ffffff; cursor: pointer;">
                        🖨️ Cetak / Simpan PDF
                    </button>
                    <a href="{{ url('/admin/coa-ledgers') }}" class="ttc-btn ttc-btn-amber">
                        General Ledger (Buku Besar)
                    </a>
                    <a href="{{ url('/admin/master-c-o-a-s') }}" class="ttc-btn ttc-btn-emerald">
                        Master COA
                    </a>
                </div>
            </div>

            <div class="ttc-filter-bar">
                <div class="ttc-filter-inputs">
                    <div class="ttc-field">
                        <label>Kelompok Akun COA</label>
                        <select wire:model.live="selectedGroup" class="ttc-input" style="min-width: 230px;">
                            <option value="">-- Semua Kelompok Akun --</option>
                            <option value="1">1 - Aset / Aktiva</option>
                            <option value="2">2 - Kewajiban / Utang</option>
                            <option value="3">3 - Ekuitas / Modal</option>
                            <option value="4">4 - Pendapatan Usaha</option>
                            <option value="5">5 - Harga Pokok Penjualan (HPP)</option>
                            <option value="6">6 - Beban Operasional</option>
                        </select>
                    </div>

                    <div class="ttc-field">
                        <label>Mode Tampilan Akun</label>
                        <select wire:model.live="onlyActiveAccounts" class="ttc-input" style="min-width: 250px;">
                            <option value="1">Hanya Akun Bermutasi (Aktif)</option>
                            <option value="0">Tampilkan Seluruh {{ $data['total_coa_db'] }} Akun COA</option>
                        </select>
                    </div>

                    <div class="ttc-field">
                        <label>Cari Kode / Nama Akun</label>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Ketik kode atau nama akun..." class="ttc-input" style="min-width: 220px;" />
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 KPI Cards --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-label">Total Mutasi Debit</div>
                <div class="ttc-kpi-value" style="color: #1d4ed8;">
                    {{ $fmt($data['grand_mut_debit'], 2) }}
                </div>
                <div class="ttc-kpi-sub">Akumulasi Debit Berjalan</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #7c3aed;">
                <div class="ttc-kpi-label">Total Mutasi Kredit</div>
                <div class="ttc-kpi-value" style="color: #6d28d9;">
                    {{ $fmt($data['grand_mut_kredit'], 2) }}
                </div>
                <div class="ttc-kpi-sub">Akumulasi Kredit Berjalan</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #059669;">
                <div class="ttc-kpi-label">Total Saldo Akhir (Debit = Kredit)</div>
                <div class="ttc-kpi-value" style="color: #059669;">
                    {{ $fmt($data['grand_end_debit'], 2) }}
                </div>
                <div class="ttc-kpi-sub">Status Neraca Saldo: {{ $data['is_balanced'] ? 'SEIMBANG (BALANCE)' : 'FILTER KELOMPOK' }}</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #d97706;">
                <div class="ttc-kpi-label">Jumlah Akun Ditampilkan</div>
                <div class="ttc-kpi-value" style="color: #b45309;">
                    {{ $data['shown_count'] }} Akun COA
                </div>
                <div class="ttc-kpi-sub">Dari Total {{ $data['total_coa_db'] }} Master COA</div>
            </div>
        </div>

        {{-- Tabel Trial Balance --}}
        <div class="ttc-card">
            <div class="ttc-table-wrap">
                <table class="ttc-tb-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Kode Akun</th>
                            <th style="text-align: left;">Nama Akun (Chart of Accounts)</th>
                            <th style="text-align: center;">Kelompok Akun</th>
                            <th style="text-align: center;">Laporan</th>
                            <th style="text-align: center;">Jml. Jurnal</th>
                            <th style="text-align: right;">Mutasi Debit (Rp)</th>
                            <th style="text-align: right;">Mutasi Kredit (Rp)</th>
                            <th style="text-align: right;">Saldo Akhir Debit</th>
                            <th style="text-align: right;">Saldo Akhir Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['rows'] as $row)
                            <tr>
                                <td>
                                    <span class="ttc-code-pill">{{ $row['kode_akun'] }}</span>
                                </td>
                                <td style="font-weight: 700; color: #0f172a;">
                                    {{ $row['nama_akun'] }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="padding: 3px 9px; border-radius: 6px; font-size: 10.5px; font-weight: 800; background: {{ $row['group_bg'] }}; color: {{ $row['group_fg'] }};">
                                        {{ $row['group_label'] }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span style="padding: 3px 9px; border-radius: 6px; font-size: 10.5px; font-weight: 800; background: {{ $row['stmt'] === 'NERACA' ? '#e0f2fe' : '#fef3c7' }}; color: {{ $row['stmt'] === 'NERACA' ? '#0369a1' : '#b45309' }};">
                                        {{ $row['stmt'] }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600; color: #475569;">
                                    {{ $row['tx_count'] }}x
                                </td>
                                <td style="text-align: right; font-weight: 600; color: {{ $row['mut_debit'] > 0 ? '#1d4ed8' : '#cbd5e1' }};">
                                    {{ $row['mut_debit'] > 0 ? $fmt($row['mut_debit'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: {{ $row['mut_kredit'] > 0 ? '#6d28d9' : '#cbd5e1' }};">
                                    {{ $row['mut_kredit'] > 0 ? $fmt($row['mut_kredit'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: {{ $row['end_debit'] > 0 ? '#059669' : '#cbd5e1' }};">
                                    {{ $row['end_debit'] > 0 ? $fmt($row['end_debit'], 2) : '-' }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: {{ $row['end_kredit'] > 0 ? '#dc2626' : '#cbd5e1' }};">
                                    {{ $row['end_kredit'] > 0 ? $fmt($row['end_kredit'], 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 26px; color: #64748b;">
                                    Tidak ada akun COA yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: left;">TOTAL NERACA SALDO ({{ $data['shown_count'] }} AKUN)</td>
                            <td style="text-align: right; color: #1d4ed8;">{{ $fmt($data['grand_mut_debit'], 2) }}</td>
                            <td style="text-align: right; color: #6d28d9;">{{ $fmt($data['grand_mut_kredit'], 2) }}</td>
                            <td style="text-align: right; color: #059669;">{{ $fmt($data['grand_end_debit'], 2) }}</td>
                            <td style="text-align: right; color: #dc2626;">{{ $fmt($data['grand_end_kredit'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

