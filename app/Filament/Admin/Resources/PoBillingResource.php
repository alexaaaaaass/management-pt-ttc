<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PoBillingResource\Pages;
use App\Filament\Admin\Resources\PoBillingResource\RelationManagers;
use App\Models\PoBilling;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PoBillingResource extends Resource
{
    protected static ?string $model = PoBilling::class;
     protected static ?string $navigationGroup = 'Finance';
     protected static ?int $navigationSort = 11;
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
    return 'Po Billing';
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
            'index' => Pages\ListPoBillings::route('/'),
            'create' => Pages\CreatePoBilling::route('/create'),
            'edit' => Pages\EditPoBilling::route('/{record}/edit'),
        ];
    }
}