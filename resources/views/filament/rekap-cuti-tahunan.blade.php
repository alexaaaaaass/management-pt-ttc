<x-filament-panels::page>
    @php
        $tahun = now()->year;
        $karyawans = \App\Models\Karyawan::where('status_pegawai', 'Aktif')->get();
    @endphp

    <div class="mb-4 flex justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">Rekap Hak & Penggunaan Cuti Tahunan</h3>
            <p class="text-xs text-gray-500">Kuota Cuti Tahunan: 12 Hari / Tahun (Tahun {{ $tahun }})</p>
        </div>
        <div class="text-xs font-semibold px-3 py-1 bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 rounded-full border border-primary-200">
            Tahun Berjalan: {{ $tahun }}
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <th class="text-left p-3">Nama Karyawan</th>
                    <th class="text-left p-3">Departemen / Jabatan</th>
                    <th class="text-center p-3">Hak Cuti Tahunan</th>
                    <th class="text-center p-3">Digunakan</th>
                    <th class="text-center p-3">Sisa Cuti</th>
                    <th class="text-center p-3">Status Kuota</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($karyawans as $karyawan)
                @php
                    $digunakan = \App\Models\Cuti::query()
                        ->where('karyawan_id', $karyawan->id)
                        ->where('jenis_cuti', 'Cuti Tahunan')
                        ->whereYear('tanggal_mulai', $tahun)
                        ->sum('jumlah_hari');

                    $sisa = max(0, 12 - $digunakan);
                @endphp

                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                        {{ $karyawan->nama }}
                        <div class="text-xs font-normal text-gray-400">NIP: {{ $karyawan->nip ?: '-' }}</div>
                    </td>

                    <td class="p-3 text-xs text-gray-600 dark:text-gray-400">
                        {{ $karyawan->departemen?->nama_departemen ?: '-' }}
                        <div class="text-gray-400">{{ $karyawan->jabatan }}</div>
                    </td>

                    <td class="p-3 text-center font-medium">
                        12 Hari
                    </td>

                    <td class="p-3 text-center font-medium text-amber-600 dark:text-amber-400">
                        {{ $digunakan }} Hari
                    </td>

                    <td class="p-3 text-center font-bold text-base {{ $sisa > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $sisa }} Hari
                    </td>

                    <td class="p-3 text-center">
                        @if($sisa > 3)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300">
                                Tersedia
                            </span>
                        @elseif($sisa > 0)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">
                                Menipis
                            </span>
                        @else
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">
                                Habis
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-400">
                        Belum ada data karyawan aktif.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>