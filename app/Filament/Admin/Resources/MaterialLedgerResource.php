<?php

namespace App\Filament\Admin\Resources;

use App\Models\MaterialMovement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use App\Filament\Admin\Resources\MaterialLedgerResource\Pages;
use App\Filament\Admin\Resources\MaterialLedgerResource\Tables\MaterialLedgerTable;

class MaterialLedgerResource extends Resource
{
    protected static ?string $model = MaterialMovement::class;

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $navigationLabel = 'Material History';

    protected static ?string $modelLabel = 'Material History';

    protected static ?string $pluralModelLabel = 'Material History';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()

            ->with([

                'item',

                'user',

            ])

            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return MaterialLedgerTable::make($table);
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\MaterialLedgerOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListMaterialLedgers::route('/'),

        ];
    }
}