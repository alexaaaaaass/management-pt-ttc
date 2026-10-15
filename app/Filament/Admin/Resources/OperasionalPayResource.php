<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperasionalPayResource\Pages;
use App\Models\OperasionalPay;
use App\Services\Finance\TreasuryService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperasionalPayResource extends Resource
{
    protected static ?string $model = OperasionalPay::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationLabel(): string
    {
        return 'Operasional Pay';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaksi Pengeluaran Operasional')
                    ->extraAttributes([
                        'class' => 'rounded-3xl border-t-4 border-purple-500 shadow-sm bg-white dark:bg-gray-900',
                    ])
                    ->schema([
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
                                Select::make('account_kas_id')
                                    ->label('Akun Kas Pembayar (Kredit)')
                                    ->relationship('accountKas', 'nama_akun')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('account_beban_id')
                                    ->label('Akun Beban Operasional (Debit)')
                                    ->relationship('accountBeban', 'nama_akun')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('karyawan_id')
                                    ->label('Karyawan Pemohon / PIC')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('nominal')
                                    ->label('Nominal Biaya')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(1)
                                    ->required(),
                            ]),

                        Section::make('Detail Kendaraan / Mesin (Opsional)')
                            ->collapsed()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nopol')
                                            ->label('No. Polisi Kendaraan')
                                            ->placeholder('B 1234 ABC'),

                                        TextInput::make('odometer')
                                            ->label('Odometer / KM'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('mesin')
                                            ->label('Mesin / Alat'),

                                        TextInput::make('jenis')
                                            ->label('Jenis Pengeluaran'),

                                        TextInput::make('kode')
                                            ->label('Kode Referensi'),
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'ACTIVE'   => 'ACTIVE',
                                        'INACTIVE' => 'INACTIVE',
                                    ])
                                    ->default('ACTIVE'),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Keperluan')
                            ->rows(3)
                            ->placeholder('Contoh: Beli bensin operasional pengiriman, ATK kantor, dll...'),
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

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan PIC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accountKas.nama_akun')
                    ->label('Akun Kas')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accountBeban.nama_akun')
                    ->label('Akun Beban')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger'  => 'INACTIVE',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(TreasuryService::class)->deleteOperasional($record)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOperasionalPays::route('/'),
            'create' => Pages\CreateOperasionalPay::route('/create'),
            'edit'   => Pages\EditOperasionalPay::route('/{record}/edit'),
        ];
    }
}