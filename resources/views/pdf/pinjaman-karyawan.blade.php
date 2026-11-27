<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $mode === 'single' ? 'Form Pinjaman ' . $pinjaman->nomor_bukti : 'Rekap Pinjaman Karyawan' }}</title>
    <style>
        @page { margin: 18px 24px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.8px; color: #0f172a; margin: 0; padding: 0; }
        .kop { width: 100%; border-bottom: 2px solid #1f4e79; padding-bottom: 5px; margin-bottom: 10px; }
        .company { font-size: 11.5px; font-weight: bold; color: #1f4e79; }
        .sub { font-size: 7.8px; color: #475569; }
        .title { font-size: 11.5px; font-weight: bold; text-align: right; }
        table.grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.grid th { background: #1f4e79; color: #fff; padding: 4px 5px; font-size: 8px; border: 1px solid #1f4e79; }
        table.grid td { border: 1px solid #cbd5e1; padding: 4px 5px; font-size: 8.3px; }
        .lbl { background: #f1f5f9; font-weight: bold; width: 25%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .sig { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .sig td { width: 33.3%; text-align: center; font-size: 8.5px; }
        .sig-space { height: 48px; }
    </style>
</head>
<body>
@php $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
    <table class="kop">
        <tr>
            <td style="width: 55px;">@if(! empty($logoBase64))<img src="{{ $logoBase64 }}" style="width: 48px;">@endif</td>
            <td>
                <div class="company">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="sub">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · +62812 3339 0799</div>
            </td>
            <td>
                <div class="title">{{ $mode === 'single' ? 'FORMULIR PINJAMAN KARYAWAN' : 'REKAP PINJAMAN KARYAWAN' }}</div>
                <div style="text-align:right;font-size:8px;color:#475569;">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

@if($mode === 'single')
    @php
        $terbayar = (float) $pinjaman->pembayaran->sum('nominal_pembayaran');
        $sisa = max(0, (float) $pinjaman->nilai_pinjaman - $terbayar);
    @endphp
    <table class="grid">
        <tr><td class="lbl">Nomor Bukti</td><td><strong>{{ $pinjaman->nomor_bukti }}</strong></td><td class="lbl">Tanggal Pengajuan</td><td>{{ $pinjaman->tanggal_pengajuan?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><td class="lbl">Nama Karyawan</td><td><strong>{{ $pinjaman->karyawan?->nama ?? '-' }}</strong></td><td class="lbl">NIP / Dept</td><td>{{ $pinjaman->karyawan?->nip ?: '-' }} / {{ $pinjaman->karyawan?->departemen?->nama_departemen ?: '-' }}</td></tr>
        <tr><td class="lbl">Nilai Pinjaman</td><td><strong>{{ $fmt($pinjaman->nilai_pinjaman) }}</strong></td><td class="lbl">Tenor / Cicilan</td><td>{{ $pinjaman->jangka_waktu }} Bulan ({{ $fmt($pinjaman->cicilan_per_bulan) }}/bln)</td></tr>
        <tr><td class="lbl">Total Terbayar</td><td>{{ $fmt($terbayar) }}</td><td class="lbl">Sisa Pinjaman</td><td style="font-weight:bold;color:{{ $sisa > 0 ? '#b91c1c' : '#047857' }};">{{ $fmt($sisa) }}</td></tr>
        <tr><td class="lbl">Keperluan</td><td colspan="3">{{ $pinjaman->keperluan_pinjaman ?: '-' }}</td></tr>
    </table>

    <div style="font-weight:bold;margin:8px 0 4px 0;">Riwayat Pembayaran Cicilan</div>
    <table class="grid">
        <thead>
            <tr><th style="width:10%;">Tahap</th><th style="width:20%;">No. Bukti</th><th style="width:16%;">Tanggal</th><th style="width:22%;">Nominal</th><th style="width:32%;">Keterangan</th></tr>
        </thead>
        <tbody>
            @forelse($pinjaman->pembayaran as $p)
                <tr>
                    <td class="text-center">Ke-{{ $p->tahap_cicilan }}</td>
                    <td>{{ $p->no_bukti }}</td>
                    <td class="text-center">{{ $p->tanggal_pembayaran?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-right">{{ $fmt($p->nominal_pembayaran) }}</td>
                    <td>{{ $p->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada pembayaran cicilan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="sig">
        <tr><td>Pemohon (Karyawan),</td><td>Diperiksa (HRD & Keuangan),</td><td>Disetujui (Owner),</td></tr>
        <tr><td class="sig-space"></td><td class="sig-space"></td><td class="sig-space"></td></tr>
        <tr style="font-weight:bold;"><td>( {{ $pinjaman->karyawan?->nama ?? '-' }} )</td><td>( Bagian Keuangan )</td><td>( Owner )</td></tr>
    </table>
@else
    <table class="grid">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:14%;">No. Bukti</th>
                <th style="width:20%;">Nama Karyawan</th>
                <th style="width:10%;">Tgl Pengajuan</th>
                <th style="width:8%;">Tenor</th>
                <th style="width:13%;">Nilai Pinjaman</th>
                <th style="width:13%;">Total Terbayar</th>
                <th style="width:13%;">Sisa Pinjaman</th>
                <th style="width:9%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totPinj = 0; $totBayar = 0; $totSisa = 0; @endphp
            @forelse($rows as $i => $item)
                @php
                    $byr = (float) $item->pembayaran->sum('nominal_pembayaran');
                    $ss = max(0, (float) $item->nilai_pinjaman - $byr);
                    $totPinj += (float) $item->nilai_pinjaman;
                    $totBayar += $byr;
                    $totSisa += $ss;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->nomor_bukti }}</td>
                    <td><strong>{{ $item->karyawan?->nama ?? '-' }}</strong></td>
                    <td class="text-center">{{ $item->tanggal_pengajuan?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-center">{{ $item->jangka_waktu }} Bln</td>
                    <td class="text-right">{{ $fmt($item->nilai_pinjaman) }}</td>
                    <td class="text-right">{{ $fmt($byr) }}</td>
                    <td class="text-right" style="font-weight:bold;color:{{ $ss > 0 ? '#b91c1c' : '#047857' }};">{{ $fmt($ss) }}</td>
                    <td class="text-center">{{ $ss <= 0 ? 'Lunas' : 'Belum Lunas' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Belum ada data pinjaman karyawan.</td></tr>
            @endforelse
            <tr style="background:#1f4e79;color:#fff;font-weight:bold;">
                <td colspan="5" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right">{{ $fmt($totPinj) }}</td>
                <td class="text-right">{{ $fmt($totBayar) }}</td>
                <td class="text-right">{{ $fmt($totSisa) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
@endif
</body>
</html>
