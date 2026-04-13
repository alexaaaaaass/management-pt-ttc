<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExternalReturnResource\Pages;
use App\Filament\Admin\Resources\ExternalReturnResource\RelationManagers;
use App\Models\ExternalReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExternalReturnResource extends Resource
{
    protected static ?string $model = ExternalReturn::class;
    protected static ?string $navigationGroup = 'Purchase';

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
    return 'External Return';
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
            'index' => Pages\ListExternalReturns::route('/'),
            'create' => Pages\CreateExternalReturn::route('/create'),
            'edit' => Pages\EditExternalReturn::route('/{record}/edit'),
        ];
    }
}
