<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PoBillPayResource\Pages;
use App\Filament\Admin\Resources\PoBillPayResource\RelationManagers;
use App\Models\PoBillPay;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PoBillPayResource extends Resource
{
    protected static ?string $model = PoBillPay::class;
     protected static ?string $navigationGroup = 'Finance';

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
    return 'Po Bill Pay';
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
            'index' => Pages\ListPoBillPays::route('/'),
            'create' => Pages\CreatePoBillPay::route('/create'),
            'edit' => Pages\EditPoBillPay::route('/{record}/edit'),
        ];
    }
}
