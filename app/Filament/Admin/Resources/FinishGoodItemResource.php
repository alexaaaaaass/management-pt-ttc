<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinishGoodItemResource\Pages;
use App\Models\FinishGoodItem;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{
    Wizard, Section, Grid, Select, TextInput, Textarea, Repeater
};
use App\Models\TypeItem;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\ActionGroup;


class FinishGoodItemResource extends Resource
{
    protected static ?string $model = FinishGoodItem::class;
    protected static ?string $navigationGroup = 'Marketing';

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
                    ->description('Spesifikasi kertas, ukuran potong, dan dimensi')
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('spesifikasi_kertas')
                                ->label('Spesifikasi Kertas / Material')
                                ->placeholder('Contoh: Metalize, Ivory 230gr')
                                ->rows(2)
                                ->columnSpan(2),

                            TextInput::make('gramasi')
                                ->label('Gramasi (GSM)')
                                ->placeholder('Contoh: 230'),

                            TextInput::make('tebal_kertas')
                                ->label('Tebal Kertas (mm)')
                                ->placeholder('Contoh: 0.35'),

                            TextInput::make('up_satu')
                                ->label('Ukuran Potong Produksi (UP)')
                                ->placeholder('Contoh: 4')
                                ->suffix('pcs'),

                            TextInput::make('ukuran_cetak')
                                ->label('Ukuran Cetak')
                                ->placeholder('Contoh: 65 x 100 cm'),

                            TextInput::make('panjang')
                                ->label('Panjang (Meter)')
                                ->numeric()
                                ->suffix('m'),

                            TextInput::make('lebar')
                                ->label('Lebar (Meter)')
                                ->numeric()
                                ->suffix('m'),

                            TextInput::make('tinggi')
                                ->label('Tinggi (Meter)')
                                ->numeric()
                                ->suffix('m'),

                            TextInput::make('berat_kotor')
                                ->label('Berat Kotor (Kg)')
                                ->placeholder('0.00'),

                            TextInput::make('berat_bersih')
                                ->label('Berat Bersih (Kg)')
                                ->placeholder('0.00'),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 4: BILL OF MATERIALS (BOM)
                // ==========================================
                Wizard\Step::make('Bill of Materials (BOM)')
                    ->icon('heroicon-o-queue-list')
                    ->description('Kebutuhan bahan baku produksi & alokasi')
                    ->schema([
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
                                        ->label('Qty Produksi Per Satuan')
                                        ->numeric()
                                        ->required(),

                                    TextInput::make('waste')
                                        ->label('Waste / Toleransi')
                                        ->numeric()
                                        ->default(0),

                                    Textarea::make('keterangan')
                                        ->label('Keterangan')
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