<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PembPinjamanResource\Pages;
use App\Models\PembPinjaman;
use App\Models\PengPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PembPinjamanResource extends Resource
{
    protected static ?string $model = PembPinjaman::class;

    protected static ?string $navigationGroup = 'HRD';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Pemb Pinjaman';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('peng_pinjaman_id')
                    ->label('Pinjaman (Karyawan)')
                    ->options(
                        PengPinjaman::with('karyawan')
                            ->get()
                            ->mapWithKeys(fn ($item) => [
                                $item->id =>
                                $item->nomor_bukti .
                                ' - ' .
                                $item->karyawan?->nama
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {

                        $pinjaman = PengPinjaman::find($state);

                        if ($pinjaman) {
                            $set('karyawan_id', $pinjaman->karyawan_id);
                        }
                    }),

                Forms\Components\Hidden::make('karyawan_id'),

                Forms\Components\TextInput::make('tahap_cicilan')
                    ->label('Tahap Cicilan')
                    ->numeric()
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->required(),

                Forms\Components\TextInput::make('nominal_pembayaran')
                    ->label('Nominal Pembayaran')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->rows(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('no_bukti')
                    ->label('No Bukti')
                    ->searchable(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tgl Pembayaran')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('nominal_pembayaran')
                    ->label('Nominal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('tahap_cicilan')
                    ->label('Tahap Cicilan')
                    ->badge(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(30),

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
        return [];
    }

    public static function getPages(): array
{
    return [
        'index' => Pages\ListPembPinjamen::route('/'),
        'create' => Pages\CreatePembPinjaman::route('/create'),
        'edit' => Pages\EditPembPinjaman::route('/{record}/edit'),

        'rekap' => Pages\RekapPinjaman::route('/rekap'),
    ];
}
}