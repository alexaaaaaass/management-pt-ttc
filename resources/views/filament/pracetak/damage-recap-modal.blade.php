@php
    $fmtRp = fn (float $v): string => 'Rp ' . number_format($v, 0, ',', '.');
    $hasDamage = ($overall['total_rusak'] ?? 0) > 0;
@endphp

<div style="display:flex; flex-direction:column; gap:16px; font-size:13px;">
    {{-- 4 KPI Ringkasan Keseluruhan --}}
    <div style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:10px;">
        <div style="padding:12px; border-radius:10px; border:1px solid rgba(16,185,129,0.3); background:rgba(16,185,129,0.08);">
            <div style="font-size:11px; opacity:0.8;">Total Plate Baik</div>
            <div style="font-size:20px; font-weight:700; color:#34d399; margin-top:4px;">{{ number_format($overall['total_baik'], 0, ',', '.') }} Lbr</div>
            <div style="font-size:11px; opacity:0.7; margin-top:2px;">Dari {{ $overall['total_proses'] }} proses pra cetak</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid {{ $hasDamage ? 'rgba(239,68,68,0.35)' : 'rgba(148,163,184,0.25)' }}; background:{{ $hasDamage ? 'rgba(239,68,68,0.10)' : 'rgba(148,163,184,0.08)' }};">
            <div style="font-size:11px; opacity:0.8;">Total Plate Rusak</div>
            <div style="font-size:20px; font-weight:700; color:{{ $hasDamage ? '#f87171' : 'inherit' }}; margin-top:4px;">{{ number_format($overall['total_rusak'], 0, ',', '.') }} Lbr</div>
            <div style="font-size:11px; opacity:0.75; margin-top:2px;">Proses: {{ $overall['total_rusak_proses'] }} &middot; Arsip: {{ $overall['total_rusak_arsip'] }}</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid rgba(59,130,246,0.3); background:rgba(59,130,246,0.08);">
            <div style="font-size:11px; opacity:0.8;">Rasio Kerusakan</div>
            <div style="font-size:20px; font-weight:700; color:#60a5fa; margin-top:4px;">{{ number_format($overall['waste_rate'], 1, ',', '.') }}%</div>
            <div style="font-size:11px; opacity:0.7; margin-top:2px;">{{ $overall['proses_bermasalah'] }} proses ada plate rusak</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid {{ $hasDamage ? 'rgba(245,158,11,0.4)' : 'rgba(148,163,184,0.25)' }}; background:{{ $hasDamage ? 'rgba(245,158,11,0.12)' : 'rgba(148,163,184,0.08)' }};">
            <div style="font-size:11px; opacity:0.8;">Total Biaya Kerusakan</div>
            <div style="font-size:18px; font-weight:800; color:{{ $hasDamage ? '#fbbf24' : 'inherit' }}; margin-top:4px;">{{ $fmtRp($overall['total_damage_cost']) }}</div>
            <div style="font-size:11px; opacity:0.75; margin-top:2px;">Akumulasi seluruh proses</div>
        </div>
    </div>

    {{-- Tabel Rincian per Proses Pra Cetak --}}
    <div style="padding:12px 14px; border-radius:10px; border:1px solid rgba(148,163,184,0.22); background:rgba(148,163,184,0.05); overflow-x:auto;">
        <div style="font-weight:700; margin-bottom:8px;">Rincian Kerusakan &amp; Biaya per Proses Pra Cetak</div>

        @if(empty($overall['rows']))
            <div style="padding:16px; text-align:center; opacity:0.7;">Belum ada data Proses Pra Cetak.</div>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.25); text-align:left; opacity:0.8;">
                        <th style="padding:7px 8px;">No SPK / Ref</th>
                        <th style="padding:7px 8px;">Nama Plat</th>
                        <th style="padding:7px 8px; text-align:right;">Baik</th>
                        <th style="padding:7px 8px; text-align:right;">Rusak Proses</th>
                        <th style="padding:7px 8px; text-align:right;">Rusak Arsip</th>
                        <th style="padding:7px 8px; text-align:right;">Total Rusak</th>
                        <th style="padding:7px 8px; text-align:right;">Biaya / Plate</th>
                        <th style="padding:7px 8px; text-align:right;">Total Biaya Rusak</th>
                        <th style="padding:7px 8px;">Basis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overall['rows'] as $row)
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.14);">
                            <td style="padding:7px 8px; font-weight:600; white-space:nowrap;">{{ $row['ref'] }}</td>
                            <td style="padding:7px 8px;">{{ $row['nama_plat'] }}</td>
                            <td style="padding:7px 8px; text-align:right; color:#34d399;">{{ number_format($row['hasil_baik'], 0, ',', '.') }}</td>
                            <td style="padding:7px 8px; text-align:right;">{{ number_format($row['rusak_proses'], 0, ',', '.') }}</td>
                            <td style="padding:7px 8px; text-align:right;">{{ number_format($row['rusak_arsip'], 0, ',', '.') }}</td>
                            <td style="padding:7px 8px; text-align:right; font-weight:700; color:{{ $row['total_rusak'] > 0 ? '#f87171' : 'inherit' }};">{{ number_format($row['total_rusak'], 0, ',', '.') }} Lbr</td>
                            <td style="padding:7px 8px; text-align:right; white-space:nowrap;">{{ $fmtRp($row['cost_per_plate']) }}</td>
                            <td style="padding:7px 8px; text-align:right; font-weight:700; white-space:nowrap; color:{{ $row['total_damage_cost'] > 0 ? '#fbbf24' : 'inherit' }};">{{ $fmtRp($row['total_damage_cost']) }}</td>
                            <td style="padding:7px 8px; font-size:11px; opacity:0.8;">{{ $row['cost_source_short'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(148,163,184,0.3); font-weight:800;">
                        <td colspan="2" style="padding:8px;">TOTAL</td>
                        <td style="padding:8px; text-align:right; color:#34d399;">{{ number_format($overall['total_baik'], 0, ',', '.') }}</td>
                        <td style="padding:8px; text-align:right;">{{ number_format($overall['total_rusak_proses'], 0, ',', '.') }}</td>
                        <td style="padding:8px; text-align:right;">{{ number_format($overall['total_rusak_arsip'], 0, ',', '.') }}</td>
                        <td style="padding:8px; text-align:right; color:{{ $hasDamage ? '#f87171' : '#34d399' }};">{{ number_format($overall['total_rusak'], 0, ',', '.') }} Lbr</td>
                        <td style="padding:8px;"></td>
                        <td style="padding:8px; text-align:right; color:{{ $hasDamage ? '#fbbf24' : '#34d399' }};">{{ $fmtRp($overall['total_damage_cost']) }}</td>
                        <td style="padding:8px;"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <div style="padding:8px 12px; border-radius:8px; border:1px solid rgba(148,163,184,0.2); background:rgba(148,163,184,0.06); font-size:11px; opacity:0.85;">
        Biaya kerusakan menggunakan <strong>Biaya Aktual Remake / Pemakaian Material Plate</strong> jika sudah diposting; jika pemakaian aktual belum diposting, otomatis menggunakan proporsi <strong>Estimasi HPP Plate (OC) &divide; Jumlah Warna/Plate</strong>.
    </div>
</div>
