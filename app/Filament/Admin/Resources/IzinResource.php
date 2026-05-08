<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IzinResource\Pages;
use App\Models\Izin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class IzinResource extends Resource
{
    protected static ?string $model = Izin::class;
     protected static ?string $navigationGroup = 'HRD';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Form Izin')
            ->schema([

                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->afterStateHydrated(function ($state, callable $set, $record) {
    if (!$record) return;

    $karyawan = $record->karyawan;

    if (!$karyawan) return;

    $set('nip', $karyawan->nip);
    $set('jabatan', $karyawan->jabatan);
    $set('departemen', $karyawan->departemen?->nama_departemen);
    $set('kantor', $karyawan->kantor);
})
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {

                        $karyawan = \App\Models\Karyawan::with('departemen')->find($state);

                        if (!$karyawan) return;

                        $set('nip', $karyawan->nip);
                        $set('jabatan', $karyawan->jabatan);
                        $set('departemen', $karyawan->departemen?->nama_departemen);
                        $set('kantor', $karyawan->kantor);
                    }),

                Forms\Components\TextInput::make('nip')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('jabatan')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('departemen')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('kantor')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\DatePicker::make('tanggal_izin')
                    ->label('Tanggal Izin')
                    ->required(),

                Forms\Components\Select::make('jenis_izin')
                    ->options([
                        'terlambat' => 'Izin Datang Terlambat',
                        'meninggalkan_kantor' => 'Izin Meninggalkan Kantor',
                        'pulang_awal' => 'Izin Pulang Awal',
                        'dinas_luar' => 'Dinas Luar',
                        'alpha' => 'Alpha',
                    ])
                    ->required(),

                Forms\Components\TimePicker::make('jam_mulai')
                    ->label('Jam Mulai'),

                Forms\Components\TimePicker::make('jam_selesai')
                    ->label('Jam Selesai'),

                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),

            ])
            ->columns(2),
    ]);
}

public static function getNavigationLabel(): string
{
    return 'Izin';
}
    

   public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('karyawan.nama')
                ->label('Karyawan')
                ->searchable(),

            Tables\Columns\TextColumn::make('karyawan.nip')
                ->label('NIP'),

            Tables\Columns\TextColumn::make('tanggal_izin')
                ->date()
                ->label('Tanggal'),

            Tables\Columns\BadgeColumn::make('jenis_izin')
                ->colors([
                    'warning' => 'terlambat',
                    'danger' => 'alpha',
                    'info' => 'dinas_luar',
                    'success' => 'pulang_awal',
                ])
                ->formatStateUsing(fn ($state) => match ($state) {
                    'terlambat' => 'Terlambat',
                    'meninggalkan_kantor' => 'Meninggalkan Kantor',
                    'pulang_awal' => 'Pulang Awal',
                    'dinas_luar' => 'Dinas Luar',
                    'alpha' => 'Alpha',
                }),

            Tables\Columns\TextColumn::make('jam_mulai'),
            Tables\Columns\TextColumn::make('jam_selesai'),

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIzins::route('/'),
            'create' => Pages\CreateIzin::route('/create'),
            'edit' => Pages\EditIzin::route('/{record}/edit'),
        ];
    }
}