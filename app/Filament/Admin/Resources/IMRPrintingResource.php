<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IMRPrintingResource\Pages;
use App\Filament\Admin\Resources\IMRPrintingResource\Schemas\IMRPrintingForm;
use App\Filament\Admin\Resources\IMRPrintingResource\Tables\IMRPrintingTable;
use App\Models\ImrPrinting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class IMRPrintingResource extends Resource
{
    protected static ?string $model = ImrPrinting::class;

    protected static ?string $navigationGroup = 'Printing';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'IMR Printing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return IMRPrintingForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return IMRPrintingTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIMRPrintings::route('/'),
            'create' => Pages\CreateIMRPrinting::route('/create'),
            'edit'   => Pages\EditIMRPrinting::route('/{record}/edit'),
        ];
    }
}