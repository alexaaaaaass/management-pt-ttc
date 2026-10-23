<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvPaymentResource\Pages;
use App\Models\Invoice;
use App\Models\InvPayment;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
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
                    | FORM PAYMENT
                    |--------------------------------------------------------------------------
                    */

                    Section::make('Input Pembayaran')
                        ->columnSpan(2)
                        ->schema([

                            Grid::make(2)
                                ->schema([

                                    Select::make('invoice_id')
    ->relationship(
        'invoice',
        'kode_invoice'
    )
    ->default(request('invoice'))
    ->disabled(fn () => request()->has('invoice'))
    ->dehydrated()
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) =>
                                                $record->kode_invoice .
                                                ' â€¢ ' .
                                                ($record->suratJalan?->salesOrder?->customer?->nama_customer ?? '-')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live(),

                                    DatePicker::make('tanggal_bayar')
                                        ->default(now())
                                        ->required(),

                                ]),

                            Grid::make(2)
                                ->schema([

                                    Select::make('metode_bayar_id')
                                        ->relationship(
                                            'metodeBayar',
                                            'metode_pembayaran'
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('coa_id')
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

                                Checkbox::make('full_payment')
    ->label('Bayar Full')
    ->live()
    ->dehydrated(false)
    ->afterStateUpdated(function (
        $state,
        Set $set,
        Get $get
    ) {

        if (!$state) {

            return;

        }

        $invoice = Invoice::find(
            $get('invoice_id')
        );

        $set(
            'nominal_bayar',
            $invoice?->sisa_tagihan
        );

    }),
                                    TextInput::make('nominal_bayar')
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->rule(function (Get $get, ?InvPayment $record) {
                                            return function (
                                                $attribute,
                                                $value,
                                                $fail
                                            ) use ($get, $record) {
                                                $invoice = Invoice::find($get('invoice_id'));
                                                if (!$invoice) {
                                                    return;
                                                }

                                                $maxAllowed = (float) $invoice->sisa_tagihan;
                                                if ($record && $record->invoice_id == $invoice->id) {
                                                    $maxAllowed += (float) $record->nominal_bayar;
                                                }

                                                if ((float) $value > $maxAllowed) {
                                                    $fail('Nominal pembayaran melebihi sisa tagihan (Maks: Rp ' . number_format($maxAllowed, 0, ',', '.') . ').');
                                                }
                                            };
                                        })
                                        ->default(function (Get $get) {
                                            $invoice = Invoice::find(
                                                $get('invoice_id')
                                            );

                                            return $invoice?->sisa_tagihan;
                                        }),

                                    Select::make('karyawan_id')
                                        ->relationship(
                                            'karyawan',
                                            'nama'
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                ]),

                            Textarea::make('keterangan')
                                ->rows(3)

                        ]),

                    /*
                    |--------------------------------------------------------------------------
                    | SIDEBAR INFO
                    |--------------------------------------------------------------------------
                    */

                    Section::make('Ringkasan Invoice')
    ->visible(fn (Get $get) => filled($get('invoice_id')))
    ->schema([

        Placeholder::make('customer')
            ->label('Customer')
            ->content(function (Get $get) {

                return Invoice::find(
                    $get('invoice_id')
                )?->customer?->nama_customer;

            }),

        Placeholder::make('grand_total')
            ->label('Grand Total')
            ->content(function (Get $get) {

                $invoice = Invoice::find(
                    $get('invoice_id')
                );

                return 'Rp '.number_format(
                    $invoice?->grand_total ?? 0,
                    0,
                    ',',
                    '.'
                );

            }),

        Placeholder::make('paid')
            ->label('Sudah Dibayar')
            ->content(function (Get $get) {

                $invoice = Invoice::find(
                    $get('invoice_id')
                );

                return 'Rp '.number_format(
                    $invoice?->total_payment ?? 0,
                    0,
                    ',',
                    '.'
                );

            }),

        Placeholder::make('outstanding')
            ->label('Outstanding')
            ->content(function (Get $get) {

                $invoice = Invoice::find(
                    $get('invoice_id')
                );

                return 'Rp '.number_format(
                    $invoice?->sisa_tagihan ?? 0,
                    0,
                    ',',
                    '.'
                );

            }),

    ])
    ->columns(2),

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
            Tables\Columns\BadgeColumn::make('invoice.status')
                ->label('Status Invoice')
                ->color(fn (string $state): string => match ($state) {
                    'Paid' => 'success',
                    'Partial' => 'warning',
                    'Unpaid' => 'danger',
                    default => 'gray',
                })

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
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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