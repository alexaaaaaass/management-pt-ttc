<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hutang, Piutang &amp; Pinjaman - PT. Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 16px 20px 20px 20px; size: A4 landscape; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5px; color: #0f172a; margin: 0; padding: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #881337; padding-bottom: 6px; margin-bottom: 8px; }
        .company-name { font-size: 13px; font-weight: bold; color: #881337; }
        .company-sub { font-size: 8px; color: #475569; margin-top: 1px; }
        .report-title { font-size: 11.5px; font-weight: bold; text-align: right; color: #0f172a; }
        .report-period { font-size: 8px; text-align: right; color: #475569; margin-top: 2px; }
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 5px 0; margin: 0 -5px 8px -5px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 3.5px solid #881337; padding: 5px 8px; width: 25%; }
        .kpi-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #475569; }
        .kpi-val { font-size: 10px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .sec-title { font-size: 9.5px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 10px 0 4px 0; }
        table.hp-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 8px; }
        table.hp-table th { background-color: #1e3a8a; color: #ffffff; padding: 5px 4px; font-size: 7.8px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.hp-table td { padding: 4px 4px; border: 1px solid #cbd5e1; font-size: 7.8px; vertical-align: top; word-wrap: break-word; }
        tr.row-even td { background-color: #f8fafc; }
        table.hp-table tfoot td { background-color: #e2e8f0; font-weight: bold; border: 1px solid #64748b; padding: 5px 4px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-rose { color: #be123c; font-weight: bold; }
        .text-green { color: #047857; font-weight: bold; }
        .text-blue { color: #1d4ed8; font-weight: bold; }
        .alert-box { border: 1px solid #d97706; background-color: #fef3c7; padding: 6px 8px; margin-bottom: 8px; font-size: 7.8px; }
        .footer-note { width: 100%; font-size: 7.5px; color: #64748b; margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => 'Rp ' . number_format((float) $val, 2, ',', '.');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 50px; vertical-align: middle;">
                @if(! empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 42px; height: auto;" alt="Logo TTC">
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="company-name">PT. TEKNOGRAFI TRI CAWANASKA</div>
                <div class="company-sub">Kartu Hutang, Piutang &amp; Pinjaman Pihak Berelasi (PT Indigama)</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-title">LAPORAN HUTANG, PIUTANG &amp; PINJAMAN</div>
                <div class="report-period">
                    Posisi Per Tanggal: <strong>{{ \Carbon\Carbon::parse($data['as_of_date'])->format('d/m/Y') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="border-left-color: #be123c;">
                <div class="kpi-label">Pinjaman PT Indigama / Relasi</div>
                <div class="kpi-val text-rose">{{ $fmt($data['total_pinjaman']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #ea580c;">
                <div class="kpi-label">Total Kewajiban Buku Besar</div>
                <div class="kpi-val">{{ $fmt($data['total_kewajiban_ledger']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #047857;">
                <div class="kpi-label">Total Piutang Buku Besar</div>
                <div class="kpi-val text-green">{{ $fmt($data['total_piutang_ledger']) }}</div>
            </td>
            <td class="kpi-card" style="border-left-color: #1d4ed8;">
                <div class="kpi-label">Sisa Tagihan Invoice AR (Modul)</div>
                <div class="kpi-val text-blue">{{ $fmt($data['total_open_invoice_ar']) }}</div>
            </td>
        </tr>
    </table>

    @if(! empty($data['anomalies']))
        <div class="alert-box">
            <strong>Catatan Audit Saldo Minus:</strong>
            @foreach($data['anomalies'] as $an)
                [{{ $an['kode_akun'] }} {{ $an['nama_akun'] }}: {{ $fmt($an['saldo']) }}] {{ $an['pesan'] }}
            @endforeach
        </div>
    @endif

    <div class="sec-title">1. Rincian Pinjaman ke PT Indigama &amp; Pihak Berelasi (Non-Kas / Jurnal Memorial)</div>
    <table class="hp-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 9%; text-align: center;">Tanggal</th>
                <th style="width: 11%; text-align: left;">No. Ref</th>
                <th style="width: 15%; text-align: left;">Akun COA</th>
                <th style="width: 28%; text-align: left;">Keterangan</th>
                <th style="width: 11%; text-align: right;">Pinjam (Rp)</th>
                <th style="width: 11%; text-align: right;">Bayar (Rp)</th>
                <th style="width: 11%; text-align: right;">Sisa Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['pinjaman_ledger_rows'] as $i => $row)
                <tr class="{{ $i % 2 === 1 ? 'row-even' : '' }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $row['tanggal'] }}</td>
                    <td class="text-bold">{{ $row['reference_no'] }}</td>
                    <td>{{ $row['kode_akun'] }} - {{ $row['nama_akun'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                    <td class="text-right text-rose">{{ $row['tambah_hutang'] > 0 ? $fmt($row['tambah_hutang']) : '-' }}</td>
                    <td class="text-right text-green">{{ $row['bayar_hutang'] > 0 ? $fmt($row['bayar_hutang']) : '-' }}</td>
                    <td class="text-right text-rose">{{ $fmt($row['saldo_berjalan']) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada transaksi pinjaman.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL PINJAMAN PT INDIGAMA &amp; PIHAK BERELASI</td>
                <td class="text-right text-rose">{{ $fmt(collect($data['pinjaman_ledger_rows'])->sum('tambah_hutang')) }}</td>
                <td class="text-right text-green">{{ $fmt(collect($data['pinjaman_ledger_rows'])->sum('bayar_hutang')) }}</td>
                <td class="text-right text-rose">{{ $fmt($data['total_pinjaman']) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="sec-title">2. Utang Usaha &amp; Kewajiban Operasional (211x, 212x, 213x, 215x)</div>
    <table class="hp-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align: left;">Kode</th>
                <th style="width: 28%; text-align: left;">Nama Akun</th>
                <th style="width: 20%; text-align: left;">Kategori</th>
                <th style="width: 8%; text-align: center;">Mutasi</th>
                <th style="width: 12%; text-align: right;">Kredit (Rp)</th>
                <th style="width: 12%; text-align: right;">Debit (Rp)</th>
                <th style="width: 12%; text-align: right;">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['hutang_ops_items'] as $i => $item)
                <tr class="{{ $i % 2 === 1 ? 'row-even' : '' }}">
                    <td class="text-bold">{{ $item['kode_akun'] }}</td>
                    <td>{{ $item['nama_akun'] }}</td>
                    <td>{{ $item['kategori'] }}</td>
                    <td class="text-center">{{ $item['tx_count'] }}x</td>
                    <td class="text-right">{{ $fmt($item['total_hutang']) }}</td>
                    <td class="text-right">{{ $fmt($item['total_bayar']) }}</td>
                    <td class="text-right text-rose">{{ $fmt($item['sisa_hutang']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada saldo kewajiban operasional.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sec-title">3. Rincian Piutang Usaha &amp; Piutang Karyawan / Kasbon (112x &amp; 113x)</div>
    <table class="hp-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">No</th>
                <th style="width: 9%; text-align: center;">Tanggal</th>
                <th style="width: 11%; text-align: left;">No. Ref</th>
                <th style="width: 16%; text-align: left;">Akun COA</th>
                <th style="width: 27%; text-align: left;">Keterangan</th>
                <th style="width: 11%; text-align: right;">Piutang Baru</th>
                <th style="width: 11%; text-align: right;">Pelunasan</th>
                <th style="width: 11%; text-align: right;">Saldo Berjalan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['piutang_ledger_rows'] as $i => $row)
                <tr class="{{ $i % 2 === 1 ? 'row-even' : '' }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $row['tanggal'] }}</td>
                    <td class="text-bold">{{ $row['reference_no'] }}</td>
                    <td>{{ $row['kode_akun'] }} - {{ $row['nama_akun'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                    <td class="text-right text-blue">{{ $row['tambah_piutang'] > 0 ? $fmt($row['tambah_piutang']) : '-' }}</td>
                    <td class="text-right text-green">{{ $row['lunas_piutang'] > 0 ? $fmt($row['lunas_piutang']) : '-' }}</td>
                    <td class="text-right text-green">{{ $fmt($row['saldo_berjalan']) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada transaksi piutang pada Buku Besar.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL PIUTANG BUKU BESAR</td>
                <td class="text-right text-blue">{{ $fmt(collect($data['piutang_ledger_rows'])->sum('tambah_piutang')) }}</td>
                <td class="text-right text-green">{{ $fmt(collect($data['piutang_ledger_rows'])->sum('lunas_piutang')) }}</td>
                <td class="text-right text-green">{{ $fmt($data['total_piutang_ledger']) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-note">
        <tr>
            <td>Dicetak otomatis dari Sistem ERP PT. Teknografi Tri Cawanaska pada {{ now()->format('d/m/Y H:i:s') }}.</td>
            <td class="text-right">Status: Terverifikasi Buku Besar Double-Entry</td>
        </tr>
    </table>
</body>
</html>
