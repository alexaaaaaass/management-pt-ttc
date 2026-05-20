<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\SuratJalan;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Placeholder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make('Tambah Invoice')
                ->schema([

                    Grid::make(2)
                        ->schema([

                            Select::make('surat_jalan_id')
                                ->label('Surat Jalan')
                                ->relationship(
                                    'suratJalan',
                                    'kode_surat_jalan'
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {

                                    $sj = SuratJalan::find($state);

                                    if (!$sj) return;

                                    $so = $sj->salesOrder;

                                    $subtotal =
                                        ($so->qty ?? 0)
                                        *
                                        ($so->harga_pcs ?? 0);

                                    $set('subtotal', $subtotal);

                                    $set(
                                        'ongkir',
                                        $so->harga_kirim ?? 0
                                    );
                                }),

                            TextInput::make('kode_invoice')
                                ->disabled()
                                ->dehydrated(false)
                                ->default('AUTO'),

                        ]),

                    Grid::make(2)
                        ->schema([

                            DatePicker::make('tanggal_invoice')
                                ->required(),

                            DatePicker::make('tanggal_jatuh_tempo')
                                ->required(),

                        ]),

                    Grid::make(2)
                        ->schema([

                            TextInput::make('diskon')
    ->numeric()
    ->inputMode('decimal')
    ->suffix('%')
    ->default(0)
    ->live(),

TextInput::make('ppn')
    ->numeric()
    ->inputMode('decimal')
    ->suffix('%')
    ->default(0)
    ->live(),
TextInput::make('subtotal')
    ->label('Subtotal')
    ->numeric()
    ->readOnly()
    ->default(0)
    ->dehydrated(true),

                        ]),

                    Grid::make(2)
                        ->schema([

                            TextInput::make('ongkir')
                                ->numeric()
                                ->default(0)
                                ->live(),

                            TextInput::make('uang_muka')
                                ->numeric()
                                ->default(0)
                                ->live(),

                        ]),

                Section::make('Ringkasan Biaya')
    ->schema([

        Placeholder::make('subtotal_view')
            ->label('Subtotal')
            ->content(function (Get $get) {

                return 'Rp ' . number_format(
                    $get('subtotal') ?? 0,
                    0,
                    ',',
                    '.'
                );
            }),

        Placeholder::make('diskon_view')
            ->label('Diskon')
            ->content(function (Get $get) {

               $subtotal = (float) ($get('subtotal') ?? 0);

$diskonPersen = (float) ($get('diskon') ?? 0);

$diskonNominal =
    ($subtotal * $diskonPersen) / 100;
                return
                    $diskonPersen
                    . '% = Rp '
                    . number_format(
                        $diskonNominal,
                        0,
                        ',',
                        '.'
                    );
            }),

        Placeholder::make('ppn_view')
            ->label('PPN')
            ->content(function (Get $get) {

            $subtotal = (float) ($get('subtotal') ?? 0);

$ppnPersen = (float) ($get('ppn') ?? 0);

$ppnNominal =
    ($subtotal * $ppnPersen) / 100;

                return
                    $ppnPersen
                    . '% = Rp '
                    . number_format(
                        $ppnNominal,
                        0,
                        ',',
                        '.'
                    );
            }),

        Placeholder::make('grand_total_view')
    ->label('Grand Total')
    ->content(function (Get $get) {

        $subtotal = (float) ($get('subtotal') ?? 0);

        $diskon = (float) ($get('diskon') ?? 0);

        $ppn = (float) ($get('ppn') ?? 0);

        $ongkir = (float) ($get('ongkir') ?? 0);

        $diskonNominal =
            ($subtotal * $diskon) / 100;

        $ppnNominal =
            ($subtotal * $ppn) / 100;

        $grandTotal =
            $subtotal
            - $diskonNominal
            + $ppnNominal
            + $ongkir;

        return 'Rp ' . number_format(
            $grandTotal,
            0,
            ',',
            '.'
        );
    }),

    ])

                ])

        ]);
}
    public static function getNavigationLabel(): string
{
    return 'Invoice';
}
    

   public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('kode_invoice')
                ->searchable(),

            Tables\Columns\TextColumn::make(
                'suratJalan.kode_surat_jalan'
            ),

            Tables\Columns\TextColumn::make(
                'suratJalan.salesOrder.customer.nama_customer'
            )
                ->label('Customer'),

            Tables\Columns\TextColumn::make('grand_total')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('sisa_tagihan')
                ->money('IDR'),

            Tables\Columns\TextColumn::make(
                'tanggal_invoice'
            )
                ->date('d/m/Y'),

        ])
        ->actions([

         Tables\Actions\Action::make('detail')
    ->label('Detail')
    ->icon('heroicon-o-eye')
    ->url(fn ($record) =>
        static::getUrl('view', [
            'record' => $record
        ])
    ),

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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
           'view' => Pages\ViewInvoice::route('/{record}'),
           'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}