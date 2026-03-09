<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Section;

class KaryawanResource extends Resource
{
   protected static ?string $navigationGroup = 'HRD';
   protected static ?string $navigationLabel = 'Karyawan';
   protected static ?int $navigationSort = 2;
   protected static ?string $model = Karyawan::class;
   protected static ?string $navigationIcon = 'heroicon-o-users'; // icon

  public static function form(Form $form): Form
{
    return $form->schema([

        Section::make('Data Pribadi')
            ->schema([
                TextInput::make('nama')->required(),

                TextInput::make('nip')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('tempat_lahir'),

                DatePicker::make('tanggal_lahir'),

                Select::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),

                TextInput::make('agama'),

                TextInput::make('no_telp')->tel(),

                TextInput::make('nik'),

                TextInput::make('no_ktp'),
            ])
            ->columns(2),

        Section::make('Data Pekerjaan')
            ->schema([
                Select::make('departemen_id')
                    ->label('Departemen')
                    ->relationship('departemen', 'nama_departemen')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('jabatan')->required(),

                TextInput::make('kantor'),

                DatePicker::make('tanggal_masuk_kerja'),

                DatePicker::make('tangal_akhir_kontrak'),

                Select::make('status_pegawai')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Nonaktif' => 'Nonaktif',
                    ])
                    ->default('Aktif'),
            ])
            ->columns(2),

        Section::make('Data Gaji')
            ->schema([
                TextInput::make('gaji_pokok')
                    ->numeric(),

                Select::make('tipe_gaji')
                    ->options([
                        'Bulanan' => 'Bulanan',
                        'Harian' => 'Harian'
                    ]),

                TextInput::make('tunjangan_kompetensi')
                    ->numeric(),

                TextInput::make('tunjangan_jabatan')
                    ->numeric(),

                TextInput::make('tunjangan_intensif')
                    ->numeric(),
            ])
            ->columns(2),

        Section::make('Data NPWP')
            ->schema([
                TextInput::make('nama_npwp'),

                TextInput::make('nomor_npwp'),

                TextInput::make('alamat_npwp'),

                DatePicker::make('tanggal_npwp'),

                TextInput::make('PTKP'),
            ])
            ->columns(2),

        Section::make('Data Bank')
            ->schema([
                TextInput::make('nama_bank'),

                TextInput::make('rekening_atas_nama'),

                TextInput::make('no_rekening'),
            ])
            ->columns(2),

        Section::make('Data BPJS')
            ->schema([
                TextInput::make('nama_bpjs'),

                TextInput::make('bpjs_kesehatan'),

                TextInput::make('bpjs_ketenagakerjaan'),
            ])
            ->columns(2),

    ]);
}
   public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('nama')
                ->searchable()
                ->sortable(),

            TextColumn::make('nip')
                ->searchable(),

            TextColumn::make('departemen.nama_departemen')
                ->label('Departemen')
                ->sortable(),

            TextColumn::make('jabatan'),

            TextColumn::make('gaji_pokok')
                ->money('IDR'),

            BadgeColumn::make('status_pegawai')
                ->colors([
                    'success' => 'Aktif',
                    'danger' => 'Nonaktif',
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}