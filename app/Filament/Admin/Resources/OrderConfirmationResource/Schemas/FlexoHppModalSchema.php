<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Schemas;

use App\Services\Costing\FlexoHppCalculatorService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Illuminate\Support\HtmlString;

class FlexoHppModalSchema
{
    /**
     * Helper untuk memicu kalkulasi ulang secara live saat field input di modal diubah.
     */
    public static function triggerLiveRecalculate(Get $get, Set $set, ?float $targetOrder = null): void
    {
        $target = $targetOrder ?? (float) ($get('target_order') ?: 100000);
        if ($target <= 0) {
            $target = 100000;
        }

        $calculator = app(FlexoHppCalculatorService::class);
        $result = $calculator->calculate([
            'panjang_etiket_jadi' => (float) ($get('panjang_etiket_jadi') ?? 0),
            'lebar_etiket_jadi' => (float) ($get('lebar_etiket_jadi') ?? 0),
            'lebihan_cetak_panjang' => (float) ($get('lebihan_cetak_panjang') ?? FlexoHppCalculatorService::DEFAULT_ALLOWANCE_PANJANG),
            'lebihan_cetak_lebar' => (float) ($get('lebihan_cetak_lebar') ?? FlexoHppCalculatorService::DEFAULT_ALLOWANCE_LEBAR),
            'lebihan_lebar_bahan' => (float) ($get('lebihan_lebar_bahan') ?? FlexoHppCalculatorService::DEFAULT_ALLOWANCE_WEB),
            'jumlah_etiket_sepanjang_repeat' => (int) ($get('jumlah_etiket_sepanjang_repeat') ?? 2),
            'jumlah_etiket_sepanjang_lebar' => (int) ($get('jumlah_etiket_sepanjang_lebar') ?? 2),
            'repeat_cylinder' => (float) ($get('repeat_cylinder') ?? FlexoHppCalculatorService::DEFAULT_REPEAT_CYLINDER),
            'waste_setting' => (float) ($get('waste_setting') ?? FlexoHppCalculatorService::DEFAULT_WASTE_SETTING),
            'waste_tambahan_persen' => (float) ($get('waste_tambahan_persen') ?? FlexoHppCalculatorService::DEFAULT_WASTE_PERSEN),
            'gramatur_kertas' => (float) ($get('gramatur_kertas') ?? FlexoHppCalculatorService::DEFAULT_GRAMATUR),
            'harga_bahan_per_kg' => (float) ($get('harga_bahan_per_kg') ?? FlexoHppCalculatorService::DEFAULT_HARGA_BAHAN),
            'kecepatan_cetak' => (float) ($get('kecepatan_cetak') ?? FlexoHppCalculatorService::DEFAULT_KECEPATAN_CETAK),
            'waktu_setup' => (float) ($get('waktu_setup') ?? FlexoHppCalculatorService::DEFAULT_WAKTU_SETUP),
            'tarif_mesin_per_jam' => (float) ($get('tarif_mesin_per_jam') ?? 0),
            'biaya_tinta_total' => (float) ($get('biaya_tinta_total') ?? 0),
            'biaya_plate' => (float) ($get('biaya_plate') ?? 0),
            'biaya_tenaga_kerja' => (float) ($get('biaya_tenaga_kerja') ?? 0),
            'biaya_overhead' => (float) ($get('biaya_overhead') ?? 0),
            'margin_persen' => (float) ($get('margin_persen') ?? 0),
        ], $target);

        // Update seluruh field hasil secara reaktif di modal
        $set('lebar_bahan', $result['lebar_bahan']);
        $set('panjang_cetak_pitch', $result['panjang_cetak_pitch']);
        $set('panjang_kebutuhan_per_repeat', $result['panjang_kebutuhan_per_repeat']);
        $set('sisa_repeat', $result['sisa_repeat']);
        $set('status_panjang_repeat', $result['status_panjang_repeat']);

        $set('etiket_per_repeat', $result['etiket_per_repeat']);
        $set('repeat_yang_dibutuhkan', $result['repeat_yang_dibutuhkan']);
        $set('panjang_produksi_teoritis', $result['panjang_produksi_teoritis']);
        $set('total_panjang_bahan', $result['total_panjang_bahan']);
        $set('berat_bahan_teoritis', $result['berat_bahan_teoritis']);
        $set('biaya_bahan', $result['biaya_bahan']);
        $set('biaya_bahan_per_etiket', $result['biaya_bahan_per_etiket']);

        $set('waktu_cetak', $result['waktu_cetak']);
        $set('total_waktu_mesin', $result['total_waktu_mesin']);
        $set('biaya_mesin', $result['biaya_mesin']);
        $set('biaya_proses_cetak_total', $result['biaya_proses_cetak_total']);
        $set('biaya_proses_per_etiket', $result['biaya_proses_per_etiket']);

        $set('total_biaya_order', \App\Support\MoneyHelper::format($result['total_biaya_order'], 2));
        $set('hpp_per_etiket', \App\Support\MoneyHelper::format($result['hpp_per_etiket'], 2));
        $set('harga_jual_indikatif', \App\Support\MoneyHelper::format($result['harga_jual_indikatif'], 2));
    }

