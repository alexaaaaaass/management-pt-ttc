<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TypeItemResource\Pages;
use App\Models\TypeItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class TypeItemResource extends Resource
{
    protected static ?string $model = TypeItem::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\TextInput::make('kode_type_item')
                ->label('Kode Type Item')
                ->required(),

            Forms\Components\TextInput::make('nama_type_item')
                ->label('Nama Type Item')
                ->required(),

            Forms\Components\Select::make('category_item_id')
                ->label('Kategori Item')
                ->relationship('category','nama_category')
                ->searchable()
                ->preload()
                ->required(),

        ]);
}

public static function getNavigationLabel(): string
{
    return 'Type Item';
}
    public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('kode_type_item')
                ->label('Kode'),

            Tables\Columns\TextColumn::make('nama_type_item')
                ->label('Type Item'),

            Tables\Columns\TextColumn::make('category.nama_category')
                ->label('Kategori'),

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTypeItems::route('/'),
            'create' => Pages\CreateTypeItem::route('/create'),
            'edit' => Pages\EditTypeItem::route('/{record}/edit'),
        ];
    }
}