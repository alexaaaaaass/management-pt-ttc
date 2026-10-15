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
use Filament\Forms\Components\Wizard;

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
        Wizard::make([

            // ==========================================
            // 🔥 TAHAP 1: DATA PRIBADI & KONTAK
            // ==========================================
            Wizard\Step::make('Data Pribadi & Identitas')
                ->icon('heroicon-o-user')
                ->description('Nama lengkap, NIP, tempat/tanggal lahir, dan kontak pribadi')
                ->schema([
                    TextInput::make('nama')
                        ->label('Nama Lengkap')
                        ->required(),

                    TextInput::make('nip')
                        ->label('NIP')
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('nik')
                        ->label('NIK (No KTP)'),

                    TextInput::make('no_ktp')
                        ->label('No Identitas Lain'),

                    TextInput::make('tempat_lahir')
                        ->label('Tempat Lahir'),

                    DatePicker::make('tanggal_lahir')
                        ->label('Tanggal Lahir'),

                    Select::make('jenis_kelamin')
                        ->label('Jenis Kelamin')
                        ->options([
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                        ]),

                    TextInput::make('agama')
                        ->label('Agama'),

                    TextInput::make('no_telp')
                        ->label('Nomor Telepon / WhatsApp')
                        ->tel(),
                ])
                ->columns(2),

            // ==========================================
            // 🔥 TAHAP 2: PEKERJAAN & STATUS
            // ==========================================
            Wizard\Step::make('Pekerjaan & Jabatan')
                ->icon('heroicon-o-briefcase')
                ->description('Departemen, posisi/jabatan, kantor, masa kontrak, dan status')
                ->schema([
                    Select::make('departemen_id')
                        ->label('Departemen')
                        ->relationship('departemen', 'nama_departemen')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('jabatan')
                        ->label('Jabatan')
                        ->required(),

                    TextInput::make('kantor')
                        ->label('Lokasi Kantor / Cabang'),

                    Select::make('status_pegawai')
                        ->label('Status Pegawai')
                        ->options([
                            'Aktif' => 'Aktif',
                            'Nonaktif' => 'Nonaktif',
                        ])
                        ->default('Aktif'),

                    DatePicker::make('tanggal_masuk_kerja')
                        ->label('Tanggal Masuk Kerja'),

                    DatePicker::make('tangal_akhir_kontrak')
                        ->label('Tanggal Akhir Kontrak'),
                ])
                ->columns(2),

            // ==========================================
            // 🔥 TAHAP 3: REMUNERASI & GAJI
            // ==========================================
            Wizard\Step::make('Remunerasi & Gaji')
                ->icon('heroicon-o-banknotes')
                ->description('Gaji pokok, skema pembayaran, dan tunjangan pegawai')
                ->schema([
                    TextInput::make('gaji_pokok')
                        ->label('Gaji Pokok')
                        ->numeric()
                        ->prefix('Rp'),

                    Select::make('tipe_gaji')
                        ->label('Tipe / Skema Gaji')
                        ->options([
                            'Bulanan' => 'Bulanan',
                            'Harian' => 'Harian'
                        ])
                        ->default('Bulanan'),

                    TextInput::make('tunjangan_kompetensi')
                        ->label('Tunjangan Kompetensi')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('tunjangan_jabatan')
                        ->label('Tunjangan Jabatan')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('tunjangan_intensif')
                        ->label('Tunjangan Intensif')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),
                ])
                ->columns(2),

            // ==========================================
            // 🔥 TAHAP 4: PAJAK, BANK & BPJS
            // ==========================================
            Wizard\Step::make('Pajak, Bank & BPJS')
                ->icon('heroicon-o-shield-check')
                ->description('Data NPWP, rekening payroll bank, dan kepesertaan BPJS')
                ->schema([
                    Section::make('Data NPWP & Perpajakan')
                        ->schema([
                            TextInput::make('nama_npwp')
                                ->label('Nama di NPWP'),

                            TextInput::make('nomor_npwp')
                                ->label('Nomor NPWP'),

                            TextInput::make('alamat_npwp')
                                ->label('Alamat NPWP'),

                            DatePicker::make('tanggal_npwp')
                                ->label('Tanggal NPWP'),

                            TextInput::make('PTKP')
                                ->label('Status PTKP (misal: TK/0, K/1)'),
                        ])
                        ->columns(2),

                    Section::make('Rekening Bank (Payroll)')
                        ->schema([
                            TextInput::make('nama_bank')
                                ->label('Nama Bank (misal: BCA, Mandiri)'),

                            TextInput::make('rekening_atas_nama')
                                ->label('Atas Nama Rekening'),

                            TextInput::make('no_rekening')
                                ->label('Nomor Rekening'),
                        ])
                        ->columns(3),

                    Section::make('Jaminan Sosial & Kesehatan (BPJS)')
                        ->schema([
                            TextInput::make('nama_bpjs')
                                ->label('Nama Terdaftar BPJS'),

                            TextInput::make('bpjs_kesehatan')
                                ->label('No BPJS Kesehatan'),

                            TextInput::make('bpjs_ketenagakerjaan')
                                ->label('No BPJS Ketenagakerjaan'),
                        ])
                        ->columns(3),
                ]),
        ])
        ->skippable()
        ->columnSpanFull(),
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