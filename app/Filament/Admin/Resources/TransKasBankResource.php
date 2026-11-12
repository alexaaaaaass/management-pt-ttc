<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasBankResource\Pages;
use App\Models\TransKasBank;
use App\Services\Finance\TreasuryService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;

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
                Section::make(fn ($get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                    ? 'Transaksi Bank Keluar'
                    : 'Transaksi Bank Masuk'
                )
                ->extraAttributes(fn ($get) => [
                    'class' => 'rounded-xl p-4 shadow-sm bg-white dark:bg-gray-900 border-l-[6px] ' .
                        ($get('tipe_transaksi') === 'BANK_KELUAR' ? 'border-red-500' : 'border-green-500')
                ])
                ->schema([
                    Hidden::make('tipe_transaksi')
                        ->default(request()->query('type') ?? 'BANK_MASUK')
                        ->dehydrated()
                        ->required(),

                    Placeholder::make('info_tipe')
                        ->label('Tipe Transaksi')
                        ->content(fn ($get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                            ? '🔴 Bank Keluar (Pengeluaran via Rekening Bank)'
                            : '🟢 Bank Masuk (Penerimaan via Rekening Bank)'
                        ),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('kode_transaksi')
                                ->label('Kode Transaksi')
                                ->disabled()
                                ->default('AUTO'),

                            TextInput::make('gudang')
                                ->default('UGRMS'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            DatePicker::make('tanggal_transaksi')
                                ->label('Tanggal Transaksi')
                                ->required()
                                ->default(now()),

                            TextInput::make('periode')
                                ->default(now()->format('F Y')),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Select::make('account_bank_id')
                                ->label(fn ($get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                    ? 'Akun Bank (Kredit / Rekening Asal Pengeluaran)'
                                    : 'Akun Bank (Debit / Rekening Tujuan Masuk)'
                                )
                                ->relationship('accountBank', 'nama_akun')
                                ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                ->searchable(['kode_akun', 'nama_akun'])
                                ->preload()
                                ->required(),

                            Select::make('account_lawan_id')
                                ->label(fn ($get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                    ? 'Akun Lawan (Debit / Tujuan Pengeluaran/Beban)'
                                    : 'Akun Lawan (Kredit / Sumber Dana/Pendapatan)'
                                )
                                ->relationship('accountLawan', 'nama_akun')
                                ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->kode_akun) . ' - ' . $record->nama_akun)
                                ->searchable(['kode_akun', 'nama_akun'])
                                ->preload()
                                ->required(),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('nominal')
                                ->label('Nominal Transaksi')
                                ->prefix('Rp')
                                ->required()
                                ->placeholder('Contoh: 503219386.91 atau 503,219,386.91')
                                ->helperText('💡 Masukkan nominal transaksi. Mendukung pecahan desimal sen (titik atau koma, misal: 503219386.91 atau copy-paste 503,219,386.91).')
                                ->extraInputAttributes(['inputmode' => 'decimal'])
                                ->formatStateUsing(fn ($state) => $state !== null ? (string) $state : null)
                                ->dehydrateStateUsing(function ($state) {
                                    if (is_numeric($state)) {
                                        return (float) $state;
                                    }
                                    if (is_string($state)) {
                                        $cleaned = trim($state);
                                        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $cleaned)) {
                                            $cleaned = str_replace(',', '', $cleaned);
                                        } elseif (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $cleaned)) {
                                            $cleaned = str_replace('.', '', $cleaned);
                                            $cleaned = str_replace(',', '.', $cleaned);
                                        } elseif (strpos($cleaned, ',') !== false && strpos($cleaned, '.') === false) {
                                            $cleaned = str_replace(',', '.', $cleaned);
                                        } else {
                                            $cleaned = preg_replace('/[^\d.]/', '', $cleaned);
                                        }
                                        return (float) $cleaned;
                                    }
                                    return (float) $state;
                                })
                                ->rules([
                                    'required',
                                    function ($attribute, $value, $fail) {
                                        $clean = is_string($value) ? trim($value) : $value;
                                        if (is_string($clean)) {
                                            if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $clean)) {
                                                $clean = str_replace(',', '', $clean);
                                            } elseif (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $clean)) {
                                                $clean = str_replace('.', '', $clean);
                                                $clean = str_replace(',', '.', $clean);
                                            } elseif (strpos($clean, ',') !== false && strpos($clean, '.') === false) {
                                                $clean = str_replace(',', '.', $clean);
                                            } else {
                                                $clean = preg_replace('/[^\d.]/', '', $clean);
                                            }
                                        }
                                        if (!is_numeric($clean) || (float) $clean <= 0) {
                                            $fail('Nominal transaksi harus berupa angka lebih dari 0.');
                                        }
                                    },
                                ]),

                            Select::make('status')
                                ->options([
                                    'ACTIVE'   => 'ACTIVE',
                                    'INACTIVE' => 'INACTIVE',
                                ])
                                ->default('ACTIVE'),
                        ]),

                    Textarea::make('keterangan')
                        ->rows(3)
                        ->placeholder('Keterangan transaksi bank...'),

                    Section::make('Informasi Rekening / Customer')
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
                                        ->label('Nama Bank Penerima/Pengirim')
                                        ->placeholder('BCA / Mandiri / BNI / dll'),

                                    TextInput::make('atas_nama')
                                        ->label('Atas Nama (A/N)'),
                                ]),

                            TextInput::make('no_rekening')
                                ->label('Nomor Rekening'),
                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('tipe_transaksi')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'BANK_MASUK',
                        'danger'  => 'BANK_KELUAR',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(function ($state) {
                        $val = (float) $state;
                        return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('accountBank.nama_akun')
                    ->label('Akun Bank')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accountLawan.nama_akun')
                    ->label('Akun Lawan')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger'  => 'INACTIVE',
                    ]),
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