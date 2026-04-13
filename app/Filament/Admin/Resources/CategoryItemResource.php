<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryItemResource\Pages;
use App\Models\CategoryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class CategoryItemResource extends Resource
{
    protected static ?string $model = CategoryItem::class;

    protected static ?string $navigationGroup = 'Purchase';

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Category Item';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_item_category')
                    ->label('Kode Category')
                    ->required()
                    ->maxLength(50),

                Forms\Components\TextInput::make('nama_category')
                    ->label('Nama Category')
                    ->required()
                    ->maxLength(100),
            ]);
    }

public static function getNavigationLabel(): string
{
    return 'Category Item';
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_item_category')
                    ->label('Kode Category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_category')
                    ->label('Nama Category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryItems::route('/'),
            'create' => Pages\CreateCategoryItem::route('/create'),
            'edit' => Pages\EditCategoryItem::route('/{record}/edit'),
        ];
    }
}