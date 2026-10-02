<x-filament::widget>
    <x-filament::section 
        icon="heroicon-o-cube" 
        heading="Material Ledger" 
        description="Ringkasan aktivitas dan pergerakan material hari ini"
    >
        {{-- Card Utama dengan Grid bawaan Filament --}}
        <x-filament::grid cols="1" sm="2" lg="4" class="gap-4">
            
            {{-- Card Barang Masuk - Hijau --}}
            <x-filament::section compact class="border-t-4 border-t-green-500 bg-green-50/40 dark:bg-green-950/10 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                    <span class="text-sm font-medium">Barang Masuk</span>
                    <x-filament::icon icon="heroicon-o-arrow-down-left" class="h-5 w-5 text-green-500" />
                </div>
                <div class="text-3xl font-bold tracking-tight mt-3 text-green-600 dark:text-green-400">
                    {{ $lpb }}
                </div>
            </x-filament::section>

            {{-- Card Dialokasikan - Amber/Kuning --}}
            <x-filament::section compact class="border-t-4 border-t-amber-500 bg-amber-50/40 dark:bg-amber-950/10 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                    <span class="text-sm font-medium">Dialokasikan</span>
                    <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-amber-500" />
                </div>
                <div class="text-3xl font-bold tracking-tight mt-3 text-amber-600 dark:text-amber-400">
                    {{ $approve }}
                </div>
            </x-filament::section>

            {{-- Card Di-Issue - Merah --}}
            <x-filament::section compact class="border-t-4 border-t-red-500 bg-red-50/40 dark:bg-red-950/10 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                    <span class="text-sm font-medium">Di-Issue</span>
                    <x-filament::icon icon="heroicon-o-arrow-up-right" class="h-5 w-5 text-red-500" />
                </div>
                <div class="text-3xl font-bold tracking-tight mt-3 text-red-600 dark:text-red-400">
                    {{ $issue }}
                </div>
            </x-filament::section>

            {{-- Card Return - Biru/Sky --}}
            <x-filament::section compact class="border-t-4 border-t-sky-500 bg-sky-50/40 dark:bg-sky-950/10 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between text-gray-500 dark:text-gray-400">
                    <span class="text-sm font-medium">Return</span>
                    <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5 text-sky-500" />
                </div>
                <div class="text-3xl font-bold tracking-tight mt-3 text-sky-600 dark:text-sky-400">
                    {{ $return }}
                </div>
            </x-filament::section>

        </x-filament::grid>

        {{-- Detail Informasi di Bagian Bawah --}}
        <div class="mt-8 border-t dark:border-gray-800 pt-6">
            <x-filament::grid cols="1" md="2" class="gap-x-12 gap-y-2">
                
                <div class="flex justify-between py-2.5 border-b dark:border-gray-800/50 px-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Qty Masuk Hari Ini</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($qtyMasuk, 2) }}</span>
                </div>

                <div class="flex justify-between py-2.5 border-b dark:border-gray-800/50 px-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Qty Issue Hari Ini</span>
                    <span class="font-semibold text-red-600 dark:text-red-400">{{ number_format($qtyIssue, 2) }}</span>
                </div>

                <div class="flex justify-between py-2.5 border-b dark:border-gray-800/50 px-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Material Aktif</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($materialAktif) }}</span>
                </div>

                <div class="flex justify-between py-2.5 border-b dark:border-gray-800/50 px-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Aktivitas Hari Ini</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($aktivitas) }}</span>
                </div>

            </x-filament::grid>
        </div>
    </x-filament::section>
</x-filament::widget>