@php
$movements = $stock->item
    ->movements()
    ->latest()
    ->take(20)
    ->get();

// Status stok — palet semantik konsisten dengan badge tabel di bawah
$status = 'Aman';
$statusDot = 'bg-emerald-500';
$statusBadge = 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20';

if ($stock->available <= $stock->item->min_stock) {
    $status = 'Minimum';
    $statusDot = 'bg-red-500';
    $statusBadge = 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20';
} elseif ($stock->available <= ($stock->item->min_stock * 1.5)) {
    $status = 'Menipis';
    $statusDot = 'bg-amber-500';
    $statusBadge = 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20';
}
@endphp

<div class="space-y-8 max-w-7xl mx-auto text-slate-900 dark:text-slate-100 py-1">

    {{-- ============ Header ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 py-6 shadow-sm">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500">
                <span>Inventory</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Detail Stok</span>
            </div>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white truncate">
                {{ $stock->item->nama_master_item }}
            </h1>
            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded">
                    {{ $stock->item->kode_master_item }}
                </span>
                @if($stock->item->satuan)
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>Satuan: {{ $stock->item->satuan->nama_satuan }}</span>
                @endif
            </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-end gap-2 sm:gap-1.5 shrink-0">
            <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                Status Stok
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium {{ $statusBadge }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                {{ $status }}
            </span>
        </div>
    </div>

    {{-- ============ Summary Cards ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- On Hand --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">On Hand</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($stock->on_hand, 2) }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Total fisik di gudang</p>
        </div>

        {{-- Allocation --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Allocation</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($stock->allocation, 2) }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Sedang diproses / dipesan</p>
        </div>

        {{-- Available --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Available</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-400/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">
                {{ number_format($stock->available, 2) }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Siap untuk digunakan</p>
        </div>

        {{-- Minimum --}}
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 p-6">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Batas Minimum</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-200/70 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-slate-700 dark:text-slate-300">
                {{ number_format($stock->item->min_stock, 2) }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Trigger restock material</p>
        </div>

    </div>

    {{-- ============ History Table ============ --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Riwayat Pergerakan Material</h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">Menampilkan {{ $movements->count() }} aktivitas mutasi terakhir</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3 text-center font-semibold">Aktivitas</th>
                        <th class="px-6 py-3 text-right font-semibold">Qty</th>
                        <th class="px-6 py-3 text-right font-semibold">Sebelum</th>
                        <th class="px-6 py-3 text-right font-semibold">Sesudah</th>
                        <th class="px-6 py-3 text-left font-semibold">Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($movements as $move)
                    @php
                        $badge = match($move->movement_type){
                            'LPB'     => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20',
                            'APPROVE' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20',
                            'ISSUE'   => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20',
                            'RETURN'  => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20',
                            default   => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-400/10 dark:text-slate-400 dark:ring-slate-400/20'
                        };

                        $activity = match($move->movement_type){
                            'LPB'     => 'Barang Masuk',
                            'APPROVE' => 'Alokasi Material',
                            'ISSUE'   => 'Issue Material',
                            'RETURN'  => 'Return Material',
                            default   => $move->movement_type
                        };
                    @endphp

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ $move->created_at->format('d M Y') }}
                            <div class="text-xs text-slate-400 dark:text-slate-500">{{ $move->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-center">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                {{ $activity }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-semibold tabular-nums {{ $move->qty < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $move->qty < 0 ? number_format($move->qty, 2) : '+'.number_format($move->qty, 2) }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right tabular-nums text-slate-500 dark:text-slate-400">
                            {{ number_format($move->stock_before, 2) }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right tabular-nums font-semibold text-slate-900 dark:text-white">
                            {{ number_format($move->stock_after, 2) }}
                        </td>
                        <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $move->remarks }}">
                            {{ $move->remarks ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800">
                                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada riwayat pergerakan material</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>