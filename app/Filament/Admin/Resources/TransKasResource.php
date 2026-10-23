<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasResource\Pages;
use App\Models\TransKas;
use App\Services\Finance\TreasuryService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;

class TransKasResource extends Resource
{
    protected static ?string $model = TransKas::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return 'Trans Kas';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaksi Kas')
                    ->extraAttributes(fn ($get) => [
                        'class' => $get('tipe_transaksi') === 'KAS_MASUK'
                            ? 'border-s-8 border-success-500 dark:border-success-400 rounded-2xl bg-white dark:bg-gray-900'
                            : 'border-s-8 border-danger-500 dark:border-danger-400 rounded-2xl bg-white dark:bg-gray-900'
                    ])
                    ->schema([
                        Placeholder::make('tipe_transaksi_placeholder')
                            ->label('Tipe Transaksi')
                            ->content(fn ($get) => 
                                $get('tipe_transaksi') === 'KAS_MASUK'
                                    ? 'ðŸŸ¢ Kas Masuk (Penerimaan Tunai)'
                                    : ($get('tipe_transaksi') === 'KAS_KELUAR' ? 'ðŸ”´ Kas Keluar (Pengeluaran Tunai)' : 'â€”')
                            ),

                        Grid::make(2)
                            ->schema([
                                Hidden::make('tipe_transaksi')
                                    ->dehydrated(true),
                                TextInput::make('kode_transaksi')
                                    ->label('Kode Transaksi')
                                    ->disabled()
                                    ->default('AUTO'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('gudang')
                                    ->default('UGRMS'),
                                DatePicker::make('tanggal_transaksi')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('periode')
                                    ->default(now()->format('F Y')),

                                Select::make('karyawan_id')
                                    ->label('Karyawan / Penanggung Jawab')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('account_kas_id')
                                    ->label(fn ($get) => $get('tipe_transaksi') === 'KAS_MASUK'
                                        ? 'Akun Kas (Penerima Masuk)'
                                        : 'Akun Kas (Pengeluaran)'
                                    )
                                    ->relationship('accountKas', 'nama_akun')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('account_bank_id')
                                    ->label(fn ($get) => $get('tipe_transaksi') === 'KAS_MASUK'
                                        ? 'Akun Lawan / Sumber Dana (Bank/Modal)'
                                        : 'Akun Lawan / Tujuan Setoran (Bank/Beban)'
                                    )
                                    ->relationship('accountBank', 'nama_akun')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer (Opsional)')
                                    ->relationship('customer', 'nama_customer')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Customer jika ada'),

                                TextInput::make('nominal')
                                    ->label('Nominal Transaksi')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(1)
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'ACTIVE' => 'ACTIVE',
                                        'INACTIVE' => 'INACTIVE',
                                    ])
                                    ->default('ACTIVE'),
                            ]),

                        Textarea::make('keterangan')
                            ->rows(4)
                            ->placeholder('Keterangan transaksi kas...'),
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
                        'success' => 'KAS_MASUK',
                        'danger'  => 'KAS_KELUAR',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('accountKas.nama_akun')
                    ->label('Akun Kas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accountBank.nama_akun')
                    ->label('Akun Lawan')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger'  => 'INACTIVE',
                    ]),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(TreasuryService::class)->deleteKas($record)),
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