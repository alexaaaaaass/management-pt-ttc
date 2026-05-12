<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ImrFinishResource\Pages;
use App\Filament\Admin\Resources\ImrFinishResource\RelationManagers;
use App\Models\ImrFinish;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImrFinishResource extends Resource
{
    protected static ?string $model = ImrFinish::class;
    protected static ?string $navigationGroup = 'Finishing';
    protected static ?int $navigationSort = 5;

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
    return 'IMR Finish';
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
            'index' => Pages\ListImrFinishes::route('/'),
            'create' => Pages\CreateImrFinish::route('/create'),
            'edit' => Pages\EditImrFinish::route('/{record}/edit'),
        ];
    }
}