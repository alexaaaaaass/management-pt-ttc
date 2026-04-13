<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountOutResource\Pages;
use App\Filament\Admin\Resources\SubcountOutResource\RelationManagers;
use App\Models\SubcountOut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubcountOutResource extends Resource
{
    protected static ?string $model = SubcountOut::class;
     protected static ?string $navigationGroup = 'Subcount';

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
    return 'Subcount Out';
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
            'index' => Pages\ListSubcountOuts::route('/'),
            'create' => Pages\CreateSubcountOut::route('/create'),
            'edit' => Pages\EditSubcountOut::route('/{record}/edit'),
        ];
    }
}
