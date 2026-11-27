<x-filament-panels::page>
@php
    $d = $this->dashboardData;
    $att = $d['attendance'];
    $attSum = $att['summary'];
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $maxAttVal = max(1, (int) $attSum['hadir'], (int) $attSum['telat'], (int) ($attSum['izin'] + $attSum['sakit'] + $attSum['cuti']), (int) ($attSum['alpha'] + ($attSum['scorsing'] ?? 0)), (int) ceil((float) $attSum['lembur']));
@endphp

<style>
    .hrd-wrap { display: flex; flex-direction: column; gap: 20px; font-family: 'Inter', system-ui, sans-serif; color: #0f172a; }
    .dark .hrd-wrap { color: #f1f5f9; }
    .hrd-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05); overflow: hidden; }
    .dark .hrd-card { background: #111827; border-color: #1f2937; }
    .hrd-hero { background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0f766e 100%); color: #ffffff; padding: 20px 24px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; }
    .hrd-badge-top { display: inline-block; background: #38bdf8; color: #082f49; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; padding: 3px 10px; border-radius: 999px; }
    .hrd-hero-title { font-size: 19px; font-weight: 800; margin-top: 6px; }
    .hrd-hero-sub { font-size: 12px; color: #cbd5e1; margin-top: 2px; }
    .hrd-toolbar { padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .dark .hrd-toolbar { background: #1f2937; border-top-color: #374151; }
    .hrd-select {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.75' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.6rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.15em 1.15em !important;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 8px;
        padding: 7px 34px 7px 12px !important;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1.3;
        cursor: pointer;
    }
    .dark .hrd-select { background-color: #0f172a !important; color: #f8fafc; border-color: #334155; }
    .hrd-btn { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700; text-decoration: none; background: #e2e8f0; color: #1e293b; }
    .hrd-btn:hover { background: #cbd5e1; }
    .dark .hrd-btn { background: #334155; color: #e2e8f0; }
    .hrd-btn-primary { background: #2563eb; color: #ffffff !important; }
    .hrd-btn-primary:hover { background: #1d4ed8; }
    .hrd-btn-emerald { background: #059669; color: #ffffff !important; }
    .hrd-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
    .hrd-kpi { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; border-top: 4px solid #2563eb; }
    .dark .hrd-kpi { background: #111827; border-color: #1f2937; }
    .hrd-kpi-amber { border-top-color: #d97706; }
    .hrd-kpi-red { border-top-color: #dc2626; }
    .hrd-kpi-purple { border-top-color: #7c3aed; }
    .hrd-kpi-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; }
    .hrd-kpi-val { font-size: 24px; font-weight: 900; margin-top: 4px; }
    .hrd-kpi-meta { margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9; font-size: 11.5px; color: #475569; display: flex; flex-direction: column; gap: 3px; }
    .dark .hrd-kpi-meta { border-top-color: #1f2937; color: #94a3b8; }
    .hrd-grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 18px; }
    .hrd-sec-head { padding: 12px 18px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .dark .hrd-sec-head { background: #1f2937; border-bottom-color: #374151; }
    .hrd-sec-title { font-size: 13.5px; font-weight: 800; }
    .hrd-sec-sub { font-size: 11px; color: #64748b; }
    .hrd-body { padding: 16px 18px; }
    .hrd-bar-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 11px; }
    .hrd-bar-track { height: 9px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .dark .hrd-bar-track { background: #1f2937; }
    .hrd-bar-fill { height: 100%; border-radius: 999px; }
    .hrd-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .hrd-table th { background: #f1f5f9; color: #475569; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    .dark .hrd-table th { background: #1f2937; color: #cbd5e1; border-bottom-color: #374151; }
    .hrd-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
    .dark .hrd-table td { border-bottom-color: #1f2937; }
    .hrd-pill { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10.5px; font-weight: 800; margin-right: 3px; margin-bottom: 2px; }
    .hrd-p-red { background: #fee2e2; color: #991b1b; }
    .hrd-p-amber { background: #fef3c7; color: #92400e; }
    .hrd-p-green { background: #d1fae5; color: #065f46; }
    .hrd-p-blue { background: #dbeafe; color: #1e40af; }
    .hrd-p-gray { background: #e2e8f0; color: #475569; }
</style>

<div class="hrd-wrap">
    {{-- Hero Banner & Quick Actions --}}
    <div class="hrd-card">
        <div class="hrd-hero">
            <div>
                <span class="hrd-badge-top">HRD Command Dashboard · PT TTC</span>
                <div class="hrd-hero-title">Ringkasan Kepegawaian, Absensi &amp; Kedisiplinan</div>
                <div class="hrd-hero-sub">Cut-off Aktif: <strong>{{ $att['cutoff_label'] ?? $att['periode_label'] }}</strong> ({{ $att['hari_kerja'] }} hari kerja efektif)</div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ url('/admin/karyawans/create') }}" class="hrd-btn hrd-btn-primary">+ Tambah Karyawan</a>
                <a href="{{ url('/admin/absensis') }}" class="hrd-btn">Import Fingerprint</a>
                <a href="{{ url('/admin/gajis') }}" class="hrd-btn hrd-btn-emerald">Buka Penggajian</a>
            </div>
        </div>
        <div class="hrd-toolbar">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:11.5px; font-weight:800; color:#64748b; text-transform:uppercase;">Periode Pantau:</span>
                <select wire:model.live="bulan" class="hrd-select" style="min-width: 132px;">
                    @foreach($bulanNama as $num => $nm)
                        <option value="{{ $num }}">{{ $nm }}</option>
                    @endforeach
                </select>
                <select wire:model.live="tahun" class="hrd-select" style="min-width: 92px;">
                    @foreach(range(now()->year - 1, now()->year + 1) as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ url('/admin/rekap-absensi-bulanan') }}" class="hrd-btn">Rekap Absensi</a>
                <a href="{{ url('/admin/rekap-cuti-tahunan') }}" class="hrd-btn">Rekap Cuti</a>
                <a href="{{ url('/admin/hr-shifts') }}" class="hrd-btn">Master Shift</a>
                <a href="{{ url('/admin/hrd-approval-page') }}" class="hrd-btn">Approval HRD ({{ $d['pending']['total'] }})</a>
            </div>
        </div>
    </div>

    {{-- 4 KPI Cards --}}
    <div class="hrd-kpi-grid">
        <div class="hrd-kpi">
            <div class="hrd-kpi-label">Karyawan Aktif</div>
            <div class="hrd-kpi-val" style="color:#2563eb;">{{ $d['total_aktif'] }} <span style="font-size:13px; font-weight:600; color:#64748b;">Orang</span></div>
            <div class="hrd-kpi-meta">
                <div>Bulanan: <strong>{{ $d['by_skema']['Bulanan'] }}</strong> · Harian: <strong>{{ $d['by_skema']['Harian'] }}</strong></div>
                <div>Freelance: <strong>{{ $d['by_skema']['Freelance'] }}</strong> · Borongan: <strong>{{ $d['by_skema']['Borongan'] }}</strong></div>
                <div>Nonaktif: {{ $d['total_nonaktif'] }} orang</div>
            </div>
        </div>

        <div class="hrd-kpi hrd-kpi-amber">
            <div class="hrd-kpi-label">Pending Approval HRD</div>
            <div class="hrd-kpi-val" style="color:#d97706;">{{ $d['pending']['total'] }} <span style="font-size:13px; font-weight:600; color:#64748b;">Menunggu</span></div>
            <div class="hrd-kpi-meta">
                <div>Cuti: <strong>{{ $d['pending']['cuti'] }}</strong> · Izin/Sakit: <strong>{{ $d['pending']['izin'] }}</strong></div>
                <div>Lembur: <strong>{{ $d['pending']['lembur'] }}</strong> · Pinjaman: <strong>{{ $d['pending']['pinjaman'] }}</strong></div>
                <a href="{{ url('/admin/hrd-approval-page') }}" style="color:#2563eb; font-weight:700; text-decoration:none;">Proses Approval →</a>
            </div>
        </div>

        <div class="hrd-kpi hrd-kpi-red">
            <div class="hrd-kpi-label">Alert Kontrak Habis (≤ 30 Hr)</div>
            <div class="hrd-kpi-val" style="color:{{ count($d['contract_alerts']) > 0 ? '#dc2626' : '#059669' }};">
                {{ count($d['contract_alerts']) }} <span style="font-size:13px; font-weight:600; color:#64748b;">Karyawan</span>
            </div>
            <div class="hrd-kpi-meta">
                <div>Expired: <strong>{{ collect($d['contract_alerts'])->where('is_expired', true)->count() }}</strong></div>
                <div>Habis ≤ 30 hari: <strong>{{ collect($d['contract_alerts'])->where('is_expired', false)->count() }}</strong></div>
                <div>SP / Scorsing Aktif: <strong>{{ count($d['active_disiplins']) }}</strong></div>
            </div>
        </div>

        <div class="hrd-kpi hrd-kpi-purple">
            <div class="hrd-kpi-label">Profil Belum Lengkap</div>
            <div class="hrd-kpi-val" style="color:{{ count($d['incomplete_profiles']) > 0 ? '#7c3aed' : '#059669' }};">
                {{ count($d['incomplete_profiles']) }} <span style="font-size:13px; font-weight:600; color:#64748b;">Karyawan</span>
            </div>
            <div class="hrd-kpi-meta">
                <div>Cek PIN Fingerprint, Rekening Bank, No. BPJS &amp; NIK</div>
                <a href="{{ url('/admin/karyawans') }}" style="color:#7c3aed; font-weight:700; text-decoration:none;">Lengkapi via Import Excel →</a>
            </div>
        </div>
    </div>

    {{-- Row 2: Grafik Kehadiran & Distribusi Departemen/Shift --}}
    <div class="hrd-grid-2">
        {{-- Grafik Kehadiran & Lembur Bulan Berjalan --}}
        <div class="hrd-card">
            <div class="hrd-sec-head">
                <div>
                    <div class="hrd-sec-title">Grafik Kehadiran &amp; Lembur ({{ $att['periode_label'] }})</div>
                    <div class="hrd-sec-sub">Periode Cut-off: {{ $att['cutoff_label'] ?? '-' }}</div>
                </div>
                <a href="{{ url('/admin/rekap-absensi-bulanan') }}" class="hrd-btn">Detail →</a>
            </div>
            <div class="hrd-body">
                @php
                    $bars = [
                        ['label' => 'Total Hadir (Hari-Orang)', 'val' => (int) $attSum['hadir'], 'unit' => 'hr', 'color' => '#059669'],
                        ['label' => 'Keterlambatan', 'val' => (int) $attSum['telat'], 'unit' => 'kali', 'color' => '#d97706'],
                        ['label' => 'Izin / Sakit / Cuti', 'val' => (int) ($attSum['izin'] + $attSum['sakit'] + $attSum['cuti']), 'unit' => 'hr', 'color' => '#2563eb'],
                        ['label' => 'Alpha & Scorsing', 'val' => (int) ($attSum['alpha'] + ($attSum['scorsing'] ?? 0)), 'unit' => 'hr', 'color' => '#dc2626'],
                        ['label' => 'Total Jam Lembur', 'val' => (float) $attSum['lembur'], 'unit' => 'jam', 'color' => '#7c3aed'],
                    ];
                @endphp
                @foreach($bars as $b)
                    <div class="hrd-bar-row">
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="font-weight:600;">{{ $b['label'] }}</span>
                            <strong style="color:{{ $b['color'] }};">{{ $b['val'] }} {{ $b['unit'] }}</strong>
                        </div>
                        <div class="hrd-bar-track">
                            <div class="hrd-bar-fill" style="width: {{ min(100, round(($b['val'] / $maxAttVal) * 100)) }}%; background: {{ $b['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Distribusi Departemen & Shift Kerja --}}
        <div class="hrd-card">
            <div class="hrd-sec-head">
                <div>
                    <div class="hrd-sec-title">Distribusi Karyawan per Departemen &amp; Shift</div>
                    <div class="hrd-sec-sub">Multi-Shift Pabrik &amp; Kantor</div>
                </div>
                <a href="{{ url('/admin/hr-shifts') }}" class="hrd-btn">Atur Shift →</a>
            </div>
            <div class="hrd-body">
                @foreach($d['by_dept'] as $dept)
                    <div class="hrd-bar-row">
                        <div style="display:flex; justify-content:space-between; font-size:12px;">
                            <span style="font-weight:600;">{{ $dept['nama'] }}</span>
                            <strong>{{ $dept['count'] }} org ({{ $dept['pct'] }}%)</strong>
                        </div>
                        <div class="hrd-bar-track">
                            <div class="hrd-bar-fill" style="width: {{ $dept['pct'] }}%; background: #2563eb;"></div>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top:14px; padding-top:12px; border-top:1px solid #e2e8f0; display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($d['by_shift'] as $sh)
                        <div style="padding:6px 10px; border-radius:8px; background:#f1f5f9; color:#0f172a; font-size:11px;">
                            <strong>{{ $sh['nama'] }}</strong> ({{ $sh['jam'] }}): <span style="color:#2563eb; font-weight:800;">{{ $sh['count'] }} org</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Alert Kontrak & Alert Kelengkapan Data --}}
    <div class="hrd-grid-2">
        {{-- Alert Kontrak Habis --}}
        <div class="hrd-card">
            <div class="hrd-sec-head">
                <div>
                    <div class="hrd-sec-title">Alert Masa Kontrak (Expired / ≤ 30 Hari)</div>
                    <div class="hrd-sec-sub">Segera proses perpanjangan PKWT atau pengangkatan PKWTT</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="hrd-table">
                    <thead>
                        <tr><th>Karyawan</th><th>Dept</th><th>Akhir Kontrak</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($d['contract_alerts'] as $ca)
                            <tr>
                                <td><a href="{{ url('/admin/karyawans/' . $ca['id'] . '/edit') }}" style="font-weight:700; color:#2563eb; text-decoration:none;">{{ $ca['nama'] }}</a> <span style="color:#64748b;">({{ $ca['nip'] }})</span></td>
                                <td>{{ $ca['departemen'] }}</td>
                                <td><strong>{{ $ca['tanggal'] }}</strong></td>
                                <td>
                                    <span class="hrd-pill {{ $ca['is_expired'] ? 'hrd-p-red' : 'hrd-p-amber' }}">
                                        {{ $ca['is_expired'] ? 'EXPIRED' : $ca['sisa_hari'] . ' hari lagi' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center; padding:18px; color:#059669; font-weight:600;">✓ Semua kontrak karyawan aman (tidak ada yang berakhir dalam 30 hari).</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Alert Data Karyawan Belum Lengkap --}}
        <div class="hrd-card">
            <div class="hrd-sec-head">
                <div>
                    <div class="hrd-sec-title">Alert Data Karyawan Belum Lengkap</div>
                    <div class="hrd-sec-sub">PIN Fingerprint, Rekening Bank Payroll, No. BPJS &amp; NIK/KTP</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="hrd-table">
                    <thead>
                        <tr><th>Karyawan</th><th>Data Belum Diisi</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($d['incomplete_profiles'], 0, 10) as $inc)
                            <tr>
                                <td><strong>{{ $inc['nama'] }}</strong><div style="font-size:10.5px; color:#64748b;">[{{ $inc['nip'] }}] {{ $inc['jabatan'] }}</div></td>
                                <td>
                                    @foreach($inc['missing'] as $m)
                                        <span class="hrd-pill hrd-p-amber">{{ $m }}</span>
                                    @endforeach
                                </td>
                                <td><a href="{{ url('/admin/karyawans/' . $inc['id'] . '/edit') }}" class="hrd-btn">Lengkapi</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; padding:18px; color:#059669; font-weight:600;">✓ Seluruh profil karyawan aktif sudah lengkap!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Row 4: Riwayat SP / Scorsing Aktif --}}
    @if(! empty($d['active_disiplins']))
        <div class="hrd-card">
            <div class="hrd-sec-head">
                <div>
                    <div class="hrd-sec-title">Catatan Kedisiplinan Aktif (SP1 / SP2 / SP3 &amp; Scorsing)</div>
                    <div class="hrd-sec-sub">Kelola detail pada tab Kedisiplinan di profil masing-masing karyawan</div>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="hrd-table">
                    <thead>
                        <tr><th>No. Surat</th><th>Karyawan</th><th>Jenis</th><th>Tanggal</th><th>Pelanggaran</th></tr>
                    </thead>
                    <tbody>
                        @foreach($d['active_disiplins'] as $sp)
                            <tr>
                                <td style="font-family:monospace; font-weight:700;">{{ $sp['nomor_surat'] }}</td>
                                <td><a href="{{ url('/admin/karyawans/' . $sp['karyawan_id'] . '/edit') }}" style="font-weight:700; color:#2563eb; text-decoration:none;">{{ $sp['nama'] }}</a></td>
                                <td><span class="hrd-pill {{ in_array($sp['jenis'], ['SP3','SCORSING']) ? 'hrd-p-red' : 'hrd-p-amber' }}">{{ $sp['jenis_label'] }}{{ $sp['hari'] > 0 ? " ({$sp['hari']} hr)" : '' }}</span></td>
                                <td>{{ $sp['tanggal'] }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($sp['pelanggaran'], 70) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</x-filament-panels::page>
