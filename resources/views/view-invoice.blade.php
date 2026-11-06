<x-filament::page>

@php
    $invoice = (isset($this) && isset($this->record)) ? $this->record : ($record ?? $invoice);
    $invoice->loadMissing([
        'customer',
        'salesOrder.customer',
        'salesOrder.itemable',
        'suratJalan.salesOrder.customer',
        'suratJalan.salesOrder.itemable',
        'suratJalan.spk',
        'payments.metodeBayar',
        'payments.coa',
        'payments.karyawan',
    ]);

    $sj = $invoice->suratJalan;
    $so = $invoice->salesOrder ?? $sj?->salesOrder;

    $totalPaid = (float) (
        $invoice->total_payment > 0
            ? $invoice->total_payment
            : (($invoice->uang_muka ?? 0) + ($invoice->payments->where('status', 'SUCCESS')->sum('nominal_bayar') ?? 0))
    );

    $spk = $sj?->spk;
    $customer = $invoice->customer ?? $so?->customer;

    $diskonNominal = (($invoice->subtotal ?? 0) * ($invoice->diskon ?? 0)) / 100;
    $ppnNominal = (($invoice->subtotal ?? 0) * ($invoice->ppn ?? 0)) / 100;
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

                    <p class="text-gray-500">
                        Detail Informasi Tagihan & Transaksi
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <p class="text-sm text-gray-500">No Invoice</p>
                    <p class="font-bold">{{ $invoice->kode_invoice }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">No Surat Jalan</p>
                    <p class="font-bold">{{ $sj?->kode_surat_jalan ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Tanggal Invoice</p>
                    <p class="font-bold">
                        {{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d/m/Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">No SPK</p>
                    <p class="font-bold">{{ $spk?->no_spk ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Jatuh Tempo</p>
                    <p class="font-bold">
                        {{ \Carbon\Carbon::parse($invoice->tanggal_jatuh_tempo)->format('d/m/Y') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">No Sales Order</p>
                    <p class="font-bold">{{ $so?->no_sales_order ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- CUSTOMER & TUJUAN --}}
        <x-filament::section>
            <h2 class="text-xl font-bold mb-5">
                Customer & Tujuan
            </h2>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Nama Customer</p>
                    <p class="font-bold">{{ $customer?->nama_customer ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Alamat Tujuan</p>
                    <p class="font-bold">{{ $sj?->alamat_tujuan ?? '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- ITEM --}}
        <x-filament::section>
            <h2 class="text-xl font-bold mb-5">
                Rincian Item Barang
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3">Nama Barang</th>
                            <th class="text-center py-3">Qty</th>
                            <th class="text-right py-3">Harga Satuan</th>
                            <th class="text-right py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-4 font-semibold">
                                {{ $so?->itemable?->nama_barang ?? $so?->product_name ?? '-' }}
                            </td>
                            <td class="text-center">
                                {{ number_format($so?->qty ?? $sj?->qty_pengiriman ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                Rp {{ number_format($so?->harga_pcs ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right font-bold">
                                Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- RIWAYAT TRANSAKSI PEMBAYARAN (DP & PELUNASAN) --}}
        <x-filament::section>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Rincian Transaksi Pembayaran (DP & Pelunasan)
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Riwayat pembayaran transaksi yang masuk untuk invoice ini
                    </p>
                </div>

                @if($invoice->sisa_tagihan > 0)
                    <div>
                        <a
                            href="{{ \App\Filament\Admin\Resources\InvPaymentResource::getUrl('create', ['invoice' => $invoice->id]) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-500 transition shadow-sm"
                        >
                            <x-heroicon-m-plus class="w-4 h-4" />
                            Tambah Pembayaran / DP
                        </a>
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold bg-gray-50 dark:bg-gray-800/40">
                            <th class="text-left py-3 px-3">No</th>
                            <th class="text-left py-3 px-3">Tanggal Bayar</th>
                            <th class="text-left py-3 px-3">Metode Bayar</th>
                            <th class="text-left py-3 px-3">Akun Kas / Bank</th>
                            <th class="text-left py-3 px-3">Diterima Oleh</th>
                            <th class="text-left py-3 px-3">Keterangan</th>
                            <th class="text-center py-3 px-3">Status</th>
                            <th class="text-right py-3 px-3">Nominal Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($invoice->payments as $index => $payment)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                <td class="py-3 px-3 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-3 font-medium">
                                    {{ \Carbon\Carbon::parse($payment->tanggal_bayar)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $payment->metodeBayar?->metode_pembayaran ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-gray-700 dark:text-gray-300">
                                    {{ $payment->coa?->nama_akun ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-gray-700 dark:text-gray-300">
                                    {{ $payment->karyawan?->nama ?? '-' }}
                                </td>
                                <td class="py-3 px-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $payment->keterangan ?: '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <x-filament::badge
                                        :color="match($payment->status){
                                            'SUCCESS' => 'success',
                                            'PENDING' => 'warning',
                                            'FAILED' => 'danger',
                                            default => 'gray',
                                        }"
                                        size="sm"
                                    >
                                        {{ $payment->status ?? 'SUCCESS' }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-gray-900 dark:text-white">
                                    Rp {{ number_format($payment->nominal_bayar, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            @if(($invoice->uang_muka ?? 0) > 0)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20">
                                    <td class="py-3 px-3 font-medium">1</td>
                                    <td class="py-3 px-3 font-medium">
                                        {{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                            Uang Muka (DP)
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-gray-500">-</td>
                                    <td class="py-3 px-3 text-gray-500">-</td>
                                    <td class="py-3 px-3 text-xs text-gray-500">Dicatat saat pembuatan invoice</td>
                                    <td class="py-3 px-3 text-center">
                                        <x-filament::badge color="success" size="sm">SUCCESS</x-filament::badge>
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($invoice->uang_muka, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-gray-400">
                                        Belum ada riwayat transaksi pembayaran / DP untuk invoice ini.
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                    @if($invoice->payments->count() > 0 || ($invoice->uang_muka ?? 0) > 0)
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 font-bold">
                                <td colspan="7" class="py-3 px-3 text-right text-gray-700 dark:text-gray-300">
                                    Total Pembayaran Masuk (DP + Pelunasan):
                                </td>
                                <td class="py-3 px-3 text-right text-success-600 dark:text-success-400 text-base">
                                    Rp {{ number_format($totalPaid, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="space-y-6">

        {{-- RINGKASAN BIAYA --}}
        <x-filament::section>
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Ringkasan Biaya
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Informasi total tagihan invoice
                </p>
            </div>

            <div class="space-y-4">
                {{-- SUBTOTAL --}}
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Subtotal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($invoice->subtotal,0,',','.') }}
                    </span>
                </div>

                {{-- DISKON --}}
                @if(($invoice->diskon ?? 0) > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-danger-600">Diskon ({{ $invoice->diskon }}%)</span>
                        <span class="font-semibold text-danger-600">
                            - Rp {{ number_format($diskonNominal,0,',','.') }}
                        </span>
                    </div>
                @endif

                {{-- PPN --}}
                @if(($invoice->ppn ?? 0) > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-300">PPN ({{ $invoice->ppn }}%)</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            + Rp {{ number_format($ppnNominal,0,',','.') }}
                        </span>
                    </div>
                @endif

                {{-- ONGKIR --}}
                @if(($invoice->ongkir ?? 0) > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Ongkir</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            + Rp {{ number_format($invoice->ongkir,0,',','.') }}
                        </span>
                    </div>
                @endif

                {{-- GRAND TOTAL --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            GRAND TOTAL
                        </span>
                        <span class="text-2xl font-bold text-primary-600">
                            Rp {{ number_format($invoice->grand_total,0,',','.') }}
                        </span>
                    </div>
                </div>

                {{-- DP / PEMBAYARAN MASUK --}}
                <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
                        DP / Sudah Dibayar
                    </span>
                    <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                        - Rp {{ number_format($totalPaid,0,',','.') }}
                    </span>
                </div>

                {{-- SISA TAGIHAN --}}
                <div class="flex justify-between items-center pt-2">
                    <span class="text-base font-bold text-gray-900 dark:text-white">
                        Sisa Tagihan
                    </span>
                    <span class="text-xl font-extrabold {{ $invoice->sisa_tagihan <= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        Rp {{ number_format($invoice->sisa_tagihan,0,',','.') }}
                    </span>
                </div>
            </div>
        </x-filament::section>

        {{-- STATUS PEMBAYARAN & SISA TAGIHAN CARD --}}
        <x-filament::section>
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                    Total DP / Terbayar
                </span>
                <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($totalPaid,0,',','.') }}
                </span>
            </div>

            @if(($invoice->uang_muka ?? 0) > 0 && $invoice->payments->count() > 0)
                <div class="text-xs text-gray-500 mb-4 space-y-1 bg-gray-50 dark:bg-gray-800 p-2.5 rounded-lg border border-gray-100 dark:border-gray-800">
                    <div class="flex justify-between">
                        <span>Uang Muka Awal:</span>
                        <span>Rp {{ number_format($invoice->uang_muka, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pembayaran Inv Payment:</span>
                        <span>Rp {{ number_format($invoice->payments->sum('nominal_bayar'), 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            {{-- CARD SISA TAGIHAN --}}
            <div class="
                rounded-2xl
                border
                {{ $invoice->sisa_tagihan <= 0 ? 'border-success-300 bg-success-50 dark:bg-success-950/50' : 'border-amber-300 bg-amber-50 dark:bg-amber-950/50' }}
                p-6
                text-center
            ">
                <p class="
                    text-xs
                    tracking-widest
                    uppercase
                    font-semibold
                    {{ $invoice->sisa_tagihan <= 0 ? 'text-success-700 dark:text-success-300' : 'text-amber-800 dark:text-amber-300' }}
                ">
                    {{ $invoice->sisa_tagihan <= 0 ? 'Tagihan Telah Lunas' : 'Sisa Tagihan Yang Harus Dibayar' }}
                </p>

                <p class="
                    text-3xl
                    font-extrabold
                    mt-2
                    {{ $invoice->sisa_tagihan <= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}
                ">
                    Rp {{ number_format($invoice->sisa_tagihan,0,',','.') }}
                </p>

                <div class="mt-4">
                    <x-filament::badge
                        :color="match($invoice->status){
                            'Paid' => 'success',
                            'Partial' => 'warning',
                            'Unpaid' => 'danger',
                            default => 'gray',
                        }"
                        size="md"
                    >
                        STATUS: {{ strtoupper($invoice->status ?? 'UNPAID') }}
                    </x-filament::badge>
                </div>
            </div>

            @if($invoice->sisa_tagihan > 0)
                <div class="mt-5">
                    <a
                        href="{{ \App\Filament\Admin\Resources\InvPaymentResource::getUrl('create', ['invoice' => $invoice->id]) }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-500 transition shadow"
                    >
                        <x-heroicon-m-credit-card class="w-4 h-4" />
                        Input Pembayaran / Pelunasan
                    </a>
                </div>
            @endif
        </x-filament::section>

    </div>
</div>

</x-filament::page>
