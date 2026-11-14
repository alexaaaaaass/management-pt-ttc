@php
    $oc = $orderConfirmation;
    $items = $oc->items;
    $customer = $oc->customer;

    $statusLabel = match ($oc->status) {
        'draft'           => 'DRAFT',
        'reviewed'        => 'REVIEWED MARKETING',
        'approved'        => 'APPROVED',
        'deal'            => 'DEAL CUSTOMER',
        'partial_so'      => 'SEBAGIAN SO TERBIT',
        'converted_to_so' => 'SEMUA SO TERBIT',
        'rejected'        => 'DITOLAK',
        default           => strtoupper($oc->status ?? '-'),
    };

    $statusColor = match ($oc->status) {
        'draft'           => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-700',
        'deal'            => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
        'partial_so'      => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-300 dark:border-amber-700',
        'converted_to_so' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
        'rejected'        => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-300 dark:border-rose-700',
        default           => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-300 dark:border-blue-700',
    };
@endphp

<div class="space-y-6 text-gray-900 dark:text-gray-100">

    {{-- ================= Top Info & Quick Actions ================= --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                        Order Confirmation
                    </span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                    @if($salesOrder)
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            No SO Terkait: {{ $salesOrder->no_sales_order }}
                        </span>
                    @endif
                </div>

                <div class="mt-2 flex flex-wrap items-baseline gap-3">
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $oc->doc_no }}
                    </h2>
                    @if($oc->order_id)
                        <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded">
                            Order ID: {{ $oc->order_id }}
                        </span>
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-2 text-xs text-gray-600 dark:text-gray-400">
                    <div>
                        <span class="block text-gray-400 dark:text-gray-500">Customer:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $customer?->nama_customer ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-gray-400 dark:text-gray-500">Tanggal OC:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $oc->tanggal ? \Carbon\Carbon::parse($oc->tanggal)->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-gray-400 dark:text-gray-500">Lead Time:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $oc->lead_time ?: '21 Hari Kerja' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-gray-400 dark:text-gray-500">Total Item:</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                            {{ $items->count() }} Item Pesanan
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick action buttons --}}
            <div class="flex flex-wrap md:flex-col items-stretch md:items-end gap-2 shrink-0">
                <a
                    href="{{ route('filament.admin.resources.order-confirmations.edit', $oc) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-500 rounded-lg shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka Dokumen OC
                </a>
                <a
                    href="{{ route('order-confirmation.preview', $oc) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ================= Daftar Item Order Confirmation ================= --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Daftar Item & Penawaran Harga
            </h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ $items->count() }} Item Terdaftar
            </span>
        </div>

        @if($items->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center text-sm text-gray-500">
                Tidak ada item terdaftar pada Order Confirmation ini.
            </div>
        @else
            <div class="space-y-4">
                @foreach($items as $index => $item)
                    @php
                        $isCurrentSoItem = $salesOrder && (
                            ($item->finish_good_item_id && (int) $item->finish_good_item_id === (int) $salesOrder->itemable_id)
                            || trim(strtolower($item->item_name ?? '')) === trim(strtolower($salesOrder->product_name ?? ''))
                        );

                        $qty = (float) ($item->qty ?? 0);
                        $harga = (float) ($item->harga ?? 0);
                        $total = (float) ($item->total ?: ($qty * $harga));
                        $hpp = (float) ($item->hpp_per_pcs ?? 0);
                        $hargaIndikatif = (float) ($item->harga_jual_indikatif ?? 0);
                        $kertasKg = (float) ($item->kebutuhan_kertas_kg ?? 0);
                        $kertasMeter = (float) ($item->kebutuhan_kertas_meter ?? 0);
                    @endphp

                    <div class="rounded-xl border {{ $isCurrentSoItem ? 'border-primary-500 ring-2 ring-primary-500/20 bg-primary-50/10 dark:bg-primary-950/10' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900' }} p-4 shadow-sm transition">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-600 dark:text-gray-300">
                                        {{ $index + 1 }}
                                    </span>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                        {{ $item->item_name ?? ($item->finishGoodItem?->nama_barang ?? 'Item Penawaran') }}
                                    </h4>
                                    @if($isCurrentSoItem)
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                            👉 Terkait Sales Order Ini
                                        </span>
                                    @endif
                                </div>

                                @if($item->finishGoodItem)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <span>Master Finish Good:</span>
                                        <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-gray-700 dark:text-gray-300">
                                            {{ $item->finishGoodItem->kode_material_produk ?? '-' }}
                                        </span>
                                        <span>({{ $item->finishGoodItem->nama_barang }})</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Harga & Total item --}}
                            <div class="sm:text-right shrink-0 bg-gray-50 dark:bg-gray-800/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($qty, 0, ',', '.') }} {{ $item->satuan ?: 'Pcs' }} &times; Rp {{ number_format($harga, 2, ',', '.') }}
                                </div>
                                <div class="text-base font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($total, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- Spesifikasi Teknis Pills --}}
                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 text-xs">
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Jenis</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">{{ $item->jenis ?: '-' }}</span>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Komponen</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">{{ $item->komponen ?: '-' }}</span>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Kertas</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">{{ $item->kertas ?: '-' }}</span>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Warna</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">{{ $item->warna ?: '-' }}</span>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Finishing</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block" title="{{ $item->finishing }}">{{ $item->finishing ?: '-' }}</span>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                                <span class="block text-gray-400 dark:text-gray-500 text-[10px] uppercase font-semibold">Isi / Batang</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate block">{{ $item->isi ? $item->isi . ' Batang' : '-' }}</span>
                            </div>
                        </div>

                        {{-- Flexo HPP breakdown jika ada --}}
                        @if($hpp > 0 || $kertasKg > 0)
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 px-3 py-2 text-xs text-amber-900 dark:text-amber-200">
                                <span class="font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    Kalkulasi Mesin Flexo:
                                </span>
                                @if($kertasKg > 0)
                                    <span>📦 Kebutuhan Kertas: <strong>{{ number_format($kertasKg, 2, ',', '.') }} kg</strong> ({{ number_format($kertasMeter, 0, ',', '.') }} m)</span>
                                @endif
                                @if($hpp > 0)
                                    <span>🏷️ HPP Pokok: <strong>Rp {{ number_format($hpp, 2, ',', '.') }}</strong> / pcs</span>
                                @endif
                                @if($hargaIndikatif > 0)
                                    <span>💡 Rekomendasi Jual: <strong>Rp {{ number_format($hargaIndikatif, 2, ',', '.') }}</strong> / pcs</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================= Ringkasan Biaya ================= --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Ringkasan Biaya Order Confirmation
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-3">
                <span class="block text-xs text-gray-500 dark:text-gray-400">Subtotal Penawaran:</span>
                <span class="text-base font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format((float) ($oc->subtotal ?? 0), 2, ',', '.') }}
                </span>
            </div>
            <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-3">
                <span class="block text-xs text-gray-500 dark:text-gray-400">Diskon:</span>
                <span class="text-base font-bold text-amber-600 dark:text-amber-400">
                    Rp {{ number_format((float) ($oc->diskon ?? 0), 2, ',', '.') }}
                </span>
            </div>
            <div class="rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-3">
                <span class="block text-xs text-emerald-700 dark:text-emerald-300 font-medium">Total Akhir (Grand Total):</span>
                <span class="text-lg font-extrabold text-emerald-800 dark:text-emerald-200">
                    Rp {{ number_format((float) ($oc->grand_total ?? 0), 2, ',', '.') }}
                </span>
            </div>
        </div>

        @if(!empty($oc->catatan))
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs">
                <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-1">Catatan Tambahan:</span>
                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $oc->catatan }}</p>
            </div>
        @endif
    </div>

</div>
