<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasBankResource\Pages;
use App\Filament\Admin\Resources\TransKasBankResource\RelationManagers;
use App\Models\TransKasBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransKasBankResource extends Resource
{
    protected static ?string $model = TransKasBank::class;
     protected static ?string $navigationGroup = 'Finance';
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
    return 'Trans Kas Bank';
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
            'index' => Pages\ListTransKasBanks::route('/'),
            'create' => Pages\CreateTransKasBank::route('/create'),
            'edit' => Pages\EditTransKasBank::route('/{record}/edit'),
        ];
    }
}