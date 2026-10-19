<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinishGoodItemResource\Pages;
use App\Models\FinishGoodItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Forms\Components\{
    Wizard, Section, Grid, Select, TextInput, Textarea, Repeater, Placeholder, Hidden
};
use App\Models\TypeItem;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\HtmlString;


class FinishGoodItemResource extends Resource
{
    protected static ?string $model = FinishGoodItem::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Wizard::make([

                // ==========================================
                // 🔥 TAHAP 1: DATA CUSTOMER & IDENTITAS PRODUK
                // ==========================================
                Wizard\Step::make('Data Customer & Identitas')
                    ->icon('heroicon-o-identification')
                    ->description('Customer, tipe item, dan kode produk')
                    ->schema([
                        Grid::make(3)->schema([
                            Hidden::make('kode_prefix'),

                            Select::make('customer_id')
                                ->relationship('customer', 'nama_customer')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('type_item_id')
                                ->label('Type Item')
                                ->relationship('typeItem', 'nama_type_item')
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $type = TypeItem::find($state);

                                    if ($type) {
                                        $set('kode_prefix', $type->kode_type_item);

                                        $suffix = $get('kode_suffix');

                                        $set(
                                            'kode_material_produk',
                                            $suffix
                                                ? $type->kode_type_item . '-' . $suffix
                                                : $type->kode_type_item
                                        );
                                    }
                                })
                                ->required(),

                            TextInput::make('kode_suffix')
                                ->label('Kode Tambahan')
                                ->placeholder('Contoh: 001, A')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $prefix = $get('kode_prefix');

                                    if ($prefix) {
                                        $set('kode_material_produk', $prefix . '-' . $state);
                                    } else {
                                        $set('kode_material_produk', $state);
                                    }
                                }),

                            TextInput::make('kode_material_produk')
                                ->label('Kode Material Produk')
                                ->readOnly()
                                ->required(),

                            TextInput::make('nama_barang')
                                ->label('Nama Barang')
                                ->placeholder('Nama lengkap produk kemasan')
                                ->columnSpan(2)
                                ->required(),

                            Select::make('satuan_id')
                                ->label('Satuan')
                                ->relationship('satuan', 'nama_satuan')
                                ->preload()
                                ->required(),

                            TextInput::make('kode_barcode')
                                ->label('Kode Barcode')
                                ->placeholder('Barcode produk jika ada'),

                            TextInput::make('pc_number')
                                ->label('PC Number')
                                ->placeholder('Nomor PC'),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 2: KARAKTERISTIK & FINISHING
                // ==========================================
                Wizard\Step::make('Karakteristik Kemasan')
                    ->icon('heroicon-o-cube')
                    ->description('Jenis, warna, komponen, dan finishing')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('jenis')
                                ->label('Jenis (Rokok/Produk)')
                                ->placeholder('Contoh: SKM, SKT, SPM'),

                            TextInput::make('komponen')
                                ->label('Komponen')
                                ->placeholder('Contoh: Etiket, Inner Frame'),

                            TextInput::make('isi')
                                ->label('Isi (Batang/Pack)')
                                ->placeholder('Contoh: 20, 16, 12'),

                            TextInput::make('warna')
                                ->label('Warna Cetak')
                                ->placeholder('Contoh: 5 Warna, 4+1'),

                            TextInput::make('finishing')
                                ->label('Proses Finishing')
                                ->placeholder('Contoh: Spot UV, Cutting, Emboss, Foil')
                                ->columnSpanFull(),

                            Textarea::make('deskripsi')
                                ->label('Deskripsi / Catatan Khusus')
                                ->placeholder('Catatan teknis atau deskripsi produk')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 3: SPESIFIKASI KERTAS & DIMENSI
                // ==========================================
                Wizard\Step::make('Kertas & Dimensi')
                    ->icon('heroicon-o-scale')
                    ->description('Spesifikasi kertas, ukuran potong, dan dimensi kemasan')
                    ->schema([
                        // Card 1: Spesifikasi Kertas Cetak (Multi-Item)
                        Section::make('Spesifikasi Kertas Cetak')
                            ->description('Spesifikasi lembaran kertas plano dan ukuran potong produksi. Anda dapat menambahkan lebih dari 1 jenis kertas jika kemasan menggunakan kombinasi beberapa bahan kertas (misal: Kertas Metalized & Ivory).')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Repeater::make('kertas_items')
                                    ->label('Daftar Kertas Cetak / Material')
                                    ->addActionLabel('+ Tambah Spesifikasi Kertas')
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->defaultItems(1)
                                    ->itemLabel(function ($state, $uuid) {
                                        $nama = $state['nama_kertas'] ?? $state['spesifikasi_kertas'] ?? null;
                                        if (!$nama) {
                                            return 'Kertas Cetak #' . (is_numeric($uuid) ? ((int)$uuid + 1) : '');
                                        }
                                        $gsm = !empty($state['gramasi']) ? " ({$state['gramasi']} GSM)" : '';
                                        $bagian = !empty($state['bagian_kemasan']) ? " [{$state['bagian_kemasan']}]" : '';
                                        return "{$nama}{$gsm}{$bagian}";
                                    })
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('nama_kertas')
                                                ->label('Spesifikasi Kertas / Material')
                                                ->placeholder('Contoh: KERTAS METALIZED atau IVORY 230 GR 53 X 68.5')
                                                ->required()
                                                ->columnSpan(2),

                                            TextInput::make('bagian_kemasan')
                                                ->label('Bagian / Komponen (Opsional)')
                                                ->placeholder('Contoh: Etiket Luar, Inner Frame, Sliding Box')
                                                ->helperText('Peruntukan bagian kemasan rokok.'),

                                            TextInput::make('gramasi')
                                                ->label('Gramasi (GSM)')
                                                ->placeholder('Contoh: 230'),

                                            TextInput::make('tebal_kertas')
                                                ->label('Tebal Kertas (mm)')
                                                ->placeholder('Contoh: 0.35'),

                                            TextInput::make('up')
                                                ->label('Ukuran Potong Produksi (UP)')
                                                ->placeholder('Contoh: 9')
                                                ->suffix('pcs'),

                                            TextInput::make('ukuran_cetak')
                                                ->label('Ukuran Cetak / Plano')
                                                ->placeholder('Contoh: 65 x 100 cm'),

                                            TextInput::make('keterangan')
                                                ->label('Catatan Kertas (Opsional)')
                                                ->placeholder('Contoh: Kertas roll disheet dulu ke plano')
                                                ->columnSpan(2),
                                        ]),
                                    ])
                                    ->columns(1),
                            ]),

                        // Card 2: Dimensi Fisik Kemasan Rokok Jadi (3D)
                        Section::make('Dimensi Kemasan Rokok Jadi (3D)')
                            ->description('Ukuran fisik 1 kotak/bungkus rokok saat dilipat jadi (Acuan tim Pisau Pond / Diemaking). Satuan dalam Centimeter (cm).')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('panjang')
                                        ->label('Panjang Kemasan (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder('Contoh: 5.5')
                                        ->helperText('Tampak depan sisi bawah kotak.'),

                                    TextInput::make('lebar')
                                        ->label('Lebar / Tebal Kemasan (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder('Contoh: 2.2')
                                        ->helperText('Sisi ketebalan samping kotak.'),

                                    TextInput::make('tinggi')
                                        ->label('Tinggi Kemasan (cm)')
                                        ->numeric()
                                        ->suffix('cm')
                                        ->placeholder('Contoh: 9.0')
                                        ->helperText('Tinggi kotak dari bawah ke atas.'),
                                ]),
                            ]),

                        // Card 3: Estimasi Bobot Kemasan (Opsional)
                        Section::make('Estimasi Berat Kemasan (Opsional)')
                            ->description('Data berat untuk estimasi logistik pengiriman')
                            ->icon('heroicon-o-scale')
                            ->collapsible()
                            ->collapsed(false)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('berat_kotor')
                                        ->label('Berat Kotor (Kg)')
                                        ->placeholder('0.00'),

                                    TextInput::make('berat_bersih')
                                        ->label('Berat Bersih (Kg)')
                                        ->placeholder('0.00'),
                                ]),
                            ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 4: BILL OF MATERIALS (BOM)
                // ==========================================
                Wizard\Step::make('Bill of Materials (BOM)')
                    ->icon('heroicon-o-queue-list')
                    ->description('Daftar resep bahan baku & penolong (Kertas, Tinta, Lem, Kimia)')
                    ->schema([
                        Placeholder::make('bom_guide')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <style>
                                    .bom-warning-card {
                                        background: linear-gradient(135deg, rgba(254, 243, 199, 0.7) 0%, rgba(253, 230, 138, 0.4) 100%);
                                        border: 1px solid #f59e0b;
                                        border-left: 6px solid #d97706;
                                        color: #78350f;
                                    }
                                    .dark .bom-warning-card {
                                        background: linear-gradient(135deg, rgba(120, 53, 15, 0.25) 0%, rgba(180, 83, 9, 0.15) 100%);
                                        border: 1px solid rgba(245, 158, 11, 0.4);
                                        border-left: 6px solid #f59e0b;
                                        color: #fef3c7;
                                    }
                                    .bom-badge {
                                        background: #fde68a;
                                        border: 1px solid #f59e0b;
                                        color: #92400e;
                                    }
                                    .dark .bom-badge {
                                        background: rgba(245, 158, 11, 0.25);
                                        border: 1px solid rgba(245, 158, 11, 0.5);
                                        color: #fde68a;
                                    }
                                    .bom-close-btn {
                                        color: #92400e;
                                        border-radius: 6px;
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        cursor: pointer;
                                        background: transparent;
                                        border: none;
                                        padding: 4px;
                                    }
                                    .bom-close-btn:hover {
                                        background-color: rgba(217, 119, 6, 0.2);
                                        color: #78350f;
                                    }
                                    .dark .bom-close-btn {
                                        color: #fde68a;
                                    }
                                    .dark .bom-close-btn:hover {
                                        background-color: rgba(245, 158, 11, 0.25);
                                        color: #ffffff;
                                    }
                                </style>
                                <div x-data="{ show: true }" class="w-full">
                                    <!-- Tombol Tampilkan Kembali Jika Sedang Di-close -->
                                    <div x-show="!show" x-cloak class="my-2">
                                        <button 
                                            type="button" 
                                            @click="show = true" 
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 hover:underline py-1 px-2.5 rounded-lg border border-amber-500/30 bg-amber-500/10 hover:bg-amber-500/20 transition cursor-pointer"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Buka Panduan Pengisian Bahan (BOM)
                                        </button>
                                    </div>

                                    <!-- Card Warning Utama -->
                                    <div 
                                        x-show="show" 
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="bom-warning-card rounded-xl p-4 my-2 shadow-sm"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="p-2.5 rounded-lg shrink-0 mt-0.5" style="background: rgba(245, 158, 11, 0.25); color: #d97706;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 space-y-2 text-xs sm:text-sm">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <h4 class="font-bold text-sm tracking-wide flex items-center gap-1.5" style="color: #b45309;">
                                                        ⚠️ PANDUAN PENTING PENGISIAN BAHAN (BOM)
                                                    </h4>
                                                    <div class="flex items-center gap-2">
                                                        <span class="bom-badge px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                                            Qty Boleh 0 / Kosong
                                                        </span>
                                                        <button 
                                                            type="button" 
                                                            @click="show = false" 
                                                            class="bom-close-btn"
                                                            title="Tutup panduan ini"
                                                            aria-label="Tutup panduan"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="space-y-1.5 leading-relaxed">
                                                    <p>
                                                        <strong>1. Bahan Penolong (Tinta, Lem, Vernish, Alkohol IPA, Kimia):</strong><br>
                                                        👉 <em>Qty Per Satuan</em> <strong>boleh diisi 0 (atau kosongkan saja)</strong>. Anda tidak perlu pusing menghitung takaran mikro per 1 pcs etiket. Cukup pilih nama bahannya saja di daftar ini.
                                                    </p>
                                                    <p>
                                                        <strong>2. Kapan Kuantiti Aslinya Diminta?</strong><br>
                                                        👉 Kebutuhan riil di lapangan (misal: <em>1 Jerigen Alkohol / 2 Kg Tinta</em>) nantinya akan diminta langsung oleh operator cetak melalui form <strong>IMR (Internal Material Request)</strong> saat mesin siap produksi.
                                                    </p>
                                                    <p>
                                                        <strong>3. Kertas / Roll Utama:</strong><br>
                                                        👉 Tetap masukkan estimasi kebutuhan lembar/meter per pcs jika sudah ada hitungan bakunya.
                                                    </p>
                                                </div>

                                                <div class="pt-2 border-t border-amber-500/20 text-xs italic opacity-90 flex items-center gap-1.5">
                                                    <span>💡</span>
                                                    <span><strong>Tips Lapangan:</strong> Mengosongkan Qty bahan penolong tidak akan menghambat pembuatan SPK maupun penarikan barang di gudang.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            '))
                            ->columnSpanFull(),

                        Repeater::make('materials')
                            ->relationship()
                            ->collapsible()
                            ->collapsed(false)
                            ->itemLabel(function ($state) {
                                if (!empty($state['master_item_id'])) {
                                    $item = \App\Models\MasterItem::find($state['master_item_id']);
                                    return $item?->nama_master_item ?? 'Material';
                                }
                                return 'Material Baru';
                            })
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('master_item_id')
                                        ->label('Material')
                                        ->relationship('item', 'nama_master_item')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('departemen_id')
                                        ->label('Departemen')
                                        ->relationship('departemen', 'nama_departemen')
                                        ->preload(),

                                    TextInput::make('qty')
                                        ->label('Qty Per Satuan (Opsional)')
                                        ->numeric()
                                        ->nullable()
                                        ->default(0)
                                        ->placeholder('0 (atau kosong)')
                                        ->helperText('Boleh diisi 0 untuk bahan penolong (Tinta, Alkohol, Lem).'),

                                    TextInput::make('waste')
                                        ->label('Waste / Toleransi (%)')
                                        ->numeric()
                                        ->default(0),

                                    Textarea::make('keterangan')
                                        ->label('Keterangan / Catatan Penggunaan')
                                        ->placeholder('Contoh: Minta 1 jerigen per shift jalan cetak')
                                        ->columnSpan(2),
                                ]),
                            ])
                            ->addActionLabel('Tambah Bahan Baku (BOM)')
                            ->columns(1)
                            ->defaultItems(0),
                    ]),

            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
}

