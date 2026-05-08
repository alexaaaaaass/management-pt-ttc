<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AbsensiResource\Pages;
use App\Models\Absensi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class AbsensiResource extends Resource
{
    protected static ?string $model = Absensi::class;
     protected static ?string $navigationGroup = 'HRD';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Data Karyawan')
            ->schema([

                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {

                        $karyawan = \App\Models\Karyawan::with('departemen')->find($state);

                        if (!$karyawan) return;

                        $set('nip', $karyawan->nip);
                        $set('pin', $karyawan->nip); // sementara pakai nip (kalau belum ada pin)

                        $set('jabatan', $karyawan->jabatan);
                        $set('departemen', $karyawan->departemen?->nama_departemen);
                        $set('kantor', $karyawan->kantor);
                    })
                    ->required(),

                Forms\Components\TextInput::make('nip')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('pin')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('jabatan')
                    ->disabled()
                    ->dehydrated(),

              Forms\Components\TextInput::make('departemen')
    ->disabled()
    ->dehydrated()
    ->afterStateHydrated(function ($state, callable $set, $record) {
        if (!$record) return;

        $karyawan = $record->karyawan;
        if (!$karyawan) return;

        $set('departemen', $karyawan->departemen?->nama_departemen);
    }),

                Forms\Components\TextInput::make('kantor')
                    ->disabled()
                    ->dehydrated(),

            ])->columns(2),

        Forms\Components\Section::make('Data Absensi')
            ->schema([

                Forms\Components\DateTimePicker::make('tanggal_scan')
                    ->label('Tanggal Scan')
                    ->required(),

                Forms\Components\TextInput::make('sn')
                    ->label('SN'),

                Forms\Components\TextInput::make('mesin')
                    ->label('Mesin'),

                Forms\Components\TextInput::make('workcode')
                    ->label('Workcode'),

                Forms\Components\Select::make('io')
                    ->label('IO')
                    ->options([
                        'Masuk' => 'Masuk',
                        'Keluar' => 'Keluar',
                    ])
                    ->required(),

            ])->columns(2),

    ]);
}

    public static function getNavigationLabel(): string
{
    return 'Absensi';
}

  public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('karyawan.nama')
                ->label('Nama'),

            Tables\Columns\TextColumn::make('nip'),

            Tables\Columns\TextColumn::make('jabatan'),

            Tables\Columns\TextColumn::make('departemen'),

            Tables\Columns\TextColumn::make('tanggal_scan')
                ->dateTime(),

            Tables\Columns\BadgeColumn::make('io')
                ->colors([
                    'success' => 'Masuk',
                    'danger' => 'Keluar',
                ]),

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
            'index' => Pages\ListAbsensis::route('/'),
            'create' => Pages\CreateAbsensi::route('/create'),
            'edit' => Pages\EditAbsensi::route('/{record}/edit'),
        ];
    }
}