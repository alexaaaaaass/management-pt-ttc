<x-filament::page>
    @php
        $record = $this->record;
        $isKeluar = $record->tipe_transaksi === 'BANK_KELUAR';

        // Palet Warna Dinamis
        // OUT = Orange/Redish (Pengeluaran), IN = Emerald/Green (Pemasukan)
        $themeColor = $isKeluar ? 'orange' : 'emerald';
        
        // Gradient untuk header/struck
        $accentGradient = $isKeluar 
            ? 'bg-gradient-to-r from-orange-500 to-amber-400' 
            : 'bg-gradient-to-r from-emerald-500 to-teal-400';
            
        // Text color helper
        $textAccent = $isKeluar ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400';
        $bgAccent = $isKeluar ? 'bg-orange-50 dark:bg-orange-950/30' : 'bg-emerald-50 dark:bg-emerald-950/30';
    @endphp

    {{-- Wrapper Layar Penuh (Bg halaman) --}}
    <div class="min-h-screen w-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4 md:p-8 transition-colors duration-300">
        
        {{-- KARTU STRUK (Receipt Card) --}}
        <div class="w-full max-w-md bg-white dark:bg-gray-950 rounded-3xl shadow-2xl overflow-hidden relative border border-gray-200 dark:border-gray-800 ring-1 ring-gray-900/5 dark:ring-gray-700/50">
            
            {{-- 1. TOPIK STRUK (Header Gradient) --}}
            <div class="h-2 w-full {{ $accentGradient }}"></div>

            <div class="p-6 sm:p-8 space-y-6">
                
                {{-- HEADER: Logo & Kode Transaksi --}}
                <div class="text-center space-y-2">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 mb-2 shadow-inner">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight uppercase">
                        {{ $isKeluar ? 'Bank Keluar' : 'Bank Masuk' }}
                    </h1>
                    <p class="text-xs font-mono text-gray-400 dark:text-gray-500">
                        REF: {{ $record->kode_transaksi }}
                    </p>
                </div>

                {{-- PEMISAH (dashed line) --}}
                <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-800 relative text-center">
                    <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-gray-950 px-2 text-gray-400 dark:text-gray-600">
                     
                    </span>
                </div>

                {{-- INFO UTAMA: Tanggal & Gudang --}}
                <div class="flex justify-between items-center text-sm">
                    <div class="text-left">
                        <p class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Tanggal</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($record->tanggal_transaksi)->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">Periode</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->periode }}</p>
                    </div>
                </div>

                {{-- PEMISAH --}}
                <div class="border-t border-gray-100 dark:border-gray-800"></div>

                {{-- AKUN: Dari & Ke --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Akun Utama</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate max-w-[60%] text-right">{{ $record->accountBank?->nama_akun ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-baseline">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Akun Lawan</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate max-w-[60%] text-right">{{ $record->accountLawan?->nama_akun ?? '-' }}</span>
                    </div>
                </div>

                {{-- PEMISAH --}}
                <div class="border-t border-gray-100 dark:border-gray-800"></div>

                {{-- DETAIL REKENING --}}
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div class="space-y-1">
                        <p class="text-gray-400 dark:text-gray-500 font-medium">Customer</p>
                        <p class="text-gray-800 dark:text-gray-200 font-semibold truncate">{{ $record->customer?->nama_customer ?? '-' }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-gray-400 dark:text-gray-500 font-medium">Bank</p>
                        <p class="text-gray-800 dark:text-gray-200 font-semibold">{{ $record->bank ?? '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-400 dark:text-gray-500 font-medium">Atas Nama</p>
                        <p class="text-gray-800 dark:text-gray-200 font-semibold truncate">{{ $record->atas_nama ?? '-' }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-gray-400 dark:text-gray-500 font-medium">No. Rekening</p>
                        <p class="text-gray-800 dark:text-gray-200 font-mono font-semibold">{{ $record->no_rekening ?? '-' }}</p>
                    </div>
                </div>

                {{-- PEMISAH --}}
                <div class="border-t-2 border-dashed border-gray-200 dark:border-gray-800"></div>

                {{-- TOTAL --}}
                <div class="text-center space-y-1 py-2">
                    <p class="text-xs uppercase tracking-widest text-gray-400 dark:text-gray-500 font-bold">Total Amount</p>
                    <div class="text-3xl md:text-4xl font-black tracking-tight {{ $textAccent }} drop-shadow-sm font-mono">
                        Rp {{ number_format($record->nominal, 0, ',', '.') }}
                    </div>
                </div>

                {{-- KETERANGAN --}}
                @if($record->keterangan)
                <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                    <p class="text-[10px] uppercase text-gray-400 dark:text-gray-500 font-bold mb-2 block">Catatan / Keterangan</p>
                    <div class="text-xs leading-relaxed text-gray-600 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-900 p-3 rounded-lg border border-gray-100 dark:border-gray-800">
                        "{{ $record->keterangan }}"
                    </div>
                </div>
                @endif

                {{-- FOOTER --}}
                <div class="pt-6 text-center">
                    <div class="inline-block px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-bold tracking-widest uppercase border border-gray-200 dark:border-gray-700">
                        {{ $isKeluar ? 'Transaksi Keluar' : 'Transaksi Masuk' }} • Valid
                    </div>
                </div>

            </div>
            
            {{-- Estetika perforated bottom (putaran kertas disobek) - Opsional visual bawah --}}
            <div class="w-full h-2 bg-gray-50 dark:bg-gray-900 relative overflow-hidden">
                 <div class="absolute top-0 left-0 w-4 h-4 bg-white dark:bg-gray-950 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                 <div class="absolute top-0 right-0 w-4 h-4 bg-white dark:bg-gray-950 rounded-full translate-x-1/2 -translate-y-1/2"></div>
            </div>
        </div>

    </div>
</x-filament::page>