<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IMRDieMakingResource\Pages;
use App\Filament\Admin\Resources\IMRDieMakingResource\Schemas\IMRDieMakingForm;
use App\Filament\Admin\Resources\IMRDieMakingResource\Tables\IMRDieMakingTable;
use App\Models\IMRPrinting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IMRDieMakingResource extends Resource
{
    protected static ?string $model = IMRPrinting::class;

    protected static ?string $slug = 'imr-die-makings';

    protected static ?string $navigationGroup = 'Die Making';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'IMR Die Making';

    protected static ?string $modelLabel = 'IMR Die Making';

    protected static ?string $pluralModelLabel = 'IMR Die Making';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('stasiun', IMRPrinting::STASIUN_DIEMAKING)
            ->with([
                'spk.salesOrder.customer',
                'spk.salesOrder.itemable',
                'departemen',
                'items.item.satuan',
                'items.item.stock',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public static function form(Form $form): Form
    {
        return IMRDieMakingForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return IMRDieMakingTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIMRDieMakings::route('/'),
            'create' => Pages\CreateIMRDieMaking::route('/create'),
            'edit'   => Pages\EditIMRDieMaking::route('/{record}/edit'),
        ];
    }
}
