<?php

namespace App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

   public function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Select::make('item_id')
            ->label('Item')
            ->relationship('item', 'nama_master_item')
            ->searchable()
            ->preload()
            ->required(),

        Forms\Components\TextInput::make('qty')
            ->label('Qty')
            ->numeric()
            ->required(),

        Forms\Components\TextInput::make('satuan')
            ->label('Satuan')
            ->required(),

        Forms\Components\DatePicker::make('eta')
            ->label('ETA'),

        Forms\Components\Textarea::make('catatan')
            ->label('Remark'),
    ]);
}

  public function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('item.nama_master_item')
                ->label('Item'),

            Tables\Columns\TextColumn::make('qty'),

            Tables\Columns\TextColumn::make('satuan'),

            Tables\Columns\TextColumn::make('eta')->date(),

            Tables\Columns\TextColumn::make('catatan'),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}
}