<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InternalreturnResource\Pages;
use App\Models\Internalreturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class InternalreturnResource extends Resource
{
    protected static ?string $model = Internalreturn::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getNavigationLabel(): string
{
    return 'Internal Return';
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListInternalreturns::route('/'),
            'create' => Pages\CreateInternalreturn::route('/create'),
            'edit' => Pages\EditInternalreturn::route('/{record}/edit'),
        ];
    }
}