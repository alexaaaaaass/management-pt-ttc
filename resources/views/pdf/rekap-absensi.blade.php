<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ $recap['periode_label'] }}</title>
    <style>
        @page { margin: 16px 20px; size: A4 landscape; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.2px; color: #0f172a; margin: 0; padding: 0; }
        .kop { width: 100%; border-bottom: 2px solid #1f4e79; padding-bottom: 5px; margin-bottom: 8px; }
        .company { font-size: 11.5px; font-weight: bold; color: #1f4e79; }
        .sub { font-size: 7.5px; color: #475569; }
        .title { font-size: 11px; font-weight: bold; text-align: right; }
        .meta { font-size: 8px; text-align: right; color: #475569; }
        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th { background: #1f4e79; color: #fff; padding: 4px 3px; font-size: 7.5px; border: 1px solid #1f4e79; }
        table.grid td { border: 1px solid #cbd5e1; padding: 3.5px 4px; font-size: 7.8px; }
        .text-center { text-align: center; }
        .grand td { background: #1f4e79; color: #fff; font-weight: bold; }
    </style>
</head>
<body>
    <table class="kop">
        <tr>
            <td style="width: 55px;">@if(! empty($logoBase64))<img src="{{ $logoBase64 }}" style="width: 48px;">@endif</td>
            <td>
                <div class="company">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="sub">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · Rekapitulasi Kehadiran & Lembur</div>
            </td>
            <td>
                <div class="title">LAPORAN REKAP ABSENSI BULANAN</div>
                <div class="meta">Periode: <strong>{{ $recap['periode_label'] }}</strong> ({{ $recap['cutoff_label'] ?? '-' }}) | Hari Kerja Efektif: <strong>{{ $recap['hari_kerja'] }} Hari</strong></div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:19%;">Nama Karyawan</th>
                <th style="width:8%;">NIP</th>
                <th style="width:14%;">Dept / Shift</th>
                <th style="width:7%;">Skema</th>
                <th style="width:5%;">H. Kerja</th>
                <th style="width:5%;">Hadir</th>
                <th style="width:5%;">Telat</th>
                <th style="width:5%;">P. Cepat</th>
                <th style="width:4%;">Izin</th>
                <th style="width:4%;">Sakit</th>
                <th style="width:4%;">Cuti</th>
                <th style="width:5%;">Alpha</th>
                <th style="width:5%;">Scors.</th>
                <th style="width:7%;">Lembur (Jm)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recap['rows'] as $i => $r)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $r['nama'] }}</strong></td>
                    <td>{{ $r['nip'] ?: '-' }}</td>
                    <td>{{ $r['departemen'] }}<div style="font-size:6.8px;color:#64748b;">{{ $r['shift'] ?? 'Default' }}</div></td>
                    <td class="text-center">{{ $r['tipe_gaji'] }}</td>
                    <td class="text-center">{{ $r['hari_kerja'] }}</td>
                    <td class="text-center" style="font-weight:bold;color:#047857;">{{ $r['hadir'] }}</td>
                    <td class="text-center">{{ $r['telat'] }}</td>
                    <td class="text-center">{{ $r['pulang_cepat'] }}</td>
                    <td class="text-center">{{ $r['izin'] }}</td>
                    <td class="text-center">{{ $r['sakit'] }}</td>
                    <td class="text-center">{{ $r['cuti'] }}</td>
                    <td class="text-center" style="color:#b91c1c;font-weight:bold;">{{ $r['alpha'] }}</td>
                    <td class="text-center" style="color:#be123c;font-weight:bold;">{{ $r['scorsing'] ?? 0 }}</td>
                    <td class="text-center">{{ $r['lembur_jam'] }}</td>
                </tr>
            @empty
                <tr><td colspan="15" class="text-center" style="padding:12px;">Belum ada data karyawan aktif.</td></tr>
            @endforelse
            <tr class="grand">
                <td colspan="6" class="text-center">TOTAL ({{ $recap['summary']['karyawan'] }} Karyawan)</td>
                <td class="text-center">{{ $recap['summary']['hadir'] }}</td>
                <td class="text-center">{{ $recap['summary']['telat'] }}</td>
                <td class="text-center">-</td>
                <td class="text-center">{{ $recap['summary']['izin'] }}</td>
                <td class="text-center">{{ $recap['summary']['sakit'] }}</td>
                <td class="text-center">{{ $recap['summary']['cuti'] }}</td>
                <td class="text-center">{{ $recap['summary']['alpha'] }}</td>
                <td class="text-center">{{ $recap['summary']['scorsing'] ?? 0 }}</td>
                <td class="text-center">{{ $recap['summary']['lembur'] }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
