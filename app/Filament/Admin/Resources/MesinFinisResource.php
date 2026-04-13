<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MesinFinisResource\Pages;
use App\Filament\Admin\Resources\MesinFinisResource\RelationManagers;
use App\Models\MesinFinis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MesinFinisResource extends Resource
{
    protected static ?string $model = MesinFinis::class;
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
    return 'Mesin Finish';
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
            'index' => Pages\ListMesinFinis::route('/'),
            'create' => Pages\CreateMesinFinis::route('/create'),
            'edit' => Pages\EditMesinFinis::route('/{record}/edit'),
        ];
    }
}
