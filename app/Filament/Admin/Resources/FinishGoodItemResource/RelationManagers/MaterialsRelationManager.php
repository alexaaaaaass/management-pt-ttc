<?php

namespace App\Filament\Admin\Resources\FinishGoodItemResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $title = 'Bill of Materials';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('master_item_id')
                ->relationship('item', 'nama_master_item')
                ->required(),

            Forms\Components\Select::make('departemen_id')
                ->relationship('departemen', 'nama_departemen'),

            Forms\Components\TextInput::make('qty')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('waste')
                ->numeric()
                ->default(0),

            Forms\Components\Textarea::make('keterangan'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.nama_master_item')
                    ->label('Material'),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen'),

                Tables\Columns\TextColumn::make('qty'),

                Tables\Columns\TextColumn::make('waste'),

                Tables\Columns\TextColumn::make('keterangan'),
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