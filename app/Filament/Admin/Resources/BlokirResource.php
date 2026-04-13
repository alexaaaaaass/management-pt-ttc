<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BlokirResource\Pages;
use App\Filament\Admin\Resources\BlokirResource\RelationManagers;
use App\Models\Blokir;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlokirResource extends Resource
{
    protected static ?string $model = Blokir::class;
     protected static ?string $navigationGroup = 'Dispatch';

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
    return 'Blokir';
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
            'index' => Pages\ListBlokirs::route('/'),
            'create' => Pages\CreateBlokir::route('/create'),
            'edit' => Pages\EditBlokir::route('/{record}/edit'),
        ];
    }
}
