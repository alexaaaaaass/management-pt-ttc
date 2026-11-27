<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GajiResource\Pages;
use App\Models\Gaji;
use App\Models\HrPayrollSetting;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Services\HR\AttendanceService;
use App\Services\HR\PayrollService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'Penggajian';
    }

    public static function getModelLabel(): string
    {
        return 'Slip Gaji';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Penggajian';
    }

    protected static function getCashBankOptions(): array
    {
        return MasterCOA::where(function ($q) {
            $q->where('kode_akun', 'like', '111%')
                ->orWhere('account_class', 'like', '%Bank%')
                ->orWhere('account_class', 'like', '%Cash%');
        })
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($coa) => [$coa->id => trim((string) $coa->kode_akun) . ' - ' . $coa->nama_akun])
            ->toArray();
    }

    protected static function applyCalculatedPayroll(array $calc, callable $set): void
    {
        $fmt = fn ($v) => MoneyHelper::format((float) $v, 0);

        $set('tipe_payroll', $calc['tipe_payroll']);
        $set('tanggal_mulai', $calc['tanggal_mulai']);
        $set('tanggal_selesai', $calc['tanggal_selesai']);
        $set('jumlah_hari_hadir', $calc['jumlah_hari_hadir']);
        $set('jumlah_alpha', $calc['jumlah_alpha']);
        $set('jumlah_terlambat', $calc['jumlah_terlambat']);

        $set('gaji_pokok', $fmt($calc['gaji_pokok']));
        $set('tunjangan_jabatan', $fmt($calc['tunjangan_jabatan']));
        $set('tunjangan_kompetensi', $fmt($calc['tunjangan_kompetensi']));
        $set('tunjangan_intensif', $fmt($calc['tunjangan_intensif']));
        $set('tunjangan_makan', $fmt($calc['tunjangan_makan']));
        $set('tunjangan_hadir', $fmt($calc['tunjangan_hadir']));
        $set('tunjangan_lain', $fmt($calc['tunjangan_lain']));
        $set('total_lembur', $fmt($calc['total_lembur']));
        $set('total_bonus', $fmt($calc['total_bonus']));
        $set('total_pendapatan', $fmt($calc['total_pendapatan']));

        $set('potongan_tunjangan', $fmt($calc['potongan_tunjangan']));
        $set('potongan_kehadiran', $fmt($calc['potongan_kehadiran']));
        $set('potongan_pinjaman', $fmt($calc['potongan_pinjaman']));
        $set('potongan_bpjs', $fmt($calc['potongan_bpjs']));
        $set('bpjs_perusahaan', $fmt($calc['bpjs_perusahaan'] ?? 0));
        $set('potongan_pph21', $fmt($calc['potongan_pph21']));
        $set('potongan_lain', $fmt($calc['potongan_lain']));
        $set('total_potongan', $fmt($calc['total_potongan']));
        $set('gaji_bersih', $fmt($calc['gaji_bersih']));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Karyawan, Skema & Periode Gaji')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('karyawan_id')
                                    ->label('Karyawan')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $periode = $get('periode') ?: now()->format('Y-m');
                                        $calc = app(PayrollService::class)->calculateEmployeePayroll(
                                            (int) $state,
                                            $periode,
                                            $get('tanggal_mulai'),
                                            $get('tanggal_selesai')
                                        );
                                        self::applyCalculatedPayroll($calc, $set);
                                    })
                                    ->columnSpan(2),

                                Select::make('tipe_payroll')
                                    ->label('Skema Payroll')
                                    ->options([
                                        'Bulanan'   => 'Bulanan',
                                        'Harian'    => 'Harian',
                                        'Freelance' => 'Freelance',
                                    ])
                                    ->default('Bulanan')
                                    ->required(),

                                TextInput::make('kode_slip')
                                    ->label('Kode Slip')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default('AUTO'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('periode')
                                    ->label('Periode (YYYY-MM)')
                                    ->default(now()->format('Y-m'))
                                    ->placeholder('2026-11')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $kid = $get('karyawan_id');
                                        if (! $kid || ! $state) {
                                            return;
                                        }
                                        $calc = app(PayrollService::class)->calculateEmployeePayroll((int) $kid, $state);
                                        self::applyCalculatedPayroll($calc, $set);
                                    }),

                                DatePicker::make('tanggal_mulai')
                                    ->label('Tgl Mulai Periode')
                                    ->default(fn () => app(AttendanceService::class)->getPeriodDateRange(now()->year, now()->month)['start']),
                                DatePicker::make('tanggal_selesai')
                                    ->label('Tgl Selesai Periode')
                                    ->default(fn () => app(AttendanceService::class)->getPeriodDateRange(now()->year, now()->month)['end']),
                                DatePicker::make('tanggal_pembayaran')->label('Tanggal Pembayaran')->default(now())->required(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('jumlah_hari_hadir')
                                    ->label('Hari Hadir (Harian/Freelance)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        if (! in_array($get('tipe_payroll'), ['Harian', 'Freelance'], true) || ! $get('karyawan_id')) {
                                            return;
                                        }
                                        $k = Karyawan::find((int) $get('karyawan_id'));
                                        if (! $k) {
                                            return;
                                        }
                                        $days = (float) ($state ?? 0);
                                        if ($days > 0) {
                                            $set('gaji_pokok', MoneyHelper::format(round((float) $k->gaji_pokok * $days, 0), 0));
                                            $set('tunjangan_makan', MoneyHelper::format(round((float) $k->tunjangan_makan * $days, 0), 0));
                                            $set('tunjangan_hadir', MoneyHelper::format(round((float) $k->tunjangan_hadir * $days, 0), 0));
                                            self::recalculateTotals($get, $set);
                                        }
                                    }),

                                TextInput::make('jumlah_alpha')->label('Alpha (Hari)')->numeric()->default(0),
                                TextInput::make('jumlah_terlambat')->label('Terlambat (Kali)')->numeric()->default(0),

                                Select::make('status')
                                    ->label('Status Slip')
                                    ->options([
                                        'Draft'    => 'Draft (Rancangan)',
                                        'Approved' => 'Approved (Disetujui)',
                                        'Paid'     => 'Paid (Sudah Dibayarkan)',
                                    ])
                                    ->default('Draft')
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'Transfer Bank' => 'Transfer Bank',
                                        'Tunai'         => 'Tunai (Kas)',
                                    ])
                                    ->default('Transfer Bank')
                                    ->required(),

                                Select::make('account_kas_id')
                                    ->label('Akun Kas / Bank Pembayar (111x)')
                                    ->options(fn () => self::getCashBankOptions())
                                    ->searchable()
                                    ->placeholder('Pilih Rekening Kas/Bank TTC'),

                                Toggle::make('post_to_ledger')
                                    ->label('Auto-Posting ke Buku Besar saat Paid')
                                    ->helperText('Matikan jika pembayaran gaji dicatat lewat impor mutasi bank / NPB agar saldo tidak dobel.')
                                    ->default(false),
                            ]),
                    ]),

                Section::make('Komponen Pendapatan (Earnings)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('gaji_pokok')->label('Gaji Pokok / Upah Harian')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set))->required(),
                                TextInput::make('tunjangan_jabatan')->label('Tunjangan Jabatan')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('tunjangan_kompetensi')->label('Tunjangan Kompetensi')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('tunjangan_intensif')->label('Tunjangan Intensif')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('tunjangan_makan')->label('Tunjangan Makan')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('tunjangan_hadir')->label('Tunjangan Kehadiran')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('tunjangan_lain')->label('Tunjangan Lain-lain')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('total_lembur')->label('Uang Lembur')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('total_bonus')->label('Bonus / Insentif')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                            ]),

                        TextInput::make('total_pendapatan')
                            ->label('Total Pendapatan Kotor')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ]),

                Section::make('Komponen Potongan (Deductions)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('potongan_tunjangan')->label('Potongan Tunjangan')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('potongan_kehadiran')->label('Potongan Alpha / Telat')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('potongan_pinjaman')->label('Cicilan Pinjaman (Auto-Catat saat Paid)')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('potongan_bpjs')->label('Potongan BPJS Karyawan (2%)')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('bpjs_perusahaan')->label('Kontribusi BPJS PT (3,7% — Non-Potong THP)')->prefix('Rp')->currencyMask(0)->default(0)->helperText('Masuk Beban 6502 & BPJS Terutang 2133 saat diposting ke GL.'),
                                TextInput::make('potongan_pph21')->label('Potongan PPh 21')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                                TextInput::make('potongan_lain')->label('Potongan Lain-lain')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, callable $get, callable $set) => self::recalculateTotals($get, $set)),
                            ]),

                        TextInput::make('total_potongan')
                            ->label('Total Seluruh Potongan')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ]),

                Section::make('Gaji Bersih Diterima (Take Home Pay)')
                    ->schema([
                        TextInput::make('gaji_bersih')
                            ->label('Gaji Bersih / Take Home Pay (THP)')
                            ->prefix('Rp')
                            ->currencyMask(0)
                            ->disabled()
                            ->dehydrated()
                            ->default(0),

                        Textarea::make('keterangan')
                            ->label('Catatan / Keterangan Slip Gaji')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function recalculateTotals(callable $get, callable $set): void
    {
        $c = fn (string $k) => MoneyHelper::clean($get($k)) ?? 0;

        $totPendapatan = round(
            $c('gaji_pokok') + $c('tunjangan_jabatan') + $c('tunjangan_kompetensi') + $c('tunjangan_intensif')
            + $c('tunjangan_makan') + $c('tunjangan_hadir') + $c('tunjangan_lain') + $c('total_lembur') + $c('total_bonus'),
            2
        );
        $totPotongan = round(
            $c('potongan_tunjangan') + $c('potongan_kehadiran') + $c('potongan_pinjaman')
            + $c('potongan_bpjs') + $c('potongan_pph21') + $c('potongan_lain'),
            2
        );
        $gajiBersih = max(0.0, round($totPendapatan - $totPotongan, 2));

        $set('total_pendapatan', MoneyHelper::format($totPendapatan, 0));
        $set('total_potongan', MoneyHelper::format($totPotongan, 0));
        $set('gaji_bersih', MoneyHelper::format($gajiBersih, 0));
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
                    ->weight('bold')
                    ->description(fn (Gaji $record) => ($record->karyawan?->nip ? "[{$record->karyawan->nip}] " : '') . ($record->karyawan?->jabatan ?: $record->karyawan?->departemen?->nama_departemen ?: '')),

                TextColumn::make('tipe_payroll')
                    ->label('Skema')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Bulanan'   => 'primary',
                        'Harian'    => 'info',
                        'Freelance' => 'warning',
                        default     => 'gray',
                    }),

                TextColumn::make('periode')
                    ->label('Periode')
                    ->badge(),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))),

                TextColumn::make('potongan_pinjaman')
                    ->label('Kasbon')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->alignRight()
                    ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))),

                TextColumn::make('potongan_bpjs')
                    ->label('BPJS')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))),

                TextColumn::make('total_potongan')
                    ->label('Potongan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->color('danger')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))),

                TextColumn::make('gaji_bersih')
                    ->label('Take Home Pay')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total THP')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Draft'    => 'warning',
                        'Approved' => 'info',
                        'Paid'     => 'success',
                        default    => 'gray',
                    }),

                IconColumn::make('is_ledger_posted')
                    ->label('GL')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('tanggal_pembayaran')
                    ->label('Tgl Bayar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_payroll')->label('Skema')->options(['Bulanan' => 'Bulanan', 'Harian' => 'Harian', 'Freelance' => 'Freelance']),
                Tables\Filters\SelectFilter::make('status')->options(['Draft' => 'Draft', 'Approved' => 'Approved', 'Paid' => 'Paid']),
                Tables\Filters\SelectFilter::make('periode')->options(fn () => Gaji::select('periode')->distinct()->orderByDesc('periode')->pluck('periode', 'periode')->toArray()),
            ])
            ->actions([
                Action::make('pdfSlip')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Gaji $record) => route('hrd.slip-gaji.preview', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('bayar')
                        ->label('Bayar Gaji (Paid)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->hidden(fn (Gaji $record) => $record->status === 'Paid')
                        ->form([
                            Select::make('account_kas_id')
                                ->label('Akun Kas / Bank Pembayar (111x)')
                                ->options(fn () => self::getCashBankOptions())
                                ->searchable()
                                ->required(),
                            Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options(['Transfer Bank' => 'Transfer Bank', 'Tunai' => 'Tunai'])
                                ->default(fn (Gaji $record) => $record->metode_pembayaran ?: 'Transfer Bank')
                                ->required(),
                            Toggle::make('post_to_ledger')
                                ->label('Auto-Posting Jurnal ke Buku Besar')
                                ->helperText('Matikan jika pembayaran sudah dicatat melalui impor mutasi bank / NPB agar saldo tidak dobel.')
                                ->default(false),
                        ])
                        ->action(function (Gaji $record, array $data) {
                            app(PayrollService::class)->payPayroll(
                                $record,
                                (int) $data['account_kas_id'],
                                $data['metode_pembayaran'],
                                (bool) ($data['post_to_ledger'] ?? false)
                            );
                            Notification::make()
                                ->title('Pembayaran Gaji Berhasil')
                                ->body("Gaji {$record->karyawan?->nama} dibayarkan" . ($record->potongan_pinjaman > 0 ? ' & cicilan pinjaman otomatis tercatat.' : '.'))
                                ->success()
                                ->send();
                        }),

                    Action::make('slip')
                        ->label('Lihat Slip (Modal)')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->modalHeading(fn (Gaji $record) => "Slip Gaji - {$record->kode_slip} ({$record->karyawan?->nama})")
                        ->modalContent(fn (Gaji $record) => view('components.slip-gaji-modal', ['gaji' => $record])),

                    Action::make('downloadSlip')
                        ->label('Download PDF Slip')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (Gaji $record) => route('hrd.slip-gaji.download', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn (Gaji $record) => app(PayrollService::class)->deletePayroll($record)),
                ]),
            ])
            ->bulkActions([
                BulkAction::make('bulkApprove')
                    ->label('Approve Terpilih')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $cnt = 0;
                        foreach ($records as $r) {
                            if ($r->status === 'Draft') {
                                $r->update(['status' => 'Approved']);
                                $cnt++;
                            }
                        }
                        Notification::make()->title("{$cnt} slip gaji disetujui (Approved)")->success()->send();
                    }),

                BulkAction::make('bulkPay')
                    ->label('Bayar & Posting GL Massal (Paid)')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->form([
                        Select::make('account_kas_id')
                            ->label('Akun Kas / Bank Pembayar (111x)')
                            ->options(fn () => self::getCashBankOptions())
                            ->default(fn () => HrPayrollSetting::first()?->default_account_kas_id)
                            ->searchable()
                            ->required(),
                        Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options(['Transfer Bank' => 'Transfer Bank', 'Tunai' => 'Tunai'])
                            ->default('Transfer Bank')
                            ->required(),
                        Toggle::make('post_to_ledger')
                            ->label('Auto-Posting Jurnal Multi-Line PSAK ke Buku Besar')
                            ->helperText('Otomatis jurnal Beban Gaji + BPJS PT vs Bank + Kasbon (1131) + BPJS Terutang (2133).')
                            ->default(true),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $service = app(PayrollService::class);
                        $cnt = 0;
                        $totalThp = 0.0;
                        foreach ($records as $r) {
                            $service->payPayroll(
                                $r,
                                (int) $data['account_kas_id'],
                                $data['metode_pembayaran'],
                                (bool) ($data['post_to_ledger'] ?? true)
                            );
                            $cnt++;
                            $totalThp += (float) $r->gaji_bersih;
                        }
                        Notification::make()
                            ->title("Pembayaran Massal Selesai ({$cnt} Slip)")
                            ->body('Total Take Home Pay: Rp ' . number_format($totalThp, 0, ',', '.') . (! empty($data['post_to_ledger']) ? ' · Jurnal Multi-Line telah diposting ke Buku Besar.' : '.'))
                            ->success()
                            ->send();
                    }),
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
