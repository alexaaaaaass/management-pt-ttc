<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterPrintingResource\Pages;
use App\Models\MasterPrinting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterPrintingResource extends Resource
{
    protected static ?string $model = MasterPrinting::class;
    protected static ?string $navigationGroup = 'Printing';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Mesin Printing';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\TextInput::make('nama_mesin')
                ->label('Nama Mesin')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('jenis_mesin')
                ->label('Jenis Mesin')
                ->required(),

            Forms\Components\TextInput::make('kapasitas')
                ->numeric()
                ->label('Kapasitas'),

            Forms\Components\TextInput::make('proses')
                ->label('Proses'),

            Forms\Components\Toggle::make('status')
                ->label('Status Mesin')
                ->onColor('success')
                ->offColor('danger')
                ->default(true),

        ])
        ->columns(2);
}

public static function getNavigationLabel(): string
{
    return 'Master Printing';
}

  public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('nama_mesin')
                ->searchable(),

            Tables\Columns\TextColumn::make('jenis_mesin')
                ->searchable(),

            Tables\Columns\TextColumn::make('kapasitas'),

            Tables\Columns\TextColumn::make('proses'),

            Tables\Columns\IconColumn::make('status')
                ->boolean()
                ->label('Status'),

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMasterPrintings::route('/'),
            'create' => Pages\CreateMasterPrinting::route('/create'),
            'edit' => Pages\EditMasterPrinting::route('/{record}/edit'),
        ];
    }
}