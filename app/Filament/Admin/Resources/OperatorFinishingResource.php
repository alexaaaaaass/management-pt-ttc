<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperatorFinishingResource\Pages;
use App\Models\OperatorFinishing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperatorFinishingResource extends Resource
{
    protected static ?string $model = OperatorFinishing::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
     protected static ?string $navigationGroup = 'Finishing';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_operator')
                    ->label('Nama Operator Finishing')
                    ->required()
                    ->maxLength(255),
            ]);
    }

public static function getNavigationLabel(): string
{
    return 'Operator Finishing';
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperatorFinishings::route('/'),
            'create' => Pages\CreateOperatorFinishing::route('/create'),
            'edit' => Pages\EditOperatorFinishing::route('/{record}/edit'),
        ];
    }
}