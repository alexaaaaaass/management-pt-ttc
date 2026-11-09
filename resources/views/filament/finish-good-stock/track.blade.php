@php
$movements = $stock->movements()
    ->with(['spk'])
    ->latest()
    ->take(25)
    ->get();

$fg = $stock->finishGoodItem;
$status = $stock->on_hand > 0 ? 'Ready Siap Kirim' : 'Stok Kosong';
$statusDot = $stock->on_hand > 0 ? 'bg-emerald-500' : 'bg-slate-400';
$statusBadge = $stock->on_hand > 0
    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20'
    : 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-400/10 dark:text-slate-400 dark:ring-slate-400/20';
@endphp

<div class="space-y-8 max-w-7xl mx-auto text-slate-900 dark:text-slate-100 py-1">

    {{-- ============ Header ============ --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 py-6 shadow-sm">
        <div class="min-w-0">
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-slate-500">
                <span>Warehouse Barang Jadi</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Detail Stok & Mutasi</span>
            </div>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-slate-900 dark:text-white truncate">
                {{ $fg?->nama_barang ?? '-' }}
            </h1>
            <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                <span class="font-mono text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded">
                    {{ $fg?->kode_material_produk ?? '-' }}
                </span>
                @if($fg?->customer)
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>Customer: {{ $fg->customer->nama_customer }}</span>
                @endif
                @if($fg?->satuan)
                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                    <span>Satuan: {{ $fg->satuan->nama_satuan }}</span>
                @endif
            </div>
        </div>

        <div class="flex sm:flex-col items-center sm:items-end gap-2 sm:gap-1.5 shrink-0">
            <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">
                Status Ketersediaan
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium {{ $statusBadge }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                {{ $status }}
            </span>
        </div>
    </div>

    {{-- ============ Summary Cards ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        {{-- On Hand --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">On Hand (Siap Kirim)</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-400/10 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">
                {{ number_format($stock->on_hand, 0, ',', '.') }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Stok fisik siap dikirim via Surat Jalan</p>
        </div>

        {{-- Blocked --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Blocked / Hold QC</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ number_format($stock->blocked, 0, ',', '.') }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Stok ditahan / blokir dari Packaging</p>
        </div>

        {{-- Total Activity --}}
        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">Total Riwayat Mutasi</span>
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ $stock->movements()->count() }}
            </div>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Total aktivitas masuk & keluar</p>
        </div>

    </div>

    {{-- ============ History Table ============ --}}
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Riwayat Pergerakan Barang Jadi</h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">Menampilkan {{ $movements->count() }} aktivitas mutasi terakhir</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-3 text-center font-semibold">Tipe / Sumber</th>
                        <th class="px-6 py-3 text-left font-semibold">No SPK</th>
                        <th class="px-6 py-3 text-right font-semibold">Qty</th>
                        <th class="px-6 py-3 text-right font-semibold">Sebelum</th>
                        <th class="px-6 py-3 text-right font-semibold">Sesudah</th>
                        <th class="px-6 py-3 text-left font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($movements as $move)
                    @php
                        $badge = match($move->movement_type){
                            'IN'         => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20',
                            'OUT'        => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20',
                            'BLOCK'      => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20',
                            'ADJUSTMENT' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/20',
                            default      => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-400/10 dark:text-slate-400 dark:ring-slate-400/20'
                        };

                        $sourceLabel = match($move->source){
                            'PACKAGING'         => 'Packaging Produksi',
                            'SUBCOUNT_IN'       => 'Subcount In',
                            'SURAT_JALAN'       => 'Surat Jalan (Kirim)',
                            'MANUAL_ADJUSTMENT' => 'Penyesuaian Manual',
                            default             => $move->source ?: $move->movement_type
                        };
                    @endphp

                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-700 dark:text-slate-300">
                            {{ $move->created_at?->format('d M Y') }}
                            <div class="text-xs text-slate-400 dark:text-slate-500">{{ $move->created_at?->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-center">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $badge }}">
                                {{ $move->movement_type }} &bull; {{ $sourceLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-600 dark:text-slate-300 font-mono text-xs">
                            {{ $move->spk?->no_spk ?? '—' }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-semibold tabular-nums {{ $move->movement_type === 'OUT' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $move->movement_type === 'OUT' ? '-' : '+' }}{{ number_format($move->qty, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right tabular-nums text-slate-500 dark:text-slate-400">
                            {{ number_format($move->before_stock, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap text-right tabular-nums font-semibold text-slate-900 dark:text-white">
                            {{ number_format($move->after_stock, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3.5 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $move->remarks }}">
                            {{ $move->remarks ?: '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Belum ada riwayat pergerakan stok barang jadi</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

