<x-filament::page>

@php

$invoice = $this->record->load([
    'customer',
    'salesOrder',
    'salesOrder.itemable',
    'salesOrder.items.material',
    'suratJalan',
    'suratJalan.spk',
    'suratJalan.items.material',
    'suratJalan.items.salesOrderItem',
    'payments.metodeBayar',
    'payments.karyawan',
]);

$so = $invoice->salesOrder;
$sj = $invoice->suratJalan;
$spk = $sj?->spk;
$customer = $invoice->customer;

$paid = (float) ($invoice->total_payment ?? ($invoice->uang_muka + $invoice->payments->sum('nominal_bayar')));

$progress = $invoice->grand_total > 0
    ? min(100, ($paid / $invoice->grand_total) * 100)
    : 0;

$diskonNominal =
    ($invoice->subtotal * ($invoice->diskon ?? 0)) / 100;

$ppnNominal =
    ($invoice->subtotal * ($invoice->ppn ?? 0)) / 100;

@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT COLUMN --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- HEADER SECTION --}}
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                {{-- Info Utama --}}
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Invoice {{ $invoice->kode_invoice }}
                        </h1>
                        <x-filament::badge
                            :color="match($invoice->status){
                                'Paid' => 'success',
                                'Partial' => 'warning',
                                'Unpaid' => 'danger',
                                'Cancelled' => 'gray',
                                default => 'gray',
                            }"
                            size="md"
                        >
                            {{ strtoupper($invoice->status ?? 'DRAFT') }}
                        </x-filament::badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                        <div>Customer: <strong class="text-gray-700 dark:text-gray-200">{{ $customer?->nama_customer ?? '-' }}</strong></div>
                        <div>Invoice Date: <span class="text-gray-700 dark:text-gray-200">{{ $invoice->tanggal_invoice ? \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d M Y') : '-' }}</span></div>
                        <div>Due Date: <span class="text-gray-700 dark:text-gray-200">{{ $invoice->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($invoice->tanggal_jatuh_tempo)->format('d M Y') : '-' }}</span></div>
                    </div>
                </div>

                {{-- Group Tombol Aksi (Rata Kanan di Desktop) --}}
                <div class="flex flex-wrap items-center justify-start md:justify-end gap-3 self-start w-full md:w-auto pt-4 md:pt-0  dark:border-gray-800">
                    <x-filament::button
                        color="gray"
                        tag="a"
                        href="{{ route('filament.admin.resources.invoices.edit', $invoice) }}"
                        icon="heroicon-m-pencil-square"
                    >
                        Edit
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        icon="heroicon-m-printer"
                    >
                        Print PDF
                    </x-filament::button>

                    <x-filament::button
                        color="success"
                        tag="a"
                        icon="heroicon-m-credit-card"
                        href="{{ \App\Filament\Admin\Resources\InvPaymentResource::getUrl('create', [
                            'invoice' => $invoice->id,
                        ]) }}"
                    >
                        Payment
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- DETAIL INFORMASI & PROGRESS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- Hubungan Dokumen --}}
            <x-filament::section>
                <x-slot name="heading">Informasi Relasi Dokumen</x-slot>
                
                <div class=" divide-gray-100 dark:divide-gray-800 text-sm">
                    <div class="flex justify-between py-2.5">
                        <span class="text-gray-500 dark:text-gray-400">Sales Order</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->salesOrder?->no_sales_order ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <span class="text-gray-500 dark:text-gray-400">Surat Jalan</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->suratJalan?->kode_surat_jalan ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <span class="text-gray-500 dark:text-gray-400">SPK</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->suratJalan?->spk?->no_spk ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <span class="text-gray-500 dark:text-gray-400">PO Customer</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $invoice->salesOrder?->no_po_customer ?? '-' }}</span>
                    </div>
                </div>
            </x-filament::section>

            {{-- Ringkasan Tagihan & Progress Bar --}}
            <x-filament::section>
                <x-slot name="heading">Status Tagihan</x-slot>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Grand Total</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Sudah Dibayar</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Outstanding</span>
                        <span class="font-semibold text-danger-600 dark:text-danger-400">Rp {{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex justify-between text-xs mb-1.5 font-medium">
                            <span class="text-gray-500 dark:text-gray-400">Progress Pembayaran</span>
                            <span class="text-gray-900 dark:text-white">{{ number_format($progress, 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-emerald-500 h-full transition-all duration-300 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- BARANG DETAIL SECTION --}}
        <x-filament::section>
            <x-slot name="heading">Detail Barang / Produk</x-slot>

            <div class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-xl">
               <div class="w-full border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto w-full">
        <table class="w-full text-sm table-auto">
            <!-- Header Tabel dengan garis bawah penuh -->
            <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 font-medium border-b border-gray-200 dark:border-gray-800">
                <tr>
                    <!-- Padding px-6 memberikan jarak antar-kolom yang lebih luas dan lega -->
                    <th class="text-left py-4 px-6 whitespace-nowrap">Kode</th>
                    <th class="text-left py-4 px-6 whitespace-nowrap">Nama Barang</th>
                    <th class="text-center py-4 px-6 whitespace-nowrap">Qty Kirim</th>
                    <th class="text-right py-4 px-6 whitespace-nowrap">Harga / PCS</th>
                    <th class="text-right py-4 px-6 whitespace-nowrap">Subtotal</th>
                </tr>
            </thead>
            
            <!-- Isi Tabel dengan garis pembatas antar-baris yang penuh -->
         <tbody class="divide-y divide-gray-200 dark:divide-gray-800">

{{-- ========================= --}}
{{-- PRODUK SPK --}}
{{-- ========================= --}}

@if($so->mode === 'SPK')

<tr>

    <td class="px-6 py-4">
        {{ $so->itemable?->kode_barang }}
    </td>

    <td class="px-6 py-4">
        {{ $so->itemable?->nama_barang }}
    </td>

    <td class="text-center px-6 py-4">
        {{ number_format($sj->qty_pengiriman) }}
    </td>

    <td class="text-right px-6 py-4">
        Rp {{ number_format($so->harga_pcs,0,',','.') }}
    </td>

    <td class="text-right px-6 py-4 font-bold">
        Rp {{ number_format($invoice->subtotal,0,',','.') }}
    </td>

</tr>

{{-- ========================= --}}
{{-- SINGLE MATERIAL --}}
{{-- ========================= --}}

@elseif($so->mode === 'SINGLE_MATERIAL')

@php

$item = $so->items->first();

@endphp

<tr>

    <td class="px-6 py-4">
        {{ $item->material?->kode_master_item }}
    </td>

    <td class="px-6 py-4">
        {{ $item->material?->nama_master_item }}
    </td>

    <td class="text-center px-6 py-4">
        {{ number_format($sj->qty_pengiriman) }}
    </td>

    <td class="text-right px-6 py-4">
        Rp {{ number_format($item->harga,0,',','.') }}
    </td>

    <td class="text-right px-6 py-4 font-bold">
        Rp {{ number_format($item->qty * $item->harga,0,',','.') }}
    </td>

</tr>

{{-- ========================= --}}
{{-- MULTI MATERIAL --}}
{{-- ========================= --}}

@else

@foreach($sj->items as $detail)

<tr>

    <td class="px-6 py-4">
        {{ $detail->material?->kode_master_item }}
    </td>

    <td class="px-6 py-4">
        {{ $detail->material?->nama_master_item }}
    </td>

    <td class="text-center px-6 py-4">
        {{ number_format($detail->qty_kirim) }}
    </td>

    <td class="text-right px-6 py-4">

        Rp {{

            number_format(

                $detail->salesOrderItem?->harga ?? 0,

                0,

                ',',

                '.'

            )

        }}

    </td>

    <td class="text-right px-6 py-4 font-bold">

        Rp {{

            number_format(

                ($detail->qty_kirim)

                *

                ($detail->salesOrderItem?->harga ?? 0),

                0,

                ',',

                '.'

            )

        }}

    </td>

</tr>

@endforeach

@endif

</tbody>
        </table>
    </div>
</div>
            </div>
        </x-filament::section>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="space-y-6">
        
        {{-- BIAYA RINGKASAN --}}
        <x-filament::section>
            <x-slot name="heading">Ringkasan Biaya</x-slot>
            <x-slot name="description">Rincian akumulasi kalkulasi nilai invoice</x-slot>

            <div class="space-y-3.5  divide-gray-100 dark:divide-gray-800">
                <div class="flex justify-between pt-1">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-3 text-danger-600 dark:text-danger-400">
                    <span>Diskon ({{ $invoice->diskon ?? 0 }}%)</span>
                    <span class="font-medium">- Rp {{ number_format($diskonNominal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-3">
                    <span class="text-gray-500 dark:text-gray-400">PPN ({{ $invoice->ppn ?? 0 }}%)</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($ppnNominal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-3">
                    <span class="text-gray-500 dark:text-gray-400">Ongkos Kirim</span>
                    <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($invoice->ongkir ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center pt-4">
                    <span class="text-base font-bold text-gray-900 dark:text-white">TOTAL</span>
                    <span class="text-2xl font-black text-primary-600 dark:text-primary-400">
                        Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- RIWAYAT PEMBAYARAN --}}
   <x-filament::section class="rounded-2xl">
    <x-slot name="heading">Riwayat Pembayaran</x-slot>

    @if($invoice->payments && $invoice->payments->count() > 0)
        {{-- Wrapper luar bersih tanpa border tambahan di tabel --}}
        <div class="w-full rounded-2xl overflow-hidden">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-xs table-auto">
                    {{-- Header bersih tanpa garis bawah (border-b dibuang) --}}
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 font-medium">
                        <tr>
                            <th class="text-left py-3.5 px-5 whitespace-nowrap">Tanggal</th>
                            <th class="text-left py-3.5 px-5 whitespace-nowrap">Metode</th>
                            <th class="text-right py-3.5 px-5 whitespace-nowrap">Nominal</th>
                            <th class="text-left py-3.5 px-5 whitespace-nowrap">Admin</th>
                            <th class="text-center py-3.5 px-5 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    {{-- Body bersih tanpa pembatas baris (divide-y dibuang) --}}
                    <tbody class="bg-white dark:bg-transparent">
                        @foreach($invoice->payments as $payment)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($payment->tanggal_bayar)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300 font-medium whitespace-nowrap">
                                    {{ $payment->metodeBayar?->metode_pembayaran }}
                                </td>
                                <td class="text-right px-5 py-3.5 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ $payment->karyawan?->nama }}
                                </td>
                                <td class="text-center px-5 py-3.5 whitespace-nowrap">
                                    <x-filament::badge
                                        :color="$payment->status == 'SUCCESS' ? 'success' : 'warning'"
                                        class="rounded-full"
                                    >
                                        {{ strtoupper($payment->status) }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 py-8 text-center bg-gray-50/50 dark:bg-gray-900/20">
            <div class="text-3xl mb-2">💳</div>
            <div class="font-semibold text-sm text-gray-700 dark:text-gray-300">Belum Ada Pembayaran</div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 px-4">Invoice ini belum memiliki histori transaksi masuk.</p>
        </div>
    @endif
</x-filament::section>
        {{-- UANG MUKA & SISA TAGIHAN HIGHLIGHT --}}
        <x-filament::section>
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">Uang Muka (DP)</span>
                <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($invoice->uang_muka ?? 0, 0, ',', '.') }}</span>
            </div>

            <div class=" border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/50 dark:bg-emerald-950/20 p-5 text-center">
                <p class="text-xs tracking-wider uppercase font-semibold text-emerald-700 dark:text-emerald-400">
                    Sisa Tagihan Akhir
                </p>
                <p class="text-3xl font-black mt-2 text-emerald-600 dark:text-emerald-400 tracking-tight">
                    Rp {{ number_format($invoice->sisa_tagihan, 0, ',', '.') }}
                </p>
            </div>
        </x-filament::section>

    </div>
</div>

</x-filament::page>