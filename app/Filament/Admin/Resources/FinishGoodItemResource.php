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
    Section, Grid, Select, TextInput, Textarea, Repeater
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

            // =============================
            // 🔥 SECTION UTAMA
            // =============================
            Section::make('Create Finish Good Item')
                ->schema([

                    Grid::make(3)->schema([

                        // hidden prefix
                        Hidden::make('kode_prefix'),

                        Select::make('customer_id')
                            ->relationship('customer', 'nama_customer')
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

                        TextInput::make('kode_barcode'),
                        TextInput::make('pc_number'),
                        TextInput::make('nama_barang'),

                        Textarea::make('deskripsi')
                            ->columnSpan(2),

                        Textarea::make('spesifikasi_kertas')
                            ->columnSpan(2),

                        TextInput::make('up_satu'),
                        TextInput::make('up_dua'),
                        TextInput::make('up_tiga'),

                        TextInput::make('ukuran_potong'),
                        TextInput::make('ukuran_cetak'),

                        TextInput::make('panjang')->numeric(),
                        TextInput::make('lebar')->numeric(),
                        TextInput::make('tinggi')->numeric(),

                        TextInput::make('berat_kotor'),
                        TextInput::make('berat_bersih'),

                        Select::make('satuan_id')
                            ->relationship('satuan', 'nama_satuan')
                            ->preload()
                            ->required(),
                    ]),
                ]),

            // =============================
            // 🔥 BILL OF MATERIAL
            // =============================
            Section::make('Bill of Materials')
             ->extraAttributes([
        'style' => '
            border-left: 5px solid #3b82f6;
            border-radius: 10px;
            padding: 12px;
        ',
    ])
                ->collapsible()
                ->schema([

                    Repeater::make('materials')
                        ->relationship()
                        ->schema([

                            Grid::make(3)->schema([

                                Select::make('master_item_id')
                                    ->label('Material')
                                    ->relationship('item', 'nama_master_item')
                                    ->preload()
                                    ->required(),

                                Select::make('departemen_id')
                                    ->label('Departemen')
                                    ->relationship('departemen', 'nama_departemen')
                                    ->preload(),

                                TextInput::make('qty')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('waste')
                                    ->numeric()
                                    ->default(0),

                                Textarea::make('keterangan')
                                    ->columnSpan(2),
                            ]),
                        ])
                        ->addActionLabel('Tambah Material')
                        ->columns(1)
                        ->defaultItems(0),

                ]),
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

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
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