<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GajiResource\Pages;
use App\Models\Gaji;
use App\Models\MasterCOA;
use App\Services\HR\PayrollService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 13;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'Penggajian (Payroll)';
    }

    public static function getModelLabel(): string
    {
        return 'Slip Gaji';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penggajian (Payroll)';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Karyawan & Periode Gaji')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('karyawan_id')
                                    ->label('Karyawan')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (!$state) return;
                                        $periode = $get('periode') ?: now()->format('Y-m');
                                        $calc = app(PayrollService::class)->calculateEmployeePayroll((int) $state, $periode);

                                        $set('gaji_pokok', $calc['gaji_pokok']);
                                        $set('tunjangan_jabatan', $calc['tunjangan_jabatan']);
                                        $set('tunjangan_kompetensi', $calc['tunjangan_kompetensi']);
                                        $set('tunjangan_intensif', $calc['tunjangan_intensif']);
                                        $set('total_lembur', $calc['total_lembur']);
                                        $set('total_bonus', $calc['total_bonus']);
                                        $set('total_pendapatan', $calc['total_pendapatan']);

                                        $set('potongan_tunjangan', $calc['potongan_tunjangan']);
                                        $set('potongan_pinjaman', $calc['potongan_pinjaman']);
                                        $set('total_potongan', $calc['total_potongan']);
                                        $set('gaji_bersih', $calc['gaji_bersih']);
                                    }),

                                TextInput::make('periode')
                                    ->label('Periode Gaji (YYYY-MM)')
                                    ->default(now()->format('Y-m'))
                                    ->placeholder('2026-10')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $karyawanId = $get('karyawan_id');
                                        if (!$karyawanId || !$state) return;
                                        $calc = app(PayrollService::class)->calculateEmployeePayroll((int) $karyawanId, $state);

                                        $set('total_lembur', $calc['total_lembur']);
                                        $set('total_bonus', $calc['total_bonus']);
                                        $set('potongan_tunjangan', $calc['potongan_tunjangan']);
                                        $set('potongan_pinjaman', $calc['potongan_pinjaman']);
                                        self::recalculateTotals($get, $set);
                                    }),

                                TextInput::make('kode_slip')
                                    ->label('Kode Slip Gaji')
                                    ->disabled()
                                    ->default('AUTO'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('tanggal_pembayaran')
                                    ->label('Tanggal Pembayaran')
                                    ->default(now())
                                    ->required(),

                                Select::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'Transfer Bank' => 'Transfer Bank',
                                        'Tunai'         => 'Tunai (Kas)',
                                    ])
                                    ->default('Transfer Bank')
                                    ->required(),

                                Select::make('account_kas_id')
                                    ->label('Akun Kas / Bank Pembayar')
                                    ->relationship('accountKas', 'nama_akun')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Akun Kas/Bank jika dibayarkan'),
                            ]),

                        Select::make('status')
                            ->label('Status Penggajian')
                            ->options([
                                'Draft'    => 'Draft (Rancangan)',
                                'Approved' => 'Approved (Disetujui)',
                                'Paid'     => 'Paid (Sudah Dibayarkan)',
                            ])
                            ->default('Draft')
                            ->required(),
                    ]),

                Section::make('Komponen Pendapatan (Earnings)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('gaji_pokok')
                                    ->label('Gaji Pokok')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set))
                                    ->required(),

                                TextInput::make('tunjangan_jabatan')
                                    ->label('Tunjangan Jabatan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('tunjangan_kompetensi')
                                    ->label('Tunjangan Kompetensi')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('tunjangan_intensif')
                                    ->label('Tunjangan Intensif')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('total_lembur')
                                    ->label('Uang Lembur')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('total_bonus')
                                    ->label('Bonus / Insentif')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                            ]),

                        TextInput::make('total_pendapatan')
                            ->label('Total Pendapatan Kotor')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold text-success-600'])
                            ->default(0),
                    ]),

                Section::make('Komponen Potongan (Deductions)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('potongan_tunjangan')
                                    ->label('Potongan Tunjangan (Ketidakhadiran/Sanksi)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('potongan_kehadiran')
                                    ->label('Potongan Keterlambatan / Alpha')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('potongan_pinjaman')
                                    ->label('Cicilan Pinjaman Karyawan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),

                                TextInput::make('potongan_bpjs')
                                    ->label('Potongan BPJS / Asuransi')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                            ]),

                        TextInput::make('total_potongan')
                            ->label('Total Seluruh Potongan')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-bold text-danger-600'])
                            ->default(0),
                    ]),

                Section::make('Gaji Bersih Diterima (Take Home Pay)')
                    ->schema([
                        TextInput::make('gaji_bersih')
                            ->label('Gaji Bersih / Take Home Pay (THP)')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'text-xl font-extrabold text-primary-600'])
                            ->default(0),

                        Textarea::make('keterangan')
                            ->label('Catatan / Keterangan Slip Gaji')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function recalculateTotals(callable $get, callable $set): void
    {
        $gajiPokok = (float) ($get('gaji_pokok') ?? 0);
        $jabatan = (float) ($get('tunjangan_jabatan') ?? 0);
        $kompetensi = (float) ($get('tunjangan_kompetensi') ?? 0);
        $intensif = (float) ($get('tunjangan_intensif') ?? 0);
        $lembur = (float) ($get('total_lembur') ?? 0);
        $bonus = (float) ($get('total_bonus') ?? 0);

        $totPendapatan = round($gajiPokok + $jabatan + $kompetensi + $intensif + $lembur + $bonus, 2);

        $potTunjangan = (float) ($get('potongan_tunjangan') ?? 0);
        $potKehadiran = (float) ($get('potongan_kehadiran') ?? 0);
        $potPinjaman = (float) ($get('potongan_pinjaman') ?? 0);
        $potBpjs = (float) ($get('potongan_bpjs') ?? 0);

        $totPotongan = round($potTunjangan + $potKehadiran + $potPinjaman + $potBpjs, 2);
        $gajiBersih = max(0.0, round($totPendapatan - $totPotongan, 2));

        $set('total_pendapatan', $totPendapatan);
        $set('total_potongan', $totPotongan);
        $set('gaji_bersih', $gajiBersih);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_slip')
                    ->label('Kode Slip')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('karyawan.departemen.nama_departemen')
                    ->label('Departemen')
                    ->badge(),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge(),

                TextColumn::make('total_pendapatan')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_potongan')
                    ->label('Total Potongan')
                    ->money('IDR')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('gaji_bersih')
                    ->label('Take Home Pay')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Draft',
                        'info'    => 'Approved',
                        'success' => 'Paid',
                    ]),

                TextColumn::make('accountKas.nama_akun')
                    ->label('Kas/Bank Pembayar')
                    ->toggleable(),

                TextColumn::make('tanggal_pembayaran')
                    ->label('Tgl Bayar')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft'    => 'Draft',
                        'Approved' => 'Approved',
                        'Paid'     => 'Paid',
                    ]),

                Tables\Filters\SelectFilter::make('periode')
                    ->options(fn () => Gaji::select('periode')->distinct()->pluck('periode', 'periode')->toArray()),
            ])
            ->actions([
                // Quick Pay Action
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn ($record) => $record->status === 'Paid')
                    ->form([
                        Select::make('account_kas_id')
                            ->label('Pilih Akun Kas / Bank untuk Pembayaran')
                            ->options(MasterCOA::pluck('nama_akun', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'Transfer Bank' => 'Transfer Bank',
                                'Tunai'         => 'Tunai',
                            ])
                            ->default(fn ($record) => $record->metode_pembayaran ?: 'Transfer Bank')
                            ->required(),
                    ])
                    ->action(function (Gaji $record, array $data) {
                        app(PayrollService::class)->payPayroll(
                            $record,
                            (int) $data['account_kas_id'],
                            $data['metode_pembayaran']
                        );

                        Notification::make()
                            ->title('Pembayaran Berhasil')
                            ->body("Gaji {$record->karyawan?->nama} berhasil dibayarkan dan dibukukan ke Kas/Bank.")
                            ->success()
                            ->send();
                    }),

                // View Slip Modal
                Tables\Actions\Action::make('slip')
                    ->label('Slip Gaji')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Slip Gaji - {$record->kode_slip} ({$record->karyawan?->nama})")
                    ->modalContent(fn ($record) => view('components.slip-gaji-modal', ['gaji' => $record])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(PayrollService::class)->deletePayroll($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGajis::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'edit'   => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}