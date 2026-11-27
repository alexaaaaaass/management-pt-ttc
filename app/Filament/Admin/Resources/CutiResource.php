<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CutiResource\Pages;
use App\Models\Cuti;
use App\Services\HR\AttendanceService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CutiResource extends Resource
{
    protected static ?string $model = Cuti::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Cuti';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pengajuan Cuti Karyawan')
                ->description('Jumlah hari otomatis mengecualikan hari Minggu dan Hari Libur Nasional yang terdaftar.')
                ->schema([
                    Forms\Components\Select::make('karyawan_id')
                        ->label('Karyawan')
                        ->relationship('karyawan', 'nama')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('jenis_cuti')
                        ->label('Jenis Cuti')
                        ->options([
                            'Cuti Tahunan'    => 'Cuti Tahunan',
                            'Cuti Menikah'    => 'Cuti Menikah',
                            'Cuti Melahirkan' => 'Cuti Melahirkan',
                            'Cuti Sakit'      => 'Cuti Sakit',
                            'Cuti Khusus'     => 'Cuti Khusus',
                        ])
                        ->default('Cuti Tahunan')
                        ->live()
                        ->searchable()
                        ->required(),

                    Forms\Components\Placeholder::make('info_kuota')
                        ->label('Info Kuota Cuti Tahunan')
                        ->visible(fn (callable $get) => $get('jenis_cuti') === 'Cuti Tahunan' && filled($get('karyawan_id')))
                        ->content(function (callable $get, $record) {
                            $kid = (int) $get('karyawan_id');
                            $year = $get('tanggal_mulai') ? Carbon::parse($get('tanggal_mulai'))->year : now()->year;
                            $q = app(AttendanceService::class)->getRemainingAnnualLeave($kid, $year, $record?->id);

                            return "Tahun {$year}: Kuota {$q['kuota']} hari · Terpakai {$q['digunakan']} hari · Sisa {$q['sisa']} hari";
                        })
                        ->columnSpanFull(),

                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::hitungHariCuti($get, $set)),

                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->default(now())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::hitungHariCuti($get, $set)),

                    Forms\Components\TextInput::make('jumlah_hari')
                        ->label('Jumlah Hari Kerja Efektif')
                        ->helperText('Otomatis tidak menghitung hari Minggu & Hari Libur.')
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->minValue(1)
                        ->rules([
                            fn (callable $get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                if ($get('jenis_cuti') !== 'Cuti Tahunan' || empty($get('karyawan_id'))) {
                                    return;
                                }
                                $year = $get('tanggal_mulai') ? Carbon::parse($get('tanggal_mulai'))->year : now()->year;
                                $q = app(AttendanceService::class)->getRemainingAnnualLeave((int) $get('karyawan_id'), $year, $record?->id);
                                if ((int) $value > $q['sisa']) {
                                    $fail("Sisa kuota Cuti Tahunan {$year} karyawan ini tinggal {$q['sisa']} hari (pengajuan: {$value} hari).");
                                }
                            },
                        ])
                        ->default(1),

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

                    Forms\Components\FileUpload::make('lampiran')
                        ->label('Lampiran (Surat Dokter / Pendukung — PDF/JPG/PNG)')
                        ->directory('cuti')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan Cuti')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function hitungHariCuti(callable $get, callable $set): void
    {
        $mulai = $get('tanggal_mulai');
        $selesai = $get('tanggal_selesai');

        if (! $mulai || ! $selesai) {
            return;
        }

        $startDate = Carbon::parse($mulai);
        $endDate = Carbon::parse($selesai);

        if ($endDate->lessThan($startDate)) {
            $set('jumlah_hari', 0);
            return;
        }

        $days = app(AttendanceService::class)->countEffectiveWorkDays($startDate, $endDate);
        $set('jumlah_hari', $days);
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

                Tables\Columns\TextColumn::make('jenis_cuti')
                    ->label('Jenis Cuti')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Cuti Tahunan'    => 'primary',
                        'Cuti Menikah'    => 'success',
                        'Cuti Melahirkan' => 'info',
                        'Cuti Sakit'      => 'danger',
                        'Cuti Khusus'     => 'warning',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tanggal_mulai')->label('Tgl Mulai')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')->label('Tgl Selesai')->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('jumlah_hari')
                    ->label('Hari')
                    ->suffix(' Hari')
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default    => 'warning',
                    }),

                Tables\Columns\TextColumn::make('keterangan')->label('Keterangan')->limit(35),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_cuti')
                    ->options([
                        'Cuti Tahunan'    => 'Cuti Tahunan',
                        'Cuti Menikah'    => 'Cuti Menikah',
                        'Cuti Melahirkan' => 'Cuti Melahirkan',
                        'Cuti Sakit'      => 'Cuti Sakit',
                        'Cuti Khusus'     => 'Cuti Khusus',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCutis::route('/'),
            'create' => Pages\CreateCuti::route('/create'),
            'edit'   => Pages\EditCuti::route('/{record}/edit'),
        ];
    }
}
