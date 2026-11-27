<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NpbRequestResource\Pages;
use App\Filament\Admin\Resources\NpbRequestResource\RelationManagers;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Services\Finance\FinanceHrdSyncService;
use App\Services\Finance\NpbService;
use App\Support\MoneyHelper;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class NpbRequestResource extends Resource
{
    protected static ?string $model = NpbRequest::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'NPB & Penyelesaian (LPJ)';
    protected static ?string $modelLabel = 'Nota Pengajuan Biaya (NPB)';
    protected static ?string $pluralModelLabel = 'NPB & Penyelesaian (LPJ)';

    public static function getNavigationBadge(): ?string
    {
        $pending = NpbRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected static function getCoaOptions(): array
    {
        return MasterCOA::orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
            ->toArray();
    }

    protected static function getCashBankCoaOptions(): array
    {
        return MasterCOA::where(function ($q) {
            $q->where('kode_akun', 'like', '111%')
                ->orWhere('account_class', 'like', '%Bank%')
                ->orWhere('account_class', 'like', '%Cash%');
        })
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
            ->toArray();
    }

    public static function form(Form $form): Form
    {
        $coaOptions = fn () => static::getCoaOptions();
        $cashBankOptions = fn () => static::getCashBankCoaOptions();

        return $form
            ->schema([
                Section::make('Identitas Nota Pengajuan Biaya (NPB)')
                    ->description('Format resmi PT Teknografi Tri Cawanaska — mendukung kebutuhan periode berjalan dan proyeksi bulan berikutnya.')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('nomor_pengajuan')
                                    ->label('Nomor Pengajuan')
                                    ->default('AUTO')
                                    ->helperText('Otomatis: NPB/KEU/XI/2026 (bisa diedit manual)')
                                    ->required()
                                    ->maxLength(60),

                                DatePicker::make('tanggal_pengajuan')
                                    ->label('Tanggal Pengajuan')
                                    ->default(now())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $d = Carbon::parse($state);
                                        $b1 = $d->translatedFormat('F Y');
                                        $b2 = $d->copy()->addMonth()->translatedFormat('F Y');
                                        if (blank($get('periode_utama'))) {
                                            $set('periode_utama', $b1);
                                        }
                                        if (blank($get('periode_proyeksi'))) {
                                            $set('periode_proyeksi', $b2);
                                        }
                                    }),

                                TextInput::make('departemen_kode')
                                    ->label('Kode Dept')
                                    ->default('KEU')
                                    ->maxLength(20)
                                    ->required(),

                                Select::make('status')
                                    ->label('Status NPB')
                                    ->options(NpbRequest::STATUS_OPTIONS)
                                    ->default('DRAFT')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('periode_utama')
                                    ->label('Periode Utama (Bulan Berjalan)')
                                    ->default(fn () => now()->translatedFormat('F Y'))
                                    ->placeholder('Contoh: November 2026')
                                    ->required()
                                    ->live(onBlur: true),

                                TextInput::make('periode_proyeksi')
                                    ->label('Periode Proyeksi (Opsional — Bulan 2)')
                                    ->default(fn () => now()->addMonth()->translatedFormat('F Y'))
                                    ->placeholder('Contoh: Desember 2026 (kosongkan jika 1 bulan)')
                                    ->live(onBlur: true),

                                TextInput::make('perihal')
                                    ->label('Perihal')
                                    ->default(fn () => 'Permohonan Pendanaan PT. Teknografi Tri Cawanaska Periode ' . now()->translatedFormat('F') . '-' . now()->addMonth()->translatedFormat('F Y'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Ringkasan Kebutuhan & Kekurangan Pendanaan (Live)')
                    ->schema([
                        Placeholder::make('ringkasan_live')
                            ->label('')
                            ->content(function (Get $get) {
                                $items = $get('items') ?? [];
                                $funds = $get('fundPositions') ?? [];

                                $totUtama = 0.0;
                                $totProyeksi = 0.0;
                                foreach ($items as $it) {
                                    $totUtama += (float) (MoneyHelper::clean($it['nominal_utama'] ?? 0) ?? 0);
                                    $totProyeksi += (float) (MoneyHelper::clean($it['nominal_proyeksi'] ?? 0) ?? 0);
                                }
                                $totGab = $totUtama + $totProyeksi;

                                $totDana = 0.0;
                                foreach ($funds as $fp) {
                                    $totDana += (float) (MoneyHelper::clean($fp['saldo'] ?? 0) ?? 0);
                                }
                                $kurang = $totDana - $totUtama;

                                $p1 = e($get('periode_utama') ?: 'Bulan 1');
                                $p2 = e($get('periode_proyeksi') ?: 'Proyeksi');
                                $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
                                $kurangColor = $kurang < 0 ? '#dc2626' : '#059669';
                                $kurangLabel = $kurang < 0 ? 'Kekurangan Pendanaan ' . $p1 : 'Surplus Pendanaan ' . $p1;

                                return new HtmlString(
                                    '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:0.75;">Kebutuhan ' . $p1 . '</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:#2563eb;margin-top:4px;">' . $fmt($totUtama) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid rgba(99,102,241,0.35);background:rgba(99,102,241,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:0.75;">Proyeksi ' . $p2 . '</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:#4f46e5;margin-top:4px;">' . $fmt($totProyeksi) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid rgba(100,116,139,0.35);background:rgba(100,116,139,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:0.75;">Total Dana Tersedia (Kas/Bank)</div>'
                                    . '<div style="font-size:18px;font-weight:800;margin-top:4px;">' . $fmt($totDana) . '</div>'
                                    . '<div style="font-size:11px;opacity:0.7;margin-top:2px;">Total 2 Bulan: ' . $fmt($totGab) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid ' . $kurangColor . ';background:rgba(220,38,38,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:' . $kurangColor . ';">' . $kurangLabel . '</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:' . $kurangColor . ';margin-top:4px;">' . ($kurang < 0 ? '-' : '') . $fmt(abs($kurang)) . '</div></div>'
                                    . '</div>'
                                );
                            }),
                    ]),

                Section::make('Posisi Pendanaan (Saldo Kas & Bank Saat Pengajuan)')
                    ->description('Catat saldo aktual rekening bank & petty cash sebelum pengajuan. Gunakan tombol "Tarik Saldo Buku Besar" untuk mengisi otomatis, lalu sesuaikan jika ada rekening operasional lain.')
                    ->headerActions([
                        FormAction::make('pullLedgerBalances')
                            ->label('Tarik Saldo dari Buku Besar')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('info')
                            ->action(function (Set $set, Get $get) {
                                $rows = app(NpbService::class)->getDefaultFundPositionsFromLedger($get('tanggal_pengajuan'));
                                $keyed = [];
                                foreach ($rows as $row) {
                                    $row['saldo'] = MoneyHelper::format($row['saldo'], 2);
                                    $keyed[(string) \Illuminate\Support\Str::uuid()] = $row;
                                }
                                $set('fundPositions', $keyed);
                                Notification::make()
                                    ->title('Saldo Kas & Bank berhasil ditarik dari Buku Besar')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Repeater::make('fundPositions')
                            ->label('')
                            ->relationship('fundPositions')
                            ->orderColumn('urutan')
                            ->defaultItems(0)
                            ->addActionLabel('+ Tambah Rekening / Kas')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('nama_rekening')
                                            ->label('Nama Rekening / Kas')
                                            ->placeholder('BRI Giro, BRI Tabungan, BCA Priska...')
                                            ->required()
                                            ->maxLength(120),

                                        Select::make('coa_id')
                                            ->label('Link COA (Opsional)')
                                            ->options($cashBankOptions)
                                            ->searchable()
                                            ->optionsLimit(40)
                                            ->nullable(),

                                        TextInput::make('saldo')
                                            ->label('Saldo Saat Ini (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->required(),

                                        TextInput::make('keterangan')
                                            ->label('Catatan')
                                            ->placeholder('Opsional')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columns(1),
                    ]),

                Section::make('Rincian Kebutuhan Dana (Kategori A – E)')
                    ->description('Kelompokkan setiap kebutuhan sesuai kategori resmi A–E. Pilih COA target agar otomatis terisi saat membuat Penyelesaian NPB (LPJ).')
                    ->headerActions([
                        FormAction::make('pullFromHrdPayroll')
                            ->label('Tarik Gaji, BPJS & Borongan dari HRD')
                            ->icon('heroicon-o-arrows-right-left')
                            ->color('primary')
                            ->form([
                                Select::make('periode_hrd')
                                    ->label('Periode Payroll HRD')
                                    ->options(fn () => app(FinanceHrdSyncService::class)->getAvailablePeriods())
                                    ->default(fn (Get $get) => app(FinanceHrdSyncService::class)->parseLabelToPeriode($get('periode_utama')) ?: now()->format('Y-m'))
                                    ->required(),
                                Select::make('basis_gaji')
                                    ->label('Basis Nominal Gaji Karyawan')
                                    ->options([
                                        'THP'        => 'Take Home Pay (Kas Ditransfer ke Karyawan)',
                                        'GAJI_POKOK' => 'Total Gaji Pokok (Gross Standar TTC)',
                                        'GROSS'      => 'Total Beban Gaji Net (THP + Potongan Kasbon/BPJS)',
                                    ])
                                    ->default('THP')
                                    ->required(),
                            ])
                            ->action(function (array $data, Set $set, Get $get) {
                                $hrdRows = app(FinanceHrdSyncService::class)->buildNpbPayrollItems(
                                    $data['periode_hrd'],
                                    $data['basis_gaji'] ?? 'THP'
                                );
                                if (empty($hrdRows)) {
                                    Notification::make()
                                        ->title("Belum ada data Payroll / Borongan HRD pada periode {$data['periode_hrd']}")
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $existingItems = $get('items') ?? [];
                                $updated = 0;
                                $added = 0;

                                foreach ($hrdRows as $entry) {
                                    $syncKey = $entry['sync_key'];
                                    unset($entry['sync_key']);
                                    $entry['nominal_utama'] = MoneyHelper::format($entry['nominal_utama'], 2);
                                    $entry['nominal_proyeksi'] = MoneyHelper::format($entry['nominal_proyeksi'], 2);

                                    $matchedKey = null;
                                    foreach ($existingItems as $k => $ex) {
                                        $u = strtolower((string) ($ex['uraian'] ?? ''));
                                        $isMatch = match ($syncKey) {
                                            'GAJI'     => str_contains($u, 'gaji') && ! str_contains($u, 'borong'),
                                            'BPJS'     => str_contains($u, 'bpjs'),
                                            'BORONGAN' => str_contains($u, 'borong') || str_contains($u, 'freelance'),
                                            default    => false,
                                        };
                                        if ($isMatch) {
                                            $matchedKey = $k;
                                            break;
                                        }
                                    }

                                    if ($matchedKey !== null) {
                                        $existingItems[$matchedKey]['coa_id'] = $entry['coa_id'] ?: ($existingItems[$matchedKey]['coa_id'] ?? null);
                                        $existingItems[$matchedKey]['nominal_utama'] = $entry['nominal_utama'];
                                        $existingItems[$matchedKey]['nominal_proyeksi'] = $entry['nominal_proyeksi'];
                                        $existingItems[$matchedKey]['keterangan'] = $entry['keterangan'];
                                        $updated++;
                                    } else {
                                        $entry['urutan'] = count($existingItems) + 1;
                                        $existingItems[(string) \Illuminate\Support\Str::uuid()] = $entry;
                                        $added++;
                                    }
                                }

                                $set('items', $existingItems);
                                Notification::make()
                                    ->title('Data Payroll HRD Berhasil Disinkronkan ke NPB')
                                    ->body("Periode {$data['periode_hrd']}: {$updated} pos diperbarui, {$added} pos baru ditambahkan.")
                                    ->success()
                                    ->send();
                            }),

                        FormAction::make('loadMonthlyTemplate')
                            ->label('Muat Template Bulanan Standar TTC')
                            ->icon('heroicon-o-sparkles')
                            ->color('success')
                            ->action(function (Set $set) {
                                $rows = app(NpbService::class)->getDefaultMonthlyTemplateItems();
                                $keyed = [];
                                foreach ($rows as $row) {
                                    $row['nominal_utama'] = MoneyHelper::format($row['nominal_utama'], 2);
                                    $row['nominal_proyeksi'] = MoneyHelper::format($row['nominal_proyeksi'], 2);
                                    $keyed[(string) \Illuminate\Support\Str::uuid()] = $row;
                                }
                                $set('items', $keyed);
                                Notification::make()
                                    ->title('8 pos rutin bulanan standar TTC berhasil dimuat')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->orderColumn('urutan')
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Pos Kebutuhan Dana')
                            ->itemLabel(fn (array $state): ?string => trim(($state['uraian'] ?? 'Pos Baru') . ' — ' . ($state['karakter'] ?? 'Bulanan')))
                            ->collapsible()
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Select::make('kategori')
                                            ->label('Kategori')
                                            ->options(NpbRequest::KATEGORI_OPTIONS)
                                            ->default('A_KEWAJIBAN_TETAP')
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('uraian')
                                            ->label('Uraian Kebutuhan')
                                            ->placeholder('Contoh: Tagihan Gudang B8-B9, Gaji Karyawan, Proyek Partisi...')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Select::make('karakter')
                                            ->label('Karakter')
                                            ->options([
                                                'Bulanan'    => 'Bulanan (Rutin)',
                                                'Insidental' => 'Insidental',
                                            ])
                                            ->default('Bulanan')
                                            ->required()
                                            ->columnSpan(1),

                                        Select::make('coa_id')
                                            ->label('Akun COA Target')
                                            ->options($coaOptions)
                                            ->searchable()
                                            ->optionsLimit(50)
                                            ->nullable()
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('nominal_utama')
                                            ->label(fn (Get $get) => 'Nominal ' . ($get('../../periode_utama') ?: 'Periode Utama') . ' (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->required(),

                                        TextInput::make('nominal_proyeksi')
                                            ->label(fn (Get $get) => 'Nominal ' . ($get('../../periode_proyeksi') ?: 'Proyeksi Bulan 2') . ' (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true),

                                        TextInput::make('keterangan')
                                            ->label('Catatan / Dasar Perhitungan')
                                            ->placeholder('Opsional (misal: termin 1, estimasi PLN, dll.)')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columns(1),
                    ]),

                Section::make('Catatan & Otorisasi Penandatangan')
                    ->collapsed()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Bawah NPB')
                            ->rows(4)
                            ->default(NpbRequest::DEFAULT_CATATAN),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('disusun_oleh')
                                    ->label('Disusun oleh')
                                    ->default('Bagian Keuangan'),

                                TextInput::make('mengetahui_nama')
                                    ->label('Mengetahui (Opsional)')
                                    ->placeholder('Kosongkan jika tidak diperlukan')
                                    ->default(''),

                                TextInput::make('diajukan_kepada')
                                    ->label('Diajukan kepada')
                                    ->default('Direktur'),

                                TextInput::make('disetujui_oleh')
                                    ->label('Disetujui oleh')
                                    ->default('Owner'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('No. Pengajuan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periode_utama')
                    ->label('Periode')
                    ->description(fn (NpbRequest $r) => $r->periode_proyeksi ? 'Proyeksi: ' . $r->periode_proyeksi : '1 Bulan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_kebutuhan_utama')
                    ->label('Kebutuhan Utama')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_dana_tersedia')
                    ->label('Dana Tersedia')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('kekurangan_pendanaan')
                    ->label('Kurang / Surplus')
                    ->formatStateUsing(fn ($state) => ((float) $state < 0 ? '-' : '') . 'Rp ' . number_format(abs((float) $state), 0, ',', '.'))
                    ->color(fn ($state) => (float) $state < 0 ? 'danger' : 'success')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('nominal_dicairkan')
                    ->label('Dicairkan (ACC)')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->alignRight()
                    ->weight('bold')
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_pencairan_posted')
                    ->label('COA')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->tooltip(fn (NpbRequest $r) => $r->is_pencairan_posted
                        ? 'Nominal dicairkan sudah otomatis masuk COA Bank & Modal/Pinjaman di Buku Besar'
                        : 'Belum diposting ke Buku Besar'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NpbRequest::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT'           => 'gray',
                        'SUBMITTED'       => 'info',
                        'REVIEWED'        => 'warning',
                        'APPROVED'        => 'success',
                        'DISBURSED'       => 'primary',
                        'PARTIAL_SETTLED' => 'warning',
                        'SETTLED'         => 'success',
                        'REJECTED'        => 'danger',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('settlements_count')
                    ->label('LPJ')
                    ->counts('settlements')
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(NpbRequest::STATUS_OPTIONS),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (NpbRequest $record) => route('finance.npb.preview', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('downloadPdf')
                        ->label('Download PDF NPB')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (NpbRequest $record) => route('finance.npb.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('submit')
                        ->label('Ajukan NPB')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (NpbRequest $r) => $r->status === 'DRAFT')
                        ->requiresConfirmation()
                        ->action(function (NpbRequest $record) {
                            $record->update(['status' => 'SUBMITTED']);
                            Notification::make()->title("NPB {$record->nomor_pengajuan} diajukan — silakan aktifkan di modul Approval NPB")->success()->send();
                        }),

                    Action::make('disburse')
                        ->label(fn (NpbRequest $r) => $r->is_approved_status ? 'Catat / Edit Pencairan Dana' : 'Approve & Catat Pencairan Dana')
                        ->icon('heroicon-o-banknotes')
                        ->color('primary')
                        ->visible(fn (NpbRequest $r) => $r->status !== 'REJECTED')
                        ->modalHeading(fn (NpbRequest $r) => "Pencairan Dana NPB — {$r->nomor_pengajuan}")
                        ->modalDescription('Catat nominal yang disetujui & dicairkan (misal ajukan Rp 100 jt lalu ACC Rp 80 jt). Secara default pencairan TIDAK otomatis masuk ke COA Bank — input uang masuk ke rekening tetap dilakukan manual melalui menu Bank Masuk seperti sebelumnya agar sesuai mutasi bank & anti-dobel.')
                        ->form([
                            DatePicker::make('tanggal_pencairan')
                                ->label('Tanggal Pencairan')
                                ->default(fn (NpbRequest $r) => $r->tanggal_pencairan ?: now())
                                ->required(),
                            TextInput::make('nominal_dicairkan')
                                ->label('Nominal Disetujui & Dicairkan (Rp)')
                                ->helperText('Contoh: pengajuan Rp 100 jt, di-ACC Rp 80 jt → isi 80.000.000.')
                                ->prefix('Rp')
                                ->currencyMask(2)
                                ->default(fn (NpbRequest $r) => $r->nominal_dicairkan > 0 ? $r->nominal_dicairkan : (abs(min(0, (float) $r->kekurangan_pendanaan)) ?: (float) $r->total_kebutuhan_utama))
                                ->required(),
                            Select::make('sumber_dana_pencairan')
                                ->label('Sumber Dana Pencairan')
                                ->options(NpbRequest::SUMBER_DANA_OPTIONS)
                                ->default(fn (NpbRequest $r) => $r->sumber_dana_pencairan ?: 'MODAL_PEMILIK')
                                ->required(),
                            Select::make('coa_tujuan_pencairan_id')
                                ->label('Rekening Bank / Kas Tujuan di PT TTC (Opsional)')
                                ->placeholder('Pilih manual jika perlu (tidak otomatis memposting ke COA Bank)')
                                ->options(fn () => static::getCashBankCoaOptions())
                                ->default(fn (NpbRequest $r) => $r->coa_tujuan_pencairan_id)
                                ->searchable()
                                ->nullable()
                                ->required(fn (Get $get) => (bool) $get('post_to_ledger')),
                            Toggle::make('post_to_ledger')
                                ->label('Auto-Posting Jurnal Pencairan ke Buku Besar (Opsional)')
                                ->helperText('Default MATI (disarankan): pencatatan masuk ke COA Bank & Modal dilakukan manual melalui menu Bank Masuk agar tidak dobel dengan mutasi rekening.')
                                ->default(fn (NpbRequest $r) => (bool) $r->is_pencairan_posted)
                                ->live(),
                        ])
                        ->action(function (NpbRequest $record, array $data) {
                            $nominal = round((float) (MoneyHelper::clean($data['nominal_dicairkan']) ?? 0), 2);
                            $nextStatus = in_array($record->status, ['PARTIAL_SETTLED', 'SETTLED'], true)
                                ? $record->status
                                : 'DISBURSED';

                            $record->update([
                                'tanggal_pencairan'       => $data['tanggal_pencairan'],
                                'nominal_dicairkan'       => $nominal,
                                'sumber_dana_pencairan'   => $data['sumber_dana_pencairan'],
                                'coa_tujuan_pencairan_id' => $data['coa_tujuan_pencairan_id'] ?? null,
                                'status'                  => $nextStatus,
                                'disetujui_oleh'          => $record->disetujui_oleh ?: 'Owner',
                                'disetujui_at'            => $record->disetujui_at ?: now(),
                            ]);

                            $svc = app(NpbService::class);
                            $posted = false;
                            if (! empty($data['post_to_ledger']) && $data['sumber_dana_pencairan'] !== 'DANA_INTERNAL') {
                                $posted = $svc->postDisbursementToLedger($record->refresh());
                            } else {
                                $svc->deleteDisbursementFromLedger($record->refresh());
                            }

                            $sumberLabel = NpbRequest::SUMBER_DANA_OPTIONS[$data['sumber_dana_pencairan']] ?? $data['sumber_dana_pencairan'];
                            Notification::make()
                                ->title("Pencairan NPB {$record->nomor_pengajuan} — Rp " . number_format($nominal, 0, ',', '.'))
                                ->body($posted
                                    ? "Diposting ke Buku Besar: DEBIT Bank/Kas Tujuan & KREDIT {$sumberLabel} sebesar Rp " . number_format($nominal, 0, ',', '.') . '.'
                                    : 'Tercatat di dokumen NPB tanpa mengubah COA Bank/Modal (silakan input manual di Bank Masuk saat dana masuk rekening).')
                                ->success()
                                ->duration(8000)
                                ->send();
                        }),

                    Action::make('createSettlement')
                        ->label('Buat Penyelesaian NPB (LPJ)')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('success')
                        ->visible(fn (NpbRequest $r) => in_array($r->status, ['APPROVED', 'DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'], true))
                        ->url(fn (NpbRequest $record) => NpbSettlementResource::getUrl('create', ['npb_request_id' => $record->id])),

                    Action::make('duplicateNextMonth')
                        ->label('Salin Pos Bulanan ke Bulan Depan')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (NpbRequest $record) {
                            $clone = app(NpbService::class)->duplicateForNextMonth($record);
                            Notification::make()
                                ->title("Draft NPB {$clone->nomor_pengajuan} berhasil dibuat dari pos bulanan")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn (NpbRequest $record) => app(NpbService::class)->deleteRequestWithLedgerCleanup($record)),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $svc = app(NpbService::class);
                            foreach ($records as $r) {
                                $svc->deleteRequestWithLedgerCleanup($r);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SettlementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNpbRequests::route('/'),
            'create' => Pages\CreateNpbRequest::route('/create'),
            'edit'   => Pages\EditNpbRequest::route('/{record}/edit'),
        ];
    }
}
