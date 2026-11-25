@php
    $fmtRp = fn (float $v): string => 'Rp ' . number_format($v, 0, ',', '.');
    $hasDamage = ($summary['total_rusak'] ?? 0) > 0;
@endphp

<div style="display:flex; flex-direction:column; gap:16px; font-size:13px;">
    {{-- Header identitas proses --}}
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:8px; padding:12px 14px; border-radius:10px; border:1px solid rgba(148,163,184,0.22); background:rgba(148,163,184,0.08);">
        <div>
            <div style="font-size:11px; opacity:0.7; text-transform:uppercase; letter-spacing:0.04em;">Proses Pra Cetak</div>
            <div style="font-weight:700; font-size:15px; margin-top:2px;">{{ $process->nama_plat ?: '-' }}</div>
            <div style="font-size:12px; opacity:0.8; margin-top:2px;">
                SPK: <strong>{{ $process->spk?->no_spk ?: ('PRE-' . ($process->approvalForm?->form_no ?: $process->id)) }}</strong>
                &middot; Warna: <strong>{{ (int) ($process->jumlah_warna_plate ?: 0) }} Warna</strong>
                &middot; Rak: <strong>{{ $process->lokasi_simpan_plate ?: '-' }}</strong>
            </div>
        </div>
        <div style="text-align:right;">
            <span style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; {{ $hasDamage ? 'background:rgba(239,68,68,0.16); color:#f87171; border:1px solid rgba(239,68,68,0.35);' : 'background:rgba(16,185,129,0.16); color:#34d399; border:1px solid rgba(16,185,129,0.35);' }}">
                {{ $hasDamage ? 'Ada Kerusakan Plate' : 'Nol Kerusakan (0 Lbr)' }}
            </span>
            <div style="font-size:11px; opacity:0.75; margin-top:6px;">
                Basis Hitung: <strong>{{ $summary['cost_source_label'] }}</strong>
            </div>
        </div>
    </div>

    {{-- 4 KPI Cards --}}
    <div style="display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:10px;">
        <div style="padding:12px; border-radius:10px; border:1px solid rgba(16,185,129,0.3); background:rgba(16,185,129,0.08);">
            <div style="font-size:11px; opacity:0.8;">Plate Baik</div>
            <div style="font-size:20px; font-weight:700; color:#34d399; margin-top:4px;">{{ number_format($summary['hasil_baik'], 0, ',', '.') }} Lbr</div>
            <div style="font-size:11px; opacity:0.7; margin-top:2px;">Target warna: {{ $summary['jumlah_warna'] }}</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid {{ $hasDamage ? 'rgba(239,68,68,0.35)' : 'rgba(148,163,184,0.25)' }}; background:{{ $hasDamage ? 'rgba(239,68,68,0.10)' : 'rgba(148,163,184,0.08)' }};">
            <div style="font-size:11px; opacity:0.8;">Total Plate Rusak</div>
            <div style="font-size:20px; font-weight:700; color:{{ $hasDamage ? '#f87171' : 'inherit' }}; margin-top:4px;">{{ number_format($summary['total_rusak'], 0, ',', '.') }} Lbr</div>
            <div style="font-size:11px; opacity:0.75; margin-top:2px;">Rasio rusak: {{ number_format($summary['waste_rate'], 1, ',', '.') }}%</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid rgba(59,130,246,0.3); background:rgba(59,130,246,0.08);">
            <div style="font-size:11px; opacity:0.8;">Biaya / Lembar Plate</div>
            <div style="font-size:17px; font-weight:700; color:#60a5fa; margin-top:4px;">{{ $fmtRp($summary['cost_per_plate']) }}</div>
            <div style="font-size:11px; opacity:0.7; margin-top:2px;">{{ $summary['cost_source_short'] }}</div>
        </div>

        <div style="padding:12px; border-radius:10px; border:1px solid {{ $hasDamage ? 'rgba(245,158,11,0.4)' : 'rgba(148,163,184,0.25)' }}; background:{{ $hasDamage ? 'rgba(245,158,11,0.12)' : 'rgba(148,163,184,0.08)' }};">
            <div style="font-size:11px; opacity:0.8;">Total Biaya Kerusakan</div>
            <div style="font-size:17px; font-weight:800; color:{{ $hasDamage ? '#fbbf24' : 'inherit' }}; margin-top:4px;">{{ $fmtRp($summary['total_damage_cost']) }}</div>
            <div style="font-size:11px; opacity:0.75; margin-top:2px;">{{ $summary['total_rusak'] }} lbr rusak</div>
        </div>
    </div>

    {{-- Breakdown sumber kerusakan --}}
    <div style="padding:12px 14px; border-radius:10px; border:1px solid rgba(148,163,184,0.22); background:rgba(148,163,184,0.05);">
        <div style="font-weight:700; margin-bottom:8px;">Rincian Sumber Kerusakan Plate</div>
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.25); text-align:left; opacity:0.8;">
                    <th style="padding:6px 8px;">Sumber Kerusakan</th>
                    <th style="padding:6px 8px; text-align:right;">Qty Rusak</th>
                    <th style="padding:6px 8px; text-align:right;">Estimasi / Aktual Biaya</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.14);">
                    <td style="padding:7px 8px;">Kerusakan saat Plate Making (Pra Cetak)</td>
                    <td style="padding:7px 8px; text-align:right; font-weight:600;">{{ number_format($summary['rusak_proses'], 0, ',', '.') }} Lbr</td>
                    <td style="padding:7px 8px; text-align:right; font-weight:600;">{{ $fmtRp($summary['biaya_rusak_proses']) }}</td>
                </tr>
                <tr style="border-bottom:1px solid rgba(148,163,184,0.14);">
                    <td style="padding:7px 8px;">Kerusakan di Arsip Rak / Printing ({{ $summary['archive_kode'] ?: 'Belum diarsip' }})</td>
                    <td style="padding:7px 8px; text-align:right; font-weight:600;">{{ number_format($summary['rusak_arsip'], 0, ',', '.') }} Lbr</td>
                    <td style="padding:7px 8px; text-align:right; font-weight:600;">{{ $fmtRp($summary['biaya_rusak_arsip']) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-weight:700;">Total Keseluruhan Kerusakan</td>
                    <td style="padding:8px; text-align:right; font-weight:800; color:{{ $hasDamage ? '#f87171' : '#34d399' }};">{{ number_format($summary['total_rusak'], 0, ',', '.') }} Lbr</td>
                    <td style="padding:8px; text-align:right; font-weight:800; color:{{ $hasDamage ? '#fbbf24' : '#34d399' }};">{{ $fmtRp($summary['total_damage_cost']) }}</td>
                </tr>
            </tbody>
        </table>

        @if(! empty($summary['catatan_kerusakan']))
            <div style="margin-top:10px; padding:8px 10px; border-radius:8px; border:1px solid rgba(245,158,11,0.3); background:rgba(245,158,11,0.08); font-size:12px;">
                <strong>Catatan Kerusakan:</strong> {{ $summary['catatan_kerusakan'] }}
            </div>
        @endif
    </div>

    {{-- Tabel baris material remake jika ada --}}
    @if(! empty($summary['remake_usages']))
        <div style="padding:12px 14px; border-radius:10px; border:1px solid rgba(239,68,68,0.28); background:rgba(239,68,68,0.06);">
            <div style="font-weight:700; margin-bottom:8px;">Log Pemakaian Material Remake / Plate Rusak</div>
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(148,163,184,0.25); text-align:left; opacity:0.8;">
                        <th style="padding:6px 8px;">Material Plate</th>
                        <th style="padding:6px 8px; text-align:right;">Plate</th>
                        <th style="padding:6px 8px; text-align:right;">Sheet</th>
                        <th style="padding:6px 8px;">Alasan</th>
                        <th style="padding:6px 8px; text-align:right;">Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['remake_usages'] as $u)
                        <tr style="border-bottom:1px solid rgba(148,163,184,0.14);">
                            <td style="padding:6px 8px;">{{ $u['material'] }}</td>
                            <td style="padding:6px 8px; text-align:right;">{{ $u['jumlah_plate'] }} Lbr</td>
                            <td style="padding:6px 8px; text-align:right;">{{ number_format($u['qty_sheet'], 2, ',', '.') }}</td>
                            <td style="padding:6px 8px;">{{ $u['alasan'] ?: '-' }}</td>
                            <td style="padding:6px 8px; text-align:right; font-weight:600;">{{ $fmtRp($u['total_biaya']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Catatan metode perhitungan --}}
    <div style="padding:8px 12px; border-radius:8px; border:1px solid rgba(148,163,184,0.2); background:rgba(148,163,184,0.06); font-size:11px; opacity:0.85;">
        {{ $summary['formula_note'] }}
    </div>
</div>
