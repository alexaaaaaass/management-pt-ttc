<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvPaymentResource\Pages;
use App\Models\InvPayment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use App\Models\Invoice;
use App\Models\MetodeBayar;
use App\Models\MasterCOA;
use App\Models\Karyawan;

use Filament\Forms\Get;
use Filament\Forms\Set;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;


class InvPaymentResource extends Resource
{
    protected static ?string $model = InvPayment::class;
    protected static ?string $navigationGroup = 'Finance';
     protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([

            Grid::make(3)
                ->schema([

                    /*
                    |--------------------------------------------------------------------------
                    | FORM
                    |--------------------------------------------------------------------------
                    */

                    Section::make('Detail Pembayaran')
                        ->columnSpan(2)
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    Select::make('invoice_id')
                                        ->label('Pilih Invoice')
                                        ->relationship(
                                            'invoice',
                                            'kode_invoice'
                                        )
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) =>
                                                $record->kode_invoice
                                                . ' - '
                                                . ($record->suratJalan?->salesOrder?->customer?->nama_customer ?? '-')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live(),

                                    DatePicker::make(
                                        'tanggal_bayar'
                                    )
                                        ->default(now())
                                        ->required(),

                                ]),

                            Grid::make(2)
                                ->schema([

                                    Select::make(
                                        'metode_bayar_id'
                                    )
                                        ->label('Metode Bayar')
                                        ->relationship(
                                            'metodeBayar',
                                            'metode_pembayaran'
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('coa_id')
                                        ->label('Kas/Bank (COA)')
                                        ->relationship(
                                            'coa',
                                            'nama_akun'
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                ]),

                            Grid::make(2)
                                ->schema([

                                    TextInput::make(
                                        'nominal_bayar'
                                    )
                                        ->numeric()
                                        ->required(),

                                    Select::make(
                                        'karyawan_id'
                                    )
                                        ->label(
                                            'Penerima / Karyawan'
                                        )
                                        ->relationship(
                                            'karyawan',
                                            'nama'
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                ]),

                            Textarea::make('keterangan')
                                ->rows(3),

                        ]),

                    /*
                    |--------------------------------------------------------------------------
                    | INFO TAGIHAN
                    |--------------------------------------------------------------------------
                    */

                    Section::make('Informasi Tagihan')
                        ->schema([

                            Placeholder::make(
                                'total_tagihan'
                            )
                                ->label('Total Tagihan')
                                ->content(function (Get $get) {

                                    $invoice =
                                        Invoice::find(
                                            $get('invoice_id')
                                        );

                                    return 'Rp '
                                        . number_format(
                                            $invoice?->grand_total ?? 0,
                                            0,
                                            ',',
                                            '.'
                                        );
                                }),

                            Placeholder::make(
                                'total_terbayar'
                            )
                                ->label('Total Terbayar')
                                ->content(function (Get $get) {

                                    $invoice =
                                        Invoice::find(
                                            $get('invoice_id')
                                        );

                                    if (!$invoice) {
                                        return 'Rp 0';
                                    }

                                    $totalBayar =
                                        $invoice
                                            ->payments()
                                            ->sum(
                                                'nominal_bayar'
                                            );

                                    return 'Rp '
                                        . number_format(
                                            $totalBayar,
                                            0,
                                            ',',
                                            '.'
                                        );
                                }),

                            Placeholder::make(
                                'sisa_piutang'
                            )
                                ->label('Sisa Piutang')
                                ->content(function (Get $get) {

                                    $invoice =
                                        Invoice::find(
                                            $get('invoice_id')
                                        );

                                    if (!$invoice) {
                                        return 'Rp 0';
                                    }

                                    $totalBayar =
                                        $invoice
                                            ->payments()
                                            ->sum(
                                                'nominal_bayar'
                                            );

                                    $sisa =
                                        $invoice->grand_total
                                        - $totalBayar;

                                    return 'Rp '
                                        . number_format(
                                            $sisa,
                                            0,
                                            ',',
                                            '.'
                                        );
                                }),

                        ]),

                ])

        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Inv Payment';
}
    

    public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make(
                'invoice.kode_invoice'
            )
                ->label('Invoice')
                ->searchable(),

            Tables\Columns\TextColumn::make(
                'invoice.suratJalan.salesOrder.customer.nama_customer'
            )
                ->label('Customer'),

          Tables\Columns\TextColumn::make('nominal_bayar')
    ->label('Nominal Bayar')
    ->money('IDR')
    ->badge()
    ->color('success')
                ->money('IDR'),

            Tables\Columns\TextColumn::make(
                'tanggal_bayar'
            )
                ->date('d/m/Y'),

            Tables\Columns\TextColumn::make(
                'metodeBayar.metode_pembayaran'
            )
                ->label('Metode'),

            // Tables\Columns\BadgeColumn::make(
            //     'invoice.status'
            // )
            //     ->colors([

            //         'success' => 'LUNAS',

            //         'danger' => 'BELUM LUNAS',

            //     ]),

        ])
        ->actions([

            Tables\Actions\EditAction::make(),

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
            'index' => Pages\ListInvPayments::route('/'),
            'create' => Pages\CreateInvPayment::route('/create'),
            'edit' => Pages\EditInvPayment::route('/{record}/edit'),
        ];
    }
}