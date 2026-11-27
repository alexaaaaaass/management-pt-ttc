<x-filament-panels::page>
@php
    $s = $this->summary;
    $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<style>
    .fhs-wrap { display: flex; flex-direction: column; gap: 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #0f172a; }
    .dark .fhs-wrap { color: #f1f5f9; }
    .fhs-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); overflow: hidden; }
    .dark .fhs-card { background: #111827; border-color: #1f2937; }
    .fhs-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #065f46 100%); color: #ffffff; padding: 20px 24px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; }
    .fhs-hero-badge { display: inline-block; background: #10b981; color: #022c22; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; padding: 3px 10px; border-radius: 999px; }
    .fhs-hero-title { font-size: 19px; font-weight: 800; margin-top: 6px; color: #ffffff; }
    .fhs-hero-sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
    .fhs-toolbar { padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .dark .fhs-toolbar { background: #1f2937; border-top-color: #374151; }
    .fhs-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.75' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.65rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.15em 1.15em !important;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 9px;
        padding: 7px 36px 7px 12px !important;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
        min-width: 190px;
        cursor: pointer;
    }
    .dark .fhs-select { background-color: #0f172a !important; color: #f8fafc; border-color: #334155; }
    .fhs-pill-link { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; background: #e2e8f0; color: #1e293b; font-size: 11.5px; font-weight: 700; text-decoration: none; transition: background 0.15s; }
    .fhs-pill-link:hover { background: #cbd5e1; }
    .dark .fhs-pill-link { background: #334155; color: #e2e8f0; }
    .fhs-pill-primary { background: #dbeafe; color: #1d4ed8; }
    .dark .fhs-pill-primary { background: #1e3a8a; color: #bfdbfe; }
    .fhs-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
    .fhs-kpi { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; border-top: 4px solid #2563eb; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
    .dark .fhs-kpi { background: #111827; border-color: #1f2937; }
    .fhs-kpi-emerald { border-top-color: #059669; }
    .fhs-kpi-amber { border-top-color: #d97706; }
    .fhs-kpi-purple { border-top-color: #7c3aed; }
    .fhs-kpi-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
    .fhs-kpi-val { font-size: 22px; font-weight: 900; margin-top: 4px; color: #0f172a; }
    .dark .fhs-kpi-val { color: #ffffff; }
    .fhs-kpi-rows { margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 4px; font-size: 11.5px; color: #475569; }
    .dark .fhs-kpi-rows { border-top-color: #1f2937; color: #94a3b8; }
    .fhs-kpi-row { display: flex; justify-content: space-between; gap: 8px; }
    .fhs-two-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 18px; }
    .fhs-sec-head { padding: 13px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .dark .fhs-sec-head { background: #1f2937; border-bottom-color: #374151; }
    .fhs-sec-title { font-size: 13.5px; font-weight: 800; color: #0f172a; }
    .dark .fhs-sec-title { color: #f8fafc; }
    .fhs-sec-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }
    .fhs-table-wrap { overflow-x: auto; }
    .fhs-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .fhs-table th { background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.03em; padding: 9px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; white-space: nowrap; }
    .dark .fhs-table th { background: #1f2937; color: #cbd5e1; border-bottom-color: #374151; }
    .fhs-table td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .dark .fhs-table td { border-bottom-color: #1f2937; }
    .fhs-right { text-align: right !important; font-variant-numeric: tabular-nums; }
    .fhs-center { text-align: center !important; }
    .fhs-badge { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 10.5px; font-weight: 800; white-space: nowrap; }
    .fhs-b-green { background: #d1fae5; color: #065f46; }
    .fhs-b-blue { background: #dbeafe; color: #1e40af; }
    .fhs-b-amber { background: #fef3c7; color: #92400e; }
    .fhs-b-red { background: #fee2e2; color: #991b1b; }
    .fhs-b-gray { background: #e2e8f0; color: #475569; }
    .dark .fhs-b-gray { background: #334155; color: #cbd5e1; }
    .fhs-text-green { color: #059669; font-weight: 700; }
    .fhs-text-red { color: #dc2626; font-weight: 700; }
    .fhs-text-amber { color: #d97706; font-weight: 700; }
    .fhs-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
</style>

<div class="fhs-wrap">
    {{-- Hero & Toolbar Periode --}}
    <div class="fhs-card">
        <div class="fhs-hero">
            <div>
                <span class="fhs-hero-badge">Integrasi 2-Arah PSAK · Anti Dobel</span>
                <div class="fhs-hero-title">Command Center Sinkronisasi Finance ↔ HRD</div>
                <div class="fhs-hero-sub">
                    Rekonsiliasi Real-Time Payroll, Borongan, NPB, BPJS (2% + 3,7%), Kasbon Karyawan (1131) &amp; Alokasi COA Cost Center
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px; color:#cbd5e1; text-transform:uppercase; font-weight:700;">Periode Aktif</div>
                <div style="font-size:18px; font-weight:900; color:#ffffff;">{{ $s['periode_label'] }} ({{ $s['periode'] }})</div>
                <div style="font-size:11.5px; color:#a7f3d0; font-weight:600; margin-top:2px;">
                    {{ $s['slip_count'] }} Karyawan · {{ $s['borongan_count'] }} Batch Borongan
                </div>
            </div>
        </div>

        <div class="fhs-toolbar">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <label style="font-size:11.5px; font-weight:800; text-transform:uppercase; color:#64748b;">Pilih Periode:</label>
                <select wire:model.live="selectedPeriode" class="fhs-select">
                    @foreach($this->availablePeriods as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <a href="{{ url('/admin/gajis') }}" class="fhs-pill-link">Penggajian HRD</a>
                <a href="{{ url('/admin/payroll-borongans') }}" class="fhs-pill-link">Borongan</a>
                <a href="{{ url('/admin/peng-pinjamen') }}" class="fhs-pill-link">Kasbon Karyawan</a>
                <a href="{{ url('/admin/npb-requests') }}" class="fhs-pill-link">NPB Finance</a>
                <a href="{{ url('/admin/npb-settlements') }}" class="fhs-pill-link">LPJ NPB</a>
                <a href="{{ url('/admin/hr-payroll-setting-page') }}" class="fhs-pill-link fhs-pill-primary">⚙ Mapping COA HRD</a>
            </div>
        </div>
    </div>

    {{-- 4 KPI Cards Utama --}}
    <div class="fhs-kpi-grid">
        <div class="fhs-kpi">
            <div class="fhs-kpi-label">Total Beban Tenaga Kerja (PSAK)</div>
            <div class="fhs-kpi-val">{{ $fmt($s['total_beban_tenaga_kerja']) }}</div>
            <div class="fhs-kpi-rows">
                <div class="fhs-kpi-row"><span>Beban Gaji Net:</span><strong>{{ $fmt($s['total_beban_gaji_net']) }}</strong></div>
                <div class="fhs-kpi-row"><span>BPJS Perusahaan (3,7%):</span><strong>{{ $fmt($s['total_bpjs_perusahaan']) }}</strong></div>
                <div class="fhs-kpi-row"><span>Borongan Produksi:</span><strong>{{ $fmt($s['total_borongan']) }}</strong></div>
                <div class="fhs-kpi-row"><span class="fhs-text-green">Terposting di GL:</span><span class="fhs-text-green">{{ $fmt($s['gl_total_posted']) }}</span></div>
            </div>
        </div>

        <div class="fhs-kpi fhs-kpi-emerald">
            <div class="fhs-kpi-label">Total Kas Keluar (THP + Borongan)</div>
            <div class="fhs-kpi-val" style="color:#059669;">{{ $fmt($s['total_kas_keluar']) }}</div>
            <div class="fhs-kpi-rows">
                <div class="fhs-kpi-row"><span>THP Gaji Reguler:</span><strong>{{ $fmt($s['total_thp']) }}</strong></div>
                <div class="fhs-kpi-row">
                    <span>Status Slip:</span>
                    <span><strong>{{ $s['paid_count'] }} Paid</strong> · {{ $s['approved_count'] }} Appr · {{ $s['draft_count'] }} Draft</span>
                </div>
                <div class="fhs-kpi-row">
                    <span>Posting Buku Besar:</span>
                    <strong class="{{ $s['posted_count'] === $s['slip_count'] && $s['slip_count'] > 0 ? 'fhs-text-green' : 'fhs-text-amber' }}">
                        {{ $s['posted_count'] }} / {{ $s['slip_count'] }} Slip
                    </strong>
                </div>
                <div class="fhs-kpi-row"><span>Kas Keluar di GL:</span><strong>{{ $fmt($s['gl_payroll_cash']) }}</strong></div>
            </div>
        </div>

        <div class="fhs-kpi fhs-kpi-amber">
            <div class="fhs-kpi-label">Kewajiban Setor BPJS (2% + 3,7%)</div>
            <div class="fhs-kpi-val" style="color:#d97706;">{{ $fmt($s['total_bpjs_setor']) }}</div>
            <div class="fhs-kpi-rows">
                <div class="fhs-kpi-row"><span>Potongan Karyawan (2%):</span><strong>{{ $fmt($s['total_bpjs_karyawan']) }}</strong></div>
                <div class="fhs-kpi-row"><span>Kontribusi PT (3,7%):</span><strong>{{ $fmt($s['total_bpjs_perusahaan']) }}</strong></div>
                <div class="fhs-kpi-row">
                    <span>Sudah Disetor (GL):</span>
                    <span class="{{ $s['gl_bpjs_remitted'] >= $s['total_bpjs_setor'] && $s['total_bpjs_setor'] > 0 ? 'fhs-text-green' : 'fhs-text-red' }}">
                        {{ $fmt($s['gl_bpjs_remitted']) }}
                    </span>
                </div>
                <div class="fhs-kpi-row"><span>Saldo COA 2133 (GL):</span><strong>{{ $fmt($s['gl_saldo_hutang_bpjs']) }}</strong></div>
            </div>
        </div>

        <div class="fhs-kpi fhs-kpi-purple">
            <div class="fhs-kpi-label">Piutang Karyawan / Kasbon (1131)</div>
            <div class="fhs-kpi-val" style="color:#7c3aed;">{{ $fmt($s['total_sisa_pinjaman_hrd']) }}</div>
            <div class="fhs-kpi-rows">
                <div class="fhs-kpi-row"><span>Potongan Kasbon {{ $s['periode'] }}:</span><span class="fhs-text-amber">{{ $fmt($s['total_potongan_kasbon']) }}</span></div>
                <div class="fhs-kpi-row"><span>Sisa Pinjaman (HRD):</span><strong>{{ $fmt($s['total_sisa_pinjaman_hrd']) }}</strong></div>
                <div class="fhs-kpi-row"><span>Saldo COA 1131 (GL):</span><strong>{{ $fmt($s['gl_saldo_piutang_karyawan']) }}</strong></div>
                <div class="fhs-kpi-row">
                    <span>Selisih HRD vs GL:</span>
                    <span class="{{ abs($s['kasbon_variance']) < 1 ? 'fhs-text-green' : 'fhs-text-red' }}">{{ $fmt($s['kasbon_variance']) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2 Kolom: Rekonsiliasi NPB & Cost Center COA --}}
    <div class="fhs-two-col">
        <div class="fhs-card">
            <div class="fhs-sec-head">
                <div>
                    <div class="fhs-sec-title">Rekonsiliasi NPB ↔ Aktual Payroll HRD</div>
                    <div class="fhs-sec-sub">Perbandingan Anggaran NPB vs Kebutuhan Aktual HRD ({{ $s['periode_label'] }})</div>
                </div>
                @if($s['npb'])
                    <a href="{{ url('/admin/npb-requests/' . $s['npb']['id'] . '/edit') }}" class="fhs-badge fhs-b-blue" style="text-decoration:none;">
                        {{ $s['npb']['nomor_pengajuan'] }} ({{ $s['npb']['status'] }})
                    </a>
                @else
                    <span class="fhs-badge fhs-b-amber">Belum Ada NPB</span>
                @endif
            </div>
            <div class="fhs-table-wrap">
                <table class="fhs-table">
                    <thead>
                        <tr>
                            <th>Pos Biaya</th>
                            <th class="fhs-right">Anggaran NPB</th>
                            <th class="fhs-right">Aktual HRD</th>
                            <th class="fhs-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $vg = ($s['npb']['budget_gaji'] ?? 0) - $s['total_thp'];
                            $vb = ($s['npb']['budget_bpjs'] ?? 0) - $s['total_bpjs_setor'];
                            $vbor = ($s['npb']['budget_borongan'] ?? 0) - $s['total_borongan'];
                        @endphp
                        <tr>
                            <td>
                                <strong>Gaji Karyawan (THP)</strong>
                                <div style="font-size:10.5px; color:#64748b;">Gaji Pokok: {{ $fmt($s['total_gaji_pokok']) }}</div>
                            </td>
                            <td class="fhs-right fhs-mono">{{ $fmt($s['npb']['budget_gaji'] ?? 0) }}</td>
                            <td class="fhs-right fhs-mono fhs-text-green">{{ $fmt($s['total_thp']) }}</td>
                            <td class="fhs-right fhs-mono {{ $vg >= 0 ? 'fhs-text-green' : 'fhs-text-red' }}">{{ $vg >= 0 ? '+' : '-' }}{{ $fmt(abs($vg)) }}</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>BPJS Ketenagakerjaan</strong>
                                <div style="font-size:10.5px; color:#64748b;">Karyawan 2% + PT 3,7%</div>
                            </td>
                            <td class="fhs-right fhs-mono">{{ $fmt($s['npb']['budget_bpjs'] ?? 0) }}</td>
                            <td class="fhs-right fhs-mono fhs-text-amber">{{ $fmt($s['total_bpjs_setor']) }}</td>
                            <td class="fhs-right fhs-mono {{ $vb >= 0 ? 'fhs-text-green' : 'fhs-text-red' }}">{{ $vb >= 0 ? '+' : '-' }}{{ $fmt(abs($vb)) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payroll Borongan / Part-Time</strong></td>
                            <td class="fhs-right fhs-mono">{{ $fmt($s['npb']['budget_borongan'] ?? 0) }}</td>
                            <td class="fhs-right fhs-mono"><strong>{{ $fmt($s['total_borongan']) }}</strong></td>
                            <td class="fhs-right fhs-mono {{ $vbor >= 0 ? 'fhs-text-green' : 'fhs-text-red' }}">{{ $vbor >= 0 ? '+' : '-' }}{{ $fmt(abs($vbor)) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fhs-card">
            <div class="fhs-sec-head">
                <div>
                    <div class="fhs-sec-title">Alokasi Beban Tenaga Kerja per COA Cost Center</div>
                    <div class="fhs-sec-sub">Pemisahan otomatis HPP Produksi (5201) vs Penjualan (6101) vs Admin/Umum (6201/6301)</div>
                </div>
            </div>
            <div class="fhs-table-wrap">
                <table class="fhs-table">
                    <thead>
                        <tr>
                            <th>Akun COA</th>
                            <th>Kelompok Laba Rugi</th>
                            <th class="fhs-center">Org</th>
                            <th class="fhs-right">Beban Diakui</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($s['cost_centers'] as $cc)
                            <tr>
                                <td><strong class="fhs-mono" style="color:#2563eb;">{{ $cc['kode_akun'] }}</strong> — {{ $cc['nama_akun'] }}</td>
                                <td style="color:#64748b; font-size:11px;">{{ $cc['kelompok'] }}</td>
                                <td class="fhs-center"><strong>{{ $cc['headcount'] }}</strong></td>
                                <td class="fhs-right fhs-mono"><strong>{{ $fmt($cc['beban_gaji']) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="fhs-center" style="padding:18px; color:#94a3b8;">Belum ada data payroll periode ini.</td></tr>
                        @endforelse
                        @if($s['total_bpjs_perusahaan'] > 0)
                            <tr>
                                <td><strong class="fhs-mono" style="color:#d97706;">6502</strong> — Kontribusi BPJS Ketenagakerjaan</td>
                                <td style="color:#64748b; font-size:11px;">Beban BPJS Perusahaan (3,7%)</td>
                                <td class="fhs-center">-</td>
                                <td class="fhs-right fhs-mono fhs-text-amber">{{ $fmt($s['total_bpjs_perusahaan']) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Slip Gaji & Status Posting GL --}}
    <div class="fhs-card">
        <div class="fhs-sec-head">
            <div>
                <div class="fhs-sec-title">Rincian Slip Gaji &amp; Status Posting Buku Besar ({{ $s['periode'] }})</div>
                <div class="fhs-sec-sub">Jurnal Multi-Line PSAK: DEBIT Beban Gaji + BPJS PT (6502) vs KREDIT Bank (THP) + Piutang Kasbon (1131) + BPJS Terutang (2133)</div>
            </div>
        </div>
        <div class="fhs-table-wrap">
            <table class="fhs-table">
                <thead>
                    <tr>
                        <th>Kode Slip</th>
                        <th>Karyawan</th>
                        <th>COA Cost Center</th>
                        <th class="fhs-right">Gaji Pokok</th>
                        <th class="fhs-right">Kasbon (1131)</th>
                        <th class="fhs-right">BPJS (2% / 3,7%)</th>
                        <th class="fhs-right">Take Home Pay</th>
                        <th class="fhs-center">Status</th>
                        <th class="fhs-center">GL Buku Besar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($s['slip_rows'] as $row)
                        <tr>
                            <td class="fhs-mono">
                                <a href="{{ url('/admin/gajis/' . $row['id'] . '/edit') }}" style="color:#2563eb; font-weight:800; text-decoration:none;">{{ $row['kode_slip'] }}</a>
                            </td>
                            <td>
                                <strong>{{ $row['nama'] }}</strong>
                                <div style="font-size:10.5px; color:#64748b;">[{{ $row['nip'] }}] {{ $row['jabatan'] }} · {{ $row['departemen'] }}</div>
                            </td>
                            <td class="fhs-mono" style="font-size:11px;">{{ $row['coa_label'] }}</td>
                            <td class="fhs-right fhs-mono">{{ $fmt($row['gaji_pokok']) }}</td>
                            <td class="fhs-right fhs-mono {{ $row['potongan_kasbon'] > 0 ? 'fhs-text-amber' : '' }}">
                                {{ $row['potongan_kasbon'] > 0 ? $fmt($row['potongan_kasbon']) : '-' }}
                            </td>
                            <td class="fhs-right fhs-mono">
                                {{ $fmt($row['potongan_bpjs']) }}
                                <div style="font-size:10px; color:#64748b;">PT: {{ $fmt($row['bpjs_perusahaan']) }}</div>
                            </td>
                            <td class="fhs-right fhs-mono fhs-text-green">{{ $fmt($row['gaji_bersih']) }}</td>
                            <td class="fhs-center">
                                <span class="fhs-badge {{ $row['status'] === 'Paid' ? 'fhs-b-green' : ($row['status'] === 'Approved' ? 'fhs-b-blue' : 'fhs-b-amber') }}">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td class="fhs-center">
                                @if($row['is_gl_posted'])
                                    <span class="fhs-badge fhs-b-green">✓ Posted GL</span>
                                @else
                                    <span class="fhs-badge fhs-b-gray">Belum Posting</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="fhs-center" style="padding:20px; color:#94a3b8;">Belum ada slip gaji untuk periode {{ $s['periode'] }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Monitoring Pinjaman / Kasbon Karyawan Aktif --}}
    @if(! empty($s['active_loans']))
        <div class="fhs-card">
            <div class="fhs-sec-head">
                <div>
                    <div class="fhs-sec-title">Monitoring Kasbon / Piutang Karyawan Aktif (Sinkron ke COA 1131)</div>
                    <div class="fhs-sec-sub">Daftar pinjaman karyawan belum lunas atau menunggu pencairan Finance</div>
                </div>
                <a href="{{ url('/admin/peng-pinjamen') }}" class="fhs-pill-link fhs-pill-primary">Kelola Pinjaman →</a>
            </div>
            <div class="fhs-table-wrap">
                <table class="fhs-table">
                    <thead>
                        <tr>
                            <th>No. Bukti</th>
                            <th>Karyawan</th>
                            <th class="fhs-right">Nilai Pinjaman</th>
                            <th class="fhs-right">Cicilan / Bln</th>
                            <th class="fhs-right">Terbayar</th>
                            <th class="fhs-right">Sisa Piutang</th>
                            <th class="fhs-center">Pencairan Finance</th>
                            <th class="fhs-center">GL 1131</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($s['active_loans'] as $loan)
                            <tr>
                                <td class="fhs-mono"><strong>{{ $loan['nomor_bukti'] }}</strong></td>
                                <td><strong>{{ $loan['karyawan'] }}</strong> <span style="color:#64748b;">({{ $loan['nip'] }})</span></td>
                                <td class="fhs-right fhs-mono">{{ $fmt($loan['nilai_pinjaman']) }}</td>
                                <td class="fhs-right fhs-mono">{{ $fmt($loan['cicilan']) }}</td>
                                <td class="fhs-right fhs-mono fhs-text-green">{{ $fmt($loan['terbayar']) }}</td>
                                <td class="fhs-right fhs-mono fhs-text-red">{{ $fmt($loan['sisa']) }}</td>
                                <td class="fhs-center">
                                    <span class="fhs-badge {{ $loan['is_disbursed'] ? 'fhs-b-green' : 'fhs-b-amber' }}">
                                        {{ $loan['is_disbursed'] ? 'Sudah Cair' : 'Belum Cair' }}
                                    </span>
                                </td>
                                <td class="fhs-center">
                                    <span class="fhs-badge {{ $loan['is_gl_posted'] ? 'fhs-b-green' : 'fhs-b-gray' }}">
                                        {{ $loan['is_gl_posted'] ? '✓ Posted' : 'Non-GL' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</x-filament-panels::page>
