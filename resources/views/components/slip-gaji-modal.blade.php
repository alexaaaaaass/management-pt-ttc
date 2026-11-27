<div class="p-6 bg-white dark:bg-gray-900 rounded-xl text-gray-800 dark:text-gray-200 shadow-sm">
    <!-- Header Perusahaan & Dokumen -->
    <div class="border-b-2 border-gray-100 dark:border-gray-800 pb-4 mb-4 flex justify-between items-start">
        <div>
            <h2 class="text-lg font-bold tracking-wide text-gray-900 dark:text-white">PT. TEKNOGRAFI TRI CAWANASKA</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400">Pergudangan Sentral Singosari, Ardimulyo, Singosari, Malang · +62812 3339 0799</p>
            <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 mt-1">SLIP PEMBAYARAN GAJI KARYAWAN ({{ strtoupper($gaji->tipe_payroll ?: 'BULANAN') }})</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 text-xs font-bold rounded-full {{ $gaji->status === 'Paid' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($gaji->status === 'Approved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300') }}">
                {{ strtoupper($gaji->status) }}
            </span>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono font-medium">No: {{ $gaji->kode_slip }}</p>
        </div>
    </div>

    <!-- Info Karyawan & Periode -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm bg-slate-50 dark:bg-slate-800/60 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
        <div>
            <table class="w-full text-xs">
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1 w-32">Nama Karyawan</td>
                    <td class="font-bold text-slate-900 dark:text-white">: {{ $gaji->karyawan?->nama }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1">NIP / Kode</td>
                    <td class="font-medium">: {{ $gaji->karyawan?->nip ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1">Jabatan / Dept</td>
                    <td>: {{ $gaji->karyawan?->jabatan ?: '-' }} ({{ $gaji->karyawan?->departemen?->nama_departemen ?: 'Umum' }})</td>
                </tr>
            </table>
        </div>
        <div>
            <table class="w-full text-xs">
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1 w-32">Periode Gaji</td>
                    <td class="font-bold text-slate-900 dark:text-white">: {{ $gaji->periode }} ({{ $gaji->tipe_payroll ?: 'Bulanan' }})</td>
                </tr>
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1">Tgl Pembayaran</td>
                    <td>: {{ $gaji->tanggal_pembayaran ? \Carbon\Carbon::parse($gaji->tanggal_pembayaran)->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-slate-500 dark:text-slate-400 py-1">Metode Bayar</td>
                    <td>: {{ $gaji->metode_pembayaran }} {{ $gaji->karyawan?->nama_bank ? "({$gaji->karyawan->nama_bank} - {$gaji->karyawan->no_rekening})" : '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Rincian Penghasilan vs Potongan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Pendapatan (Earnings) -->
        <div class="border rounded-lg p-4 border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/20 dark:bg-emerald-950/10">
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-emerald-100 dark:border-emerald-900/50 pb-2 mb-3">
                (+) Komponen Pendapatan
            </h3>
            <table class="w-full text-xs">
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Gaji Pokok {{ $gaji->jumlah_hari_hadir > 0 && in_array($gaji->tipe_payroll, ['Harian','Freelance']) ? "({$gaji->jumlah_hari_hadir} hari)" : '' }}</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Jabatan</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->tunjangan_jabatan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Kompetensi</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->tunjangan_kompetensi, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Insentif Kehadiran</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->tunjangan_intensif, 0, ',', '.') }}</td>
                </tr>
                @if((float) $gaji->tunjangan_makan > 0 || (float) $gaji->tunjangan_hadir > 0)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Makan & Hadir</td>
                        <td class="py-1.5 text-right font-medium">Rp {{ number_format((float)$gaji->tunjangan_makan + (float)$gaji->tunjangan_hadir, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Uang Lembur</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->total_lembur, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Bonus & Tambahan</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->total_bonus, 0, ',', '.') }}</td>
                </tr>
                @if((float) $gaji->tunjangan_lain > 0)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Lain-lain</td>
                        <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->tunjangan_lain, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="font-bold text-gray-900 dark:text-white bg-emerald-100/60 dark:bg-emerald-950/40">
                    <td class="py-2 px-1">Total Pendapatan Kotor</td>
                    <td class="py-2 px-1 text-right text-emerald-700 dark:text-emerald-400">Rp {{ number_format((float) $gaji->total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Potongan (Deductions) -->
        <div class="border rounded-lg p-4 border-rose-200 dark:border-rose-900/50 bg-rose-50/20 dark:bg-rose-950/10">
            <h3 class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 border-b border-rose-100 dark:border-rose-900/50 pb-2 mb-3">
                (-) Komponen Potongan
            </h3>
            <table class="w-full text-xs">
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">BPJS Ketenagakerjaan</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->potongan_bpjs, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Potongan Absen / Scorsing</td>
                    <td class="py-1.5 text-right font-medium text-rose-600">Rp {{ number_format((float) $gaji->potongan_kehadiran, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Potongan Terlambat / Izin</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->potongan_tunjangan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Cicilan Pinjaman (Kasbon)</td>
                    <td class="py-1.5 text-right font-medium text-amber-600">Rp {{ number_format((float) $gaji->potongan_pinjaman, 0, ',', '.') }}</td>
                </tr>
                @if((float) $gaji->potongan_pph21 > 0)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">PPh 21</td>
                        <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->potongan_pph21, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if((float) $gaji->potongan_lain > 0)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-1.5 text-gray-600 dark:text-gray-400">Potongan Tabungan / Lain-lain</td>
                        <td class="py-1.5 text-right font-medium">Rp {{ number_format((float) $gaji->potongan_lain, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="font-bold text-gray-900 dark:text-white bg-rose-100/60 dark:bg-rose-950/40">
                    <td class="py-2 px-1">Total Seluruh Potongan</td>
                    <td class="py-2 px-1 text-right text-rose-700 dark:text-rose-400">Rp {{ number_format((float) $gaji->total_potongan, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Take Home Pay Highlight Box -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 border-2 border-blue-500 dark:border-blue-700 rounded-xl p-4 flex justify-between items-center mb-6">
        <div>
            <div class="text-xs uppercase font-bold text-blue-900 dark:text-blue-300">Gaji Bersih Diterima (Take Home Pay)</div>
            <div class="text-xs text-blue-700 dark:text-blue-400 italic mt-0.5">
                # {{ \App\Support\MoneyHelper::terbilang($gaji->gaji_bersih) }} Rupiah #
            </div>
        </div>
        <div class="text-2xl font-black text-blue-900 dark:text-blue-200 font-mono">
            Rp {{ number_format((float) $gaji->gaji_bersih, 0, ',', '.') }}
        </div>
    </div>

    @if($gaji->keterangan)
        <div class="text-xs text-gray-500 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
            <strong>Catatan:</strong> {{ $gaji->keterangan }}
        </div>
    @endif
</div>
