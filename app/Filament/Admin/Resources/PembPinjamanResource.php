<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PembPinjamanResource\Pages;
use App\Filament\Admin\Resources\PembPinjamanResource\RelationManagers;
use App\Models\PembPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PembPinjamanResource extends Resource
{
    protected static ?string $model = PembPinjaman::class;
    protected static ?string $navigationGroup = 'HRD';

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
            'index' => Pages\ListPembPinjamen::route('/'),
            'create' => Pages\CreatePembPinjaman::route('/create'),
            'edit' => Pages\EditPembPinjaman::route('/{record}/edit'),
        ];
    }
}
