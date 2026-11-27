<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pengajuan Biaya (NPB) - {{ $npb->nomor_pengajuan }}</title>
    <style>
        @page { margin: 18px 24px 18px 24px; size: A4 portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.8px; color: #0f172a; margin: 0; padding: 0; }
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .kop-company { font-size: 10.5px; font-weight: bold; color: #0f172a; }
        .kop-detail { font-size: 8.2px; color: #334155; line-height: 1.35; }
        .doc-title { text-align: center; font-size: 12px; font-weight: bold; letter-spacing: 0.4px; margin: 4px 0 8px 0; color: #0f172a; }

        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th, table.grid td { border: 1px solid #1e293b; padding: 3px 5px; vertical-align: middle; word-wrap: break-word; }

        .lbl-cell { background-color: #d9e1f2; font-weight: bold; color: #0f172a; }
        .navy-head { background-color: #1f4e79; color: #ffffff; font-weight: bold; text-align: center; font-size: 8.8px; padding: 4px 5px; }
        .navy-subhead { background-color: #2f5597; color: #ffffff; font-weight: bold; text-align: center; font-size: 8.3px; padding: 4px 5px; }
        .cat-bar { background-color: #bdd7ee; color: #0f172a; font-weight: bold; text-align: center; font-size: 8.3px; padding: 3px 5px; }
        .subtot-row td { background-color: #d9e1f2; font-weight: bold; color: #0f172a; }
        .grand-row td { background-color: #1f4e79; color: #ffffff; font-weight: bold; font-size: 8.8px; padding: 4px 5px; }
        .highlight-row td { background-color: #d9e1f2; font-weight: bold; }
        .deficit-val { color: #b91c1c; font-weight: bold; }
        .surplus-val { color: #047857; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .rp-wrap { width: 100%; border-collapse: collapse; }
        .rp-wrap td { border: none !important; padding: 0 !important; }

        .notes-box { margin-top: 8px; font-size: 8px; }
        .notes-title { font-weight: bold; font-style: italic; margin-bottom: 3px; }
        .notes-body { border: 1px solid #1e293b; padding: 5px 8px; line-height: 1.4; }

        .sig-outer { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .sig-outer > tbody > tr > td { border: none; padding: 0; vertical-align: top; }
        table.sig-box { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.sig-box td { border: 1px solid #1e293b; text-align: center; padding: 3px 4px; font-size: 8.3px; }
        .sig-space { height: 42px; position: relative; }
        .stamp-approved {
            display: inline-block;
            border: 2px solid #dc2626;
            color: #dc2626;
            font-weight: bold;
            font-size: 10px;
            letter-spacing: 1px;
            padding: 2px 7px;
            transform: rotate(-8deg);
            margin-top: 8px;
        }
    </style>
</head>
<body>
    @php
        $hasProyeksi = ! empty(trim((string) $npb->periode_proyeksi));
        $fmt = fn ($val, $dec = 0) => number_format((float) $val, $dec, ',', '.');
        $fmtDash = fn ($val) => abs((float) $val) < 0.01 ? '-' : number_format((float) $val, 0, ',', '.');
        $kurang = (float) $npb->kekurangan_pendanaan;
        $shortMonth1 = explode(' ', trim((string) $npb->periode_utama))[0] ?? $npb->periode_utama;

        // Kelompokkan item per kategori A..F
        $grouped = [];
        foreach (\App\Models\NpbRequest::KATEGORI_OPTIONS as $kKey => $kLabel) {
            $catItems = $npb->items->where('kategori', $kKey)->values();
            if ($catItems->isNotEmpty()) {
                $grouped[$kKey] = [
                    'code'     => substr($kKey, 0, 1),
                    'label'    => $kLabel,
                    'items'    => $catItems,
                    'sub_1'    => round((float) $catItems->sum('nominal_utama'), 2),
                    'sub_2'    => round((float) $catItems->sum('nominal_proyeksi'), 2),
                ];
            }
        }
    @endphp

    {{-- Kop Perusahaan --}}
    <table class="kop-table">
        <tr>
            <td style="width: 75%; vertical-align: top;">
                <div class="kop-company">PT Teknografi Tri Cawanaska</div>
                <div class="kop-detail">
                    Pergudangan Sentral Singosari<br>
                    Ardimulyo, Singosari, Malang<br>
                    teknografitricawanaska@gmail.com<br>
                    +62812 3339 0799
                </div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top;">
                @if(! empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 86px; height: auto;" alt="Logo TTC">
                @endif
            </td>
        </tr>
    </table>

    <div class="doc-title">NOTA PENGAJUAN BIAYA (NPB)</div>

    {{-- Identitas Pengajuan --}}
    <table class="grid" style="margin-bottom: 8px;">
        <tr>
            <td class="lbl-cell" style="width: 20%;">Nomor Pengajuan</td>
            <td style="width: 40%; font-weight: bold;">{{ $npb->nomor_pengajuan }}</td>
            <td class="lbl-cell" style="width: 15%;">Tanggal</td>
            <td style="width: 25%; text-align: center;">{{ $npb->tanggal_pengajuan ? \Carbon\Carbon::parse($npb->tanggal_pengajuan)->format('d-M-y') : '-' }}</td>
        </tr>
        <tr>
            <td class="lbl-cell">Perihal</td>
            <td colspan="3">{{ $npb->perihal }}</td>
        </tr>
    </table>

    {{-- Blok 2 Kolom: Ringkasan Kebutuhan Dana & Posisi Pendanaan --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            {{-- Kiri: Ringkasan Kebutuhan --}}
            <td style="width: 58%; vertical-align: top; padding-right: 4px;">
                <table class="grid">
                    <tr>
                        <td colspan="2" class="navy-head">RINGKASAN KEBUTUHAN DANA</td>
                    </tr>
                    <tr>
                        <td style="width: 65%;">Total Kebutuhan {{ $npb->periode_utama }}</td>
                        <td style="width: 35%;">
                            <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_kebutuhan_utama) }}</td></tr></table>
                        </td>
                    </tr>
                    @if($hasProyeksi)
                        <tr>
                            <td>Total Kebutuhan {{ $npb->periode_proyeksi }}</td>
                            <td>
                                <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_kebutuhan_proyeksi) }}</td></tr></table>
                            </td>
                        </tr>
                        <tr class="highlight-row">
                            <td>Total Kebutuhan 2 Bulan</td>
                            <td>
                                <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_kebutuhan_gabungan) }}</td></tr></table>
                            </td>
                        </tr>
                    @else
                        <tr class="highlight-row">
                            <td>Total Kebutuhan Dana</td>
                            <td>
                                <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_kebutuhan_utama) }}</td></tr></table>
                            </td>
                        </tr>
                    @endif
                </table>
            </td>

            {{-- Kanan: Posisi Pendanaan --}}
            <td style="width: 42%; vertical-align: top; padding-left: 4px;">
                <table class="grid">
                    <tr>
                        <td colspan="2" class="navy-head">POSISI PENDANAAN {{ strtoupper((string) $npb->periode_utama) }}</td>
                    </tr>
                    @forelse($npb->fundPositions as $fp)
                        <tr>
                            <td style="width: 54%;">{{ $fp->nama_rekening }}</td>
                            <td style="width: 46%;">
                                <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmtDash($fp->saldo) }}</td></tr></table>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="color: #64748b;">Tidak ada saldo kas/bank tercatat</td>
                        </tr>
                    @endforelse
                    <tr>
                        <td colspan="2" style="border: none; height: 6px; padding: 0;"></td>
                    </tr>
                    <tr class="highlight-row">
                        <td>Total Saldo Sebelumnya</td>
                        <td>
                            <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_saldo_sebelumnya) }}</td></tr></table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: none; height: 6px; padding: 0;"></td>
                    </tr>
                    <tr class="text-bold">
                        <td>Total Dana Tersedia</td>
                        <td>
                            <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmt($npb->total_dana_tersedia) }}</td></tr></table>
                        </td>
                    </tr>
                    <tr class="highlight-row">
                        <td>{{ $kurang < 0 ? 'Kekurangan Pendanaan ' . $shortMonth1 : 'Surplus Pendanaan ' . $shortMonth1 }}</td>
                        <td class="{{ $kurang < 0 ? 'deficit-val' : 'surplus-val' }}">
                            <table class="rp-wrap">
                                <tr>
                                    <td style="width: 24px;">{{ $kurang < 0 ? '-Rp' : 'Rp' }}</td>
                                    <td class="text-right">{{ $fmt(abs($kurang), 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Tabel Utama: Rincian Kebutuhan Dana --}}
    <table class="grid">
        <thead>
            <tr>
                <th colspan="{{ $hasProyeksi ? 5 : 4 }}" class="navy-head">RINCIAN KEBUTUHAN DANA</th>
            </tr>
            <tr>
                <th class="navy-subhead" style="width: 7%;">No.</th>
                <th class="navy-subhead" style="width: {{ $hasProyeksi ? '38%' : '56%' }};">Uraian Kebutuhan</th>
                <th class="navy-subhead" style="width: 15%;">Karakter</th>
                <th class="navy-subhead" style="width: {{ $hasProyeksi ? '20%' : '22%' }};">{{ $npb->periode_utama }} (Rp)</th>
                @if($hasProyeksi)
                    <th class="navy-subhead" style="width: 20%;">{{ $npb->periode_proyeksi }} (Rp)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php $rowNo = 1; @endphp
            @foreach($grouped as $grp)
                <tr>
                    <td colspan="{{ $hasProyeksi ? 5 : 4 }}" class="cat-bar">{{ $grp['label'] }}</td>
                </tr>
                @foreach($grp['items'] as $item)
                    <tr>
                        <td class="text-center">{{ $rowNo++ }}</td>
                        <td>{{ $item->uraian }}</td>
                        <td class="text-center">{{ $item->karakter }}</td>
                        <td>
                            <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmtDash($item->nominal_utama) }}</td></tr></table>
                        </td>
                        @if($hasProyeksi)
                            <td>
                                <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmtDash($item->nominal_proyeksi) }}</td></tr></table>
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr class="subtot-row">
                    <td colspan="3">Subtotal {{ $grp['code'] }}</td>
                    <td>
                        <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmtDash($grp['sub_1']) }}</td></tr></table>
                    </td>
                    @if($hasProyeksi)
                        <td>
                            <table class="rp-wrap"><tr><td style="width: 20px;">Rp</td><td class="text-right">{{ $fmtDash($grp['sub_2']) }}</td></tr></table>
                        </td>
                    @endif
                </tr>
            @endforeach

            <tr class="grand-row">
                <td colspan="3" class="text-center">Sub Total</td>
                <td>
                    <table class="rp-wrap"><tr><td style="width: 20px; color:#fff;">Rp</td><td class="text-right" style="color:#fff;">{{ $fmt($npb->total_kebutuhan_utama) }}</td></tr></table>
                </td>
                @if($hasProyeksi)
                    <td>
                        <table class="rp-wrap"><tr><td style="width: 20px; color:#fff;">Rp</td><td class="text-right" style="color:#fff;">{{ $fmt($npb->total_kebutuhan_proyeksi) }}</td></tr></table>
                    </td>
                @endif
            </tr>
            <tr class="grand-row">
                <td colspan="3" class="text-center">Total Kebutuhan Dana</td>
                <td colspan="{{ $hasProyeksi ? 2 : 1 }}">
                    <table class="rp-wrap">
                        <tr>
                            <td style="width: 20px; color:#fff;">Rp</td>
                            <td class="text-right" style="color:#fff;">{{ $fmt($hasProyeksi ? $npb->total_kebutuhan_gabungan : $npb->total_kebutuhan_utama) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Catatan --}}
    @if(! empty(trim((string) $npb->catatan)))
        <div class="notes-box">
            <div class="notes-title">CATATAN</div>
            <div class="notes-body">{!! nl2br(e($npb->catatan)) !!}</div>
        </div>
    @endif

    {{-- 4 Kolom Otorisasi & Tanda Tangan --}}
    <table class="sig-outer">
        <tr>
            <td style="width: 45%;">
                <table class="sig-box">
                    <tr>
                        <td style="width: 50%;">Disusun oleh,</td>
                        <td style="width: 50%;">Mengetahui,</td>
                    </tr>
                    <tr>
                        <td class="sig-space">&nbsp;</td>
                        <td class="sig-space">&nbsp;</td>
                    </tr>
                    <tr class="text-bold">
                        <td>{{ $npb->disusun_oleh ?: 'Bagian Keuangan' }}</td>
                        <td>{{ $npb->mengetahui_nama ?: '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%;">
                <table class="sig-box">
                    <tr>
                        <td style="width: 50%;">Diajukan kepada,</td>
                        <td style="width: 50%;">Disetujui oleh,</td>
                    </tr>
                    <tr>
                        <td class="sig-space">&nbsp;</td>
                        <td class="sig-space">
                            @if($npb->is_approved_status)
                                <div class="stamp-approved">APPROVED</div>
                                @if($npb->disetujui_at)
                                    <div style="font-size: 7px; color: #b91c1c; margin-top: 2px;">{{ $npb->disetujui_at->format('d/m/Y') }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                    <tr class="text-bold">
                        <td>{{ $npb->diajukan_kepada ?: 'Direktur' }}</td>
                        <td>{{ $npb->disetujui_oleh ?: 'Owner' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
