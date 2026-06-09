<?php

namespace App\Filament\Admin\Resources;

use App\Models\PotTunjangan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use App\Filament\Admin\Resources\PotTunjanganResource\Pages;

class PotTunjanganResource extends Resource
{
    protected static ?string $model = PotTunjangan::class;

    protected static ?string $navigationGroup = 'HRD';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-minus-circle';

    protected static ?string $navigationLabel = 'Pot Tunjangan';

    public static function getModelLabel(): string
    {
        return 'Pot Tunjangan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pot Tunjangan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('periode_payroll')
                    ->label('Periode Payroll')
                    ->placeholder('Contoh: Mei 2026')
                    ->required(),

                Forms\Components\TextInput::make('potongan_jabatan')
                    ->label('Potongan Tunjangan Jabatan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('potongan_kompetensi')
                    ->label('Potongan Kompetensi')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('potongan_intensif')
                    ->label('Potongan Intensif')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama_lengkap')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_payroll')
                    ->label('Periode Payroll'),

                Tables\Columns\TextColumn::make('potongan_jabatan')
                    ->money('IDR')
                    ->label('Potongan Jabatan'),

                Tables\Columns\TextColumn::make('potongan_kompetensi')
                    ->money('IDR')
                    ->label('Potongan Kompetensi'),

                Tables\Columns\TextColumn::make('potongan_intensif')
                    ->money('IDR')
                    ->label('Potongan Intensif'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(30),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPotTunjangans::route('/'),
            'create' => Pages\CreatePotTunjangan::route('/create'),
            'edit' => Pages\EditPotTunjangan::route('/{record}/edit'),
        ];
    }
}