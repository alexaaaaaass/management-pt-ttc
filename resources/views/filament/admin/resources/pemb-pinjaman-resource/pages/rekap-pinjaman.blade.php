<x-filament-panels::page>
    <div class="mb-4 flex flex-wrap justify-between items-center gap-3 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div>
            <h3 class="font-bold text-gray-800 dark:text-gray-100">Rekap Pinjaman & Cicilan Karyawan</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan total pinjaman, total cicilan (manual & potong gaji otomatis), serta sisa piutang per karyawan.</p>
        </div>
        <a href="{{ route('hrd.pinjaman.rekap.preview') }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700">
            Cetak PDF Rekap Pinjaman
        </a>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-300 font-semibold text-xs uppercase">
                    <th class="p-3 text-left">No. Bukti</th>
                    <th class="p-3 text-left">Nama Karyawan</th>
                    <th class="p-3 text-center">Tgl Pengajuan</th>
                    <th class="p-3 text-right">Total Pinjaman</th>
                    <th class="p-3 text-right">Terbayar</th>
                    <th class="p-3 text-right">Sisa Pinjaman</th>
                    <th class="p-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($this->data as $item)
                    @php
                        $totalPembayaran = (float) $item->pembayaran->sum('nominal_pembayaran');
                        $sisa = max(0, (float) $item->nilai_pinjaman - $totalPembayaran);
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                        <td class="p-3 font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $item->nomor_bukti }}</td>
                        <td class="p-3 font-semibold text-gray-900 dark:text-white">{{ $item->karyawan?->nama ?? '-' }}</td>
                        <td class="p-3 text-center text-gray-600 dark:text-gray-300">{{ $item->tanggal_pengajuan ? \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d/m/Y') : '-' }}</td>
                        <td class="p-3 text-right text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $item->nilai_pinjaman, 0, ',', '.') }}</td>
                        <td class="p-3 text-right text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</td>
                        <td class="p-3 text-right font-bold {{ $sisa <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            @if($sisa <= 0)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Lunas</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">Belum Lunas</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-400">Belum ada data pinjaman karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
