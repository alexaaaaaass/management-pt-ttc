<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LemburResource\Pages;
use App\Models\Lembur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class LemburResource extends Resource
{
    protected static ?string $model = Lembur::class;
     protected static ?string $navigationGroup = 'HRD';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Data Lembur')
            ->schema([

                Forms\Components\TextInput::make('kode_lembur')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('kode_gudang')
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_lembur')
                    ->required(),

                Forms\Components\TimePicker::make('jam_mulai')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) =>
                        self::hitungDurasi($get, $set)
                    ),

                Forms\Components\TimePicker::make('jam_selesai')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) =>
                        self::hitungDurasi($get, $set)
                    ),

                Forms\Components\TextInput::make('durasi_lembur')
                    ->label('Durasi (Jam)')
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                self::hitungDurasi($get, $set);
            })->rule('after:jam_mulai')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ])->columns(2),
    ]);
}

public static function getNavigationLabel(): string
{
    return 'Lembur';
}

public static function hitungDurasi($get, $set)
{
    $mulai = $get('jam_mulai');
    $selesai = $get('jam_selesai');

    if (!$mulai || !$selesai) return;

    $start = \Carbon\Carbon::parse($mulai);
    $end = \Carbon\Carbon::parse($selesai);

    // 🔥 handle lembur lewat tengah malam
    if ($end->lessThan($start)) {
        $end->addDay();
    }

    $durasi = $start->diffInMinutes($end) / 60;

    $set('durasi_lembur', round($durasi, 2));
}
    

 public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('kode_lembur')
                ->label('Kode'),

            Tables\Columns\TextColumn::make('karyawan.nama')
                ->label('Karyawan'),

            Tables\Columns\TextColumn::make('kode_gudang'),

            Tables\Columns\TextColumn::make('tanggal_lembur')
                ->date(),

            Tables\Columns\TextColumn::make('durasi_lembur')
                ->label('Durasi (Jam)'),

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
            'index' => Pages\ListLemburs::route('/'),
            'create' => Pages\CreateLembur::route('/create'),
            'edit' => Pages\EditLembur::route('/{record}/edit'),
        ];
    }
}