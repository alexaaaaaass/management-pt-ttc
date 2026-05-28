<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OperasionalPayResource\Pages;
use App\Models\OperasionalPay;
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
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

 public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make('Transaksi Operasional')
                ->extraAttributes([

                    'class' => '

                        rounded-3xl
                        border-t-4
                        border-purple-500

                    '

                ])

                ->schema([

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

                            TextInput::make('periode')
                                ->default(
                                    now()->format('Y')
                                ),

                            Select::make(
                                'account_kas_id'
                            )
                                ->label('Account Kas')
                                ->relationship(
                                    'accountKas',
                                    'nama_akun'
                                )
                                ->searchable()
                                ->preload()
                                ->required(),

                        ]),

                    Grid::make(2)
                        ->schema([

                            Select::make(
                                'account_beban_id'
                            )
                                ->label('Account Beban')
                                ->relationship(
                                    'accountBeban',
                                    'nama_akun'
                                )
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make(
                                'karyawan_id'
                            )
                                ->label('Karyawan')
                                ->relationship(
                                    'karyawan',
                                    'nama'
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

                            TextInput::make('nopol')
                                ->placeholder(
                                    'B 1234 ABC'
                                ),

                        ]),

                    Grid::make(2)
                        ->schema([

                            TextInput::make(
                                'odometer'
                            ),

                            TextInput::make(
                                'mesin'
                            ),

                        ]),

                    Grid::make(2)
                        ->schema([

                            TextInput::make(
                                'kode'
                            ),

                            TextInput::make(
                                'jenis'
                            ),

                        ]),

                    Textarea::make(
                        'keterangan'
                    )
                        ->rows(4),

                ])
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Operasional Pay';
}
    

   public static function table(Table $table): Table
{
    return $table

        ->columns([

            Tables\Columns\TextColumn::make(
                'kode_transaksi'
            )
                ->searchable()
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make(
                'tanggal_transaksi'
            )
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make(
                'karyawan.nama'
            )
                ->label('Karyawan')
                ->searchable(),

            Tables\Columns\TextColumn::make(
                'accountKas.nama_akun'
            )
                ->label('Kas'),

            Tables\Columns\TextColumn::make(
                'accountBeban.nama_akun'
            )
                ->label('Beban'),

            Tables\Columns\TextColumn::make(
                'nominal'
            )
                ->money('IDR'),

            Tables\Columns\BadgeColumn::make(
                'status'
            )
                ->colors([

                    'success'
                        => 'ACTIVE',

                    'danger'
                        => 'INACTIVE',

                ])

        ])

        ->actions([

            Tables\Actions\EditAction::make(),

            Tables\Actions\ViewAction::make(),

        ]);
}
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperasionalPays::route('/'),
            'create' => Pages\CreateOperasionalPay::route('/create'),
            'edit' => Pages\EditOperasionalPay::route('/{record}/edit'),
        ];
    }
}