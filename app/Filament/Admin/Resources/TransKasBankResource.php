<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasBankResource\Pages;
use App\Models\MasterCOA;
use App\Models\TransKasBank;
use App\Services\Finance\TreasuryService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransKasBankResource extends Resource
{
    protected static ?string $model = TransKasBank::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $modelLabel = 'Transaksi Bank';
    protected static ?string $pluralModelLabel = 'Transaksi Bank Masuk & Bank Keluar';

    public static function getNavigationLabel(): string
    {
        return 'Bank Masuk & Bank Keluar';
    }

    public static function extractBankLabel(MasterCOA $coa): string
    {
        $name = trim((string) $coa->nama_akun);
        if (preg_match('/\(([^)]+)\)/', $name, $matches)) {
            return trim($matches[1]);
        }

        return $name;
    }

    public static function getNavigationItems(): array
    {
        if (! static::canAccess()) {
            return [];
        }

        return [
            \Filament\Navigation\NavigationItem::make('Bank Masuk')
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-arrow-down-circle')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*') && (request()->query('activeTab') === 'BANK_MASUK' || request()->query('type') === 'BANK_MASUK'))
                ->sort(9)
                ->url(static::getUrl('index', ['activeTab' => 'BANK_MASUK'])),
            \Filament\Navigation\NavigationItem::make('Bank Keluar')
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-arrow-up-circle')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*') && (request()->query('activeTab') === 'BANK_KELUAR' || request()->query('type') === 'BANK_KELUAR'))
                ->sort(10)
                ->url(static::getUrl('index', ['activeTab' => 'BANK_KELUAR'])),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                    ? 'Transaksi Bank Keluar'
                    : 'Transaksi Bank Masuk'
                )
                    ->description(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                        ? 'Catat transfer keluar / pembayaran dari rekening bank perusahaan.'
                        : 'Catat transfer masuk / penerimaan ke rekening bank perusahaan.'
                    )
                    ->extraAttributes(fn (Get $get) => [
                        'class' => 'rounded-xl p-4 shadow-sm bg-white dark:bg-gray-900 border-l-[6px] ' .
                            ($get('tipe_transaksi') === 'BANK_KELUAR' ? 'border-red-500' : 'border-green-500'),
                    ])
                    ->schema([
                        Hidden::make('tipe_transaksi')
                            ->default(fn () => request()->query('type', 'BANK_MASUK'))
                            ->dehydrated(true)
                            ->required(),

                        Hidden::make('periode')
                            ->default(fn () => now()->format('F Y'))
                            ->dehydrated(true),

                        Hidden::make('status')
                            ->default('ACTIVE')
                            ->dehydrated(true),

                        Placeholder::make('info_tipe')
                            ->label('Tipe Transaksi')
                            ->content(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                ? '🔴 Bank Keluar (Pengeluaran / Transfer via Rekening Bank)'
                                : '🟢 Bank Masuk (Penerimaan / Transfer via Rekening Bank)'
                            ),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('kode_transaksi')
                                    ->label('Kode Transaksi')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->default('AUTO'),

                                DatePicker::make('tanggal_transaksi')
                                    ->label('Tanggal Transaksi')
                                    ->required()
                                    ->default(now())
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $set('periode', Carbon::parse($state)->format('F Y'));
                                        }
                                    }),

                                TextInput::make('gudang')
                                    ->label('Gudang')
                                    ->default('UGRMS')
                                    ->datalist(['UGRMS', 'TTC'])
                                    ->placeholder('Contoh: UGRMS / TTC'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('account_bank_id')
                                    ->label(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                        ? 'Rekening Bank Asal'
                                        : 'Rekening Bank Penerima'
                                    )
                                    ->placeholder('Pilih rekening bank (BCA / BRI / dll)...')
                                    ->helperText(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                        ? 'Khusus rekening bank pengirim/pembayar (Kredit).'
                                        : 'Khusus rekening bank penerima dana masuk (Debit).'
                                    )
                                    ->relationship(
                                        name: 'accountBank',
                                        titleAttribute: 'nama_akun',
                                        modifyQueryUsing: fn (Builder $query, ?TransKasBank $record) => $query
                                            ->where(function (Builder $q) use ($record) {
                                                $q->where(function (Builder $sub) {
                                                    $sub->where('account_class', 'Current - Bank')
                                                        ->where('posting', 'Yes');
                                                });

                                                if ($record?->account_bank_id) {
                                                    $q->orWhere('id', $record->account_bank_id);
                                                }
                                            })
                                            ->orderBy('kode_akun')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                    ->searchable(['kode_akun', 'nama_akun'])
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }

                                        $coa = MasterCOA::find($state);
                                        if ($coa) {
                                            $set('bank', static::extractBankLabel($coa));
                                        }
                                    })
                                    ->required(),

                                Select::make('account_lawan_id')
                                    ->label(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                        ? 'Tujuan Pembayaran (Untuk Apa?)'
                                        : 'Diterima Dari (Sumber Dana)'
                                    )
                                    ->placeholder(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                        ? 'Cari utang supplier, beban operasional, atau transfer ke Kas Kecil...'
                                        : 'Cari pendapatan, pelunasan piutang/invoice, atau modal...'
                                    )
                                    ->helperText(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                        ? 'Akun lawan tujuan pembayaran/transfer (Debit).'
                                        : 'Akun lawan sumber penerimaan dana (Kredit).'
                                    )
                                    ->relationship(
                                        name: 'accountLawan',
                                        titleAttribute: 'nama_akun',
                                        modifyQueryUsing: fn (Builder $query, ?TransKasBank $record) => $query
                                            ->where(function (Builder $q) use ($record) {
                                                $q->where('posting', 'Yes');
                                                if ($record?->account_lawan_id) {
                                                    $q->orWhere('id', $record->account_lawan_id);
                                                }
                                            })
                                            ->orderBy('kode_akun')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                    ->searchable(['kode_akun', 'nama_akun'])
                                    ->preload()
                                    ->optionsLimit(50)
                                    ->different('account_bank_id')
                                    ->validationMessages([
                                        'different' => 'Akun lawan tidak boleh sama dengan rekening bank.',
                                    ])
                                    ->required(),
                            ]),

                        TextInput::make('nominal')
                            ->label('Nominal Transaksi')
                            ->prefix('Rp')
                            ->currencyMask(2)
                            ->placeholder('0.00')
                            ->required()
                            ->helperText('💡 Otomatis diberi pemisah ribuan saat diketik. Mendukung pecahan sen (contoh: 1,000,000.00).'),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->placeholder(fn (Get $get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                ? 'Contoh: Transfer ke kasir untuk petty cash / pembayaran tagihan supplier...'
                                : 'Contoh: Transfer masuk dari customer / setoran modal operasional...'
                            ),

                        Section::make('Informasi Rekening / Customer (Opsional)')
                            ->description('Nama bank otomatis terisi saat rekening bank dipilih. Lengkapi atas nama / no rekening jika diperlukan.')
                            ->collapsed()
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer (Opsional)')
                                    ->relationship('customer', 'nama_customer')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Customer jika ada'),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('bank')
                                            ->label('Nama Bank')
                                            ->placeholder('Otomatis terisi dari akun bank (BCA / BRI / dll)'),

                                        TextInput::make('atas_nama')
                                            ->label('Atas Nama (A/N)')
                                            ->placeholder('Nama pemilik rekening tujuan/pengirim'),
                                    ]),

                                TextInput::make('no_rekening')
                                    ->label('Nomor Rekening')
                                    ->placeholder('Nomor rekening (opsional)'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('tipe_transaksi')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'BANK_MASUK',
                        'danger'  => 'BANK_KELUAR',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'BANK_KELUAR' ? 'Bank Keluar' : 'Bank Masuk'),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gudang')
                    ->label('Gudang')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('accountBank.nama_akun')
                    ->label('Rekening Bank')
                    ->formatStateUsing(fn ($record) => $record->accountBank
                        ? trim($record->accountBank->kode_akun) . ' - ' . $record->accountBank->nama_akun
                        : '-')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('accountLawan.nama_akun')
                    ->label('Sumber / Tujuan')
                    ->formatStateUsing(fn ($record) => $record->accountLawan
                        ? trim($record->accountLawan->kode_akun) . ' - ' . $record->accountLawan->nama_akun
                        : '-')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(function ($state) {
                        $val = (float) $state;
                        return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                    })
                    ->alignEnd()
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->keterangan)
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal_transaksi')
                    ->form([
                        DatePicker::make('dari')->label('Dari Tanggal'),
                        DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari'], fn (Builder $q, $date) => $q->whereDate('tanggal_transaksi', '>=', $date))
                            ->when($data['sampai'], fn (Builder $q, $date) => $q->whereDate('tanggal_transaksi', '<=', $date));
                    }),

                Tables\Filters\SelectFilter::make('account_bank_id')
                    ->label('Rekening Bank')
                    ->options(fn () => MasterCOA::query()
                        ->where('account_class', 'Current - Bank')
                        ->where('posting', 'Yes')
                        ->orderBy('kode_akun')
                        ->get()
                        ->mapWithKeys(fn ($coa) => [$coa->id => trim($coa->kode_akun) . ' - ' . $coa->nama_akun])
                        ->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('gudang')
                    ->label('Gudang')
                    ->options(fn () => TransKasBank::query()
                        ->whereNotNull('gudang')
                        ->where('gudang', '!=', '')
                        ->distinct()
                        ->orderBy('gudang')
                        ->pluck('gudang', 'gudang')
                        ->toArray()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('bank_masuk')
                    ->label('Bank Masuk')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('success')
                    ->url(fn () => static::getUrl('create', ['type' => 'BANK_MASUK'])),

                Tables\Actions\Action::make('bank_keluar')
                    ->label('Bank Keluar')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->url(fn () => static::getUrl('create', ['type' => 'BANK_KELUAR'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('infoDataTerpilih')
                    ->label('Info Data Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        \Filament\Notifications\Notification::make()
                            ->title($records->count() . ' data dipilih')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Voucher PDF')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn (TransKasBank $record) => route('finance.voucher.bank.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (TransKasBank $record) => route('finance.voucher.bank.download', $record)),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn ($record) => app(TreasuryService::class)->deleteBank($record)),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('bottom-end'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransKasBanks::route('/'),
            'create' => Pages\CreateTransKasBank::route('/create'),
            'edit'   => Pages\EditTransKasBank::route('/{record}/edit'),
            'view'   => Pages\ViewTransKasBank::route('/{record}'),
        ];
    }
}
