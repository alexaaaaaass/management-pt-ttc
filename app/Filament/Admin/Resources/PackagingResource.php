<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PackagingResource\Pages;
use App\Filament\Admin\Resources\PackagingResource\RelationManagers;
use App\Models\Packaging;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackagingResource extends Resource
{
    protected static ?string $model = Packaging::class;
    protected static ?string $navigationGroup = 'Finishing';

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
    return 'Packaging';
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
            'index' => Pages\ListPackagings::route('/'),
            'create' => Pages\CreatePackaging::route('/create'),
            'edit' => Pages\EditPackaging::route('/{record}/edit'),
        ];
    }
}
