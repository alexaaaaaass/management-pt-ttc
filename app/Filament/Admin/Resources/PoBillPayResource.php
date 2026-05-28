<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PoBillPayResource\Pages;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Models\MetodeBayar;
use App\Models\PoBilling;
use App\Models\PoBillPay;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PoBillPayResource extends Resource
{
    protected static ?string $model = PoBillPay::class;
     protected static ?string $navigationGroup = 'Finance';
protected static ?int $navigationSort = 12;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make(
                'Data Header Transaksi'
            )

            ->schema([

                Grid::make(4)
                    ->schema([

                        DatePicker::make(
                            'tanggal_pembayaran'
                        )
                            ->required()
                            ->default(now()),

                        Select::make(
                            'po_billing_id'
                        )

                            ->label(
                                'Billing Reference'
                            )

                            ->relationship(
                                'poBilling',
                                'kode_tagihan'
                            )

                            ->searchable()

                            ->live()

                            ->afterStateUpdated(
                                function (
                                    $state,
                                    Set $set
                                ) {

                                    $bill =
                                        PoBilling::find(
                                            $state
                                        );

                                    if (!$bill) {
                                        return;
                                    }

                                    $set(
                                        'gudang',
                                        'UGRMS'
                                    );

                                    $set(
                                        'periode',
                                        $bill->periode
                                    );

                                    $set(
                                        'total_tagihan',
                                        $bill->grand_total
                                    );
                                }
                            ),

                        TextInput::make(
                            'gudang'
                        ),

                        TextInput::make(
                            'periode'
                        ),
                    ]),

                Select::make(
                    'karyawan_id'
                )

                    ->label('PIC / Karyawan')

                    ->options(
                        Karyawan::pluck(
                            'nama',
                            'id'
                        )
                    )

                    ->searchable(),

                TextInput::make(
                    'total_tagihan'
                )
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(),
            ]),

            Section::make(
                'Rincian Pembayaran'
            )

            ->schema([

                Repeater::make('items')

                    ->relationship()

                    ->schema([

                        Grid::make(2)
                            ->schema([

                                Select::make(
                                    'metode_bayar_id'
                                )

                                    ->label(
                                        'Metode Pembayaran'
                                    )

                                    ->options(
                                        MetodeBayar::pluck(
                                            'metode_pembayaran',
                                            'id'
                                        )
                                    )

                                    ->searchable(),

                                TextInput::make(
                                    'nominal_pembayaran'
                                )
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live(),
                            ]),

                        Grid::make(2)
                            ->schema([

                                Select::make(
                                    'coa_debit_id'
                                )

                                    ->label(
                                        'Akun Debit'
                                    )

                                    ->options(
                                        MasterCOA::pluck(
                                            'nama_akun',
                                            'id'
                                        )
                                    )

                                    ->searchable(),

                                Select::make(
                                    'coa_kredit_id'
                                )

                                    ->label(
                                        'Akun Kredit'
                                    )

                                    ->options(
                                        MasterCOA::pluck(
                                            'nama_akun',
                                            'id'
                                        )
                                    )

                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->schema([

                                TextInput::make(
                                    'mata_uang'
                                )
                                    ->default('IDR'),

                                TextInput::make(
                                    'nama_bank'
                                ),
                            ]),

                        Grid::make(2)
                            ->schema([

                                TextInput::make(
                                    'no_rekening'
                                ),

                                TextInput::make(
                                    'atas_nama'
                                ),
                            ]),

                        Textarea::make(
                            'memo'
                        ),

                    ])

                    ->live()

                    ->afterStateUpdated(
                        function (
                            $state,
                            Set $set
                        ) {

                            $total = collect(
                                $state
                            )->sum(
                                'nominal_pembayaran'
                            );

                            $set(
                                'total_pembayaran',
                                $total
                            );
                        }
                    ),
            ]),

            Section::make(
                'Summary'
            )

            ->schema([

                TextInput::make(
                    'total_pembayaran'
                )
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(),
            ]),
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Po Bill Pay';
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPoBillPays::route('/'),
            'create' => Pages\CreatePoBillPay::route('/create'),
            'edit' => Pages\EditPoBillPay::route('/{record}/edit'),
        ];
    }
}