<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterCOAResource\Pages;
use App\Models\MasterCOA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterCOAResource extends Resource
{
    protected static ?string $model = MasterCOA::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
     protected static ?int $navigationSort = 6;
  public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('karyawan_id')
                ->relationship('karyawan', 'nama')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('coa_class_id')
    ->label('COA Class')
    ->relationship('coaClass', 'name')
    ->preload() // 🔥 WAJIB biar langsung load
    ->required(),

            Forms\Components\TextInput::make('periode')
                ->placeholder('2026-04')
                ->required(),

            Forms\Components\TextInput::make('gudang'),

            Forms\Components\TextInput::make('kode_akun')
                ->required(),

            Forms\Components\TextInput::make('nama_akun')
                ->required(),

            Forms\Components\TextInput::make('saldo_debit')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('saldo_kredit')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('nominal_default')
                ->numeric()
                ->default(0),

            Forms\Components\Textarea::make('keterangan'),

            Forms\Components\Select::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ])
                ->default('aktif')
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Master Coa';
}
    

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('karyawan.nama')->label('Karyawan'),

            Tables\Columns\TextColumn::make('coaClass.name')->label('COA Class'),

            Tables\Columns\TextColumn::make('periode'),

            Tables\Columns\TextColumn::make('kode_akun')->label('Kode'),

            Tables\Columns\TextColumn::make('nama_akun')->label('Nama Akun'),

            Tables\Columns\TextColumn::make('saldo_debit')->money('IDR'),

            Tables\Columns\TextColumn::make('saldo_kredit')->money('IDR'),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'aktif',
                    'danger' => 'nonaktif',
                ]),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMasterCOAS::route('/'),
            'create' => Pages\CreateMasterCOA::route('/create'),
            'edit' => Pages\EditMasterCOA::route('/{record}/edit'),
        ];
    }
}