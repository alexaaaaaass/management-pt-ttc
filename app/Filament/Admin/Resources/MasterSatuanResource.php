<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterSatuanResource\Pages;
use App\Models\MasterSatuan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterSatuanResource extends Resource
{
    protected static ?string $model = MasterSatuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?int $navigationSort = 4;

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('kode_satuan')
                ->label('Kode Satuan')
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('nama_satuan')
                ->label('Nama Satuan')
                ->required()
                ->maxLength(100),
        ]);
}

 public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('kode_satuan')
                ->label('Kode Satuan')
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_satuan')
                ->label('Nama Satuan')
                ->searchable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->date('d M Y'),
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
            'index' => Pages\ListMasterSatuans::route('/'),
            'create' => Pages\CreateMasterSatuan::route('/create'),
            'edit' => Pages\EditMasterSatuan::route('/{record}/edit'),
        ];
    }
}