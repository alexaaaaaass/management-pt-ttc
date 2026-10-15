<div class="p-6 bg-white dark:bg-gray-900 rounded-xl text-gray-800 dark:text-gray-200">
    <!-- Header -->
    <div class="border-b-2 border-gray-200 dark:border-gray-700 pb-4 mb-4 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold tracking-wide text-gray-900 dark:text-white">PT. TATA TOKO CEMERLANG (TTC)</h2>
            <p class="text-xs text-gray-500">Jl. Industri Raya No. 88, Sidoarjo - Jawa Timur</p>
            <p class="text-xs font-semibold text-primary-600">SLIP PEMBAYARAN GAJI KARYAWAN</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 text-xs font-bold rounded-full {{ $gaji->status === 'Paid' ? 'bg-green-100 text-green-800' : ($gaji->status === 'Approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                {{ strtoupper($gaji->status) }}
            </span>
            <p class="text-xs text-gray-400 mt-1">Kode: <span class="font-mono font-bold">{{ $gaji->kode_slip }}</span></p>
        </div>
    </div>

    <!-- Info Karyawan -->
    <div class="grid grid-cols-2 gap-4 mb-6 text-sm bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
        <div>
            <table class="w-full text-xs">
                <tr>
                    <td class="text-gray-500 py-1 w-28">Nama Karyawan</td>
                    <td class="font-bold">: {{ $gaji->karyawan?->nama }}</td>
                </tr>
                <tr>
                    <td class="text-gray-500 py-1">NIP / NIK</td>
                    <td>: {{ $gaji->karyawan?->nip ?: '-' }} / {{ $gaji->karyawan?->nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-gray-500 py-1">Departemen / Jabatan</td>
                    <td>: {{ $gaji->karyawan?->departemen?->nama_departemen ?: '-' }} / {{ $gaji->karyawan?->jabatan ?: '-' }}</td>
                </tr>
            </table>
        </div>
        <div>
            <table class="w-full text-xs">
                <tr>
                    <td class="text-gray-500 py-1 w-28">Periode Gaji</td>
                    <td class="font-bold">: {{ $gaji->periode }}</td>
                </tr>
                <tr>
                    <td class="text-gray-500 py-1">Tgl Pembayaran</td>
                    <td>: {{ $gaji->tanggal_pembayaran ? \Carbon\Carbon::parse($gaji->tanggal_pembayaran)->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-gray-500 py-1">Metode Bayar</td>
                    <td>: {{ $gaji->metode_pembayaran }} {{ $gaji->karyawan?->nama_bank ? "({$gaji->karyawan->nama_bank} - {$gaji->karyawan->no_rekening})" : '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Rincian Penghasilan vs Potongan -->
    <div class="grid grid-cols-2 gap-6 mb-6">
        <!-- Kolom Kiri: PENDAPATAN -->
        <div class="border rounded-lg p-4 border-gray-200 dark:border-gray-700">
            <h3 class="text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-400 border-b pb-2 mb-3">
                (+) Komponen Pendapatan
            </h3>
            <table class="w-full text-xs">
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Gaji Pokok</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Jabatan</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->tunjangan_jabatan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Kompetensi</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->tunjangan_kompetensi, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Tunjangan Intensif</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->tunjangan_intensif, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Uang Lembur</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->total_lembur, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Bonus & Insentif</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->total_bonus, 0, ',', '.') }}</td>
                </tr>
                <tr class="font-bold text-gray-900 dark:text-white bg-green-50 dark:bg-green-950/30">
                    <td class="py-2 px-1">Total Pendapatan Kotor</td>
                    <td class="py-2 px-1 text-right text-green-600 dark:text-green-400">Rp {{ number_format($gaji->total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Kolom Kanan: POTONGAN -->
        <div class="border rounded-lg p-4 border-gray-200 dark:border-gray-700">
            <h3 class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-400 border-b pb-2 mb-3">
                (-) Komponen Potongan
            </h3>
            <table class="w-full text-xs">
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Potongan Tunjangan (Sanksi)</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->potongan_tunjangan, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Potongan Keterlambatan/Alpha</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->potongan_kehadiran, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">Cicilan Pinjaman Karyawan</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->potongan_pinjaman, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-1.5 text-gray-600 dark:text-gray-400">BPJS / Asuransi</td>
                    <td class="py-1.5 text-right font-medium">Rp {{ number_format($gaji->potongan_bpjs, 0, ',', '.') }}</td>
                </tr>
                <tr class="font-bold text-gray-900 dark:text-white bg-red-50 dark:bg-red-950/30">
                    <td class="py-2 px-1">Total Seluruh Potongan</td>
                    <td class="py-2 px-1 text-right text-red-600 dark:text-red-400">Rp {{ number_format($gaji->total_potongan, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Take Home Pay Box -->
    <div class="p-4 bg-gradient-to-r from-primary-50 to-emerald-50 dark:from-primary-950/40 dark:to-emerald-950/40 border-2 border-primary-500 rounded-xl flex justify-between items-center mb-6">
        <div>
            <p class="text-xs uppercase font-bold text-primary-700 dark:text-primary-300">Gaji Bersih Diterima (Take Home Pay)</p>
            <p class="text-xs text-gray-500">Hak pembayaran bersih setelah potongan</p>
        </div>
        <div class="text-2xl font-black text-primary-700 dark:text-primary-300">
            Rp {{ number_format($gaji->gaji_bersih, 0, ',', '.') }}
        </div>
    </div>

    <!-- Tanda Tangan Footer -->
    <div class="grid grid-cols-2 text-center text-xs pt-4 border-t border-gray-200 dark:border-gray-700 text-gray-500">
        <div>
            <p class="mb-14">Penerima,</p>
            <p class="font-bold uppercase text-gray-900 dark:text-white">{{ $gaji->karyawan?->nama }}</p>
        </div>
        <div>
            <p class="mb-14">Bagian Keuangan & HRD,</p>
            <p class="font-bold uppercase text-gray-900 dark:text-white">PT. TATA TOKO CEMERLANG</p>
        </div>
    </div>
</div>
