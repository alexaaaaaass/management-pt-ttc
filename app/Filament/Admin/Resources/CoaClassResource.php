<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaClassResource\Pages;
use App\Filament\Admin\Resources\CoaClassResource\RelationManagers;
use App\Models\CoaClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoaClassResource extends Resource
{
    protected static ?string $model = CoaClass::class;
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
    return 'Coa Class';
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
            'index' => Pages\ListCoaClasses::route('/'),
            'create' => Pages\CreateCoaClass::route('/create'),
            'edit' => Pages\EditCoaClass::route('/{record}/edit'),
        ];
    }
}
