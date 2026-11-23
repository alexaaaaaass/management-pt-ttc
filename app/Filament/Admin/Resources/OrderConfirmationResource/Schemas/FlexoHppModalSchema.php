<?php

namespace App\Filament\Admin\Resources\OrderConfirmationResource\Schemas;

use App\Models\MasterMaterialPlate;
use App\Models\PracetakPlateArchive;
use App\Services\Costing\FlexoHppCalculatorService;
use App\Services\Pracetak\PracetakCostingService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
            'harga_bahan_per_kg' => (float) (MoneyHelper::clean($get('harga_bahan_per_kg')) ?? 0),
            'harga_bahan_per_meter' => (float) (MoneyHelper::clean($get('harga_bahan_per_meter')) ?? 0),
            'basis_harga_bahan' => $get('basis_harga_bahan') ?? 'kg',
            'kecepatan_cetak' => (float) ($get('kecepatan_cetak') ?? FlexoHppCalculatorService::DEFAULT_KECEPATAN_CETAK),
            'waktu_setup' => (float) ($get('waktu_setup') ?? FlexoHppCalculatorService::DEFAULT_WAKTU_SETUP),
            'tarif_mesin_per_jam' => (float) (MoneyHelper::clean($get('tarif_mesin_per_jam')) ?? 0),
            'biaya_tinta_total' => (float) (MoneyHelper::clean($get('biaya_tinta_total')) ?? 0),
            'biaya_plate' => (float) (MoneyHelper::clean($get('biaya_plate')) ?? 0),
            'plate_mode' => $get('plate_mode') ?? 'manual',
            'plate_charge_mode' => $get('plate_charge_mode') ?? 'include_in_hpp',
            'plate_margin_persen' => (float) ($get('plate_margin_persen') ?? 0),
            'master_material_plate_id' => $get('master_material_plate_id'),
            'plate_archive_id' => $get('plate_archive_id'),
            'plate_jumlah_warna' => (int) ($get('plate_jumlah_warna') ?? 4),
            'plate_margin_potong_cm' => (float) ($get('plate_margin_potong_cm') ?? PracetakCostingService::DEFAULT_MARGIN_PLATE_CM),
            'plate_toleransi_persen' => (float) ($get('plate_toleransi_persen') ?? PracetakCostingService::DEFAULT_TOLERANSI_RUSAK_PERSEN),
            'plate_biaya_design' => (float) (MoneyHelper::clean($get('plate_biaya_design')) ?? 0),
            'plate_biaya_prove' => (float) (MoneyHelper::clean($get('plate_biaya_prove')) ?? 0),
            'plate_tarif_proses' => (float) (MoneyHelper::clean($get('plate_tarif_proses')) ?? 0),
            'biaya_tenaga_kerja' => (float) (MoneyHelper::clean($get('biaya_tenaga_kerja')) ?? 0),
            'biaya_overhead' => (float) (MoneyHelper::clean($get('biaya_overhead')) ?? 0),
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

        // Sinkronisasi harga bahan (kg vs meter) dengan format mata uang
        if (($get('basis_harga_bahan') ?? 'kg') === 'meter') {
            $set('harga_bahan_per_kg', MoneyHelper::format($result['harga_bahan_per_kg'], 2) ?? '0');
        } else {
            $set('harga_bahan_per_meter', MoneyHelper::format($result['harga_bahan_per_meter'], 2) ?? '0');
        }

        $set('biaya_bahan', MoneyHelper::format($result['biaya_bahan'], 2) ?? '0');
        $set('biaya_bahan_per_etiket', MoneyHelper::format($result['biaya_bahan_per_etiket'], 2) ?? '0');

        // Update hasil kalkulasi Plate Prepress (terformat)
        $set('plate_estimasi_sheet', $result['plate_estimasi_sheet']);
        $set('plate_harga_per_sheet', MoneyHelper::format($result['plate_harga_per_sheet'], 2) ?? '0');
        $set('plate_estimasi_material', MoneyHelper::format($result['plate_estimasi_material'], 2) ?? '0');
        $set('plate_estimasi_proses', MoneyHelper::format($result['plate_estimasi_proses'], 2) ?? '0');
        if (($get('plate_mode') ?? 'manual') !== 'manual') {
            $set('biaya_plate', MoneyHelper::format($result['biaya_plate'], 2) ?? '0');
        }
        $set('biaya_plate_terpisah', MoneyHelper::format($result['biaya_plate_terpisah'], 2) ?? '0');

        $set('waktu_cetak', $result['waktu_cetak']);
        $set('total_waktu_mesin', $result['total_waktu_mesin']);
        $set('biaya_mesin', MoneyHelper::format($result['biaya_mesin'], 2) ?? '0');
        $set('biaya_proses_cetak_total', MoneyHelper::format($result['biaya_proses_cetak_total'], 2) ?? '0');
        $set('biaya_proses_per_etiket', MoneyHelper::format($result['biaya_proses_per_etiket'], 2) ?? '0');

        $set('total_biaya_order', MoneyHelper::format($result['total_biaya_order'], 2) ?? '0');
        $set('hpp_per_etiket', MoneyHelper::format($result['hpp_per_etiket'], 2) ?? '0');
        $set('harga_jual_indikatif', MoneyHelper::format($result['harga_jual_indikatif'], 2) ?? '0');
        $set('terbilang_total_biaya', $result['terbilang_total_biaya']);
    }

    /**
     * Schema Form lengkap untuk Modal Kalkulator HPP & Kertas Flexo Zonten (Satuan CM)
     */
    public static function getFormSchema(): array
    {
        return [
            // Hidden target order & basis harga untuk referensi hitungan
            Hidden::make('target_order'),
            Hidden::make('basis_harga_bahan')->default('kg'),
            Hidden::make('terbilang_total_biaya'),

            Placeholder::make('header_info')
                ->content(fn (Get $get) => new HtmlString('
                    <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(59, 130, 246, 0.15)); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <span style="font-size: 13px; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.5px;">Mesin Standar: Flexo Zonten TTC</span>
                            <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">
                                Seluruh ukuran dimensi menggunakan satuan <strong>Centimeter (cm)</strong>. Input kuning dapat disesuaikan; nilai hijau dihitung otomatis.
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
                    // TAB 1: SPESIFIKASI ORDER & LAYOUT (CM)
                    // =========================================================
                    Tabs\Tab::make('A. Spesifikasi Order & Layout')
                        ->icon('heroicon-o-view-columns')
                        ->schema([
                            Section::make('Dimensi & Allowance (Input)')
                                ->description('Ukuran etiket jadi dan toleransi pisau/mesin dalam satuan Centimeter (cm)')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('panjang_etiket_jadi')
                                            ->label('Panjang Etiket Jadi (cm)')
                                            ->numeric()
                                            ->helperText('Arah jalan bahan (feeding)')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebar_etiket_jadi')
                                            ->label('Lebar Etiket Jadi (cm)')
                                            ->numeric()
                                            ->helperText('Melintang terhadap arah bahan')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('repeat_cylinder')
                                            ->label('Repeat Cylinder (cm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_REPEAT_CYLINDER)
                                            ->helperText('Standar Zonten: 41.28 cm (412.75 mm)')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(3)->schema([
                                        TextInput::make('lebihan_cetak_panjang')
                                            ->label('Lebihan Cetak Panjang (cm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_PANJANG)
                                            ->helperText('Allowance pitch (default: 0.3 cm / 3 mm)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebihan_cetak_lebar')
                                            ->label('Lebihan Cetak Lebar (cm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_LEBAR)
                                            ->helperText('Allowance lebar per item (default: 0.3 cm / 3 mm)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('lebihan_lebar_bahan')
                                            ->label('Lebihan Lebar Bahan / Web (cm)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_ALLOWANCE_WEB)
                                            ->helperText('Allowance tepi bahan (default: 2.0 cm / 20 mm)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('jumlah_etiket_sepanjang_repeat')
                                            ->label('Jumlah Etiket Sepanjang Repeat')
                                            ->numeric()
                                            ->default(2)
                                            ->helperText('Arah keliling cylinder')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('jumlah_etiket_sepanjang_lebar')
                                            ->label('Jumlah Etiket Sepanjang Lebar (Lane/Jalur)')
                                            ->numeric()
                                            ->default(2)
                                            ->helperText('Jumlah baris melintang web')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Hasil Analisis Layout Cylinder (Otomatis)')
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextInput::make('lebar_bahan')
                                            ->label('Lebar Bahan / Web (cm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('panjang_cetak_pitch')
                                            ->label('Panjang Cetak / Pitch (cm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('panjang_kebutuhan_per_repeat')
                                            ->label('Kebutuhan Panjang / Repeat (cm)')
                                            ->numeric()
                                            ->readOnly()
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                        TextInput::make('sisa_repeat')
                                            ->label('Sisa Repeat (cm)')
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
                                    Grid::make(3)->schema([
                                        TextInput::make('gramatur_kertas')
                                            ->label('Gramatur Kertas (GSM)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_GRAMATUR)
                                            ->helperText('Contoh: 230 gsm')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('harga_bahan_per_meter')
                                            ->label('Harga Bahan per Meter (Rp/m)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->helperText('Harga kertas per meter linear')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $set('basis_harga_bahan', 'meter');
                                                self::triggerLiveRecalculate($get, $set);
                                            }),

                                        TextInput::make('harga_bahan_per_kg')
                                            ->label('Harga Bahan per Kg (Rp/kg)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(FlexoHppCalculatorService::DEFAULT_HARGA_BAHAN)
                                            ->helperText('Harga kertas per kilogram')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $set('basis_harga_bahan', 'kg');
                                                self::triggerLiveRecalculate($get, $set);
                                            }),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('waste_setting')
                                            ->label('Waste Setting Awal (meter)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_WASTE_SETTING)
                                            ->helperText('Persiapan awal mesin (default: 40 m)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('waste_tambahan_persen')
                                            ->label('Waste Tambahan (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(FlexoHppCalculatorService::DEFAULT_WASTE_PERSEN)
                                            ->helperText('Persentase toleransi waste produksi')
                                            ->live(onBlur: true)
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
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),

                                        TextInput::make('biaya_bahan_per_etiket')
                                            ->label('Biaya Bahan per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
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
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('waktu_setup')
                                            ->label('Waktu Setup (menit)')
                                            ->numeric()
                                            ->default(FlexoHppCalculatorService::DEFAULT_WAKTU_SETUP)
                                            ->helperText('Persiapan mesin sebelum jalan')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('tarif_mesin_per_jam')
                                            ->label('Tarif Mesin per Jam (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->helperText('Tarif biaya mesin per jam')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('biaya_tenaga_kerja')
                                            ->label('Tenaga Kerja Tambahan (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->helperText('Jika di luar tarif mesin')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),

                                    Grid::make(1)->schema([
                                        TextInput::make('biaya_tinta_total')
                                            ->label('Biaya Tinta Total (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->helperText('Total estimasi pemakaian tinta seluruh warna')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                    ]),
                                ]),

                            Section::make('Estimasi Biaya Plate & Prepress (Terintegrasi Stok Plate)')
                                ->description('Hitung kebutuhan plate dari luas layout cylinder & harga rata-rata stok prepress, atau pilih Repeat Order (Rp 0)')
                                ->schema([
                                    Radio::make('plate_mode')
                                        ->label('Status Plate untuk Order Ini')
                                        ->options([
                                            'new'    => 'Plate Baru — Hitung otomatis dari Master Material Plate & Layout Cylinder',
                                            'repeat' => 'Repeat Order — Pakai Arsip Plate di Rak (Biaya Plate = Rp 0)',
                                            'manual' => 'Input Manual — Masukkan langsung nominal Biaya Plate',
                                        ])
                                        ->default('manual')
                                        ->live()
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                    Select::make('plate_archive_id')
                                        ->label('Pilih Arsip Plate Jadi dari Rak Penyimpanan')
                                        ->options(function () {
                                            return PracetakPlateArchive::with('customer')
                                                ->whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])
                                                ->orderByDesc('id')
                                                ->get()
                                                ->mapWithKeys(fn (PracetakPlateArchive $a) => [$a->id => $a->display_label]);
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get) => $get('plate_mode') === 'repeat')
                                        ->helperText('Plate arsip tidak memerlukan pembelian/pemotongan bahan plate baru.'),

                                    Grid::make(1)
                                        ->visible(fn (Get $get) => $get('plate_mode') === 'new')
                                        ->schema([
                                            Select::make('master_material_plate_id')
                                                ->label('Master Material Plate Mentah')
                                                ->options(function () {
                                                    return MasterMaterialPlate::with('stock')
                                                        ->where('status', 'aktif')
                                                        ->orderBy('nama_material')
                                                        ->get()
                                                        ->mapWithKeys(function (MasterMaterialPlate $p) {
                                                            $stok = number_format((float) ($p->stock?->stok_sheet_utuh ?? 0), 2, '.', ',');
                                                            $avg = (float) ($p->stock?->harga_rata_rata ?? 0) > 0
                                                                ? (float) $p->stock->harga_rata_rata
                                                                : (float) $p->harga_beli;

                                                            return [
                                                                $p->id => "{$p->kode_material} · {$p->nama_material} ({$p->ukuran_sheet} | Stok: {$stok} sh | Rp " . (MoneyHelper::format($avg, 0) ?? '0') . '/sh)',
                                                            ];
                                                        });
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                            Grid::make(3)->schema([
                                                TextInput::make('plate_jumlah_warna')
                                                    ->label('Jumlah Warna Plate')
                                                    ->numeric()
                                                    ->default(4)
                                                    ->minValue(1)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                                TextInput::make('plate_margin_potong_cm')
                                                    ->label('Margin Potong Plate (cm)')
                                                    ->numeric()
                                                    ->default(PracetakCostingService::DEFAULT_MARGIN_PLATE_CM)
                                                    ->helperText('Tambahan margin keliling plate (default 2 cm)')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                                TextInput::make('plate_toleransi_persen')
                                                    ->label('Toleransi Afkir / Remake (%)')
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->default(PracetakCostingService::DEFAULT_TOLERANSI_RUSAK_PERSEN)
                                                    ->helperText('Cadangan kerusakan plate (default 10%)')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                            ]),

                                            Grid::make(3)->schema([
                                                TextInput::make('plate_biaya_design')
                                                    ->label('Biaya Trial Design / Layout (Rp)')
                                                    ->prefix('Rp')
                                                    ->currencyMask(2)
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                                TextInput::make('plate_biaya_prove')
                                                    ->label('Biaya Prove Print Sampel (Rp)')
                                                    ->prefix('Rp')
                                                    ->currencyMask(2)
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                                TextInput::make('plate_tarif_proses')
                                                    ->label('Tarif Proses CTP / Plate (Rp)')
                                                    ->prefix('Rp')
                                                    ->currencyMask(2)
                                                    ->default(0)
                                                    ->helperText('Dikalikan jumlah warna plate')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),
                                            ]),

                                            Grid::make(4)->schema([
                                                TextInput::make('plate_estimasi_sheet')
                                                    ->label('Est. Kebutuhan (Sheet)')
                                                    ->readOnly()
                                                    ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                                TextInput::make('plate_harga_per_sheet')
                                                    ->label('Harga Rata-rata / Sheet')
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                    ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                    ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                                TextInput::make('plate_estimasi_material')
                                                    ->label('Est. Material Plate (Rp)')
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                    ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                    ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),

                                                TextInput::make('plate_estimasi_proses')
                                                    ->label('Est. Proses Prepress (Rp)')
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                    ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                    ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),
                                            ]),
                                        ]),

                                    TextInput::make('biaya_plate')
                                        ->label(fn (Get $get) => $get('plate_mode') === 'new' ? 'Total Estimasi HPP Plate (Otomatis) (Rp)' : 'Biaya Plate / Cliché (Rp)')
                                        ->prefix('Rp')
                                        ->currencyMask(2)
                                        ->default(0)
                                        ->readOnly(fn (Get $get) => ($get('plate_mode') ?? 'manual') !== 'manual')
                                        ->helperText('Total HPP pembuatan plate (material + proses prepress)')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                    Radio::make('plate_charge_mode')
                                        ->label('Strategi Pembebanan Biaya Plate ke Harga Jual')
                                        ->options([
                                            'include_in_hpp'  => 'Lebur ke HPP Produk / Pcs (Diamortisasi ke Qty Order, menaikkan harga jual per pcs)',
                                            'separate_charge' => 'Tagih Terpisah sebagai Biaya Plate Baru (HPP/pcs murni cetak; Biaya Plate ditambahkan di Ringkasan OC & PDF)',
                                        ])
                                        ->default('include_in_hpp')
                                        ->visible(fn (Get $get) => ($get('plate_mode') ?? 'manual') !== 'repeat')
                                        ->live()
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                    Grid::make(2)
                                        ->visible(fn (Get $get) => ($get('plate_mode') ?? 'manual') !== 'repeat' && $get('plate_charge_mode') === 'separate_charge')
                                        ->schema([
                                            TextInput::make('plate_margin_persen')
                                                ->label('Margin Khusus Biaya Plate Terpisah (%)')
                                                ->suffix('%')
                                                ->numeric()
                                                ->default(0)
                                                ->helperText('Isi 0 jika ditagihkan pas sesuai HPP plate, atau isi markup misal 15%')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                            TextInput::make('biaya_plate_terpisah')
                                                ->label('Tagihan Biaya Plate Terpisah di OC (Rp)')
                                                ->prefix('Rp')
                                                ->readOnly()
                                                ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                ->extraInputAttributes(['style' => 'background-color: rgba(59, 130, 246, 0.15); font-weight: 700; color: #3b82f6;']),
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
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.1); font-weight: bold; color: #10b981;']),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('biaya_proses_cetak_total')
                                            ->label('Biaya Proses Cetak Total (Rp)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.15); font-weight: 700; color: #10b981; font-size: 14px;']),

                                        TextInput::make('biaya_proses_per_etiket')
                                            ->label('Biaya Proses per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
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
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->helperText('Biaya operasional/overhead per order')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::triggerLiveRecalculate($get, $set)),

                                        TextInput::make('margin_persen')
                                            ->label('Margin Keuntungan (%)')
                                            ->suffix('%')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Markup atas HPP (contoh: isi 15 untuk 15%)')
                                            ->live(onBlur: true)
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
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(59, 130, 246, 0.15); font-weight: 700; color: #3b82f6; font-size: 14px;']),

                                        TextInput::make('hpp_per_etiket')
                                            ->label('HPP Pokok per Etiket (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(245, 158, 11, 0.2); font-weight: 800; color: #f59e0b; font-size: 16px;']),

                                        TextInput::make('harga_jual_indikatif')
                                            ->label('Harga Jual Indikatif (+Margin) (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->readOnly()
                                            ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                            ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                            ->extraInputAttributes(['style' => 'background-color: rgba(16, 185, 129, 0.25); font-weight: 800; color: #10b981; font-size: 16px;']),
                                    ]),

                                    Placeholder::make('plate_charge_summary_box')
                                        ->hiddenLabel()
                                        ->visible(fn (Get $get) => ($get('plate_charge_mode') ?? 'include_in_hpp') === 'separate_charge' && (float) MoneyHelper::clean($get('biaya_plate_terpisah')) > 0)
                                        ->content(function (Get $get) {
                                            $plateSep = (float) (MoneyHelper::clean($get('biaya_plate_terpisah')) ?? 0);
                                            $totalBiaya = (float) (MoneyHelper::clean($get('total_biaya_order')) ?? 0);
                                            $grandWithPlate = $totalBiaya + $plateSep;

                                            return new HtmlString('
                                                <div style="background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.3); border-left: 3px solid #8b5cf6; border-radius: 6px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                                    <div>
                                                        <div style="font-size: 10px; font-weight: 700; color: #8b5cf6; text-transform: uppercase;">Mode Biaya Plate Terpisah (Cliché Charge)</div>
                                                        <div style="font-size: 12px; margin-top: 2px;">Biaya Plate tidak dimasukkan ke HPP/pcs, melainkan ditambahkan di Ringkasan OC & PDF.</div>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <div style="font-size: 11px; color: #6b7280;">Tagihan Plate Terpisah: <strong style="color: #8b5cf6;">Rp ' . (MoneyHelper::format($plateSep, 2) ?? '0') . '</strong></div>
                                                        <div style="font-size: 12px; font-weight: 700;">Total Biaya + Plate: Rp ' . (MoneyHelper::format($grandWithPlate, 2) ?? '0') . '</div>
                                                    </div>
                                                </div>
                                            ');
                                        })
                                        ->columnSpanFull(),

                                    Placeholder::make('terbilang_total_biaya_box')
                                        ->label('')
                                        ->content(function (Get $get) {
                                            $val = $get('total_biaya_order');
                                            $terbilang = \App\Support\MoneyHelper::terbilang($val);
                                            return new HtmlString('
                                                <div style="background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.25); border-left: 3px solid #3b82f6; border-radius: 6px; padding: 8px 14px; margin-top: 2px;">
                                                    <div style="font-size: 10px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.05em;">Terbilang Total Biaya</div>
                                                    <div style="font-size: 13px; font-weight: 600; margin-top: 2px; font-style: italic; line-height: 1.4;">
                                                        “' . e($terbilang) . '”
                                                    </div>
                                                </div>
                                            ');
                                        })
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Pengaturan Harga Satuan & Fix HPP')
                                ->description('Pilih strategi penetapan harga penawaran atau kunci Fix HPP manual')
                                ->icon('heroicon-o-adjustments-horizontal')
                                ->collapsible()
                                ->collapsed(false)
                                ->schema([
                                    Radio::make('harga_mode')
                                        ->label('Opsi Penetapan Harga Penawaran:')
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
