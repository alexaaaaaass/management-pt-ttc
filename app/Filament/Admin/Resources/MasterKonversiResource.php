<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterKonversiResource\Pages;
use App\Models\MasterKonversi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterKonversiResource extends Resource
{
    protected static ?string $model = MasterKonversi::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 6;

   public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Select::make('type_item_id')
                ->label('Type Item')
                ->relationship('typeItem','nama_type_item')
                ->preload()
                ->required(),

            Forms\Components\Select::make('satuan_satu_id')
                ->label('Satuan Satu')
                ->relationship('satuanSatu','nama_satuan')
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('nilai_konversi')
                ->label('Jumlah Konversi')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('satuan_dua_id')
                ->label('Satuan Dua')
                ->relationship('satuanDua','nama_satuan')
                ->preload()
                ->required(),

        ]);
}

public static function getNavigationLabel(): string
{
    return 'Master Konvensi';
}

   public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('typeItem.nama_type_item')
                ->label('Type Item'),

            Tables\Columns\TextColumn::make('satuanSatu.nama_satuan')
                ->label('Satuan Satu'),

            Tables\Columns\TextColumn::make('nilai_konversi')
                ->label('Konversi'),

            Tables\Columns\TextColumn::make('satuanDua.nama_satuan')
                ->label('Satuan Dua'),

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
            'index' => Pages\ListMasterKonversis::route('/'),
            'create' => Pages\CreateMasterKonversi::route('/create'),
            'edit' => Pages\EditMasterKonversi::route('/{record}/edit'),
        ];
    }
}