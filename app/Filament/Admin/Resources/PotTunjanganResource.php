<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PotTunjanganResource\Pages;
use App\Models\PotTunjangnan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Forms\Components\Select;

class PotTunjanganResource extends Resource
{
    protected static ?string $model = PotTunjangnan::class;
     protected static ?string $navigationGroup = 'HRD';
      protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('karyawan_id')
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
                TextColumn::make('karyawan.nama_lengkap')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_payroll')
                    ->label('Periode Payroll'),

                TextColumn::make('potongan_jabatan')
                    ->money('IDR')
                    ->label('Potongan Jabatan'),

                TextColumn::make('potongan_kompetensi')
                    ->money('IDR')
                    ->label('Potongan Kompetensi'),

                TextColumn::make('potongan_intensif')
                    ->money('IDR')
                    ->label('Potongan Intensif'),

                TextColumn::make('keterangan')
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