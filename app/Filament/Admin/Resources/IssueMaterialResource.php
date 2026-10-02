<?php

namespace App\Filament\Admin\Resources;
use App\Filament\Admin\Resources\IssueMaterialResource\Pages\ListIssueMaterials as PagesListIssueMaterials;
use App\Filament\Admin\Resources\IssueMaterialResource\Tables\IssueMaterialTable;
use App\Models\ImrPrinting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class IssueMaterialResource extends Resource
{
    protected static ?string $model = ImrPrinting::class;

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $navigationLabel = 'Pengeluaran Material';

    protected static ?string $pluralModelLabel = 'Pengeluaran Material';

    protected static ?string $modelLabel = 'Pengeluaran Material';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()

            ->with([
                'spk',
                'departemen',
                'items.item.stock',
            ])

            ->whereIn('status', [

                'Approved',

                'Partial Issued',

            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string)

            ImrPrinting::whereIn('status', [

                'Approved',

                'Partial Issued',

            ])->count();
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return IssueMaterialTable::make($table);
    }

    public static function getPages(): array
    {
        return [

            'index' =>

                PagesListIssueMaterials::route('/'),

        ];
    }
}