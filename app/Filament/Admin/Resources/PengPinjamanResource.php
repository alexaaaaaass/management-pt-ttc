<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PengPinjamanResource\Pages;
use App\Models\PengPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengPinjamanResource extends Resource
{
    protected static ?string $model = PengPinjaman::class;

    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Peng Pinjaman';
    protected static ?string $modelLabel = 'Pengajuan';
    protected static ?string $pluralModelLabel = 'Peng Pinjaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('kode_gudang')
                    ->label('Kode Gudang')
                    ->required()
                    ->maxLength(50),

                Forms\Components\DatePicker::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('nilai_pinjaman')
                    ->label('Nilai Pinjaman')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('jangka_waktu')
                    ->label('Jangka Waktu (Bulan)')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('cicilan_per_bulan')
                    ->label('Cicilan Per Bulan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Textarea::make('keperluan_pinjaman')
                    ->label('Keperluan Pinjaman')
                    ->rows(4)
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nomor_bukti')
                    ->label('Nomor Bukti')
                    ->searchable(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nilai_pinjaman')
                    ->label('Nilai Pinjaman')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jangka_waktu')
                    ->label('Jangka Waktu')
                    ->suffix(' Bulan'),

                Tables\Columns\TextColumn::make('cicilan_per_bulan')
                    ->label('Cicilan / Bulan')
                    ->money('IDR'),

            ])
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Cari nomor bukti atau nama karyawan...')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengPinjamen::route('/'),
            'create' => Pages\CreatePengPinjaman::route('/create'),
            'edit' => Pages\EditPengPinjaman::route('/{record}/edit'),
        ];
    }
}