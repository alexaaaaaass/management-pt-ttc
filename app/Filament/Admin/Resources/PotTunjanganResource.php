<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PotTunjanganResource\Pages;
use App\Models\PotTunjangan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PotTunjanganResource extends Resource
{
    protected static ?string $model = PotTunjangan::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-scissors';

    public static function getNavigationLabel(): string
    {
        return 'Potongan Tunjangan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('periode_payroll')
                    ->label('Periode Payroll (YYYY-MM)')
                    ->default(now()->format('Y-m'))
                    ->placeholder('Contoh: 2026-10')
                    ->required(),

                TextInput::make('potongan_jabatan')
                    ->label('Potongan Tunjangan Jabatan')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungTotal($get, $set))
                    ->required(),

                TextInput::make('potongan_kompetensi')
                    ->label('Potongan Kompetensi')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungTotal($get, $set))
                    ->required(),

                TextInput::make('potongan_intensif')
                    ->label('Potongan Intensif')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungTotal($get, $set))
                    ->required(),

                TextInput::make('total_potongan')
                    ->label('Total Potongan')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                Textarea::make('keterangan')
                    ->label('Keterangan / Alasan Potongan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function hitungTotal($get, $set): void
    {
        $jabatan = (float) ($get('potongan_jabatan') ?? 0);
        $kompetensi = (float) ($get('potongan_kompetensi') ?? 0);
        $intensif = (float) ($get('potongan_intensif') ?? 0);

        $set('total_potongan', round($jabatan + $kompetensi + $intensif, 2));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('periode_payroll')
                    ->label('Periode')
                    ->badge(),

                TextColumn::make('potongan_jabatan')
                    ->money('IDR')
                    ->label('Pot. Jabatan'),

                TextColumn::make('potongan_kompetensi')
                    ->money('IDR')
                    ->label('Pot. Kompetensi'),

                TextColumn::make('potongan_intensif')
                    ->money('IDR')
                    ->label('Pot. Intensif'),

                TextColumn::make('total_potongan')
                    ->money('IDR')
                    ->label('Total Potongan')
                    ->weight('bold')
                    ->color('danger'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPotTunjangans::route('/'),
            'create' => Pages\CreatePotTunjangan::route('/create'),
            'edit'   => Pages\EditPotTunjangan::route('/{record}/edit'),
        ];
    }
}