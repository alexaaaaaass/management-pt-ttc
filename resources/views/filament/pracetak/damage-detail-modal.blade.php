@php
    $fmtRp = fn (float $v): string => 'Rp ' . number_format($v, 0, ',', '.');
    $hasDamage = ($summary['total_rusak'] ?? 0) > 0;
@endphp

<div style="display:flex; flex-direction:column; gap:18px; font-size:13px; font-variant-numeric:tabular-nums;">
    {{-- Identity Strip --}}
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; padding-bottom:14px; border-bottom:1px solid rgba(148,163,184,0.16);">
        <div>
            <div style="font-size:15px; font-weight:600; letter-spacing:-0.01em;">{{ $process->nama_plat ?: '-' }}</div>
            <div style="font-size:12px; opacity:0.68; margin-top:3px;">
                {{ $process->spk?->no_spk ?: ('PRE-' . ($process->approvalForm?->form_no ?: $process->id)) }}
                &nbsp;&middot;&nbsp; {{ (int) ($process->jumlah_warna_plate ?: 0) }} Warna
                &nbsp;&middot;&nbsp; {{ $process->lokasi_simpan_plate ?: 'Belum di rak' }}
            </div>
        </div>
        <div style="display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:999px; font-size:11px; font-weight:600; border:1px solid {{ $hasDamage ? 'rgba(248,113,113,0.32)' : 'rgba(52,211,153,0.32)' }}; background:{{ $hasDamage ? 'rgba(248,113,113,0.08)' : 'rgba(52,211,153,0.08)' }}; color:{{ $hasDamage ? '#f87171' : '#34d399' }};">
            <span style="width:6px; height:6px; border-radius:999px; background:currentColor;"></span>
            {{ $hasDamage ? $summary['total_rusak'] . ' Plate Rusak (' . number_format($summary['waste_rate'], 1, ',', '.') . '%)' : 'Nol Kerusakan' }}
        </div>
    </div>

    {{-- Minimalist Metric Grid --}}
    <div style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px;">
        <div style="padding:14px 16px; border-radius:12px; border:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Plate Baik</div>
            <div style="font-size:20px; font-weight:600; letter-spacing:-0.02em; margin-top:6px;">{{ number_format($summary['hasil_baik'], 0, ',', '.') }} <span style="font-size:12px; font-weight:400; opacity:0.65;">lbr</span></div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid {{ $hasDamage ? 'rgba(248,113,113,0.28)' : 'rgba(148,163,184,0.16)' }}; background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Total Rusak</div>
            <div style="font-size:20px; font-weight:600; letter-spacing:-0.02em; color:{{ $hasDamage ? '#f87171' : 'inherit' }}; margin-top:6px;">{{ number_format($summary['total_rusak'], 0, ',', '.') }} <span style="font-size:12px; font-weight:400; opacity:0.7;">lbr</span></div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.04);">
            <div style="font-size:11px; opacity:0.65;">Biaya / Lembar</div>
            <div style="font-size:17px; font-weight:600; letter-spacing:-0.02em; margin-top:7px;">{{ $fmtRp($summary['cost_per_plate']) }}</div>
        </div>

        <div style="padding:14px 16px; border-radius:12px; border:1px solid {{ $hasDamage ? 'rgba(251,191,36,0.32)' : 'rgba(148,163,184,0.16)' }}; background:{{ $hasDamage ? 'rgba(251,191,36,0.06)' : 'rgba(148,163,184,0.04)' }};">
            <div style="font-size:11px; opacity:0.65;">Total Biaya Rusak</div>
            <div style="font-size:17px; font-weight:700; letter-spacing:-0.02em; color:{{ $hasDamage ? '#fbbf24' : 'inherit' }}; margin-top:7px;">{{ $fmtRp($summary['total_damage_cost']) }}</div>
        </div>
    </div>

    {{-- Clean Breakdown Table --}}
    <div style="border-radius:12px; border:1px solid rgba(148,163,184,0.16); overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:12.5px;">
            <thead>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.16); background:rgba(148,163,184,0.05); text-align:left;">
                    <th style="padding:10px 14px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65;">Sumber Kerusakan</th>
                    <th style="padding:10px 14px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Qty</th>
                    <th style="padding:10px 14px; font-weight:500; font-size:11px; text-transform:uppercase; letter-spacing:0.04em; opacity:0.65; text-align:right;">Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.10);">
                    <td style="padding:11px 14px;">Plate Making (Pra Cetak)</td>
                    <td style="padding:11px 14px; text-align:right;">{{ number_format($summary['rusak_proses'], 0, ',', '.') }} lbr</td>
                    <td style="padding:11px 14px; text-align:right;">{{ $fmtRp($summary['biaya_rusak_proses']) }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.10);">
                    <td style="padding:11px 14px;">Arsip Rak / Printing <span style="opacity:0.6;">({{ $summary['archive_kode'] ?: 'Belum diarsip' }})</span></td>
                    <td style="padding:11px 14px; text-align:right;">{{ number_format($summary['rusak_arsip'], 0, ',', '.') }} lbr</td>
                    <td style="padding:11px 14px; text-align:right;">{{ $fmtRp($summary['biaya_rusak_arsip']) }}</td>
                </tr>
                <tr style="background:rgba(148,163,184,0.04);">
                    <td style="padding:11px 14px; font-weight:600;">Total Kerusakan</td>
                    <td style="padding:11px 14px; text-align:right; font-weight:600; color:{{ $hasDamage ? '#f87171' : '#34d399' }};">{{ number_format($summary['total_rusak'], 0, ',', '.') }} lbr</td>
                    <td style="padding:11px 14px; text-align:right; font-weight:700; color:{{ $hasDamage ? '#fbbf24' : '#34d399' }};">{{ $fmtRp($summary['total_damage_cost']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(! empty($summary['catatan_kerusakan']))
        <div style="padding:10px 14px; border-radius:10px; border:1px solid rgba(148,163,184,0.18); background:rgba(148,163,184,0.04); font-size:12px;">
            <span style="opacity:0.65;">Catatan:</span> {{ $summary['catatan_kerusakan'] }}
        </div>
    @endif

    @if(! empty($summary['remake_usages']))
        <div style="border-radius:12px; border:1px solid rgba(148,163,184,0.16); overflow:hidden;">
            <div style="padding:10px 14px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; opacity:0.7; border-bottom:1px solid rgba(148,163,184,0.14); background:rgba(148,163,184,0.05);">
                Log Material Remake
            </div>
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <tbody>
                    @foreach($summary['remake_usages'] as $u)
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.10);">
                            <td style="padding:9px 14px;">{{ $u['material'] }}</td>
                            <td style="padding:9px 14px; text-align:right; opacity:0.8;">{{ $u['jumlah_plate'] }} lbr &middot; {{ number_format($u['qty_sheet'], 2, ',', '.') }} sh</td>
                            <td style="padding:9px 14px; opacity:0.75;">{{ $u['alasan'] ?: '-' }}</td>
                            <td style="padding:9px 14px; text-align:right; font-weight:600;">{{ $fmtRp($u['total_biaya']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Subtle Footnote --}}
    <div style="font-size:11px; opacity:0.62; line-height:1.5;">
        Basis hitung: <strong>{{ $summary['cost_source_label'] }}</strong> &mdash; {{ $summary['formula_note'] }}
    </div>
</div>
