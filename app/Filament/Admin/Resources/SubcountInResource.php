<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountInResource\Pages;
use App\Filament\Admin\Resources\SubcountInResource\RelationManagers;
use App\Models\SubcountIn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubcountInResource extends Resource
{
    protected static ?string $model = SubcountIn::class;
    protected static ?string $navigationGroup = 'Subcount';

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
            'index' => Pages\ListSubcountIns::route('/'),
            'create' => Pages\CreateSubcountIn::route('/create'),
            'edit' => Pages\EditSubcountIn::route('/{record}/edit'),
        ];
    }
}
