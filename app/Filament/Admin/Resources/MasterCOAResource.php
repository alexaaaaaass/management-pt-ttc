<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterCOAResource\Pages;
use App\Filament\Admin\Resources\MasterCOAResource\RelationManagers;
use App\Models\MasterCOA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterCOAResource extends Resource
{
    protected static ?string $model = MasterCOA::class;
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
    return 'Master Coa';
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
            'index' => Pages\ListMasterCOAS::route('/'),
            'create' => Pages\CreateMasterCOA::route('/create'),
            'edit' => Pages\EditMasterCOA::route('/{record}/edit'),
        ];
    }
}
