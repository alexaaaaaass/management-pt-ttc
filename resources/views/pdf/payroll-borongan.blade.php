<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Payroll Borongan {{ $borongan->kode_bukti }}</title>
    <style>
        @page { margin: 16px 20px; size: A4 landscape; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8px; color: #0f172a; margin: 0; padding: 0; }
        .kop { width: 100%; border-bottom: 2px solid #1f4e79; padding-bottom: 5px; margin-bottom: 8px; }
        .company { font-size: 11.5px; font-weight: bold; color: #1f4e79; }
        .sub { font-size: 7.5px; color: #475569; }
        .title { font-size: 11px; font-weight: bold; text-align: right; }
        .meta { font-size: 8px; text-align: right; color: #475569; }
        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th { background: #1f4e79; color: #fff; padding: 4px 3px; font-size: 7.3px; border: 1px solid #1f4e79; }
        table.grid td { border: 1px solid #cbd5e1; padding: 3.5px 3px; font-size: 7.6px; word-wrap: break-word; }
        .grand td { background: #1f4e79; color: #fff; font-weight: bold; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .sig { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .sig td { width: 25%; text-align: center; font-size: 8px; }
        .sig-space { height: 40px; }
    </style>
</head>
<body>
@php $fmt = fn ($v) => number_format((float) $v, 0, ',', '.'); @endphp
    <table class="kop">
        <tr>
            <td style="width: 55px;">@if(! empty($logoBase64))<img src="{{ $logoBase64 }}" style="width: 48px;">@endif</td>
            <td>
                <div class="company">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="sub">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · Daftar Upah & Tanda Terima Pekerja Borongan</div>
            </td>
            <td>
                <div class="title">LAPORAN PAYROLL BORONGAN / PART-TIME</div>
                <div class="meta">
                    No: <strong>{{ $borongan->kode_bukti }}</strong> | Periode: <strong>{{ $borongan->periode }}</strong>
                    ({{ $borongan->tanggal_mulai?->format('d/m/Y') }} s/d {{ $borongan->tanggal_selesai?->format('d/m/Y') }}) | Status: <strong>{{ strtoupper($borongan->status) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th rowspan="2" style="width:3%;">No</th>
                <th rowspan="2" style="width:15%;">Nama Pekerja / NIK</th>
                <th rowspan="2" style="width:7%;">Tanggal</th>
                <th colspan="3" style="width:24%;">Upah Borongan (Rp)</th>
                <th colspan="3" style="width:21%;">Tunjangan (Rp)</th>
                <th rowspan="2" style="width:7%;">Potongan</th>
                <th rowspan="2" style="width:10%;">Total Terima</th>
                <th rowspan="2" style="width:13%;">Paraf Penerima</th>
            </tr>
            <tr>
                <th>Protol</th>
                <th>Packing</th>
                <th>Lainnya</th>
                <th>Hadir</th>
                <th>Makan</th>
                <th>Lain</th>
            </tr>
        </thead>
        <tbody>
            @forelse($borongan->items as $i => $it)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $it->nama_pekerja }}</strong>@if($it->nik_pekerja)<br><span style="color:#64748b;font-size:6.8px;">{{ $it->nik_pekerja }}</span>@endif</td>
                    <td class="text-center">{{ $it->tanggal_kerja?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-right">{{ $fmt($it->protol_gaji) }}@if($it->protol_qty > 0)<br><span style="color:#64748b;font-size:6.5px;">({{ $fmt($it->protol_qty) }} u)</span>@endif</td>
                    <td class="text-right">{{ $fmt($it->packing_gaji) }}@if($it->packing_qty > 0)<br><span style="color:#64748b;font-size:6.5px;">({{ $fmt($it->packing_qty) }} u)</span>@endif</td>
                    <td class="text-right">{{ $fmt($it->lain_gaji) }}</td>
                    <td class="text-right">{{ $fmt($it->tunj_hadir) }}</td>
                    <td class="text-right">{{ $fmt($it->tunj_makan) }}</td>
                    <td class="text-right">{{ $fmt($it->tunj_lain) }}</td>
                    <td class="text-right">{{ $fmt($it->potongan) }}</td>
                    <td class="text-right" style="font-weight:bold;color:#065f46;">Rp {{ $fmt($it->total) }}</td>
                    <td style="height:22px;"></td>
                </tr>
            @empty
                <tr><td colspan="12" class="text-center" style="padding:10px;">Belum ada baris pekerja.</td></tr>
            @endforelse
            <tr class="grand">
                <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('protol_gaji')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('packing_gaji')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('lain_gaji')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('tunj_hadir')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('tunj_makan')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('tunj_lain')) }}</td>
                <td class="text-right">{{ $fmt($borongan->items->sum('potongan')) }}</td>
                <td class="text-right">Rp {{ $fmt($borongan->grand_total) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:6px;font-size:8px;">
        <strong>Terbilang:</strong> <em>{{ \App\Support\MoneyHelper::terbilang($borongan->grand_total) }}</em>
        @if($borongan->keterangan) | <strong>Catatan:</strong> {{ $borongan->keterangan }} @endif
    </div>

    <table class="sig">
        <tr><td>Disusun oleh,</td><td>Diperiksa oleh,</td><td>Mengetahui,</td><td>Disetujui oleh,</td></tr>
        <tr><td class="sig-space"></td><td class="sig-space"></td><td class="sig-space"></td><td class="sig-space"></td></tr>
        <tr style="font-weight:bold;"><td>Bagian Keuangan & HRD</td><td>Kepala Produksi</td><td>Direktur</td><td>Owner</td></tr>
    </table>
</body>
</html>
