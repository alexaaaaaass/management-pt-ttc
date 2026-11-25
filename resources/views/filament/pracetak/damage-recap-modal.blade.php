@php
    $fmtRp = fn (float $v): string => 'Rp ' . number_format($v, 0, ',', '.');
    $hasDamage = ($overall['total_rusak'] ?? 0) > 0;
@endphp

<div style="display:flex; flex-direction:column; gap:18px; font-size:13px; font-variant-numeric:tabular-nums;">
    {{-- 4 Minimalist Summary Metrics --}}
    <div style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px;">
        <div style="padding:14px 16px; border-radius:12px; border:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Total Plate Baik</div>
            <div style="font-size:20px; font-weight:600; letter-spacing:-0.02em; margin-top:6px;">{{ number_format($overall['total_baik'], 0, ',', '.') }} <span style="font-size:12px; font-weight:400; opacity:0.65;">lbr</span></div>
            <div style="font-size:11px; opacity:0.58; margin-top:3px;">{{ $overall['total_proses'] }} proses pra cetak</div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid {{ $hasDamage ? 'rgba(248,113,113,0.28)' : 'rgba(148,163,184,0.16)' }}; background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Total Plate Rusak</div>
            <div style="font-size:20px; font-weight:600; letter-spacing:-0.02em; color:{{ $hasDamage ? '#f87171' : 'inherit' }}; margin-top:6px;">{{ number_format($overall['total_rusak'], 0, ',', '.') }} <span style="font-size:12px; font-weight:400; opacity:0.7;">lbr</span></div>
            <div style="font-size:11px; opacity:0.58; margin-top:3px;">Proses {{ $overall['total_rusak_proses'] }} &middot; Arsip {{ $overall['total_rusak_arsip'] }}</div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Rasio Kerusakan</div>
            <div style="font-size:20px; font-weight:600; letter-spacing:-0.02em; margin-top:6px;">{{ number_format($overall['waste_rate'], 1, ',', '.') }}%</div>
            <div style="font-size:11px; opacity:0.58; margin-top:3px;">{{ $overall['proses_bermasalah'] }} proses terdampak</div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid {{ $hasDamage ? 'rgba(251,191,36,0.32)' : 'rgba(148,163,184,0.16)' }}; background:{{ $hasDamage ? 'rgba(251,191,36,0.06)' : 'rgba(148,163,184,0.04)' }};">
            <div style="font-size:11px; opacity:0.65;">Total Biaya Rusak</div>
            <div style="font-size:17px; font-weight:700; letter-spacing:-0.02em; color:{{ $hasDamage ? '#fbbf24' : 'inherit' }}; margin-top:7px;">{{ $fmtRp($overall['total_damage_cost']) }}</div>
            <div style="font-size:11px; opacity:0.58; margin-top:3px;">Akumulasi keseluruhan</div>
        </div>
    </div>

    {{-- Clean Minimalist Table --}}
    <div style="border-radius:12px; border:1px solid rgba(148,163,184,0.16); overflow-x:auto;">
        @if(empty($overall['rows']))
            <div style="padding:24px; text-align:center; opacity:0.6;">Belum ada data Proses Pra Cetak.</div>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:12.5px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.05); text-align:left;">
                        <th style="padding:10px 14px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65;">SPK &amp; Plat</th>
                        <th style="padding:10px 12px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Baik</th>
                        <th style="padding:10px 12px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Rusak</th>
                        <th style="padding:10px 12px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Biaya / Lbr</th>
                        <th style="padding:10px 14px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Total Biaya Rusak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($overall['rows'] as $row)
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.10);">
                            <td style="padding:11px 14px;">
                                <div style="font-weight:600;">{{ $row['ref'] }}</div>
                                <div style="font-size:11.5px; opacity:0.65; margin-top:2px;">{{ $row['nama_plat'] }} &middot; {{ $row['cost_source_short'] }}</div>
                            </td>
                            <td style="padding:11px 12px; text-align:right; opacity:0.85;">{{ number_format($row['hasil_baik'], 0, ',', '.') }}</td>
                            <td style="padding:11px 12px; text-align:right; font-weight:600; color:{{ $row['total_rusak'] > 0 ? '#f87171' : 'inherit' }};">
                                {{ number_format($row['total_rusak'], 0, ',', '.') }} lbr
                            </td>
                            <td style="padding:11px 12px; text-align:right; opacity:0.8; white-space:nowrap;">{{ $fmtRp($row['cost_per_plate']) }}</td>
                            <td style="padding:11px 14px; text-align:right; font-weight:600; white-space:nowrap; color:{{ $row['total_damage_cost'] > 0 ? '#fbbf24' : 'inherit' }};">{{ $fmtRp($row['total_damage_cost']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top:1px solid rgba(148,163,184,0.22); background:rgba(148,163,184,0.04); font-weight:700;">
                        <td style="padding:11px 14px;">Total</td>
                        <td style="padding:11px 12px; text-align:right;">{{ number_format($overall['total_baik'], 0, ',', '.') }}</td>
                        <td style="padding:11px 12px; text-align:right; color:{{ $hasDamage ? '#f87171' : '#34d399' }};">{{ number_format($overall['total_rusak'], 0, ',', '.') }} lbr</td>
                        <td style="padding:11px 12px;"></td>
                        <td style="padding:11px 14px; text-align:right; color:{{ $hasDamage ? '#fbbf24' : '#34d399' }};">{{ $fmtRp($overall['total_damage_cost']) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <div style="font-size:11px; opacity:0.6; line-height:1.5;">
        Biaya kerusakan menggunakan <strong>Biaya Aktual Remake / Material Plate</strong> bila sudah diposting, atau proporsi <strong>Estimasi HPP Plate (OC) &divide; Jumlah Warna</strong> secara otomatis.
    </div>
</div>
