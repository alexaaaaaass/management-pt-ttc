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

    public static function getNavigationLabel(): string
    {
        return 'Trans Kas Bank';
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
                            ? 'ðŸ”´ Bank Keluar (Pengeluaran via Rekening Bank)'
                            : 'ðŸŸ¢ Bank Masuk (Penerimaan via Rekening Bank)'
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
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('account_lawan_id')
                                ->label(fn ($get) => $get('tipe_transaksi') === 'BANK_KELUAR'
                                    ? 'Akun Lawan (Debit / Tujuan Pengeluaran/Beban)'
                                    : 'Akun Lawan (Kredit / Sumber Dana/Pendapatan)'
                                )
                                ->relationship('accountLawan', 'nama_akun')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('nominal')
                                ->label('Nominal Transaksi')
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(1)
                                ->required(),

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
                    ->money('IDR')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(TreasuryService::class)->deleteBank($record)),
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