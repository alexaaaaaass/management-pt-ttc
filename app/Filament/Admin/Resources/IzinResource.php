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
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Izin, Sakit & Scorsing';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Form Izin / Sakit / Alpha / Scorsing')
                ->schema([
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateHydrated(function ($state, callable $set, $record) {
                            if (! $record || ! $record->karyawan) {
                                return;
                            }
                            $k = $record->karyawan;
                            $set('nip', $k->nip);
                            $set('jabatan', $k->jabatan);
                            $set('departemen', $k->departemen?->nama_departemen);
                            $set('kantor', $k->kantor);
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            $k = \App\Models\Karyawan::with('departemen')->find($state);
                            if (! $k) {
                                return;
                            }
                            $set('nip', $k->nip);
                            $set('jabatan', $k->jabatan);
                            $set('departemen', $k->departemen?->nama_departemen);
                            $set('kantor', $k->kantor);
                        }),

                    Forms\Components\TextInput::make('nip')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('jabatan')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('departemen')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('kantor')->disabled()->dehydrated(false),

                    Forms\Components\DatePicker::make('tanggal_izin')
                        ->label('Tanggal')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('jenis_izin')
                        ->label('Jenis Izin / Ketidakhadiran')
                        ->options(Izin::JENIS_OPTIONS)
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Status Approval')
                        ->options([
                            'Pending'  => 'Pending',
                            'Approved' => 'Approved',
                            'Rejected' => 'Rejected',
                        ])
                        ->default('Pending')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TimePicker::make('jam_mulai')
                        ->label('Jam Mulai (Opsional)')
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->format('H:i:s'),

                    Forms\Components\TimePicker::make('jam_selesai')
                        ->label('Jam Selesai (Opsional)')
                        ->seconds(false)
                        ->displayFormat('H:i')
                        ->format('H:i:s'),

                    Forms\Components\FileUpload::make('lampiran')
                        ->label('Lampiran Surat Dokter / Bukti (PDF / JPG / PNG)')
                        ->directory('izin')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->openable()
                        ->downloadable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('karyawan.nip')->label('NIP'),

                Tables\Columns\TextColumn::make('tanggal_izin')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_izin')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'terlambat'           => 'Terlambat',
                        'meninggalkan_kantor' => 'Meninggalkan Kantor',
                        'pulang_awal'         => 'Pulang Awal',
                        'dinas_luar'          => 'Dinas Luar',
                        'sakit'               => 'Sakit',
                        'sakit_surat_dokter'  => 'Sakit (S. Dokter)',
                        'alpha'               => 'Alpha',
                        'scorsing'            => 'Scorsing (Potong Gaji)',
                        default               => $state ?: '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'terlambat'           => 'warning',
                        'alpha', 'scorsing'   => 'danger',
                        'dinas_luar'          => 'info',
                        'pulang_awal'         => 'warning',
                        'sakit', 'sakit_surat_dokter' => 'primary',
                        default               => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default    => 'warning',
                    }),

                Tables\Columns\TextColumn::make('keterangan')->limit(30),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_izin')->options(Izin::JENIS_OPTIONS),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']),
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
            'index'  => Pages\ListIzins::route('/'),
            'create' => Pages\CreateIzin::route('/create'),
            'edit'   => Pages\EditIzin::route('/{record}/edit'),
        ];
    }
}
