<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperatorPraCetakResource\Pages;
use App\Models\OperatorPraCetak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperatorPraCetakResource extends Resource
{
    protected static ?string $model = OperatorPraCetak::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Operator Pra Cetak';
    protected static ?string $navigationGroup = 'Pra Cetak';
    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_operator')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_operator')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperatorPraCetaks::route('/'),
            'create' => Pages\CreateOperatorPraCetak::route('/create'),
            'edit' => Pages\EditOperatorPraCetak::route('/{record}/edit'),
        ];
    }
}