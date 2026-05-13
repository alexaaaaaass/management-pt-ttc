<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasResource\Pages;
use App\Filament\Admin\Resources\TransKasResource\RelationManagers;
use App\Models\TransKas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransKasResource extends Resource
{
    protected static ?string $model = TransKas::class;
     protected static ?string $navigationGroup = 'Finance';
protected static ?int $navigationSort = 8;
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
            'index' => Pages\ListTransKas::route('/'),
            'create' => Pages\CreateTransKas::route('/create'),
            'edit' => Pages\EditTransKas::route('/{record}/edit'),
        ];
    }
}