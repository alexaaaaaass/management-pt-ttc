<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasResource\Pages;
use App\Models\MasterCOA;
use App\Models\TransKas;
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

class TransKasResource extends Resource
{
    protected static ?string $model = TransKas::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $modelLabel = 'Transaksi Kas';
    protected static ?string $pluralModelLabel = 'Transaksi Kas Masuk & Kas Keluar';

    public static function getNavigationLabel(): string
    {
        return 'Kas Masuk & Kas Keluar';
    }

    public static function getDefaultKasCoaId(): ?int
    {
        return MasterCOA::where('kode_akun', '101.001')->value('id')
            ?? MasterCOA::where('account_class', 'Current - Cash')
                ->where('posting', 'Yes')
                ->orderBy('kode_akun')
                ->value('id');
    }

    public static function getNavigationItems(): array
    {
        if (! static::canAccess()) {
            return [];
        }

        return [
            \Filament\Navigation\NavigationItem::make('Kas Masuk')
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-arrow-down-circle')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*') && (request()->query('activeTab') === 'KAS_MASUK' || request()->query('type') === 'KAS_MASUK'))
                ->sort(7)
                ->url(static::getUrl('index', ['activeTab' => 'KAS_MASUK'])),
            \Filament\Navigation\NavigationItem::make('Kas Keluar')
                ->group(static::getNavigationGroup())
                ->icon('heroicon-o-arrow-up-circle')
                ->isActiveWhen(fn (): bool => request()->routeIs(static::getRouteBaseName() . '.*') && (request()->query('activeTab') === 'KAS_KELUAR' || request()->query('type') === 'KAS_KELUAR'))
                ->sort(8)
                ->url(static::getUrl('index', ['activeTab' => 'KAS_KELUAR'])),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                    ? 'Transaksi Kas Keluar'
                    : 'Transaksi Kas Masuk'
                )
                    ->description(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                        ? 'Catat pengeluaran uang tunai fisik dari kas kecil/laci kasir.'
                        : 'Catat penerimaan uang tunai fisik masuk ke kas kecil/laci kasir.'
                    )
                    ->extraAttributes(fn (Get $get) => [
                        'class' => $get('tipe_transaksi') === 'KAS_KELUAR'
                            ? 'border-s-8 border-danger-500 dark:border-danger-400 rounded-2xl bg-white dark:bg-gray-900'
                            : 'border-s-8 border-success-500 dark:border-success-400 rounded-2xl bg-white dark:bg-gray-900',
                    ])
                    ->schema([
                        Hidden::make('tipe_transaksi')
                            ->default(fn () => request()->query('type', 'KAS_MASUK'))
                            ->dehydrated(true)
                            ->required(),

                        Hidden::make('periode')
                            ->default(fn () => now()->format('F Y'))
                            ->dehydrated(true),

                        Hidden::make('status')
                            ->default('ACTIVE')
                            ->dehydrated(true),

                        Placeholder::make('tipe_transaksi_placeholder')
                            ->label('Tipe Transaksi')
                            ->content(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                ? '🔴 Kas Keluar (Pengeluaran Tunai)'
                                : '🟢 Kas Masuk (Penerimaan Tunai)'
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
                                    ->default(now())
                                    ->required()
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
                                Select::make('account_kas_id')
                                    ->label(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                        ? 'Keluar dari Kas'
                                        : 'Masuk ke Kas'
                                    )
                                    ->helperText(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                        ? 'Otomatis mengarah ke Kas Kecil (Kredit).'
                                        : 'Otomatis mengarah ke Kas Kecil (Debit).'
                                    )
                                    ->default(fn () => static::getDefaultKasCoaId())
                                    ->relationship(
                                        name: 'accountKas',
                                        titleAttribute: 'nama_akun',
                                        modifyQueryUsing: fn (Builder $query, ?TransKas $record) => $query
                                            ->where(function (Builder $q) use ($record) {
                                                $q->where(function (Builder $sub) {
                                                    $sub->where('account_class', 'Current - Cash')
                                                        ->where('posting', 'Yes');
                                                });

                                                if ($record?->account_kas_id) {
                                                    $q->orWhere('id', $record->account_kas_id);
                                                }
                                            })
                                            ->orderBy('kode_akun')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                    ->searchable(['kode_akun', 'nama_akun'])
                                    ->preload()
                                    ->required(),

                                Select::make('account_bank_id')
                                    ->label(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                        ? 'Keperluan Pengeluaran (Untuk Apa?)'
                                        : 'Sumber Uang (Dari Mana?)'
                                    )
                                    ->placeholder(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                        ? 'Cari beban operasional (BBM, ATK, konsumsi) / setor bank...'
                                        : 'Cari pendapatan, piutang, tarik tunai bank, atau modal...'
                                    )
                                    ->helperText(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                        ? 'Akun lawan tujuan pengeluaran (Debit).'
                                        : 'Akun lawan sumber dana masuk (Kredit).'
                                    )
                                    ->relationship(
                                        name: 'accountBank',
                                        titleAttribute: 'nama_akun',
                                        modifyQueryUsing: fn (Builder $query, ?TransKas $record) => $query
                                            ->where(function (Builder $q) use ($record) {
                                                $q->where('posting', 'Yes');
                                                if ($record?->account_bank_id) {
                                                    $q->orWhere('id', $record->account_bank_id);
                                                }
                                            })
                                            ->orderBy('kode_akun')
                                    )
                                    ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                    ->searchable(['kode_akun', 'nama_akun'])
                                    ->preload()
                                    ->optionsLimit(50)
                                    ->different('account_kas_id')
                                    ->validationMessages([
                                        'different' => 'Akun lawan tidak boleh sama dengan akun kas.',
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
                            ->placeholder(fn (Get $get) => $get('tipe_transaksi') === 'KAS_KELUAR'
                                ? 'Contoh: Beli ATK kantor / BBM operasional / konsumsi rapat...'
                                : 'Contoh: Tarik tunai dari BCA untuk pengisian kas kecil...'
                            ),

                        Section::make('Info Tambahan (Opsional)')
                            ->description('Isi jika transaksi berkaitan dengan karyawan/penanggung jawab atau customer tertentu.')
                            ->collapsed()
                            ->columns(2)
                            ->schema([
                                Select::make('karyawan_id')
                                    ->label('Karyawan / Penanggung Jawab')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih karyawan (opsional)'),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'nama_customer')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih customer (opsional)'),
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
                        'success' => 'KAS_MASUK',
                        'danger'  => 'KAS_KELUAR',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'KAS_KELUAR' ? 'Kas Keluar' : 'Kas Masuk'),

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

                Tables\Columns\TextColumn::make('accountKas.nama_akun')
                    ->label('Akun Kas')
                    ->formatStateUsing(fn ($record) => $record->accountKas
                        ? trim($record->accountKas->kode_akun) . ' - ' . $record->accountKas->nama_akun
                        : '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accountBank.nama_akun')
                    ->label('Sumber / Keperluan')
                    ->formatStateUsing(fn ($record) => $record->accountBank
                        ? trim($record->accountBank->kode_akun) . ' - ' . $record->accountBank->nama_akun
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

                Tables\Filters\SelectFilter::make('gudang')
                    ->label('Gudang')
                    ->options(fn () => TransKas::query()
                        ->whereNotNull('gudang')
                        ->where('gudang', '!=', '')
                        ->distinct()
                        ->orderBy('gudang')
                        ->pluck('gudang', 'gudang')
                        ->toArray()),
            ])
            ->headerActions([
                Tables\Actions\Action::make('kas_masuk')
                    ->label('Kas Masuk')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('success')
                    ->url(fn () => static::getUrl('create', ['type' => 'KAS_MASUK'])),

                Tables\Actions\Action::make('kas_keluar')
                    ->label('Kas Keluar')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->url(fn () => static::getUrl('create', ['type' => 'KAS_KELUAR'])),
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
                        ->url(fn (TransKas $record) => route('finance.voucher.kas.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (TransKas $record) => route('finance.voucher.kas.download', $record)),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn ($record) => app(TreasuryService::class)->deleteKas($record)),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('bottom-end'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransKas::route('/'),
            'create' => Pages\CreateTransKas::route('/create'),
            'edit'   => Pages\EditTransKas::route('/{record}/edit'),
        ];
    }
}