    /**
     * Schema Form lengkap untuk Modal Kalkulator HPP & Kertas Flexo Zonten
     */
    public static function getFormSchema(): array
    {
        return [
            // Hidden target order untuk referensi hitungan
            Hidden::make('target_order'),

            Placeholder::make('header_info')
                ->content(fn (Get $get) => new HtmlString('
                    <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(59, 130, 246, 0.15)); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span style="font-size: 13px; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px;">Mesin Standar: Flexo Zonten TTC</span>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                Input kuning dapat disesuaikan; nilai hijau dihitung otomatis oleh sistem secara real-time.
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11px; color: #9ca3af;">Target Produksi:</span>
                            <span style="font-size: 14px; font-weight: 700; color: #60a5fa; margin-left: 6px;">' . number_format((float)($get('target_order') ?: 100000), 0, '.', ',') . ' Pcs</span>
                        </div>
                    </div>
                '))
                ->columnSpanFull(),

            Tabs::make('HppTabs')
                ->tabs([
                    // =========================================================
                    // TAB 1: SPESIFIKASI ORDER & LAYOUT
                    // =========================================================
                    Tabs\Tab::make('A. Spesifikasi Order & Layout')
                        ->icon('heroicon-o-view-columns')
                        ->schema([
                            Section::make('Dimensi & Allowance (Input)')
                                ->description('Ukuran etiket jadi dan toleransi pisau/mesin')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('panjang_etiket_jadi')
                                            ->label('Panjang Etiket Jadi (mm)')
                                            ->numeric()
                                            ->helperText('Arah jalan bahan (feeding)')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebar_etiket_jadi')
                                            ->label('Lebar Etiket Jadi (mm)')
                                            ->numeric()
                                            ->helperText('Melintang terhadap arah bahan')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('repeat_cylinder')
                                            ->label('Repeat Cylinder (mm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_REPEAT_CYLINDER)
                                            ->helperText('Standar Zonten: 412.75 mm')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(3)->schema([
                                        TextInput::make('lebihan_cetak_panjang')
                                            ->label('Lebihan Cetak Panjang (mm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_PANJANG)
                                            ->helperText('Allowance pitch (default: 3 mm)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebihan_cetak_lebar')
                                            ->label('Lebihan Cetak Lebar (mm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_LEBAR)
                                            ->helperText('Allowance lebar per item (default: 3 mm)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebihan_lebar_bahan')
                                            ->label('Lebihan Lebar Bahan / Web (mm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_WEB)
                                            ->helperText('Allowance tepi bahan (default: 20 mm)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('jumlah_etiket_sepanjang_repeat')
                                            ->label('Jumlah Etiket Sepanjang Repeat')
                                            ->numeric()
                                            ->default(2)
                                            ->helperText('Arah keliling cylinder')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('jumlah_etiket_sepanjang_lebar')
                                            ->label('Jumlah Etiket Sepanjang Lebar (Lane/Jalur)')
                                            ->numeric()
                                            ->default(2)
                                            ->helperText('Jumlah baris melintang web')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Hasil Analisis Layout Cylinder (Otomatis)')
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextInput::make('lebar_bahan')
                                            ->label('Lebar Bahan / Web (mm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('panjang_cetak_pitch')
                                            ->label('Panjang Cetak / Pitch (mm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('panjang_kebutuhan_per_repeat')
                                            ->label('Kebutuhan Panjang / Repeat (mm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('sisa_repeat')
                                            ->label('Sisa Repeat (mm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),
                                    ]),

                                    TextInput::make('status_panjang_repeat')
                                        ->label('Status Kelayakan Layout Repeat')
                                        ->readOnly()
                                        ->extraInputAttributes(fn ($state) => [
                                            'style' => str_contains($state ?? '', 'MUAT')
                                                ? 'background-color: rgba(16, 185, 129, 0.2); font-weight: bold; color: #10b981;'
                                                : 'background-color: rgba(239, 68, 68, 0.2); font-weight: bold; color: #ef4444;',
                                        ]),
                                ]),
                        ]),

                    // =========================================================
                    // TAB 2: KEBUTUHAN BAHAN (KERTAS)
                    // =========================================================
                    Tabs\Tab::make('B. Kebutuhan Bahan (Kertas)')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Parameter Bahan Kertas (Input)')
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextInput::make('gramatur_kertas')
                                            ->label('Gramatur Kertas (GSM)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_GRAMATUR)
                                            ->helperText('Contoh: 230 gsm')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('harga_bahan_per_kg')
                                            ->label('Harga Bahan per Kg (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_HARGA_BAHAN)
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('waste_setting')
                                            ->label('Waste Setting Awal (meter)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_WASTE_SETTING)
                                            ->helperText('Persiapan awal mesin (default: 40 m)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('waste_tambahan_persen')
                                            ->label('Waste Tambahan (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(FlexoHppCalculatorService::DEFAULT_WASTE_PERSEN)
                                            ->helperText('Persentase toleransi waste produksi')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Kalkulasi Kebutuhan Kertas & Biaya (Otomatis)')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('etiket_per_repeat')
                                            ->label('Etiket per Repeat (Pcs)')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('repeat_yang_dibutuhkan')
                                            ->label('Repeat yang Dibutuhkan (Putaran)')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('panjang_produksi_teoritis')
                                            ->label('Panjang Produksi Teoritis (meter)')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('total_panjang_bahan')
                                            ->label('Total Panjang Web Kertas (meter)')
                                            ->helperText('Termasuk waste setting & waste persen')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),

                                        TextInput::make('berat_bahan_teoritis')
                                            ->label('Total Berat Kertas (kg)')
                                            ->helperText('Kebutuhan netto + waste yang harus disediakan')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('biaya_bahan')
                                            ->label('Total Biaya Bahan Kertas (Rp)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => number_format((float)$state, 2, '.', ','))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),

                                        TextInput::make('biaya_bahan_per_etiket')
                                            ->label('Biaya Bahan per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => number_format((float)$state, 4, '.', ','))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),
                                    ]),
                                ]),
                        ]),

                    // =========================================================
                    // TAB 3: BIAYA PROSES CETAK FLEXO
                    // =========================================================
                    Tabs\Tab::make('C. Biaya Proses Flexo')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Waktu Mesin & Parameter Proses (Input)')
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextInput::make('kecepatan_cetak')
                                            ->label('Kecepatan Cetak (m/menit)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_KECEPATAN_CETAK)
                                            ->helperText('Kecepatan aktual rata-rata')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('waktu_setup')
                                            ->label('Waktu Setup (menit)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_WAKTU_SETUP)
                                            ->helperText('Persiapan mesin sebelum jalan')
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('tarif_mesin_per_jam')
                                            ->label('Tarif Mesin per Jam (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Tarif biaya mesin per jam')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('biaya_tenaga_kerja')
                                            ->label('Tenaga Kerja Tambahan (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Jika di luar tarif mesin')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('biaya_tinta_total')
                                            ->label('Biaya Tinta Total (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Total estimasi pemakaian tinta seluruh warna')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('biaya_plate')
                                            ->label('Biaya Plate / Cliché (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Alokasi biaya pembuatan plate flexo')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Hasil Waktu & Biaya Proses Cetak (Otomatis)')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('waktu_cetak')
                                            ->label('Waktu Cetak (menit)')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('total_waktu_mesin')
                                            ->label('Total Waktu Mesin (menit)')
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('biaya_mesin')
                                            ->label('Biaya Mesin (Rp)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => number_format((float)$state, 2, '.', ','))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('biaya_proses_cetak_total')
                                            ->label('Biaya Proses Cetak Total (Rp)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => number_format((float)$state, 2, '.', ','))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),

                                        TextInput::make('biaya_proses_per_etiket')
                                            ->label('Biaya Proses per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => number_format((float)$state, 4, '.', ','))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),
                                    ]),
                                ]),
                        ]),

                    // =========================================================
                    // TAB 4: RINGKASAN HPP & HARGA JUAL
                    // =========================================================
                    Tabs\Tab::make('D. Ringkasan HPP & Margin')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Section::make('Overhead & Margin Penjualan')
                                ->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('biaya_overhead')
                                            ->label('Biaya Lain-lain / Overhead (Rp)')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Biaya operasional/overhead per order')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('margin_persen')
                                            ->label('Margin Keuntungan (%)')
                                            ->suffix('%')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Markup atas HPP (contoh: isi 15 untuk 15%)')
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Hasil Akhir HPP & Rekomendasi Harga Jual')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('total_biaya_order')
                                            ->label('Total Biaya Order (Rp)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => \App\Support\MoneyHelper::format($state, 2))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(59, 130, 246, 0.15); font-weight: 700; color: #3b82f6; font-size: 14px;']),

                                        TextInput::make('hpp_per_etiket')
                                            ->label('HPP Pokok per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => \App\Support\MoneyHelper::format($state, 2))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(245, 158, 11, 0.2); font-weight: 800; color: #f59e0b; font-size: 16px;']),

                                        TextInput::make('harga_jual_indikatif')
                                            ->label('Harga Jual Indikatif (+Margin) (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => \App\Support\MoneyHelper::format($state, 2))
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.25); font-weight: 800; color: #10b981; font-size: 16px;']),
                                    ]),

                                    Radio::make('harga_mode')
                                        ->label('Pengaturan Harga Satuan & Fix HPP')
                                        ->options([
                                            'indikatif' => 'Gunakan Harga Jual Indikatif (+Margin) ke Harga (Rp/pcs)',
                                            'hpp'       => 'Samakan Harga (Rp/pcs) dengan HPP Pokok (Margin 0%)',
                                            'custom'    => 'Setting Fix HPP / Harga Satuan Manual (Samakan HPP & Harga per Pcs)',
                                        ])
                                        ->default('indikatif')
                                        ->live(),

                                    TextInput::make('fix_hpp_manual')
                                        ->label('Nilai Fix HPP / Harga Satuan (Rp/pcs)')
                                        ->prefix('Rp')
                                        ->currencyMask(2)
                                        ->visible(fn (Get $get) => $get('harga_mode') === 'custom')
                                        ->required(fn (Get $get) => $get('harga_mode') === 'custom')
                                        ->helperText('Nilai ini akan diterapkan sekaligus ke HPP Pokok dan Harga (Rp/pcs) agar sama persis sehingga tidak membingungkan.'),

                                    Checkbox::make('apply_harga_to_item')
                                        ->label('Terapkan nilai harga yang dipilih langsung ke field "Harga (Rp/pcs)" penawaran item')
                                        ->default(true)
                                        ->helperText('Jika dicentang, saat Anda klik "Simpan & Terapkan", harga satuan item penawaran akan otomatis terisi dan diformat rapi.'),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}

