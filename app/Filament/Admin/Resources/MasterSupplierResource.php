<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterSupplierResource\Pages;
use App\Models\MasterSupplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterSupplierResource extends Resource
{
    protected static ?string $model = MasterSupplier::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\TextInput::make('kode_supplier')
                ->label('Kode Supplier')
                ->required()
                ->maxLength(50),

            Forms\Components\TextInput::make('nama_supplier')
                ->label('Nama Supplier')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('jenis_supplier')
                ->label('Jenis Supplier'),

            Forms\Components\Textarea::make('alamat')
                ->label('Alamat')
                ->rows(3),

            Forms\Components\Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(3),

        ]);
}

public static function getNavigationLabel(): string
{
    return 'Master Supplier';
}

   public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('kode_supplier')
                ->label('Kode')
                ->searchable(),

            Tables\Columns\TextColumn::make('nama_supplier')
                ->label('Nama Supplier')
                ->searchable(),

            Tables\Columns\TextColumn::make('jenis_supplier')
                ->label('Jenis Supplier'),

            Tables\Columns\TextColumn::make('alamat')
                ->limit(40),

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
            'index' => Pages\ListMasterSuppliers::route('/'),
            'create' => Pages\CreateMasterSupplier::route('/create'),
            'edit' => Pages\EditMasterSupplier::route('/{record}/edit'),
        ];
    }
}