<x-filament-panels::page>
    <style>
        .ttc-comm-wrap {
            display: flex;
            flex-direction: column;
            gap: 22px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .dark .ttc-comm-wrap { color: #f1f5f9; }

        .ttc-comm-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .dark .ttc-comm-card {
            background: #111827;
            border-color: #1f2937;
        }

        .ttc-comm-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1e1b4b 100%);
            color: #ffffff;
            padding: 20px 26px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .ttc-comm-badge {
            display: inline-block;
            background: #f59e0b;
            color: #0f172a;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 12px;
            border-radius: 999px;
        }
        .ttc-comm-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin-top: 8px;
        }
        .ttc-comm-sub {
            font-size: 12.5px;
            color: #cbd5e1;
            margin-top: 4px;
        }

        .ttc-comm-btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ttc-comm-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
        }
        .ttc-comm-btn-amber { background: #f59e0b; color: #0f172a; }
        .ttc-comm-btn-amber:hover { background: #fbbf24; }
        .ttc-comm-btn-emerald { background: #10b981; color: #052e16; }
        .ttc-comm-btn-emerald:hover { background: #34d399; }

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
            .ttc-comm-btn-group, 
            .ttc-comm-filter, 
            .ttc-hide-print {
                display: none !important;
            }
            .ttc-comm-wrap {
                display: block !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .ttc-comm-card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                overflow: visible !important;
                display: block !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
                margin-bottom: 12px !important;
            }
            .ttc-comm-hero {
                background: #1e3a8a !important;
                color: #ffffff !important;
                padding: 12px 16px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .ttc-comm-hero-title {
                color: #ffffff !important;
                font-size: 15px !important;
            }
            .ttc-comm-kpi-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px !important;
            }
            .ttc-comm-kpi {
                padding: 8px 10px !important;
                border: 1px solid #cbd5e1 !important;
                break-inside: avoid !important;
            }
            .ttc-comm-table-wrap {
                overflow: visible !important;
                display: block !important;
                padding: 8px 12px !important;
            }
            table.ttc-comm-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10px !important;
            }
            table.ttc-comm-table th, table.ttc-comm-table td {
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

        .ttc-comm-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .ttc-comm-kpi {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #2563eb;
            border-radius: 14px;
            padding: 16px 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .dark .ttc-comm-kpi {
            background: #111827;
            border-color: #1f2937;
        }
        .ttc-comm-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .ttc-comm-kpi-val {
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 6px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .dark .ttc-comm-kpi-val { color: #f8fafc; }
        .ttc-comm-kpi-note {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 4px;
            white-space: nowrap;
        }

        .ttc-comm-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        table.ttc-comm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            font-variant-numeric: tabular-nums;
        }
        table.ttc-comm-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 12px 12px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        .dark table.ttc-comm-table thead th {
            background: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        table.ttc-comm-table tbody td {
            padding: 12px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            white-space: nowrap;
        }
        .dark table.ttc-comm-table tbody td {
            border-bottom-color: #1f2937;
        }
        table.ttc-comm-table tbody tr:hover td {
            background: #f8fafc;
        }
        .dark table.ttc-comm-table tbody tr:hover td {
            background: rgba(30, 41, 59, 0.4);
        }
        table.ttc-comm-table tfoot td {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 12.5px;
            padding: 14px 12px;
            border-top: 2px solid #94a3b8;
            white-space: nowrap;
        }
        .dark table.ttc-comm-table tfoot td {
            background: #1e293b;
            color: #f8fafc;
            border-top-color: #475569;
        }
        .ttc-act-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: opacity 0.15s;
        }
        .ttc-act-btn:hover { opacity: 0.88; }
    </style>

    <div class="ttc-comm-wrap">
        <!-- Hero Banner -->
        <div class="ttc-comm-card">
            <div class="ttc-comm-hero">
                <div>
                    <div>
                        <span class="ttc-comm-badge">Sales &amp; Marketing Commission — Bismillah Macro</span>
                    </div>
                    <div class="ttc-comm-title">
                        Tabel Perhitungan Adjustment Harga, Komisi Marketing (2%), &amp; Voucher Commission Request
                    </div>
                    <div class="ttc-comm-sub">
                        Terintegrasi otomatis dengan jurnal pengeluaran pada menu <strong>Finance &rarr; Operasional Keluar</strong> (Akun <code>6101 - Beban Bagian Penjualan / Komisi</code>).
                    </div>
                </div>
                <div class="ttc-comm-btn-group">
                    <a href="{{ url('/admin/operasional-pays') }}" class="ttc-comm-btn ttc-comm-btn-amber">
                        Buka Menu Operasional Keluar
                    </a>
                    <a href="{{ url('/admin/operasional-pays/create') }}" class="ttc-comm-btn ttc-comm-btn-emerald">
                        + Input Pembayaran Komisi Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 KPI Cards -->
        <div class="ttc-comm-kpi-grid">
            <div class="ttc-comm-kpi" style="border-left-color: #2563eb;">
                <div class="ttc-comm-kpi-label">Total Revenue (Gross)</div>
                <div class="ttc-comm-kpi-val">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="ttc-comm-kpi-note">{{ count($commissionRows) }} Order Berkomisi Aktif</div>
            </div>

            <div class="ttc-comm-kpi" style="border-left-color: #d97706;">
                <div class="ttc-comm-kpi-label">Total Adjustment (Titipan/Unit)</div>
                <div class="ttc-comm-kpi-val" style="color: #b45309;">
                    Rp {{ number_format($totalAdj, 0, ',', '.') }}
                </div>
                <div class="ttc-comm-kpi-note">Rev. After Adj: Rp {{ number_format($totalRevAfterAdj, 0, ',', '.') }}</div>
            </div>

            <div class="ttc-comm-kpi" style="border-left-color: #dc2626;">
                <div class="ttc-comm-kpi-label">Total Marketing Comm (2%)</div>
                <div class="ttc-comm-kpi-val" style="color: #dc2626;">
                    Rp {{ number_format($totalMarketingComm, 2, ',', '.') }}
                </div>
                <div class="ttc-comm-kpi-note">Tercatat di Beban Operasional Laba Rugi</div>
            </div>

            <div class="ttc-comm-kpi" style="border-left-color: #059669;">
                <div class="ttc-comm-kpi-label">Net Revenue (Bersih TTC)</div>
                <div class="ttc-comm-kpi-val" style="color: #059669;">
                    Rp {{ number_format($totalNetRevenue, 2, ',', '.') }}
                </div>
                <div class="ttc-comm-kpi-note">Setelah Dikurangi Adjustment &amp; Komisi 2%</div>
            </div>
        </div>

        <!-- Tabel Rekap Commission -->
        <div class="ttc-comm-card">
            <div class="ttc-comm-table-wrap">
                <table class="ttc-comm-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">No. Komisi &amp; Order ID</th>
                            <th style="text-align: left;">Customer &amp; Order Summary</th>
                            <th style="text-align: center;">Tgl. Order</th>
                            <th style="text-align: right;">Revenue &amp; Qty</th>
                            <th style="text-align: right;">Adjustment (Titipan)</th>
                            <th style="text-align: right;">Rev. After Adj</th>
                            <th style="text-align: right;">Sales Comm (2%)</th>
                            <th style="text-align: right;">Net Revenue</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Cetak Form / Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissionRows as $row)
                            <tr>
                                <td>
                                    <div style="font-weight: 800; color: #1e40af; font-size: 12.5px;">
                                        {{ $row['comm_no'] }}
                                    </div>
                                    <div style="font-size: 11px; font-weight: 600; color: #475569; margin-top: 2px;">
                                        Order: {{ $row['order_id'] }}
                                    </div>
                                </td>
                                <td style="white-space: normal; min-width: 190px;">
                                    <div style="font-weight: 700; color: #0f172a; line-height: 1.3;">
                                        {{ $row['customer'] }}
                                    </div>
                                    <div style="display: inline-block; margin-top: 3px; padding: 2px 8px; border-radius: 5px; background: #eff6ff; color: #1d4ed8; font-size: 10.5px; font-weight: 700;">
                                        {{ $row['summary'] }}
                                    </div>
                                </td>
                                <td style="text-align: center; color: #475569; font-weight: 600;">
                                    {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 700; color: #0f172a;">
                                        Rp {{ number_format($row['revenue'], 0, ',', '.') }}
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        Qty: {{ number_format($row['qty'], 0, ',', '.') }} Pcs
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    @if($row['total_adj'] > 0)
                                        <div style="font-weight: 700; color: #b45309;">
                                            Rp {{ number_format($row['total_adj'], 0, ',', '.') }}
                                        </div>
                                        <div style="font-size: 10.5px; color: #92400e; margin-top: 2px;">
                                            {{ $row['adj_type'] }} (@ Rp {{ number_format($row['adj_unit'], 0, ',', '.') }})
                                        </div>
                                    @else
                                        <div style="font-weight: 600; color: #94a3b8;">Rp 0</div>
                                        <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">NONE</div>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #1e40af;">
                                    Rp {{ number_format($row['rev_af_adj'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #dc2626;">
                                    Rp {{ number_format($row['mkt_comm'], 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #059669;">
                                    Rp {{ number_format($row['net_rev'], 2, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 800; background: {{ $row['payment_status'] === 'PAID' ? '#dcfce7' : '#fef3c7' }}; color: {{ $row['payment_status'] === 'PAID' ? '#15803d' : '#b45309' }};">
                                        {{ $row['payment_status'] }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 5px;">
                                        @if(!empty($row['voucher_url']))
                                            <a href="{{ $row['voucher_url'] }}" target="_blank" class="ttc-act-btn" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                                                Preview PDF
                                            </a>
                                        @endif
                                        @if(!empty($row['download_url']))
                                            <a href="{{ $row['download_url'] }}" target="_blank" class="ttc-act-btn" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                                Download
                                            </a>
                                        @endif
                                        <a href="{{ $row['edit_url'] }}" class="ttc-act-btn" style="background: #f8fafc; color: #334155; border: 1px solid #cbd5e1;">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: left;">TOTAL KESELURUHAN COMMISSION ({{ count($commissionRows) }} ORDER)</td>
                            <td style="text-align: right;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                            <td style="text-align: right; color: #b45309;">Rp {{ number_format($totalAdj, 0, ',', '.') }}</td>
                            <td style="text-align: right; color: #1e40af;">Rp {{ number_format($totalRevAfterAdj, 0, ',', '.') }}</td>
                            <td style="text-align: right; color: #dc2626;">Rp {{ number_format($totalMarketingComm, 2, ',', '.') }}</td>
                            <td style="text-align: right; color: #059669;">Rp {{ number_format($totalNetRevenue, 2, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
