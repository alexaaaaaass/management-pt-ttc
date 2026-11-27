<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Payroll {{ $periode }} - PT Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 16px 20px; size: A4 landscape; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7.8px; color: #0f172a; margin: 0; padding: 0; }
        .kop { width: 100%; border-bottom: 2px solid #1f4e79; padding-bottom: 5px; margin-bottom: 8px; }
        .company { font-size: 12px; font-weight: bold; color: #1f4e79; letter-spacing: 0.3px; }
        .sub { font-size: 7.5px; color: #475569; margin-top: 2px; }
        .title { font-size: 11.5px; font-weight: bold; text-align: right; color: #1e3a8a; }
        .meta { font-size: 8px; text-align: right; color: #475569; margin-top: 2px; }
        
        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th { background: #1f4e79; color: #ffffff; padding: 4px 3px; font-size: 7.2px; border: 1px solid #1f4e79; text-align: center; }
        table.grid td { border: 1px solid #cbd5e1; padding: 3px 3px; font-size: 7.4px; word-wrap: break-word; }
        
        .grand-row td { background: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 7.8px; border: 1px solid #1e3a8a; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .sig-table td { width: 33.3%; text-align: center; font-size: 8px; vertical-align: top; }
        .sig-space { height: 44px; position: relative; }
        .stamp-approved {
            display: inline-block;
            border: 2px solid #dc2626;
            color: #dc2626;
            font-size: 8.5px;
            font-weight: bold;
            padding: 2px 7px;
            border-radius: 3px;
            transform: rotate(-7deg);
            margin-top: 8px;
        }
    </style>
</head>
<body>
@php $fmt = fn ($v) => number_format((float) $v, 0, ',', '.'); @endphp
    <table class="kop">
        <tr>
            <td style="width: 55px; vertical-align: middle;">
                @if(! empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 48px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="sub">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · Lampiran Rekapitulasi Penggajian Karyawan</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="title">REKAPITULASI PAYROLL KARYAWAN</div>
                <div class="meta">Periode: <strong>{{ $periode }}</strong> | Skema: <strong>{{ $tipe ?: 'Semua' }}</strong> | Total: <strong>{{ $rows->count() }} Orang</strong></div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width: 2.5%;">No</th>
                <th style="width: 5%;">Kode</th>
                <th style="width: 14%;">Nama Karyawan</th>
                <th style="width: 9%;">Jabatan / Dept</th>
                <th style="width: 7.5%;">Gaji Pokok</th>
                <th style="width: 6.5%;">T. Jabatan</th>
                <th style="width: 6.5%;">T. Komp.</th>
                <th style="width: 6.5%;">Insentif</th>
                <th style="width: 5.5%;">Lembur</th>
                <th style="width: 5%;">Bonus</th>
                <th style="width: 8%;">Pendapatan</th>
                <th style="width: 6%;">Pot. Absen</th>
                <th style="width: 6%;">Kasbon</th>
                <th style="width: 5.5%;">BPJS</th>
                <th style="width: 6.5%;">Total Pot.</th>
                <th style="width: 8.5%;">THP (Bersih)</th>
                <th style="width: 5%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
                @php
                    $potAbsenTelat = (float)$r->potongan_tunjangan + (float)$r->potongan_kehadiran;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center" style="font-weight:bold; color:#1e3a8a;">{{ $r->karyawan?->nip ?: $r->kode_slip }}</td>
                    <td><strong>{{ $r->karyawan?->nama ?? '-' }}</strong></td>
                    <td>{{ $r->karyawan?->jabatan ?: '-' }}<br><span style="color:#64748b; font-size:6.5px;">{{ $r->karyawan?->departemen?->nama_departemen }}</span></td>
                    <td class="text-right">{{ $fmt($r->gaji_pokok) }}</td>
                    <td class="text-right">{{ $fmt($r->tunjangan_jabatan) }}</td>
                    <td class="text-right">{{ $fmt($r->tunjangan_kompetensi) }}</td>
                    <td class="text-right">{{ $fmt($r->tunjangan_intensif) }}</td>
                    <td class="text-right">{{ $fmt($r->total_lembur) }}</td>
                    <td class="text-right">{{ $fmt($r->total_bonus) }}</td>
                    <td class="text-right" style="font-weight:bold;">{{ $fmt($r->total_pendapatan) }}</td>
                    <td class="text-right" style="color: {{ $potAbsenTelat > 0 ? '#991b1b' : '#0f172a' }};">{{ $fmt($potAbsenTelat) }}</td>
                    <td class="text-right" style="color: {{ (float)$r->potongan_pinjaman > 0 ? '#b45309' : '#0f172a' }};">{{ $fmt($r->potongan_pinjaman) }}</td>
                    <td class="text-right">{{ $fmt($r->potongan_bpjs) }}</td>
                    <td class="text-right" style="color:#991b1b;">{{ $fmt($r->total_potongan) }}</td>
                    <td class="text-right" style="font-weight:bold; color:#065f46; background:#f0fdf4;">{{ $fmt($r->gaji_bersih) }}</td>
                    <td class="text-center">
                        <span style="font-weight:bold; color: {{ $r->status === 'Paid' ? '#059669' : ($r->status === 'Approved' ? '#2563eb' : '#d97706') }};">
                            {{ $r->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="17" class="text-center" style="padding:14px;">Belum ada data payroll untuk periode {{ $periode }}.</td></tr>
            @endforelse
            <tr class="grand-row">
                <td colspan="4" class="text-center">TOTAL KESELURUHAN ({{ $rows->count() }} ORANG)</td>
                <td class="text-right">{{ $fmt($rows->sum('gaji_pokok')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('tunjangan_jabatan')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('tunjangan_kompetensi')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('tunjangan_intensif')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('total_lembur')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('total_bonus')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('total_pendapatan')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum(fn($r) => $r->potongan_tunjangan + $r->potongan_kehadiran)) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('potongan_pinjaman')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('potongan_bpjs')) }}</td>
                <td class="text-right">{{ $fmt($rows->sum('total_potongan')) }}</td>
                <td class="text-right" style="color:#a7f3d0;">{{ $fmt($rows->sum('gaji_bersih')) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN 3 KOLOM BERSIH (TANPA KEPALA) --}}
    <table class="sig-table">
        <tr>
            <td>Disusun oleh,</td>
            <td>Diajukan kepada,</td>
            <td>Disetujui oleh,</td>
        </tr>
        <tr>
            <td class="sig-space">&nbsp;</td>
            <td class="sig-space">&nbsp;</td>
            <td class="sig-space">
                @if($rows->every(fn($r) => in_array($r->status, ['Approved', 'Paid'])))
                    <div class="stamp-approved">APPROVED</div>
                @endif
            </td>
        </tr>
        <tr class="text-bold">
            <td>Bagian Keuangan & HRD</td>
            <td>Direktur</td>
            <td>Owner</td>
        </tr>
    </table>
</body>
</html>
