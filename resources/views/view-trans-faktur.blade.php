<x-filament::page>

@php
    $record = $this->record;
@endphp

<div class="space-y-6">

    {{-- ==================== INVOICE HEADER SUMMARY ==================== --}}
    <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-xs dark:bg-gray-900 dark:border-gray-800">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            {{-- Left Info --}}
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 text-xs font-medium border border-primary-200/30 dark:border-primary-500/20">
                        Faktur Pajak
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 text-xs font-medium border border-emerald-200/30 dark:border-emerald-500/20">
                        Transaksi Berhasil
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $record->no_faktur }}
                </h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                    <p>Ref Invoice: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $record->no_invoice ?? '-' }}</span></p>
                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                    <p>{{ \Carbon\Carbon::parse($record->tanggal_transaksi)->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            {{-- Right Info (Grand Total) --}}
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 dark:bg-gray-900 dark:border-gray-800 min-w-[240px] md:text-right flex flex-col justify-center">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grand Total</span>
                <p class="text-2xl font-black text-primary-600 dark:text-primary-400 mt-1">
                    Rp {{ number_format($record->grand_total, 0, ',', '.') }}
                </p>
            </div>

        </div>
    </div>


    {{-- ==================== TWO COLUMN DETAILS (LIKE FILAMENT FORMS) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- DETAIL TRANSAKSI --}}
        <div class="lg:col-span-2 p-6 bg-white rounded-xl border border-gray-200 shadow-xs dark:bg-gray-900 dark:border-gray-800">
            <h2 class="text-sm font-semibold leading-6 text-gray-950 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Detail Transaksi
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-gray-100 dark:border-gray-800 pt-4">
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Order (PO)</dt>
                    <dd class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $record->purchaseOrder->no_po }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Person In Charge (PIC)</dt>
                    <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $record->pic?->nama ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Gudang Tujuan</dt>
                    <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $record->gudang ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Transaksi</dt>
                    <dd class="text-sm text-gray-900 dark:text-white font-medium">
                        {{ \Carbon\Carbon::parse($record->tanggal_transaksi)->translatedFormat('d F Y') }}
                    </dd>
                </div>
            </div>
        </div>

        {{-- VENDOR / SUPPLIER --}}
        <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-xs dark:bg-gray-900 dark:border-gray-800">
            <h2 class="text-sm font-semibold leading-6 text-gray-950 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Vendor / Supplier
            </h2>
            
            <div class="space-y-4 border-t border-gray-100 dark:border-gray-800 pt-4">
                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Perusahaan</dt>
                    <dd class="text-sm font-bold text-gray-900 dark:text-white">{{ $record->purchaseOrder->supplier?->nama_supplier }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">NPWP</dt>
                    <dd class="text-sm font-mono tracking-wide text-gray-900 dark:text-white font-medium">{{ $record->purchaseOrder->supplier?->npwp ?? '-' }}</dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat Operasional</dt>
                    <dd class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $record->purchaseOrder->supplier?->alamat ?? '-' }}</dd>
                </div>
            </div>
        </div>

    </div>


    {{-- ==================== LINE ITEMS TABLE (NATIVE REPEATER STYLE) ==================== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
        
        {{-- Header Section --}}
        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/10 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold leading-6 text-gray-950 dark:text-white">Rincian Item & Perpajakan</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kalkulasi nilai subtotal, potongan diskon, dan PPN</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 text-xs font-medium">
                {{ $record->items->count() }} Item
            </span>
        </div>

        {{-- Repeater Content --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($record->items as $item)
                @php
                    $bruto = $item->qty * $item->harga_satuan;
                    $subtotal = $bruto - $item->diskon;
                    $ppn = $subtotal * 0.11;
                    $total = $subtotal + $ppn;
                @endphp

                <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    {{-- Left side: Item & Qty --}}
                    <div class="lg:col-span-4 space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 block uppercase tracking-wider">Deskripsi Produk</span>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">
                                {{ $item->deskripsi }}
                            </h3>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 text-xs font-medium border border-gray-200/50 dark:border-gray-700/50">
                                {{ $item->qty }} {{ $item->unit }}
                            </span>
                        </div>
                    </div>

                    {{-- Right side: Detailed Costs Grid --}}
                    <div class="lg:col-span-8 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50/50 dark:bg-gray-800/30 p-4 rounded-xl border border-gray-100 dark:border-gray-800/50">
                        <div class="space-y-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Harga Satuan</span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Diskon</span>
                            <p class="text-sm font-medium {{ $item->diskon > 0 ? 'text-danger-600 dark:text-danger-400 font-semibold' : 'text-gray-500' }}">
                                {{ $item->diskon > 0 ? '-Rp ' . number_format($item->diskon, 0, ',', '.') : 'Rp 0' }}
                            </p>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Subtotal</span>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                        </div>

                        <div class="space-y-0.5">
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">PPN (11%)</span>
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400 font-semibold">+Rp {{ number_format($ppn, 0, ',', '.') }}</p>
                        </div>

                        {{-- Total Item Highlight --}}
                        <div class="col-span-2 sm:col-span-4 border-t border-gray-200 dark:border-gray-700/60 pt-3 mt-1 flex justify-between items-center">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Akhir Item</span>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>

</div>

</x-filament::page>