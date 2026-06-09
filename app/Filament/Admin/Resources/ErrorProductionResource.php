<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ErrorProductionResource\Pages;
use App\Models\ErrorProduction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ErrorProductionResource extends Resource
{
    protected static ?string $model = ErrorProduction::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?string $navigationLabel = 'Error Production';

    protected static ?string $modelLabel = 'Error Production';

    protected static ?string $pluralModelLabel = 'Error Production';
   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('kode_error')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Forms\Components\TextInput::make('nama_error')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
        ]);
}

  public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('kode_error')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('nama_error')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime('d M Y H:i')
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
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
            'index' => Pages\ListErrorProductions::route('/'),
            'create' => Pages\CreateErrorProduction::route('/create'),
            'edit' => Pages\EditErrorProduction::route('/{record}/edit'),
        ];
    }
}