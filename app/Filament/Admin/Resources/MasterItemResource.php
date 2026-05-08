<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterItemResource\Pages;
use App\Models\MasterItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterItemResource extends Resource
{
    protected static ?string $model = MasterItem::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Purchase';

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('kode_master_item')
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('nama_master_item')
                ->required(),

            Forms\Components\TextInput::make('min_stock')
                ->numeric(),

            Forms\Components\TextInput::make('min_order')
                ->numeric(),

          Forms\Components\Select::make('satuan_id')
    ->label('Satuan')
    ->relationship('satuan','nama_satuan')
    ->searchable()
    ->preload()
    ->required(),

Forms\Components\Select::make('category_item_id')
    ->label('Kategori')
    ->relationship('kategori','nama_category')
    ->searchable()
    ->preload()
    ->reactive() // penting
    ->afterStateUpdated(fn ($state, callable $set) => $set('type_item_id', null))
    ->required(),

Forms\Components\Select::make('type_item_id')
    ->label('Type Item')
    ->options(function (callable $get) {

        $categoryId = $get('category_item_id');

        if (!$categoryId) {
            return [];
        }

        return \App\Models\TypeItem::where('category_item_id', $categoryId)
            ->pluck('nama_type_item', 'id');
    })
    ->searchable()
    ->preload()
    ->required(),

   Forms\Components\Section::make('Detail Material')
    ->schema([

        Forms\Components\TextInput::make('qty')
            ->numeric()
            ->label('Qty'),

        Forms\Components\TextInput::make('panjang')
            ->numeric()
            ->label('Panjang'),

        Forms\Components\TextInput::make('lebar')
            ->numeric()
            ->label('Lebar'),

        Forms\Components\TextInput::make('tinggi')
            ->numeric()
            ->label('Tinggi'),

        Forms\Components\TextInput::make('berat')
            ->numeric()
            ->label('Berat'),

        Forms\Components\Select::make('tipe_penjualan')
            ->label('Tipe Penjualan')
            ->options([
                'JUAL' => 'JUAL',
                'TIDAK_DIJUAL' => 'TIDAK DIJUAL',
            ])
            ->placeholder('Pilih Tipe Penjualan')
            ->required(),

    ])
    ->columns(2)
    ->visible(function (callable $get) {

        $categoryId = $get('category_item_id');

        if (!$categoryId) {
            return false;
        }

        $category = \App\Models\CategoryItem::find($categoryId);

        return $category?->nama_category === 'MATERIAL PRODUCTION';
    }),
        ])
        ->columns(2);
}

public static function getNavigationLabel(): string
{
    return 'Master Item';
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('kode_master_item')
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_master_item')
                ->searchable(),

            Tables\Columns\TextColumn::make('satuan.nama_satuan')
                ->label('Satuan'),

            Tables\Columns\TextColumn::make('kategori.nama_category')
                ->label('Kategori'),

            Tables\Columns\TextColumn::make('typeItem.nama_type_item')
                ->label('Type Item'),

            Tables\Columns\TextColumn::make('min_stock'),

            Tables\Columns\TextColumn::make('min_order'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMasterItems::route('/'),
            'create' => Pages\CreateMasterItem::route('/create'),
            'edit' => Pages\EditMasterItem::route('/{record}/edit'),
        ];
    }
}