<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji Karyawan - PT Teknografi Tri Cawanaska</title>
    <style>
        @page { margin: 22px 28px; size: A4 portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.8px; color: #0f172a; margin: 0; padding: 0; line-height: 1.35; }
        .slip-page { page-break-after: always; }
        .slip-page:last-child { page-break-after: auto; }
        
        .kop { width: 100%; border-collapse: collapse; border-bottom: 2.5px solid #1f4e79; padding-bottom: 6px; margin-bottom: 12px; }
        .company { font-size: 13px; font-weight: bold; color: #1f4e79; letter-spacing: 0.5px; }
        .sub { font-size: 8px; color: #475569; margin-top: 2px; line-height: 1.4; }
        .doc-title { text-align: right; font-size: 13px; font-weight: bold; color: #1e3a8a; letter-spacing: 0.5px; }
        .doc-meta { text-align: right; font-size: 8.5px; color: #334155; margin-top: 3px; }
        
        table.info-box { width: 100%; border-collapse: collapse; margin-bottom: 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; }
        table.info-box td { padding: 4.5px 8px; font-size: 8.5px; }
        .info-label { width: 18%; color: #64748b; font-weight: normal; }
        .info-val { width: 32%; font-weight: bold; color: #0f172a; }
        
        .cols-wrap { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .cols-wrap > tbody > tr > td { width: 50%; vertical-align: top; }
        
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid th { background: #1f4e79; color: #ffffff; padding: 5px 8px; font-size: 8.5px; font-weight: bold; letter-spacing: 0.3px; }
        table.grid th.pot-head { background: #991b1b; }
        table.grid td { border: 1px solid #cbd5e1; padding: 4.5px 8px; font-size: 8.5px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        .tot-row-income td { background: #ecfdf5; font-weight: bold; color: #065f46; font-size: 8.8px; border-top: 1.5px solid #059669; }
        .tot-row-deduct td { background: #fef2f2; font-weight: bold; color: #991b1b; font-size: 8.8px; border-top: 1.5px solid #dc2626; }
        
        .thp-card { border: 2px solid #1f4e79; background: #f0f7ff; padding: 9px 14px; margin-bottom: 14px; border-radius: 4px; }
        .thp-title { font-size: 9.5px; font-weight: bold; color: #1f4e79; letter-spacing: 0.5px; }
        .thp-terbilang { font-size: 8px; color: #475569; margin-top: 3px; font-style: italic; }
        .thp-amount { font-size: 15px; font-weight: bold; color: #1f4e79; text-align: right; }
        
        .notes-box { font-size: 8px; color: #475569; background: #f8fafc; border: 1px dashed #cbd5e1; padding: 6px 10px; margin-bottom: 14px; border-radius: 3px; }
        
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .sig-table td { width: 50%; text-align: center; font-size: 8.5px; vertical-align: top; }
        .sig-space { height: 50px; }
    </style>
</head>
<body>
@php $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp
@forelse($slips as $gaji)
    <div class="slip-page">
        {{-- KOP SURAT --}}
        <table class="kop">
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div class="company">PT. TEKNOGRAFI TRI CAWANASKA</div>
                    <div class="sub">
                        Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang<br>
                        Email: teknografitricawanaska@gmail.com | Telp/WA: +62812 3339 0799
                    </div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    @if(! empty($logoBase64))
                        <img src="{{ $logoBase64 }}" style="width: 65px; height: auto; margin-bottom: 3px;" alt="Logo TTC"><br>
                    @endif
                    <div class="doc-title">SLIP GAJI KARYAWAN</div>
                    <div class="doc-meta">
                        No. Slip: <strong>{{ $gaji->kode_slip }}</strong> | 
                        Status: <strong style="color: {{ $gaji->status === 'Paid' ? '#059669' : ($gaji->status === 'Approved' ? '#2563eb' : '#d97706') }};">{{ strtoupper($gaji->status) }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        {{-- INFO KARYAWAN & PERIODE --}}
        <table class="info-box">
            <tr>
                <td class="info-label">Nama Karyawan</td>
                <td class="info-val">: {{ $gaji->karyawan?->nama ?? '-' }}</td>
                <td class="info-label">Periode Gaji</td>
                <td class="info-val">: {{ $gaji->periode }} ({{ $gaji->tipe_payroll ?: 'Bulanan' }})</td>
            </tr>
            <tr>
                <td class="info-label">NIP / Kode</td>
                <td class="info-val">: {{ $gaji->karyawan?->nip ?: '-' }}</td>
                <td class="info-label">Tgl Pembayaran</td>
                <td class="info-val">: {{ $gaji->tanggal_pembayaran ? \Carbon\Carbon::parse($gaji->tanggal_pembayaran)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Jabatan / Dept</td>
                <td class="info-val">: {{ $gaji->karyawan?->jabatan ?: '-' }} ({{ $gaji->karyawan?->departemen?->nama_departemen ?: 'Umum' }})</td>
                <td class="info-label">Metode Pembayaran</td>
                <td class="info-val">: {{ $gaji->metode_pembayaran }} {{ $gaji->karyawan?->nama_bank ? '(' . $gaji->karyawan->nama_bank . ' ' . $gaji->karyawan->no_rekening . ')' : '' }}</td>
            </tr>
        </table>

        {{-- RINCIAN PENDAPATAN & POTONGAN --}}
        <table class="cols-wrap">
            <tr>
                {{-- KOLOM KIRI: PENDAPATAN --}}
                <td style="padding-right: 6px;">
                    <table class="grid">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">(+) PENDAPATAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 60%;">Gaji Pokok {{ $gaji->jumlah_hari_hadir > 0 && in_array($gaji->tipe_payroll, ['Harian','Freelance']) ? "({$gaji->jumlah_hari_hadir} hr)" : '' }}</td>
                                <td class="text-right">{{ $fmt($gaji->gaji_pokok) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Jabatan</td>
                                <td class="text-right">{{ $fmt($gaji->tunjangan_jabatan) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Kompetensi</td>
                                <td class="text-right">{{ $fmt($gaji->tunjangan_kompetensi) }}</td>
                            </tr>
                            <tr>
                                <td>Insentif Kehadiran</td>
                                <td class="text-right">{{ $fmt($gaji->tunjangan_intensif) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Makan & Hadir</td>
                                <td class="text-right">{{ $fmt((float)$gaji->tunjangan_makan + (float)$gaji->tunjangan_hadir) }}</td>
                            </tr>
                            <tr>
                                <td>Uang Lembur</td>
                                <td class="text-right">{{ $fmt($gaji->total_lembur) }}</td>
                            </tr>
                            <tr>
                                <td>Bonus & Tambahan</td>
                                <td class="text-right">{{ $fmt($gaji->total_bonus) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Lain-lain</td>
                                <td class="text-right">{{ $fmt($gaji->tunjangan_lain) }}</td>
                            </tr>
                            <tr class="tot-row-income">
                                <td>TOTAL PENDAPATAN</td>
                                <td class="text-right">{{ $fmt($gaji->total_pendapatan) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>

                {{-- KOLOM KANAN: POTONGAN --}}
                <td style="padding-left: 6px;">
                    <table class="grid">
                        <thead>
                            <tr>
                                <th colspan="2" class="pot-head text-center">(-) POTONGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 60%;">BPJS Ketenagakerjaan</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_bpjs) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Absen / Scorsing</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_kehadiran) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Terlambat / Izin</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_tunjangan) }}</td>
                            </tr>
                            <tr>
                                <td>Cicilan Pinjaman (Kasbon)</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_pinjaman) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan PPh 21</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_pph21) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Tabungan / Target / Lain</td>
                                <td class="text-right">{{ $fmt($gaji->potongan_lain) }}</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr class="tot-row-deduct">
                                <td>TOTAL POTONGAN</td>
                                <td class="text-right">{{ $fmt($gaji->total_potongan) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        {{-- KOTAK TAKE HOME PAY (THP) --}}
        <div class="thp-card">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: middle;">
                        <div class="thp-title">GAJI BERSIH DITERIMA (TAKE HOME PAY)</div>
                        <div class="thp-terbilang">Terbilang: # {{ \App\Support\MoneyHelper::terbilang($gaji->gaji_bersih) }} Rupiah #</div>
                    </td>
                    <td class="thp-amount" style="vertical-align: middle;">
                        {{ $fmt($gaji->gaji_bersih) }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- CATATAN JIKA ADA --}}
        @if($gaji->keterangan)
            <div class="notes-box">
                <strong>Catatan:</strong> {{ $gaji->keterangan }}
            </div>
        @endif

        {{-- TANDA TANGAN (2 KOLOM RESMI: MENGETAHUI & PENERIMA) --}}
        <table class="sig-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    <strong>Bagian Keuangan & HRD</strong>
                </td>
                <td>
                    Diterima oleh,<br>
                    <strong>Penerima / Karyawan</strong>
                </td>
            </tr>
            <tr>
                <td class="sig-space">&nbsp;</td>
                <td class="sig-space">&nbsp;</td>
            </tr>
            <tr class="text-bold">
                <td>( PT. Teknografi Tri Cawanaska )</td>
                <td>( {{ $gaji->karyawan?->nama ?? 'Karyawan' }} )</td>
            </tr>
        </table>
    </div>
@empty
    <p style="text-align:center; padding: 30px;">Tidak ada slip gaji untuk periode {{ $periode ?? '-' }}.</p>
@endforelse
</body>
</html>
