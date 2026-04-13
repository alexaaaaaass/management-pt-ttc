<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IzinResource\Pages;
use App\Filament\Admin\Resources\IzinResource\RelationManagers;
use App\Models\Izin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
     protected static ?string $navigationGroup = 'HRD';

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
    return 'Izin';
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
            'index' => Pages\ListIzins::route('/'),
            'create' => Pages\CreateIzin::route('/create'),
            'edit' => Pages\EditIzin::route('/{record}/edit'),
        ];
    }
}
