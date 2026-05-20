<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasBankResource\Pages;
use App\Models\TransKasBank;

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

    protected static ?string $navigationIcon =
        'heroicon-o-building-library';

    public static function getNavigationLabel(): string
    {
        return 'Trans Kas Bank';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(function ($get) {

                    return $get('tipe_transaksi')
                        === 'BANK_KELUAR'

                        ? 'Transaksi Bank Keluar'

                        : 'Transaksi Bank Masuk';
                })

                ->extraAttributes(function ($get) {

    $isKeluar =
        $get('tipe_transaksi')
        === 'BANK_KELUAR';

    return [

        'class' => '

            rounded-xl
            p-4
            shadow-sm

            bg-white
            dark:bg-gray-900

            border-l-[6px]

            ' . (

                $isKeluar

                ? 'border-red-500'
                : 'border-green-500'

            ),

    ];
})

                    ->schema([

                        Hidden::make('tipe_transaksi'),

                        Placeholder::make(
                            'info_tipe'
                        )
                            ->label('Tipe')
                            ->content(fn ($get) =>

                                $get('tipe_transaksi')
                                === 'BANK_KELUAR'

                                ? '🔴 Bank Keluar'

                                : '🟢 Bank Masuk'
                            ),

                        Grid::make(2)
                            ->schema([

                                TextInput::make('gudang')
                                    ->default('UGRMS'),

                                DatePicker::make(
                                    'tanggal_transaksi'
                                )
                                    ->required()
                                    ->default(now()),

                            ]),

                        Grid::make(2)
                            ->schema([

                                Select::make(
                                    'account_bank_id'
                                )
                                    ->label(
                                        'Account Bank (Debit)'
                                    )
                                    ->relationship(
                                        'accountBank',
                                        'nama_akun'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make(
                                    'account_lawan_id'
                                )
                                    ->label(
                                        'Account Lawan (Kredit)'
                                    )
                                    ->relationship(
                                        'accountLawan',
                                        'nama_akun'
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                            ]),

                        Grid::make(2)
                            ->schema([

                                TextInput::make('nominal')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('periode')
                                    ->default(
                                        now()->format('Y')
                                    ),

                            ]),

                        Textarea::make('keterangan')
                            ->rows(4),

                        Section::make('Terima dari:')
                            ->schema([

                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('bank')
                                            ->placeholder(
                                                'BCA / Mandiri / dll'
                                            ),

                                        TextInput::make(
                                            'atas_nama'
                                        )
                                            ->label(
                                                'Bank A/N'
                                            ),

                                    ]),

                                TextInput::make(
                                    'no_rekening'
                                ),

                            ])

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([

                Tables\Columns\TextColumn::make(
                    'kode_transaksi'
                )
                    ->searchable(),

                Tables\Columns\BadgeColumn::make(
                    'tipe_transaksi'
                )
                    ->colors([

                        'success'
                            => 'BANK_MASUK',

                        'danger'
                            => 'BANK_KELUAR',

                    ]),

                Tables\Columns\TextColumn::make(
                    'tanggal_transaksi'
                )
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make(
                    'nominal'
                )
                    ->money('IDR'),

                Tables\Columns\TextColumn::make(
                    'accountBank.nama_akun'
                )
                    ->label('Account Bank'),

                Tables\Columns\TextColumn::make(
                    'accountLawan.nama_akun'
                )
                    ->label('Account Lawan'),

                Tables\Columns\BadgeColumn::make(
                    'status'
                )
                    ->colors([

                        'success'
                            => 'ACTIVE',

                        'danger'
                            => 'INACTIVE',

                    ]),

            ])

            ->headerActions([

                Tables\Actions\Action::make(
                    'bank_masuk'
                )
                    ->label('Bank Masuk')
                    ->icon(
                        'heroicon-o-arrow-down-circle'
                    )
                    ->color('success')
                    ->url(fn () =>
                        static::getUrl(
                            'create',
                            [
                                'type'
                                    => 'BANK_MASUK'
                            ]
                        )
                    ),

                Tables\Actions\Action::make(
                    'bank_keluar'
                )
                    ->label('Bank Keluar')
                    ->icon(
                        'heroicon-o-arrow-up-circle'
                    )
                    ->color('danger')
                    ->url(fn () =>
                        static::getUrl(
                            'create',
                            [
                                'type'
                                    => 'BANK_KELUAR'
                            ]
                        )
                    ),

            ])

            ->actions([

                Tables\Actions\EditAction::make(),

            ]);
    }

    public static function getPages(): array
    {
        return [

            'index'
                => Pages\ListTransKasBanks::route('/'),

            'create'
                => Pages\CreateTransKasBank::route('/create'),

            'edit'
                => Pages\EditTransKasBank::route('/{record}/edit'),
        ];
    }
}