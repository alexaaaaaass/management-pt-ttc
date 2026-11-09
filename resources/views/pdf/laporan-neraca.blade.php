<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca - PT. Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 20px 26px; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5px; color: #0f172a; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 10px; }
        .company-name { font-size: 14.5px; font-weight: bold; color: #1e3a8a; }
        .company-sub { font-size: 9px; color: #475569; margin-top: 2px; }
        .report-title { font-size: 13px; font-weight: bold; text-align: right; color: #0f172a; }
        .report-period { font-size: 9.5px; text-align: right; color: #475569; margin-top: 2px; }

        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 12px -6px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 3.5px solid #1e3a8a; border-radius: 4px; padding: 6px 10px; width: 25%; }
        .kpi-label { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .kpi-val { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        .two-col { width: 100%; border-collapse: collapse; }
        .two-col > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 4px; }

        table.side-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.side-table th { background-color: #1e3a8a; color: #ffffff; padding: 5px 6px; font-size: 9px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.side-table td { padding: 4.5px 6px; border-bottom: 1px solid #e2e8f0; font-size: 9.5px; }
        .sec-header td { background-color: #eff6ff; font-weight: bold; color: #1e3a8a; }
        .sec-subtotal td { background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #94a3b8; }
        .sec-grand td { background-color: #dbeafe; font-weight: bold; font-size: 10px; color: #1e3a8a; border-top: 1.5px solid #1e3a8a; border-bottom: 1.5px solid #1e3a8a; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 0, ',', '.');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 65px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 52px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Offset Printing, Packaging &amp; Label Manufacturing</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">REPORT ANALISA TRANSAKSI — NERACA</div>
                <div class="report-period">
                    Per Tanggal: {{ \Carbon\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}
                    | Status: <strong>{{ $data['is_balanced'] ? 'SEIMBANG (BALANCE)' : 'SELISIH' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-label">Total Aktiva (Aset)</div>
                <div class="kpi-val">{{ $fmt($data['total_aktiva']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #ea580c;">
                <div class="kpi-label">Total Kewajiban (Utang)</div>
                <div class="kpi-val">{{ $fmt($data['total_kewajiban']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #4f46e5;">
                <div class="kpi-label">Total Ekuitas (Modal)</div>
                <div class="kpi-val">{{ $fmt($data['total_ekuitas']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #059669;">
                <div class="kpi-label">Total Pasiva (Kewajiban + Ekuitas)</div>
                <div class="kpi-val" style="color: #047857;">{{ $fmt($data['total_pasiva']) }}</div>
            </td>
        </tr>
    </table>

    <table class="two-col">
        <tbody>
            <tr>
                {{-- KOLOM KIRI: AKTIVA --}}
                <td>
                    <table class="side-table">
                        <thead>
                            <tr>
                                <th style="width: 18%; text-align: left;">Kode</th>
                                <th style="width: 52%; text-align: left;">AKTIVA (ASET)</th>
                                <th style="width: 30%; text-align: right;">Saldo (IDR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="sec-header">
                                <td colspan="3">I. ASET LANCAR</td>
                            </tr>
                            @forelse ($data['aset_lancar'] as $item)
                                <tr>
                                    <td>{{ $item['kode_akun'] }}</td>
                                    <td>{{ $item['nama_akun'] }}</td>
                                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><em>Tidak ada saldo aset lancar</em></td></tr>
                            @endforelse
                            <tr class="sec-subtotal">
                                <td colspan="2">Total Aset Lancar</td>
                                <td class="text-right">{{ $fmt($data['total_aset_lancar']) }}</td>
                            </tr>

                            <tr class="sec-header">
                                <td colspan="3">II. ASET TETAP</td>
                            </tr>
                            @forelse ($data['aset_tetap'] as $item)
                                <tr>
                                    <td>{{ $item['kode_akun'] }}</td>
                                    <td>{{ $item['nama_akun'] }}</td>
                                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><em>Tidak ada saldo aset tetap</em></td></tr>
                            @endforelse
                            <tr class="sec-subtotal">
                                <td colspan="2">Total Aset Tetap</td>
                                <td class="text-right">{{ $fmt($data['total_aset_tetap']) }}</td>
                            </tr>

                            @if(!empty($data['aset_lainnya']))
                                <tr class="sec-header">
                                    <td colspan="3">III. ASET LAINNYA</td>
                                </tr>
                                @foreach ($data['aset_lainnya'] as $item)
                                    <tr>
                                        <td>{{ $item['kode_akun'] }}</td>
                                        <td>{{ $item['nama_akun'] }}</td>
                                        <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="sec-subtotal">
                                    <td colspan="2">Total Aset Lainnya</td>
                                    <td class="text-right">{{ $fmt($data['total_aset_lainnya']) }}</td>
                                </tr>
                            @endif

                            <tr class="sec-grand">
                                <td colspan="2">TOTAL AKTIVA</td>
                                <td class="text-right">{{ $fmt($data['total_aktiva']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>

                {{-- KOLOM KANAN: PASIVA --}}
                <td>
                    <table class="side-table">
                        <thead>
                            <tr>
                                <th style="width: 18%; text-align: left;">Kode</th>
                                <th style="width: 52%; text-align: left;">PASIVA (KEWAJIBAN &amp; EKUITAS)</th>
                                <th style="width: 30%; text-align: right;">Saldo (IDR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="sec-header">
                                <td colspan="3">I. KEWAJIBAN LANCAR</td>
                            </tr>
                            @forelse ($data['kewajiban_lancar'] as $item)
                                <tr>
                                    <td>{{ $item['kode_akun'] }}</td>
                                    <td>{{ $item['nama_akun'] }}</td>
                                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><em>Tidak ada saldo kewajiban lancar</em></td></tr>
                            @endforelse
                            <tr class="sec-subtotal">
                                <td colspan="2">Total Kewajiban Lancar</td>
                                <td class="text-right">{{ $fmt($data['total_kewajiban_lancar']) }}</td>
                            </tr>

                            @if(!empty($data['kewajiban_jangka_panjang']))
                                <tr class="sec-header">
                                    <td colspan="3">II. KEWAJIBAN JANGKA PANJANG</td>
                                </tr>
                                @foreach ($data['kewajiban_jangka_panjang'] as $item)
                                    <tr>
                                        <td>{{ $item['kode_akun'] }}</td>
                                        <td>{{ $item['nama_akun'] }}</td>
                                        <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="sec-subtotal">
                                    <td colspan="2">Total Kewajiban Jangka Panjang</td>
                                    <td class="text-right">{{ $fmt($data['total_kewajiban_jangka_panjang']) }}</td>
                                </tr>
                            @endif

                            <tr class="sec-subtotal">
                                <td colspan="2">TOTAL KEWAJIBAN</td>
                                <td class="text-right">{{ $fmt($data['total_kewajiban']) }}</td>
                            </tr>

                            <tr class="sec-header">
                                <td colspan="3">III. EKUITAS / MODAL</td>
                            </tr>
                            @forelse ($data['ekuitas_items'] as $item)
                                <tr>
                                    <td>{{ $item['kode_akun'] }}</td>
                                    <td>{{ $item['nama_akun'] }}</td>
                                    <td class="text-right">{{ $fmt($item['nominal']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><em>Tidak ada saldo ekuitas</em></td></tr>
                            @endforelse
                            <tr class="sec-subtotal">
                                <td colspan="2">TOTAL EKUITAS</td>
                                <td class="text-right">{{ $fmt($data['total_ekuitas']) }}</td>
                            </tr>

                            <tr class="sec-grand">
                                <td colspan="2">TOTAL PASIVA</td>
                                <td class="text-right">{{ $fmt($data['total_pasiva']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 20px; text-align: center;">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br><br><br><br>
                <strong>( Accounting / Finance )</strong>
            </td>
            <td style="width: 33%;">
                Diperiksa Oleh,<br><br><br><br>
                <strong>( Kepala Bagian Keuangan )</strong>
            </td>
            <td style="width: 34%;">
                Disetujui Oleh,<br><br><br><br>
                <strong>( Direksi PT. Teknografi Tri Cawanaska )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
