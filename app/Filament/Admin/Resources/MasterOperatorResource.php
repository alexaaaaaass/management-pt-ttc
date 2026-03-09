<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterOperatorResource\Pages;
use App\Models\MasterOperator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterOperatorResource extends Resource
{
    protected static ?string $model = MasterOperator::class;
    protected static ?string $navigationGroup = 'Printing';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?int $navigationSort = 2;

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nama_operator')
                ->label('Nama Operator Printing')
                ->required()
                ->maxLength(100),
        ]);
}

   public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nama_operator')
                ->label('Nama Operator')
                ->searchable(),
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
            'index' => Pages\ListMasterOperators::route('/'),
            'create' => Pages\CreateMasterOperator::route('/create'),
            'edit' => Pages\EditMasterOperator::route('/{record}/edit'),
        ];
    }
}