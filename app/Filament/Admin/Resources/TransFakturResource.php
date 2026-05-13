<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransFakturResource\Pages;
use App\Filament\Admin\Resources\TransFakturResource\RelationManagers;
use App\Models\TransFaktur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransFakturResource extends Resource
{
    protected static ?string $model = TransFaktur::class;
     protected static ?string $navigationGroup = 'Finance';
protected static ?int $navigationSort = 7;
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
    return 'Trans Faktur';
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
            'index' => Pages\ListTransFakturs::route('/'),
            'create' => Pages\CreateTransFaktur::route('/create'),
            'edit' => Pages\EditTransFaktur::route('/{record}/edit'),
        ];
    }
}