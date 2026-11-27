<x-filament-panels::page>
    @php
        $data = $this->reportData;
        $fmt = fn ($val, $dec = 2) => 'Rp ' . number_format((float) $val, $dec, ',', '.');
        $fmt0 = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
    @endphp

    <style>
        .ttc-hp-wrap {
            display: flex;
            flex-direction: column;
            gap: 22px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }
        .dark .ttc-hp-wrap { color: #f1f5f9; }

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
            background: linear-gradient(135deg, #0f172a 0%, #881337 55%, #1e1b4b 100%);
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
            color: #fecdd3;
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
            padding: 9px 15px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.14);
        }
        .ttc-btn-blue { background: #2563eb; color: #ffffff; }
        .ttc-btn-emerald { background: #10b981; color: #052e16; }
        .ttc-btn-indigo { background: #4f46e5; color: #ffffff; }
        .ttc-btn-amber { background: #f59e0b; color: #0f172a; }

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
            font-weight: 600;
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
            text-decoration: none;
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

        .ttc-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }
        .ttc-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #e11d48;
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
        table.ttc-hp-table {
            width: 100%;
            min-width: 920px;
            border-collapse: collapse;
            font-size: 12.5px;
            font-variant-numeric: tabular-nums;
        }
        table.ttc-hp-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 11px 14px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        .dark table.ttc-hp-table thead th {
            background: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        table.ttc-hp-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .dark table.ttc-hp-table tbody td {
            border-bottom-color: #1f2937;
        }
        table.ttc-hp-table tfoot td {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 800;
            font-size: 13px;
            padding: 13px 14px;
            border-top: 2px solid #94a3b8;
        }
        .dark table.ttc-hp-table tfoot td {
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
        .ttc-code-pill-rose {
            background: #be123c;
        }
        .ttc-pill-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 800;
        }
        .ttc-alert-box {
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #f59e0b;
            background: rgba(245, 158, 11, 0.12);
            font-size: 12.5px;
            line-height: 1.55;
        }
        .ttc-info-box {
            margin: 14px 22px 6px 22px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid #3b82f6;
            background: rgba(59, 130, 246, 0.10);
            font-size: 12px;
            line-height: 1.5;
        }
    </style>

    <div class="ttc-hp-wrap">
        @include('filament.admin.partials.finance-report-tabs')

        {{-- Hero & Filter Control --}}
        <div class="ttc-card">
            <div class="ttc-hero">
                <div>
                    <div>
                        <span class="ttc-badge-amber">Kartu Hutang, Piutang &amp; Pinjaman Indigama</span>
                        <span class="ttc-hero-sub">PT. Teknografi Tri Cawanaska</span>
                    </div>
                    <div class="ttc-hero-title">
                        Laporan Hutang, Piutang &amp; Pinjaman Pihak Berelasi (AR / AP / Loan)
                    </div>
                </div>
                <div class="ttc-btn-group">
                    <a href="{{ route('finance.report.hutang-piutang.preview', ['as_of_date' => $this->asOfDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-blue">
                        🖨️ Preview PDF
                    </a>
                    <a href="{{ route('finance.report.hutang-piutang.download', ['as_of_date' => $this->asOfDate]) }}"
                       target="_blank"
                       class="ttc-btn ttc-btn-emerald">
                        ⬇️ Simpan PDF
                    </a>
                    <a href="{{ url('/admin/jurnal-memorials/create') }}" class="ttc-btn ttc-btn-indigo">
                        + Catat Pinjaman Non-Kas
                    </a>
                    <a href="{{ url('/admin/trans-kas-banks/create') }}" class="ttc-btn ttc-btn-amber">
                        + Bayar Cicilan Bank
                    </a>
                </div>
            </div>

            <div class="ttc-filter-bar">
                <div class="ttc-field">
                    <label>Posisi Per Tanggal (Cut-Off Date)</label>
                    <input type="date" wire:model.live="asOfDate" class="ttc-input" />
                </div>
                <div class="ttc-preset-group">
                    <span style="font-size: 12px; font-weight: 700; color: #64748b;">Posisi Cepat:</span>
                    <button type="button" wire:click="setPresetDate('today')" class="ttc-preset-btn">Hari Ini</button>
                    <button type="button" wire:click="setPresetDate('end_of_month')" class="ttc-preset-btn">Akhir Bulan Ini</button>
                    <button type="button" wire:click="setPresetDate('last_month')" class="ttc-preset-btn">Akhir Bulan Lalu</button>
                    <button type="button" wire:click="setPresetDate('end_of_year')" class="ttc-preset-btn">Akhir Tahun 2026</button>
                </div>
            </div>
        </div>

        {{-- 4 KPI Cards --}}
        <div class="ttc-kpi-grid">
            <div class="ttc-kpi-card" style="border-left-color: #e11d48;">
                <div class="ttc-kpi-label">Pinjaman ke PT Indigama / Relasi</div>
                <div class="ttc-kpi-value" style="color: #e11d48;">{{ $fmt($data['total_pinjaman']) }}</div>
                <div class="ttc-kpi-sub">{{ count($data['pinjaman_ledger_rows']) }} transaksi non-kas &amp; cicilan (Akun 214x / 22xx)</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #ea580c;">
                <div class="ttc-kpi-label">Total Kewajiban Buku Besar (Netto)</div>
                <div class="ttc-kpi-value" style="color: #ea580c;">{{ $fmt($data['total_kewajiban_ledger']) }}</div>
                <div class="ttc-kpi-sub">Pinjaman + Utang Usaha / Operasional ({{ $fmt0($data['total_hutang_ops']) }})</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #059669;">
                <div class="ttc-kpi-label">Total Piutang Buku Besar</div>
                <div class="ttc-kpi-value" style="color: #059669;">{{ $fmt($data['total_piutang_ledger']) }}</div>
                <div class="ttc-kpi-sub">Piutang Usaha (112x) &amp; Piutang Karyawan / Kasbon (1131)</div>
            </div>

            <div class="ttc-kpi-card" style="border-left-color: #2563eb;">
                <div class="ttc-kpi-label">Monitoring Tagihan Operasional</div>
                <div class="ttc-kpi-value" style="color: #2563eb;">{{ $fmt0($data['total_open_invoice_ar']) }}</div>
                <div class="ttc-kpi-sub">{{ count($data['open_invoices']) }} Invoice AR belum lunas · {{ count($data['open_po_billings']) }} PO Billing AP ({{ $fmt0($data['total_open_po_ap']) }})</div>
            </div>
        </div>

        {{-- Alert Anomali jika ada akun Hutang bersaldo minus --}}
        @if(! empty($data['anomalies']))
            <div class="ttc-alert-box">
                <strong style="color: #d97706;">⚠️ Catatan Audit Akun Hutang Bersaldo Minus:</strong>
                <ul style="margin: 6px 0 0 18px; padding: 0;">
                    @foreach($data['anomalies'] as $an)
                        <li><strong>{{ $an['kode_akun'] }} — {{ $an['nama_akun'] }}</strong> ({{ $fmt($an['saldo']) }}): {{ $an['pesan'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SECTION 1: KARTU PINJAMAN KE PT INDIGAMA & PIHAK BERELASI --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">1. Kartu Pinjaman ke PT Indigama &amp; Pihak Berelasi (Akun 214x &amp; 22xx)</div>
                    <div class="ttc-section-desc">Pencatatan talangan/pembayaran langsung oleh PT Indigama yang tidak keluar dari rekening Bank PT TTC (dicatat melalui Jurnal Memorial non-kas).</div>
                </div>
                <span class="ttc-pill-badge" style="background: #ffe4e6; color: #be123c;">
                    Sisa Pinjaman: {{ $fmt($data['total_pinjaman']) }}
                </span>
            </div>

            <div class="ttc-info-box">
                <strong>Alur Pencatatan Pinjaman PT Indigama:</strong>
                (1) Saat PT Indigama menalangi pembelian aset/biaya (tidak lewat rekening PT TTC), input di <strong>Finance → Jurnal Memorial</strong>: <em>Debit Aset/Biaya</em> ↔ <em>Kredit 2144 Pinjaman / Hutang ke PT Indigama</em>.
                (2) Saat PT TTC mencicil/melunasi dari rekening BRI/BCA, input di <strong>Finance → Transaksi Bank (Bank Keluar)</strong> dengan akun lawan <em>2144 Pinjaman / Hutang ke PT Indigama</em> — sisa pinjaman otomatis berkurang.
            </div>

            <div class="ttc-table-wrap">
                <table class="ttc-hp-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Kode COA</th>
                            <th style="text-align: left;">Nama Akun Pinjaman</th>
                            <th style="text-align: center;">Jml. Transaksi</th>
                            <th style="text-align: right;">Total Pinjaman (Kredit)</th>
                            <th style="text-align: right;">Total Dibayar / Cicilan (Debit)</th>
                            <th style="text-align: right;">Sisa Pinjaman Belum Dibayar</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['pinjaman_items'] as $item)
                            <tr>
                                <td><span class="ttc-code-pill ttc-code-pill-rose">{{ $item['kode_akun'] }}</span></td>
                                <td style="font-weight: 700;">{{ $item['nama_akun'] }}</td>
                                <td style="text-align: center; font-weight: 600;">{{ $item['tx_count'] }}x</td>
                                <td style="text-align: right; font-weight: 700; color: #be123c;">{{ $fmt($item['total_pinjam']) }}</td>
                                <td style="text-align: right; font-weight: 700; color: #059669;">{{ $fmt($item['total_bayar']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: #e11d48;">{{ $fmt($item['sisa_hutang']) }}</td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="{{ url('/admin/coa-ledgers?coa_id=' . $item['coa_id']) }}" class="ttc-preset-btn" style="padding: 4px 10px; font-size: 11px;">Buku Besar</a>
                                    <a href="{{ route('finance.report.general-ledger.preview', ['start_date' => '2025-01-01', 'end_date' => $this->asOfDate, 'coa_id' => $item['coa_id']]) }}" target="_blank" class="ttc-preset-btn" style="padding: 4px 10px; font-size: 11px;">🖨️ PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">Belum ada mutasi pinjaman pihak berelasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Rincian Kronologis Pinjaman PT Indigama --}}
            @if(! empty($data['pinjaman_ledger_rows']))
                <div style="padding: 12px 24px 6px 24px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; border-top: 1px solid #e2e8f0;">
                    Rincian Histori Transaksi Pinjaman ({{ count($data['pinjaman_ledger_rows']) }} Baris)
                </div>
                <div class="ttc-table-wrap">
                    <table class="ttc-hp-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 44px;">No</th>
                                <th style="text-align: center; width: 95px;">Tanggal</th>
                                <th style="text-align: left; width: 125px;">No. Referensi</th>
                                <th style="text-align: center; width: 155px;">Sumber</th>
                                <th style="text-align: left;">Keterangan / Uraian Pinjaman</th>
                                <th style="text-align: right; width: 145px;">Pinjaman Masuk (Rp)</th>
                                <th style="text-align: right; width: 145px;">Cicilan / Bayar (Rp)</th>
                                <th style="text-align: right; width: 155px;">Sisa Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['pinjaman_ledger_rows'] as $i => $row)
                                <tr>
                                    <td style="text-align: center; color: #64748b;">{{ $i + 1 }}</td>
                                    <td style="text-align: center; font-weight: 600;">{{ $row['tanggal'] }}</td>
                                    <td style="font-weight: 700; color: #3b82f6;">{{ $row['reference_no'] }}</td>
                                    <td style="text-align: center;">
                                        <span class="ttc-pill-badge" style="background: #f3e8ff; color: #6b21a8;">{{ $row['ref_label'] }}</span>
                                    </td>
                                    <td>{{ $row['remarks'] }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #be123c;">{{ $row['tambah_hutang'] > 0 ? $fmt($row['tambah_hutang']) : '-' }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #059669;">{{ $row['bayar_hutang'] > 0 ? $fmt($row['bayar_hutang']) : '-' }}</td>
                                    <td style="text-align: right; font-weight: 800; color: #e11d48;">{{ $fmt($row['saldo_berjalan']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">TOTAL PINJAMAN PT INDIGAMA &amp; PIHAK BERELASI</td>
                                <td style="text-align: right; color: #be123c;">{{ $fmt(collect($data['pinjaman_ledger_rows'])->sum('tambah_hutang')) }}</td>
                                <td style="text-align: right; color: #059669;">{{ $fmt(collect($data['pinjaman_ledger_rows'])->sum('bayar_hutang')) }}</td>
                                <td style="text-align: right; color: #e11d48;">{{ $fmt($data['total_pinjaman']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        {{-- SECTION 2: UTANG USAHA, PAJAK & KEWAJIBAN OPERASIONAL --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">2. Utang Usaha Supplier, Pajak &amp; Biaya Terutang (Akun 211x, 212x, 213x, 215x)</div>
                    <div class="ttc-section-desc">Kewajiban operasional jangka pendek di luar pinjaman PT Indigama.</div>
                </div>
                <span class="ttc-pill-badge" style="background: #ffedd5; color: #c2410c;">
                    Total Netto: {{ $fmt($data['total_hutang_ops']) }}
                </span>
            </div>
            <div class="ttc-table-wrap">
                <table class="ttc-hp-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Kode COA</th>
                            <th style="text-align: left;">Nama Akun Kewajiban</th>
                            <th style="text-align: left;">Kategori</th>
                            <th style="text-align: center;">Mutasi</th>
                            <th style="text-align: right;">Hutang Timbul (Kredit)</th>
                            <th style="text-align: right;">Pembayaran (Debit)</th>
                            <th style="text-align: right;">Saldo Akhir</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['hutang_ops_items'] as $item)
                            <tr>
                                <td><span class="ttc-code-pill">{{ $item['kode_akun'] }}</span></td>
                                <td style="font-weight: 700;">{{ $item['nama_akun'] }}</td>
                                <td>{{ $item['kategori'] }}</td>
                                <td style="text-align: center;">{{ $item['tx_count'] }}x</td>
                                <td style="text-align: right;">{{ $fmt($item['total_hutang']) }}</td>
                                <td style="text-align: right;">{{ $fmt($item['total_bayar']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: {{ $item['is_anomaly'] ? '#dc2626' : '#ea580c' }};">
                                    {{ $fmt($item['sisa_hutang']) }}
                                    @if($item['is_anomaly']) ⚠️ @endif
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="{{ url('/admin/coa-ledgers?coa_id=' . $item['coa_id']) }}" class="ttc-preset-btn" style="padding: 4px 10px; font-size: 11px;">Buku Besar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">Tidak ada saldo utang usaha / operasional pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION 3: KARTU PIUTANG BUKU BESAR (112x & 1131) --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">3. Kartu Piutang Usaha &amp; Piutang Karyawan / Kasbon (Akun 112x &amp; 113x)</div>
                    <div class="ttc-section-desc">Piutang resmi yang tercatat pada Buku Besar (COA 112x Piutang Customer &amp; 1131 Piutang/Kasbon Karyawan).</div>
                </div>
                <span class="ttc-pill-badge" style="background: #dcfce7; color: #15803d;">
                    Total Piutang: {{ $fmt($data['total_piutang_ledger']) }}
                </span>
            </div>
            <div class="ttc-table-wrap">
                <table class="ttc-hp-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Kode COA</th>
                            <th style="text-align: left;">Nama Akun Piutang</th>
                            <th style="text-align: left;">Kategori</th>
                            <th style="text-align: center;">Mutasi</th>
                            <th style="text-align: right;">Piutang Baru (Debit)</th>
                            <th style="text-align: right;">Pelunasan (Kredit)</th>
                            <th style="text-align: right;">Sisa Piutang</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['piutang_items'] as $item)
                            <tr>
                                <td><span class="ttc-code-pill" style="background: #047857;">{{ $item['kode_akun'] }}</span></td>
                                <td style="font-weight: 700;">{{ $item['nama_akun'] }}</td>
                                <td>{{ $item['kategori'] }}</td>
                                <td style="text-align: center;">{{ $item['tx_count'] }}x</td>
                                <td style="text-align: right; color: #1d4ed8; font-weight: 700;">{{ $fmt($item['total_piutang']) }}</td>
                                <td style="text-align: right; color: #059669; font-weight: 700;">{{ $fmt($item['total_lunas']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: #047857;">{{ $fmt($item['sisa_piutang']) }}</td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="{{ url('/admin/coa-ledgers?coa_id=' . $item['coa_id']) }}" class="ttc-preset-btn" style="padding: 4px 10px; font-size: 11px;">Buku Besar</a>
                                    <a href="{{ route('finance.report.general-ledger.preview', ['start_date' => '2025-01-01', 'end_date' => $this->asOfDate, 'coa_id' => $item['coa_id']]) }}" target="_blank" class="ttc-preset-btn" style="padding: 4px 10px; font-size: 11px;">🖨️ PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">Belum ada saldo piutang di Buku Besar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(! empty($data['piutang_ledger_rows']))
                <div style="padding: 12px 24px 6px 24px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; border-top: 1px solid #e2e8f0;">
                    Rincian Histori Mutasi Piutang ({{ count($data['piutang_ledger_rows']) }} Baris)
                </div>
                <div class="ttc-table-wrap">
                    <table class="ttc-hp-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 44px;">No</th>
                                <th style="text-align: center; width: 95px;">Tanggal</th>
                                <th style="text-align: left; width: 125px;">No. Referensi</th>
                                <th style="text-align: left; width: 165px;">Akun COA</th>
                                <th style="text-align: left;">Keterangan</th>
                                <th style="text-align: right; width: 140px;">Piutang Baru (Rp)</th>
                                <th style="text-align: right; width: 140px;">Pelunasan (Rp)</th>
                                <th style="text-align: right; width: 145px;">Saldo Berjalan (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['piutang_ledger_rows'] as $i => $row)
                                <tr>
                                    <td style="text-align: center; color: #64748b;">{{ $i + 1 }}</td>
                                    <td style="text-align: center; font-weight: 600;">{{ $row['tanggal'] }}</td>
                                    <td style="font-weight: 700; color: #3b82f6;">{{ $row['reference_no'] }}</td>
                                    <td><span class="ttc-code-pill" style="background: #047857;">{{ $row['kode_akun'] }}</span> {{ $row['nama_akun'] }}</td>
                                    <td>{{ $row['remarks'] }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #1d4ed8;">{{ $row['tambah_piutang'] > 0 ? $fmt($row['tambah_piutang']) : '-' }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #059669;">{{ $row['lunas_piutang'] > 0 ? $fmt($row['lunas_piutang']) : '-' }}</td>
                                    <td style="text-align: right; font-weight: 800; color: #047857;">{{ $fmt($row['saldo_berjalan']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- SECTION 4: MONITORING DOKUMEN OPERASIONAL (INVOICE AR & PO BILLING AP) --}}
        <div class="ttc-card">
            <div class="ttc-section-head">
                <div>
                    <div class="ttc-section-title">4. Monitoring Dokumen Tagihan Operasional (Modul Invoice &amp; PO Billing)</div>
                    <div class="ttc-section-desc">Daftar dokumen tagihan customer (AR) &amp; tagihan supplier (AP) yang masih memiliki sisa belum terbayar di modul operasional.</div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ url('/admin/invoices') }}" class="ttc-preset-btn">Buka Modul Invoices</a>
                    <a href="{{ url('/admin/po-billings') }}" class="ttc-preset-btn">Buka Modul PO Billing</a>
                </div>
            </div>
            <div class="ttc-table-wrap">
                <table class="ttc-hp-table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Jenis</th>
                            <th style="text-align: left;">No. Dokumen</th>
                            <th style="text-align: left;">Customer / Supplier</th>
                            <th style="text-align: center;">Tgl. Dokumen</th>
                            <th style="text-align: center;">Jatuh Tempo</th>
                            <th style="text-align: right;">Total Tagihan</th>
                            <th style="text-align: right;">Terbayar</th>
                            <th style="text-align: right;">Sisa Tagihan</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['open_invoices'] as $inv)
                            <tr>
                                <td><span class="ttc-pill-badge" style="background: #dbeafe; color: #1e40af;">PIUTANG (AR)</span></td>
                                <td style="font-weight: 700; color: #2563eb;">{{ $inv['kode_invoice'] }}</td>
                                <td style="font-weight: 600;">{{ $inv['customer'] }}</td>
                                <td style="text-align: center;">{{ $inv['tanggal_invoice'] }}</td>
                                <td style="text-align: center;">{{ $inv['tanggal_jatuh_tempo'] }}</td>
                                <td style="text-align: right;">{{ $fmt0($inv['grand_total']) }}</td>
                                <td style="text-align: right; color: #059669;">{{ $fmt0($inv['total_payment']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: #1d4ed8;">{{ $fmt0($inv['sisa_tagihan']) }}</td>
                                <td style="text-align: center;">
                                    <span class="ttc-pill-badge" style="background: {{ $inv['status'] === 'Partial' ? '#fef3c7' : '#fee2e2' }}; color: {{ $inv['status'] === 'Partial' ? '#b45309' : '#b91c1c' }};">
                                        {{ $inv['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @foreach($data['open_po_billings'] as $bill)
                            <tr>
                                <td><span class="ttc-pill-badge" style="background: #fee2e2; color: #b91c1c;">HUTANG PO (AP)</span></td>
                                <td style="font-weight: 700; color: #dc2626;">{{ $bill['kode_tagihan'] }}</td>
                                <td style="font-weight: 600;">{{ $bill['supplier'] }} (PO: {{ $bill['no_po'] }})</td>
                                <td style="text-align: center;">{{ $bill['tanggal_transaksi'] }}</td>
                                <td style="text-align: center;">{{ $bill['tanggal_jatuh_tempo'] }}</td>
                                <td style="text-align: right;">{{ $fmt0($bill['grand_total']) }}</td>
                                <td style="text-align: right; color: #059669;">{{ $fmt0($bill['total_payment']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: #dc2626;">{{ $fmt0($bill['sisa_tagihan']) }}</td>
                                <td style="text-align: center;">
                                    <span class="ttc-pill-badge" style="background: #fee2e2; color: #b91c1c;">{{ $bill['status'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @foreach($data['open_employee_loans'] ?? [] as $loan)
                            <tr>
                                <td><span class="ttc-pill-badge" style="background: #f3e8ff; color: #6b21a8;">KASBON HRD (1131)</span></td>
                                <td style="font-weight: 700; color: #7c3aed;">{{ $loan['nomor_bukti'] }}</td>
                                <td style="font-weight: 600;">{{ $loan['karyawan'] }} (Cicilan: {{ $fmt0($loan['cicilan']) }}/bln)</td>
                                <td style="text-align: center;">{{ $loan['tanggal'] }}</td>
                                <td style="text-align: center;">Potong Gaji</td>
                                <td style="text-align: right;">{{ $fmt0($loan['nilai_pinjaman']) }}</td>
                                <td style="text-align: right; color: #059669;">{{ $fmt0($loan['total_terbayar']) }}</td>
                                <td style="text-align: right; font-weight: 800; color: #7c3aed;">{{ $fmt0($loan['sisa_pinjaman']) }}</td>
                                <td style="text-align: center;">
                                    <span class="ttc-pill-badge" style="background: {{ $loan['is_disbursed'] ? '#d1fae5' : '#fef3c7' }}; color: {{ $loan['is_disbursed'] ? '#065f46' : '#92400e' }};">
                                        {{ $loan['is_disbursed'] ? 'Cair' : 'Belum Cair' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @if(empty($data['open_invoices']) && empty($data['open_po_billings']) && empty($data['open_employee_loans']))
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 20px; color: #64748b;">
                                    Tidak ada dokumen Invoice AR, Tagihan PO Supplier, atau Kasbon Karyawan yang belum lunas.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
