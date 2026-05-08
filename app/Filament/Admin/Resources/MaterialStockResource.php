<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MaterialStockResource\Pages;

use App\Models\MaterialStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialStockResource extends Resource
{
    protected static ?string $model = MaterialStock::class;
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('item.kode_master_item')
                ->label('Kode Item')
                ->searchable(),

            Tables\Columns\TextColumn::make('item.nama_master_item')
                ->label('Nama Item')
                ->searchable(),

            Tables\Columns\TextColumn::make('item.satuan.nama_satuan')
                ->label('Satuan'),

            Tables\Columns\TextColumn::make('item.min_stock')
                ->label('Min Stock'),

           Tables\Columns\TextColumn::make('on_hand')
    ->label('On Hand')
    ->badge()
    ->color(fn ($record) => 
        $record->on_hand < $record->item->min_stock ? 'danger' : 'success'
    ),

            Tables\Columns\TextColumn::make('allocation')
                ->label('Allocation')
                ->badge()
                ->color('warning'),

            Tables\Columns\TextColumn::make('outstanding')
                ->label('Outstanding')
                ->badge()
                ->color(fn ($state) => $state < 0 ? 'danger' : 'primary'),

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
            'index' => Pages\ListMaterialStocks::route('/'),
            'create' => Pages\CreateMaterialStock::route('/create'),
            'edit' => Pages\EditMaterialStock::route('/{record}/edit'),
        ];
    }
}