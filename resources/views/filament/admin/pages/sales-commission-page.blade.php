<x-filament-panels::page>
    <style>
        .sc-page {
            --sc-surface: #ffffff;
            --sc-surface-subtle: #f8fafc;
            --sc-border: #e5e7eb;
            --sc-border-subtle: #f1f5f9;
            --sc-text: #0f172a;
            --sc-text-secondary: #475569;
            --sc-text-muted: #64748b;
            --sc-hover: #f8fafc;
            --sc-chip-bg: #f1f5f9;
            --sc-chip-fg: #475569;
            --sc-amber: #b45309;
            --sc-rose: #e11d48;
            --sc-emerald: #059669;
            --sc-blue: #2563eb;
            --sc-dot-blue: #3b82f6;
            --sc-dot-amber: #f59e0b;
            --sc-dot-rose: #f43f5e;
            --sc-dot-emerald: #10b981;
            --sc-paid-bg: #ecfdf5;
            --sc-paid-fg: #047857;
            --sc-paid-dot: #10b981;
            --sc-pending-bg: #fffbeb;
            --sc-pending-fg: #b45309;
            --sc-pending-dot: #f59e0b;
            --sc-act-fg: #475569;
            --sc-act-hover-bg: #f1f5f9;
            --sc-act-hover-fg: #0f172a;

            display: flex;
            flex-direction: column;
            gap: 20px;
            color: var(--sc-text);
        }

        .dark .sc-page {
            --sc-surface: #18181b;
            --sc-surface-subtle: rgba(255, 255, 255, 0.025);
            --sc-border: rgba(255, 255, 255, 0.09);
            --sc-border-subtle: rgba(255, 255, 255, 0.06);
            --sc-text: #f8fafc;
            --sc-text-secondary: #cbd5e1;
            --sc-text-muted: #94a3b8;
            --sc-hover: rgba(255, 255, 255, 0.03);
            --sc-chip-bg: rgba(255, 255, 255, 0.07);
            --sc-chip-fg: #cbd5e1;
            --sc-amber: #fbbf24;
            --sc-rose: #fb7185;
            --sc-emerald: #34d399;
            --sc-blue: #60a5fa;
            --sc-paid-bg: rgba(16, 185, 129, 0.14);
            --sc-paid-fg: #34d399;
            --sc-paid-dot: #34d399;
            --sc-pending-bg: rgba(245, 158, 11, 0.14);
            --sc-pending-fg: #fbbf24;
            --sc-pending-dot: #fbbf24;
            --sc-act-fg: #94a3b8;
            --sc-act-hover-bg: rgba(255, 255, 255, 0.08);
            --sc-act-hover-fg: #f8fafc;
        }

        /* 4 Minimalist KPI Cards */
        .sc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        @media (max-width: 1100px) {
            .sc-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .sc-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .sc-kpi-card {
            background: var(--sc-surface);
            border: 1px solid var(--sc-border);
            border-radius: 12px;
            padding: 18px 20px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .sc-kpi-card:hover {
            border-color: rgba(148, 163, 184, 0.35);
            box-shadow: 0 4px 12px -4px rgba(15, 23, 42, 0.06);
        }

        .sc-kpi-top {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sc-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .sc-dot-blue { background: var(--sc-dot-blue); }
        .sc-dot-amber { background: var(--sc-dot-amber); }
        .sc-dot-rose { background: var(--sc-dot-rose); }
        .sc-dot-emerald { background: var(--sc-dot-emerald); }

        .sc-kpi-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--sc-text-muted);
            line-height: 1.2;
        }

        .sc-kpi-value {
            margin-top: 10px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.2;
            color: var(--sc-text);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .sc-kpi-value.is-amber { color: var(--sc-amber); }
        .sc-kpi-value.is-rose { color: var(--sc-rose); }
        .sc-kpi-value.is-emerald { color: var(--sc-emerald); }

        .sc-kpi-meta {
            margin-top: 6px;
            font-size: 11.5px;
            color: var(--sc-text-muted);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Minimalist Table Card */
        .sc-table-card {
            background: var(--sc-surface);
            border: 1px solid var(--sc-border);
            border-radius: 12px;
            overflow: hidden;
        }

        .sc-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid var(--sc-border-subtle);
        }

        .sc-card-head-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sc-card-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--sc-text);
        }

        .sc-count-pill {
            font-size: 11.5px;
            font-weight: 500;
            color: var(--sc-chip-fg);
            background: var(--sc-chip-bg);
            padding: 2px 9px;
            border-radius: 999px;
            font-variant-numeric: tabular-nums;
        }

        .sc-card-note {
            font-size: 12px;
            color: var(--sc-text-muted);
        }

        .sc-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table.sc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            font-variant-numeric: tabular-nums;
        }

        table.sc-table thead th {
            background: var(--sc-surface-subtle);
            color: var(--sc-text-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 11px 16px;
            border-bottom: 1px solid var(--sc-border);
            white-space: nowrap;
        }

        table.sc-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--sc-border-subtle);
            vertical-align: middle;
            white-space: nowrap;
            color: var(--sc-text);
        }

        table.sc-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.sc-table tbody tr:hover td {
            background: var(--sc-hover);
        }

        table.sc-table tfoot td {
            background: var(--sc-surface-subtle);
            color: var(--sc-text);
            font-weight: 600;
            font-size: 13px;
            padding: 14px 16px;
            border-top: 1px solid var(--sc-border);
            white-space: nowrap;
        }

        .sc-code {
            font-weight: 600;
            font-size: 12.5px;
            color: var(--sc-text);
            letter-spacing: -0.01em;
        }

        .sc-sub {
            margin-top: 2px;
            font-size: 11.5px;
            color: var(--sc-text-muted);
        }

        .sc-customer {
            font-weight: 600;
            color: var(--sc-text);
            line-height: 1.3;
        }

        .sc-item-tag {
            display: inline-block;
            margin-top: 4px;
            padding: 1px 7px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 500;
            background: var(--sc-chip-bg);
            color: var(--sc-chip-fg);
        }

        .sc-num {
            font-weight: 500;
            color: var(--sc-text);
        }

        .sc-num-amber {
            font-weight: 600;
            color: var(--sc-amber);
        }

        .sc-num-rose {
            font-weight: 600;
            color: var(--sc-rose);
        }

        .sc-num-emerald {
            font-weight: 600;
            color: var(--sc-emerald);
        }

        .sc-dash {
            color: var(--sc-text-muted);
            opacity: 0.65;
        }

        .sc-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .sc-status.is-paid {
            background: var(--sc-paid-bg);
            color: var(--sc-paid-fg);
        }

        .sc-status.is-pending {
            background: var(--sc-pending-bg);
            color: var(--sc-pending-fg);
        }

        .sc-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
        }

        .sc-status.is-paid .sc-status-dot { background: var(--sc-paid-dot); }
        .sc-status.is-pending .sc-status-dot { background: var(--sc-pending-dot); }

        .sc-actions {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .sc-act-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 8px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            color: var(--sc-act-fg);
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .sc-act-link:hover {
            background: var(--sc-act-hover-bg);
            color: var(--sc-act-hover-fg);
        }

        .sc-act-link svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .sc-act-link.is-pdf:hover { color: var(--sc-blue); }
        .sc-act-link.is-dl:hover { color: var(--sc-emerald); }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }
            *, .dark *, .dark {
                color: #0f172a !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }
            html, body, .fi-body, .fi-layout, .fi-main-ctn, .fi-main, .fi-page, main {
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }
            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .fi-header-actions,
            .fi-footer,
            aside,
            nav,
            .sc-hide-print {
                display: none !important;
            }
            .sc-kpi-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px !important;
            }
            .sc-kpi-card,
            .sc-table-card {
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid !important;
            }
            .sc-table-wrap {
                overflow: visible !important;
            }
            table.sc-table {
                font-size: 10px !important;
            }
            table.sc-table th,
            table.sc-table td {
                padding: 6px 8px !important;
            }
            thead {
                display: table-header-group !important;
            }
            tr {
                break-inside: avoid !important;
            }
        }
    </style>

    <div class="sc-page">
        <!-- 4 Minimalist Summary Metrics -->
        <div class="sc-kpi-grid">
            <div class="sc-kpi-card">
                <div class="sc-kpi-top">
                    <span class="sc-dot sc-dot-blue"></span>
                    <span class="sc-kpi-label">Total Revenue</span>
                </div>
                <div class="sc-kpi-value">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="sc-kpi-meta">
                    {{ count($commissionRows) }} order berkomisi aktif
                </div>
            </div>

            <div class="sc-kpi-card">
                <div class="sc-kpi-top">
                    <span class="sc-dot sc-dot-amber"></span>
                    <span class="sc-kpi-label">Adjustment (Titipan)</span>
                </div>
                <div class="sc-kpi-value is-amber">
                    Rp {{ number_format($totalAdj, 0, ',', '.') }}
                </div>
                <div class="sc-kpi-meta">
                    Rev. after adj · Rp {{ number_format($totalRevAfterAdj, 0, ',', '.') }}
                </div>
            </div>

            <div class="sc-kpi-card">
                <div class="sc-kpi-top">
                    <span class="sc-dot sc-dot-rose"></span>
                    <span class="sc-kpi-label">Komisi Marketing (2%)</span>
                </div>
                <div class="sc-kpi-value is-rose">
                    Rp {{ number_format($totalMarketingComm, 2, ',', '.') }}
                </div>
                <div class="sc-kpi-meta">
                    Beban Operasional · 601.002
                </div>
            </div>

            <div class="sc-kpi-card">
                <div class="sc-kpi-top">
                    <span class="sc-dot sc-dot-emerald"></span>
                    <span class="sc-kpi-label">Net Revenue</span>
                </div>
                <div class="sc-kpi-value is-emerald">
                    Rp {{ number_format($totalNetRevenue, 2, ',', '.') }}
                </div>
                <div class="sc-kpi-meta">
                    Bersih setelah adjustment &amp; komisi 2%
                </div>
            </div>
        </div>

        <!-- Minimalist Rekap Table -->
        <div class="sc-table-card">
            <div class="sc-card-head">
                <div class="sc-card-head-left">
                    <span class="sc-card-title">Daftar Komisi Order</span>
                    <span class="sc-count-pill">{{ count($commissionRows) }} order</span>
                </div>
                <span class="sc-card-note">Komisi 2% dihitung dari Revenue After Adjustment</span>
            </div>

            <div class="sc-table-wrap">
                <table class="sc-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">No. Komisi &amp; Order</th>
                            <th style="text-align: left;">Customer &amp; Item</th>
                            <th style="text-align: center;">Tanggal</th>
                            <th style="text-align: right;">Revenue</th>
                            <th style="text-align: right;">Adjustment</th>
                            <th style="text-align: right;">Rev. After Adj</th>
                            <th style="text-align: right;">Komisi (2%)</th>
                            <th style="text-align: right;">Net Revenue</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;" class="sc-hide-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissionRows as $row)
                            <tr>
                                <td>
                                    <div class="sc-code">{{ $row['comm_no'] }}</div>
                                    <div class="sc-sub">{{ $row['order_id'] }}</div>
                                </td>
                                <td style="white-space: normal; min-width: 195px;">
                                    <div class="sc-customer">{{ $row['customer'] }}</div>
                                    <span class="sc-item-tag">{{ $row['summary'] }}</span>
                                </td>
                                <td style="text-align: center; color: var(--sc-text-secondary);">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                                </td>
                                <td style="text-align: right;">
                                    <div class="sc-num">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</div>
                                    <div class="sc-sub">{{ number_format($row['qty'], 0, ',', '.') }} pcs</div>
                                </td>
                                <td style="text-align: right;">
                                    @if($row['total_adj'] > 0)
                                        <div class="sc-num-amber">Rp {{ number_format($row['total_adj'], 0, ',', '.') }}</div>
                                        <div class="sc-sub">@ Rp {{ number_format($row['adj_unit'], 0, ',', '.') }}/pcs</div>
                                    @else
                                        <span class="sc-dash">—</span>
                                    @endif
                                </td>
                                <td style="text-align: right;" class="sc-num">
                                    Rp {{ number_format($row['rev_af_adj'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right;" class="sc-num-rose">
                                    Rp {{ number_format($row['mkt_comm'], 2, ',', '.') }}
                                </td>
                                <td style="text-align: right;" class="sc-num-emerald">
                                    Rp {{ number_format($row['net_rev'], 2, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="sc-status {{ $row['payment_status'] === 'PAID' ? 'is-paid' : 'is-pending' }}">
                                        <span class="sc-status-dot"></span>
                                        {{ ucfirst(strtolower($row['payment_status'])) }}
                                    </span>
                                </td>
                                <td style="text-align: right;" class="sc-hide-print">
                                    <div class="sc-actions">
                                        @if(!empty($row['voucher_url']))
                                            <a href="{{ $row['voucher_url'] }}" target="_blank" class="sc-act-link is-pdf" title="Preview PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                                <span>PDF</span>
                                            </a>
                                        @endif
                                        @if(!empty($row['download_url']))
                                            <a href="{{ $row['download_url'] }}" target="_blank" class="sc-act-link is-dl" title="Download PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                <span>Unduh</span>
                                            </a>
                                        @endif
                                        <a href="{{ $row['edit_url'] }}" class="sc-act-link" title="Lihat Detail">
                                            <span>Detail</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: left; color: var(--sc-text-secondary);">
                                Total Keseluruhan ({{ count($commissionRows) }} order)
                            </td>
                            <td style="text-align: right;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                            <td style="text-align: right;" class="sc-num-amber">Rp {{ number_format($totalAdj, 0, ',', '.') }}</td>
                            <td style="text-align: right;">Rp {{ number_format($totalRevAfterAdj, 0, ',', '.') }}</td>
                            <td style="text-align: right;" class="sc-num-rose">Rp {{ number_format($totalMarketingComm, 2, ',', '.') }}</td>
                            <td style="text-align: right;" class="sc-num-emerald">Rp {{ number_format($totalNetRevenue, 2, ',', '.') }}</td>
                            <td colspan="2" class="sc-hide-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
