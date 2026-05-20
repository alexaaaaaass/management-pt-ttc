<x-filament::page>

@php

    $invoice = $this->record;

    $sj = $invoice->suratJalan;

    $so = $sj?->salesOrder;

    $spk = $sj?->spk;

    $customer = $so?->customer;

    $diskonNominal =
        ($invoice->subtotal * $invoice->diskon) / 100;

    $ppnNominal =
        ($invoice->subtotal * $invoice->ppn) / 100;

@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- HEADER --}}
        <x-filament::section>

            <div class="flex justify-between items-start">

                <div>

                    <h2 class="text-2xl font-bold">
                        Detail Invoice
                    </h2>

                    <p class="text-gray-500">
                        {{ $invoice->kode_invoice }}
                    </p>

                </div>

            </div>

            <div class="grid grid-cols-2 gap-6 mt-6">

                <div>

                    <p class="text-sm text-gray-500">
                        No Invoice
                    </p>

                    <p class="font-bold">
                        {{ $invoice->kode_invoice }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        No Surat Jalan
                    </p>

                    <p class="font-bold">
                        {{ $sj->kode_surat_jalan }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        Tanggal Invoice
                    </p>

                    <p class="font-bold">
                        {{ \Carbon\Carbon::parse($invoice->tanggal_invoice)->format('d/m/Y') }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        No SPK
                    </p>

                    <p class="font-bold">
                        {{ $spk?->no_spk }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        Jatuh Tempo
                    </p>

                    <p class="font-bold">
                        {{ \Carbon\Carbon::parse($invoice->tanggal_jatuh_tempo)->format('d/m/Y') }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        No Sales Order
                    </p>

                    <p class="font-bold">
                        {{ $so?->no_sales_order }}
                    </p>

                </div>

            </div>

        </x-filament::section>

        {{-- CUSTOMER --}}
        <x-filament::section>

            <h2 class="text-xl font-bold mb-5">
                Customer & Tujuan
            </h2>

            <div class="grid grid-cols-2 gap-6">

                <div>

                    <p class="text-sm text-gray-500">
                        Nama Customer
                    </p>

                    <p class="font-bold">
                        {{ $customer?->nama_customer }}
                    </p>

                </div>

                <div>

                    <p class="text-sm text-gray-500">
                        Alamat Tujuan
                    </p>

                    <p class="font-bold">
                        {{ $sj?->alamat_tujuan }}
                    </p>

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

                            <th class="text-left py-3">
                                Nama Barang
                            </th>

                            <th class="text-center py-3">
                                Qty
                            </th>

                            <th class="text-right py-3">
                                Harga Satuan
                            </th>

                            <th class="text-right py-3">
                                Total
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <tr>

                            <td class="py-4 font-semibold">
                                {{ $so?->itemable?->nama_barang ?? '-' }}
                            </td>

                            <td class="text-center">
                                {{ number_format($so?->qty,0,',','.') }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format($so?->harga_pcs,0,',','.') }}
                            </td>

                            <td class="text-right font-bold">
                                Rp {{ number_format($invoice->subtotal,0,',','.') }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </x-filament::section>

    </div>

  {{-- RIGHT --}}
<div class="space-y-6">

    <x-filament::section>

        <div class="mb-6">

            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Ringkasan Biaya
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Informasi total tagihan invoice
            </p>

        </div>

        <div class="space-y-5">

            {{-- SUBTOTAL --}}
            <div class="flex justify-between items-center">

                <span class="text-gray-600 dark:text-gray-300">
                    Subtotal
                </span>

                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->subtotal,0,',','.') }}
                </span>

            </div>

            {{-- DISKON --}}
            <div class="flex justify-between items-center">

                <span class="text-danger-600">
                    Diskon ({{ $invoice->diskon }}%)
                </span>

                <span class="font-semibold text-danger-600">
                    - Rp {{ number_format($diskonNominal,0,',','.') }}
                </span>

            </div>

            {{-- PPN --}}
            <div class="flex justify-between items-center">

                <span class="text-gray-600 dark:text-gray-300">
                    PPN ({{ $invoice->ppn }}%)
                </span>

                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($ppnNominal,0,',','.') }}
                </span>

            </div>

            {{-- ONGKIR --}}
            <div class="flex justify-between items-center">

                <span class="text-gray-600 dark:text-gray-300">
                    Ongkir
                </span>

                <span class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($invoice->ongkir,0,',','.') }}
                </span>

            </div>

            {{-- LINE --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-5">

                <div class="flex justify-between items-center">

                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        TOTAL
                    </span>

                    <span class="text-3xl font-bold text-primary-600">
                        Rp {{ number_format($invoice->grand_total,0,',','.') }}
                    </span>

                </div>

            </div>

        </div>

    </x-filament::section>

    {{-- UANG MUKA --}}
    <x-filament::section>

        <div class="flex justify-between items-center mb-6">

            <span class="text-lg font-bold text-primary-600">
                Uang Muka
            </span>

            <span class="text-xl font-bold text-primary-600">
                Rp {{ number_format($invoice->uang_muka,0,',','.') }}
            </span>

        </div>

        {{-- CARD --}}
        <div class="
            rounded-2xl
            border
            border-success-200
            dark:border-success-800
            bg-success-50
            dark:bg-success-950
            p-8
        ">

            <p class="
                text-center
                text-sm
                tracking-widest
                uppercase
                text-success-700
                dark:text-success-300
            ">
                Sisa Tagihan
            </p>

            <p class="
                text-center
                text-4xl
                font-bold
                mt-3
                text-success-600
                dark:text-success-400
            ">

                Rp {{ number_format($invoice->sisa_tagihan,0,',','.') }}

            </p>

        </div>

    </x-filament::section>

</div>
</div>

</x-filament::page>