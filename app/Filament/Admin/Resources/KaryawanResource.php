<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KaryawanResource\Pages;
use App\Filament\Admin\Resources\KaryawanResource\RelationManagers;
use App\Models\HrPayrollSetting;
use App\Models\HrShift;
use App\Models\Karyawan;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KaryawanResource extends Resource
{
    protected static ?string $navigationGroup = 'HRD';
    protected static ?string $navigationLabel = 'Karyawan & Master';
    protected static ?int $navigationSort = 2;
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Wizard\Step::make('Data Pribadi & Identitas')
                    ->icon('heroicon-o-user')
                    ->description('Nama lengkap, NIP, PIN Fingerprint, dan kontak pribadi')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        TextInput::make('pin_finger')
                            ->label('PIN Mesin Fingerprint')
                            ->helperText('Dipakai untuk pencocokan otomatis saat import absensi')
                            ->maxLength(50),

                        TextInput::make('nik')
                            ->label('NIK (No KTP)')
                            ->maxLength(30),

                        TextInput::make('no_ktp')
                            ->label('No Identitas Lain')
                            ->maxLength(30),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(100),

                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),

                        Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ]),

                        TextInput::make('agama')
                            ->label('Agama')
                            ->maxLength(30),

                        TextInput::make('no_telp')
                            ->label('Nomor Telepon / WhatsApp')
                            ->tel()
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Wizard\Step::make('Pekerjaan & Status')
                    ->icon('heroicon-o-briefcase')
                    ->description('Departemen, jabatan, lokasi kantor, masa kontrak, dan status aktif')
                    ->schema([
                        Select::make('departemen_id')
                            ->label('Departemen')
                            ->relationship('departemen', 'nama_departemen')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('shift_id')
                            ->label('Shift Kerja')
                            ->relationship('shift', 'nama')
                            ->searchable()
                            ->preload()
                            ->default(fn () => HrShift::where('kode', 'NON-SHIFT')->value('id'))
                            ->helperText('Acuan jam masuk/pulang & toleransi telat mesin fingerprint'),

                        TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kantor')
                            ->label('Lokasi Kantor / Cabang')
                            ->default('Singosari - Malang')
                            ->maxLength(255),

                        Select::make('status_pegawai')
                            ->label('Status Pegawai')
                            ->options([
                                'Aktif'    => 'Aktif',
                                'Nonaktif' => 'Nonaktif',
                            ])
                            ->default('Aktif')
                            ->required(),

                        DatePicker::make('tanggal_masuk_kerja')
                            ->label('Tanggal Masuk Kerja')
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state && blank($get('tanggal_mulai_kontrak'))) {
                                    $set('tanggal_mulai_kontrak', $state);
                                }
                                $masa = $get('masa_kontrak');
                                $start = $get('tanggal_mulai_kontrak') ?: $state;
                                if ($masa && ! in_array($masa, ['MANUAL', 'TETAP'], true) && $start) {
                                    $set('tangal_akhir_kontrak', Karyawan::calculateContractEndDate($start, $masa));
                                }
                            }),

                        Select::make('masa_kontrak')
                            ->label('Masa / Periode Kontrak')
                            ->options(Karyawan::MASA_KONTRAK_OPTIONS)
                            ->placeholder('Pilih: 1 Bulan / 3 Bulan / 1 Tahun / Manual / Tetap')
                            ->helperText('Otomatis menghitung Tanggal Akhir Kontrak (tetap bisa diedit manual kapan saja).')
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state === 'TETAP') {
                                    $set('tangal_akhir_kontrak', null);
                                    return;
                                }
                                if ($state === 'MANUAL' || ! $state) {
                                    return;
                                }
                                $start = $get('tanggal_mulai_kontrak') ?: $get('tanggal_masuk_kerja') ?: now()->toDateString();
                                if (blank($get('tanggal_mulai_kontrak'))) {
                                    $set('tanggal_mulai_kontrak', $start);
                                }
                                $set('tangal_akhir_kontrak', Karyawan::calculateContractEndDate($start, $state));
                            }),

                        DatePicker::make('tanggal_mulai_kontrak')
                            ->label('Tanggal Mulai Kontrak Berjalan')
                            ->helperText('Dipakai sebagai tanggal awal periode kontrak (1 bln / 3 bln / 1 thn).')
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $masa = $get('masa_kontrak');
                                if ($state && $masa && ! in_array($masa, ['MANUAL', 'TETAP'], true)) {
                                    $set('tangal_akhir_kontrak', Karyawan::calculateContractEndDate($state, $masa));
                                }
                            }),

                        DatePicker::make('tangal_akhir_kontrak')
                            ->label('Tanggal Akhir Kontrak (Bisa Input Manual)')
                            ->helperText('Terisi otomatis dari Masa Kontrak atau isi bebas secara manual (kosongkan jika Karyawan Tetap).'),
                    ])
                    ->columns(2),

                Wizard\Step::make('Remunerasi & Gaji')
                    ->icon('heroicon-o-banknotes')
                    ->description('Skema penggajian (Bulanan / Harian / Freelance / Borongan), gaji pokok, dan tunjangan')
                    ->schema([
                        Select::make('tipe_gaji')
                            ->label('Tipe / Skema Gaji')
                            ->options(Karyawan::TIPE_GAJI_OPTIONS)
                            ->default('Bulanan')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('is_borongan', $state === 'Borongan')),

                        Toggle::make('is_borongan')
                            ->label('Pekerja Borongan / Part-Time')
                            ->helperText('Aktifkan jika ikut dimuat di modul Payroll Borongan')
                            ->default(false),

                        TextInput::make('gaji_pokok')
                            ->label(fn (callable $get) => in_array($get('tipe_gaji'), ['Harian', 'Freelance'], true)
                                ? 'Tarif Harian / Upah per Hari'
                                : 'Gaji Pokok (Bulanan)')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('tarif_lembur_custom')
                            ->label('Tarif Lembur Khusus / Jam (Opsional)')
                            ->helperText('Kosongkan untuk memakai aturan di Pengaturan Payroll')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->nullable(),

                        TextInput::make('tunjangan_jabatan')
                            ->label('Tunjangan Jabatan')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),

                        TextInput::make('tunjangan_kompetensi')
                            ->label('Tunjangan Kompetensi')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),

                        TextInput::make('tunjangan_intensif')
                            ->label('Tunjangan Intensif')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),

                        TextInput::make('tunjangan_makan')
                            ->label('Tunjangan Makan')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),

                        TextInput::make('tunjangan_hadir')
                            ->label('Tunjangan Kehadiran')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),

                        TextInput::make('tunjangan_lain')
                            ->label('Tunjangan Lain-lain')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->default(0),
                    ])
                    ->columns(2),

                Wizard\Step::make('Pajak, Bank & BPJS')
                    ->icon('heroicon-o-shield-check')
                    ->description('Opsional — data NPWP, rekening bank payroll, dan kepesertaan BPJS')
                    ->schema([
                        Section::make('Data NPWP & Perpajakan (Opsional)')
                            ->schema([
                                TextInput::make('nama_npwp')->label('Nama di NPWP')->maxLength(100),
                                TextInput::make('nomor_npwp')->label('Nomor NPWP')->maxLength(50),
                                TextInput::make('alamat_npwp')->label('Alamat NPWP')->maxLength(255),
                                DatePicker::make('tanggal_npwp')->label('Tanggal NPWP'),
                                TextInput::make('PTKP')->label('Status PTKP (mis. TK/0, K/1)')->maxLength(50),
                            ])
                            ->columns(2),

                        Section::make('Rekening Bank Payroll (Opsional)')
                            ->schema([
                                TextInput::make('nama_bank')->label('Nama Bank (mis. BRI, BCA)')->maxLength(50),
                                TextInput::make('rekening_atas_nama')->label('Atas Nama Rekening')->maxLength(100),
                                TextInput::make('no_rekening')->label('Nomor Rekening')->maxLength(50),
                            ])
                            ->columns(3),

                        Section::make('BPJS Kesehatan & Ketenagakerjaan (Opsional)')
                            ->schema([
                                TextInput::make('nama_bpjs')->label('Nama Terdaftar BPJS')->maxLength(100),
                                TextInput::make('bpjs_kesehatan')->label('No BPJS Kesehatan')->maxLength(50),
                                TextInput::make('bpjs_ketenagakerjaan')->label('No BPJS Ketenagakerjaan')->maxLength(50),
                                TextInput::make('bpjs_kesehatan_nominal')
                                    ->label('Potongan BPJS Kes / Bulan')
                                    ->prefix('Rp')
                                    ->currencyMask(0)
                                    ->default(0),
                                TextInput::make('bpjs_ketenagakerjaan_nominal')
                                    ->label('Potongan BPJS TK / Bulan')
                                    ->prefix('Rp')
                                    ->currencyMask(0)
                                    ->default(0),
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
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Karyawan $r) => 'NIP: ' . ($r->nip ?: '-') . ($r->pin_finger ? " · PIN: {$r->pin_finger}" : '')),

                TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('jabatan')
                    ->searchable(),

                TextColumn::make('shift.nama')
                    ->label('Shift')
                    ->badge()
                    ->color('info')
                    ->placeholder('Default')
                    ->toggleable(),

                TextColumn::make('tipe_gaji')
                    ->label('Skema Gaji')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Bulanan'   => 'primary',
                        'Harian'    => 'info',
                        'Freelance' => 'warning',
                        'Borongan'  => 'success',
                        default     => 'gray',
                    }),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji / Tarif')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('tangal_akhir_kontrak')
                    ->label('Kontrak')
                    ->badge()
                    ->formatStateUsing(function ($state, Karyawan $r) {
                        if ($r->masa_kontrak === 'TETAP' || ! $state) {
                            return $r->masa_kontrak === 'TETAP' ? 'Tetap (PKWTT)' : (Karyawan::MASA_KONTRAK_SHORT[$r->masa_kontrak] ?? 'Tetap / -');
                        }
                        $d = Carbon::parse($state);
                        $diff = now()->startOfDay()->diffInDays($d->copy()->startOfDay(), false);
                        $alertDays = (int) (HrPayrollSetting::current()->alert_kontrak_hari ?: 30);
                        if ($diff < 0) {
                            return $d->format('d/m/Y') . ' (Expired)';
                        }
                        if ($diff <= $alertDays) {
                            return $d->format('d/m/Y') . " ({$diff}h lagi)";
                        }

                        return $d->format('d/m/Y');
                    })
                    ->description(fn (Karyawan $r) => $r->masa_kontrak ? (Karyawan::MASA_KONTRAK_SHORT[$r->masa_kontrak] ?? $r->masa_kontrak) : null)
                    ->color(function ($state, Karyawan $r) {
                        if ($r->masa_kontrak === 'TETAP' || ! $state) {
                            return 'gray';
                        }
                        $diff = now()->startOfDay()->diffInDays(Carbon::parse($state)->startOfDay(), false);
                        $alertDays = (int) (HrPayrollSetting::current()->alert_kontrak_hari ?: 30);
                        if ($diff < 0) {
                            return 'danger';
                        }
                        if ($diff <= $alertDays) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                TextColumn::make('status_pegawai')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => $state === 'Aktif' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departemen_id')
                    ->label('Departemen')
                    ->relationship('departemen', 'nama_departemen'),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift Kerja')
                    ->relationship('shift', 'nama'),
                Tables\Filters\SelectFilter::make('masa_kontrak')
                    ->label('Masa Kontrak')
                    ->options(Karyawan::MASA_KONTRAK_OPTIONS),
                Tables\Filters\SelectFilter::make('tipe_gaji')
                    ->label('Skema Gaji')
                    ->options(Karyawan::TIPE_GAJI_OPTIONS),
                Tables\Filters\SelectFilter::make('status_pegawai')
                    ->label('Status Pegawai')
                    ->options(['Aktif' => 'Aktif', 'Nonaktif' => 'Nonaktif']),
            ])
            ->actions([
                Tables\Actions\Action::make('aturKontrak')
                    ->label('Kontrak')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->modalHeading(fn (Karyawan $r) => "Atur / Perpanjang Kontrak — {$r->nama}")
                    ->modalDescription('Pilih masa kontrak (1 Bulan / 3 Bulan / 6 Bulan / 1 Tahun / 2 Tahun / Manual / Tetap). Tanggal akhir dihitung otomatis dan tetap bisa diubah manual.')
                    ->form([
                        Select::make('masa_kontrak')
                            ->label('Masa / Periode Kontrak')
                            ->options(Karyawan::MASA_KONTRAK_OPTIONS)
                            ->default(fn (Karyawan $r) => $r->masa_kontrak ?: '1_TAHUN')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state === 'TETAP') {
                                    $set('tangal_akhir_kontrak', null);
                                    return;
                                }
                                if ($state === 'MANUAL' || ! $state) {
                                    return;
                                }
                                $start = $get('tanggal_mulai_kontrak') ?: now()->toDateString();
                                $set('tangal_akhir_kontrak', Karyawan::calculateContractEndDate($start, $state));
                            }),
                        DatePicker::make('tanggal_mulai_kontrak')
                            ->label('Tanggal Mulai Kontrak')
                            ->default(fn (Karyawan $r) => $r->tangal_akhir_kontrak ?: $r->tanggal_mulai_kontrak ?: $r->tanggal_masuk_kerja ?: now())
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $masa = $get('masa_kontrak');
                                if ($state && $masa && ! in_array($masa, ['MANUAL', 'TETAP'], true)) {
                                    $set('tangal_akhir_kontrak', Karyawan::calculateContractEndDate($state, $masa));
                                }
                            }),
                        DatePicker::make('tangal_akhir_kontrak')
                            ->label('Tanggal Akhir Kontrak (Bisa Diubah Manual)')
                            ->default(fn (Karyawan $r) => $r->tangal_akhir_kontrak ?: Karyawan::calculateContractEndDate(now()->toDateString(), $r->masa_kontrak ?: '1_TAHUN')),
                    ])
                    ->action(function (Karyawan $record, array $data) {
                        $record->update([
                            'masa_kontrak'          => $data['masa_kontrak'],
                            'tanggal_mulai_kontrak' => $data['tanggal_mulai_kontrak'] ?? null,
                            'tangal_akhir_kontrak'  => $data['masa_kontrak'] === 'TETAP' ? null : ($data['tangal_akhir_kontrak'] ?? null),
                        ]);
                        Notification::make()
                            ->title("Kontrak {$record->nama} diperbarui")
                            ->body('Periode: ' . (Karyawan::MASA_KONTRAK_SHORT[$data['masa_kontrak']] ?? $data['masa_kontrak']) . ($record->tangal_akhir_kontrak ? ' · Berakhir ' . $record->tangal_akhir_kontrak->format('d/m/Y') : ''))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DokumensRelationManager::class,
            RelationManagers\DisiplinsRelationManager::class,
            RelationManagers\GajisRelationManager::class,
            RelationManagers\PinjamansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit'   => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}
