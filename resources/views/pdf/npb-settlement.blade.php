<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penyelesaian NPB (LPJ) - {{ $settlement->nomor_penyelesaian }}</title>
    <style>
        @page { margin: 16px 20px 18px 20px; size: A4 landscape; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.3px; color: #0f172a; margin: 0; padding: 0; }
        .kop-table { width: 100%; border-bottom: 2px solid #1f4e79; padding-bottom: 5px; margin-bottom: 8px; }
        .company-name { font-size: 12px; font-weight: bold; color: #1f4e79; }
        .company-sub { font-size: 7.8px; color: #475569; margin-top: 1px; }
        .report-title { font-size: 11px; font-weight: bold; text-align: right; color: #0f172a; }
        .report-period { font-size: 8px; text-align: right; color: #475569; margin-top: 2px; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 0 -5px 8px -5px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 3.5px solid #1f4e79; padding: 5px 8px; width: 25%; }
        .kpi-label { font-size: 7.3px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .kpi-val { font-size: 10px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        table.grid { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 8px; }
        table.grid th { background-color: #1f4e79; color: #ffffff; padding: 4px 4px; font-size: 7.8px; text-transform: uppercase; border: 1px solid #1f4e79; }
        table.grid td { padding: 3.5px 4px; border: 1px solid #cbd5e1; font-size: 7.8px; vertical-align: top; word-wrap: break-word; }
        .cat-bar { background-color: #bdd7ee; color: #0f172a; font-weight: bold; font-size: 8px; }
        .subtot-row td { background-color: #e2e8f0; font-weight: bold; }
        .grand-row td { background-color: #1f4e79; color: #ffffff; font-weight: bold; font-size: 8.3px; padding: 4.5px 4px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-green { color: #047857; font-weight: bold; }
        .text-red { color: #b91c1c; font-weight: bold; }
        .text-blue { color: #1d4ed8; font-weight: bold; }

        .summary-box { border: 1px solid #1e293b; padding: 6px 8px; margin-bottom: 10px; font-size: 8px; background-color: #f8fafc; }
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .sig-table td { border: 1px solid #1e293b; text-align: center; padding: 3px 5px; font-size: 8px; width: 25%; }
        .sig-space { height: 38px; }
        .stamp-approved { display: inline-block; border: 2px solid #dc2626; color: #dc2626; font-weight: bold; font-size: 9.5px; padding: 2px 6px; margin-top: 6px; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
        $pct = (float) $settlement->total_anggaran > 0
            ? round(((float) $settlement->total_realisasi / (float) $settlement->total_anggaran) * 100, 1)
            : 0;
        $selisih = (float) $settlement->selisih_anggaran;
        $sisa = (float) $settlement->sisa_dana;

        $grouped = [];
        foreach (\App\Models\NpbRequest::KATEGORI_OPTIONS as $kKey => $kLabel) {
            $catItems = $settlement->items->where('kategori', $kKey)->values();
            if ($catItems->isNotEmpty()) {
                $grouped[$kKey] = [
                    'code'    => substr($kKey, 0, 1),
                    'label'   => $kLabel,
                    'items'   => $catItems,
                    'ang'     => round((float) $catItems->sum('nominal_anggaran'), 2),
                    'real'    => round((float) $catItems->sum('nominal_realisasi'), 2),
                    'sel'     => round((float) $catItems->sum('selisih'), 2),
                ];
            }
        }
    @endphp

    <table class="kop-table">
        <tr>
            <td style="width: 55px; vertical-align: middle;">
                @if(! empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 48px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · Laporan Pertanggungjawaban / Penyelesaian NPB</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">LAPORAN PENYELESAIAN NPB (LPJ)</div>
                <div class="report-period">
                    No. LPJ: <strong>{{ $settlement->nomor_penyelesaian }}</strong> | Ref. NPB: <strong>{{ $settlement->npbRequest?->nomor_pengajuan ?? '-' }}</strong> |
                    Periode: <strong>{{ $settlement->label_periode }}</strong> | Tgl: {{ $settlement->tanggal_penyelesaian ? \Carbon\Carbon::parse($settlement->tanggal_penyelesaian)->format('d/m/Y') : '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="border-left-color: #1f4e79;">
                <div class="kpi-label">Total Anggaran NPB</div>
                <div class="kpi-val">{{ $fmt($settlement->total_anggaran) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #4f46e5;">
                <div class="kpi-label">Total Realisasi Aktual ({{ $pct }}%)</div>
                <div class="kpi-val text-blue">{{ $fmt($settlement->total_realisasi) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $selisih >= 0 ? '#047857' : '#b91c1c' }};">
                <div class="kpi-label">{{ $selisih >= 0 ? 'Selisih (Hemat Anggaran)' : 'Selisih (Over Budget)' }}</div>
                <div class="kpi-val {{ $selisih >= 0 ? 'text-green' : 'text-red' }}">{{ ($selisih < 0 ? '-' : '+') . $fmt(abs($selisih)) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: {{ $sisa >= 0 ? '#2563eb' : '#b91c1c' }};">
                <div class="kpi-label">{{ $sisa >= 0 ? 'Sisa Dana Akhir' : 'Kekurangan Dana / Reimburse' }}</div>
                <div class="kpi-val {{ $sisa >= 0 ? 'text-blue' : 'text-red' }}">{{ ($sisa < 0 ? '-' : '') . $fmt(abs($sisa)) }}</div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width: 3.5%;">No</th>
                <th style="width: 22%;">Uraian Pos Biaya</th>
                <th style="width: 8%;">Tgl Bayar</th>
                <th style="width: 14.5%;">Metode / Sumber Bayar</th>
                <th style="width: 10%;">No. Bukti</th>
                <th style="width: 11.5%;">Anggaran NPB</th>
                <th style="width: 11.5%;">Realisasi Aktual</th>
                <th style="width: 10.5%;">Selisih</th>
                <th style="width: 8.5%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($grouped as $grp)
                <tr>
                    <td colspan="9" class="cat-bar">{{ $grp['label'] }}</td>
                </tr>
                @foreach($grp['items'] as $it)
                    @php
                        $metLabel = match ($it->metode_pembayaran) {
                            'BANK_TTC'          => 'Bank PT TTC',
                            'KAS_TTC'           => 'Kas / Petty Cash',
                            'TALANGAN_INDIGAMA' => 'Talangan Indigama (2144)',
                            'BELUM_DIBAYAR'     => 'Belum Dibayar',
                            default             => $it->metode_pembayaran ?: '-',
                        };
                        $stLabel = \App\Models\NpbSettlementItem::STATUS_ITEM_OPTIONS[$it->status_item] ?? $it->status_item;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td>
                            <strong>{{ $it->uraian }}</strong>
                            @if($it->keterangan)
                                <div style="font-size: 7px; color: #475569;">{{ $it->keterangan }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $it->tanggal_realisasi ? \Carbon\Carbon::parse($it->tanggal_realisasi)->format('d/m/Y') : '-' }}</td>
                        <td>
                            {{ $metLabel }}
                            @if($it->coaSumber)
                                <div style="font-size: 7px; color: #475569;">{{ trim($it->coaSumber->kode_akun) }} - {{ $it->coaSumber->nama_akun }}</div>
                            @endif
                        </td>
                        <td>{{ $it->no_referensi_bukti ?: '-' }}</td>
                        <td class="text-right">{{ $fmt($it->nominal_anggaran) }}</td>
                        <td class="text-right text-bold">{{ $fmt($it->nominal_realisasi) }}</td>
                        <td class="text-right {{ (float)$it->selisih >= 0 ? 'text-green' : 'text-red' }}">
                            {{ ((float)$it->selisih < 0 ? '-' : '+') . $fmt(abs((float)$it->selisih)) }}
                        </td>
                        <td class="text-center">{{ $stLabel }}</td>
                    </tr>
                @endforeach
                <tr class="subtot-row">
                    <td colspan="5">Subtotal {{ $grp['code'] }}</td>
                    <td class="text-right">{{ $fmt($grp['ang']) }}</td>
                    <td class="text-right">{{ $fmt($grp['real']) }}</td>
                    <td class="text-right {{ $grp['sel'] >= 0 ? 'text-green' : 'text-red' }}">{{ ($grp['sel'] < 0 ? '-' : '+') . $fmt(abs($grp['sel'])) }}</td>
                    <td></td>
                </tr>
            @endforeach

            <tr class="grand-row">
                <td colspan="5">TOTAL PENYELESAIAN / REALISASI NPB</td>
                <td class="text-right">{{ $fmt($settlement->total_anggaran) }}</td>
                <td class="text-right">{{ $fmt($settlement->total_realisasi) }}</td>
                <td class="text-right">{{ ($selisih < 0 ? '-' : '+') . $fmt(abs($selisih)) }}</td>
                <td class="text-center">{{ $pct }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Rekonsiliasi Arus Dana:</strong>
        Total Dana Tersedia + Dicairkan = <strong>{{ $fmt($settlement->total_dana_dicairkan) }}</strong> |
        Total Realisasi Aktual = <strong>{{ $fmt($settlement->total_realisasi) }}</strong> |
        Sisa / (Kekurangan) Dana = <strong>{{ ($sisa < 0 ? '-' : '') . $fmt(abs($sisa)) }}</strong> |
        Tindak Lanjut: <strong>{{ \App\Models\NpbSettlement::TINDAK_LANJUT_OPTIONS[$settlement->tindak_lanjut_sisa] ?? $settlement->tindak_lanjut_sisa }}</strong> |
        Status Pembukuan GL: <strong>{{ $settlement->is_posted_to_ledger ? 'Sudah Diposting ke Buku Besar' : 'Dokumen LPJ / Tracking' }}</strong>
        @if($settlement->catatan)
            <br><strong>Catatan:</strong> {{ $settlement->catatan }}
        @endif
    </div>

    <table class="sig-table">
        <tr>
            <td>Disusun oleh,</td>
            <td>Diperiksa oleh,</td>
            <td>Diketahui oleh,</td>
            <td>Disetujui oleh,</td>
        </tr>
        <tr>
            <td class="sig-space">&nbsp;</td>
            <td class="sig-space">&nbsp;</td>
            <td class="sig-space">&nbsp;</td>
            <td class="sig-space">
                @if($settlement->status === 'APPROVED')
                    <div class="stamp-approved">APPROVED</div>
                    @if($settlement->disetujui_at)
                        <div style="font-size: 7px; color: #b91c1c;">{{ $settlement->disetujui_at->format('d/m/Y') }}</div>
                    @endif
                @endif
            </td>
        </tr>
        <tr class="text-bold">
            <td>{{ $settlement->disusun_oleh ?: 'Bagian Keuangan' }}</td>
            <td>{{ $settlement->diperiksa_oleh ?: '' }}</td>
            <td>Direktur</td>
            <td>{{ $settlement->disetujui_oleh ?: 'Owner' }}</td>
        </tr>
    </table>
</body>
</html>
