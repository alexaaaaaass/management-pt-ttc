<x-filament-panels::page>
    @php
        $recap = $this->getRecapData();
        $tahunList = range(now()->year - 2, now()->year + 1);
    @endphp

    <div class="mb-4 flex flex-wrap justify-between items-center gap-3 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">Rekap Hak & Penggunaan Cuti Tahunan</h3>
            <p class="text-xs text-gray-500">Kuota Cuti Tahunan: {{ $recap['kuota'] }} Hari / Tahun (Tahun {{ $recap['tahun'] }}) — tidak menghitung cuti berstatus Rejected.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ \App\Filament\Admin\Resources\CutiResource::getUrl() }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                ← Kembali ke Data Cuti
            </a>
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Filter Tahun:</label>
            <select wire:model.live="tahun" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm py-1.5 pl-3 pr-9" style="min-width:96px;padding-right:2.25rem;">
                @foreach($tahunList as $yr)
                    <option value="{{ $yr }}">{{ $yr }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 font-semibold text-xs uppercase tracking-wider">
                    <th class="text-left p-3">Nama Karyawan</th>
                    <th class="text-left p-3">Departemen / Jabatan</th>
                    <th class="text-center p-3">Hak Cuti</th>
                    <th class="text-center p-3">Digunakan</th>
                    <th class="text-center p-3">Sisa Cuti</th>
                    <th class="text-center p-3">Status Kuota</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recap['rows'] as $row)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="p-3 font-semibold text-gray-900 dark:text-white">
                            {{ $row['nama'] }}
                            <div class="text-xs font-normal text-gray-400">NIP: {{ $row['nip'] ?: '-' }}</div>
                        </td>
                        <td class="p-3 text-xs text-gray-600 dark:text-gray-400">
                            {{ $row['departemen'] }}
                            <div class="text-gray-400">{{ $row['jabatan'] }}</div>
                        </td>
                        <td class="p-3 text-center font-medium">{{ $row['kuota'] }} Hari</td>
                        <td class="p-3 text-center font-medium text-amber-600 dark:text-amber-400">{{ $row['digunakan'] }} Hari</td>
                        <td class="p-3 text-center font-bold text-base {{ $row['sisa'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $row['sisa'] }} Hari
                        </td>
                        <td class="p-3 text-center">
                            @if($row['sisa'] > 3)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300">Tersedia</span>
                            @elseif($row['sisa'] > 0)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">Menipis</span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">Habis</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">Belum ada data karyawan aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