public static function getNavigationLabel(): string
{
    return 'Finish Good Item';
}
   public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            TextColumn::make('kode_material_produk')
                ->searchable()
                ->sortable(),

            TextColumn::make('nama_barang')
                ->searchable()
                ->sortable(),

            TextColumn::make('customer.nama_customer')
                ->label('Customer')
                ->sortable(),

            TextColumn::make('typeItem.nama_type_item')
                ->label('Type'),

            TextColumn::make('satuan.nama_satuan')
                ->label('Satuan'),
        ])

        ->actions([
            ActionGroup::make([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->label('Cut Off')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->trashed()), // 🔥 hanya muncul kalau belum dihapus

                Tables\Actions\RestoreAction::make()
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn ($record) => $record->trashed()), // 🔥 hanya muncul kalau sudah dihapus

                // 🔥 OPTIONAL (biar lebih pro)
                Tables\Actions\ViewAction::make(),

            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->tooltip('Actions')
        ])

        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->label('Cut Off Selected'),

            Tables\Actions\RestoreBulkAction::make()
                ->label('Restore Selected'),
        ])

        ->filters([
            Tables\Filters\TrashedFilter::make(), // 🔥 penting untuk lihat data terhapus
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinishGoodItems::route('/'),
            'create' => Pages\CreateFinishGoodItem::route('/create'),
            'edit' => Pages\EditFinishGoodItem::route('/{record}/edit'),
        ];
    }
}