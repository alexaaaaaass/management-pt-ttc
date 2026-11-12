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
                                                ' • ' .
                                                ($record->suratJalan?->salesOrder?->customer?->nama_customer ?? '-')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if (! $state) {
                                                return;
                                            }

                                            $invoice = Invoice::with(['suratJalan.salesOrder.customer'])->find($state);
                                            if (! $invoice) {
                                                return;
                                            }

                                            $custName = $invoice->suratJalan?->salesOrder?->customer?->nama_customer ?? '';
                                            $autoKet = 'Pembayaran Invoice ' . $invoice->kode_invoice . ($custName ? ' - ' . $custName : '');
                                            $currentKet = $get('keterangan');
                                            if (blank($currentKet) || str_starts_with($currentKet, 'Pembayaran Invoice')) {
                                                $set('keterangan', $autoKet);
                                            }

                                            if ($get('full_payment')) {
                                                $set('nominal_bayar', $invoice->sisa_tagihan);
                                            }
                                        }),

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
                                        ->label('Nominal Bayar')
                                        ->prefix('Rp')
                                        ->currencyMask(2)
                                        ->placeholder('0.00')
                                        ->required()
                                        ->extraInputAttributes(['onwheel' => 'this.blur()'])
                                        ->helperText('Otomatis diberi pemisah ribuan. Dibulatkan ke sisa tagihan jika selisih ≤ Rp 1.000')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Get $get, Set $set, ?InvPayment $record) {
                                            $invoice = Invoice::find($get('invoice_id'));
                                            if (!$invoice) {
                                                return;
                                            }

                                            $maxAllowed = (float) $invoice->sisa_tagihan;
                                            if ($record && $record->invoice_id == $invoice->id) {
                                                $maxAllowed += (float) $record->nominal_bayar;
                                            }

                                            $val = \App\Support\MoneyHelper::clean($state) ?? 0;
                                            if (
                                                $maxAllowed > 0 &&
                                                $val > 0 &&
                                                abs($maxAllowed - $val) > 0 &&
                                                abs($maxAllowed - $val) <= \App\Services\Finance\InvoicePaymentService::PAYMENT_TOLERANCE
                                            ) {
                                                $diff = abs($maxAllowed - $val);
                                                $set('nominal_bayar', $maxAllowed);

                                                \Filament\Notifications\Notification::make()
                                                    ->title('Auto-Snap Nominal Pembayaran')
                                                    ->body('Selisih Rp ' . number_format($diff, 0, ',', '.') . ' berada dalam batas toleransi (≤ Rp 1.000). Nominal otomatis disesuaikan menjadi Rp ' . number_format($maxAllowed, 0, ',', '.') . '.')
                                                    ->info()
                                                    ->send();
                                            }
                                        })
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

                                                $val = \App\Support\MoneyHelper::clean($value) ?? 0;
                                                if ($val > ($maxAllowed + \App\Services\Finance\InvoicePaymentService::PAYMENT_TOLERANCE)) {
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
                                ->label('Keterangan / Catatan Pembayaran (Opsional)')
                                ->placeholder('Otomatis: Pembayaran Invoice ... (Boleh dikosongkan jika tidak ada memo/bukti transfer khusus)')
                                ->helperText('💡 Otomatis terisi saat invoice dipilih. Boleh diedit jika ingin mencatat no. referensi transfer / catatan bank.')
                                ->default(function () {
                                    if (request()->has('invoice')) {
                                        $inv = Invoice::with(['suratJalan.salesOrder.customer'])->find(request('invoice'));
                                        if ($inv) {
                                            $custName = $inv->suratJalan?->salesOrder?->customer?->nama_customer ?? '';
                                            return 'Pembayaran Invoice ' . $inv->kode_invoice . ($custName ? ' - ' . $custName : '');
                                        }
                                    }
                                    return null;
                                })
                                ->rows(3),

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
    return 'Pembayaran Bon/Cicilan';
}

    public static function getModelLabel(): string
    {
        return 'Pembayaran Bon/Cicilan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pembayaran Bon/Cicilan';
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_bayar', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice.kode_invoice')
                    ->label('No. Invoice')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice.order_id')
                    ->label('Order ID')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->getStateUsing(fn ($record) => $record->invoice?->customer?->nama_customer ?? $record->invoice?->suratJalan?->salesOrder?->customer?->nama_customer ?? '-')
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('invoice.customer', fn ($q) => $q->where('nama_customer', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tgl. Bayar')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('coa.nama_akun')
                    ->label('Rekening Bank')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal_bayar')
                    ->label('Nominal Diterima')
                    ->formatStateUsing(function ($state) {
                        $val = (float) $state;
                        return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                    })
                    ->alignRight()
                    ->weight('bold')
                    ->color('success')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Pembayaran Diterima')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan (DP / Termin / Pelunasan)')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->keterangan)
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice.status')
                    ->label('Status Invoice')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(\App\Services\Finance\InvoicePaymentService::class)->deletePayment($record)),
            ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
                ->button()
                ->dropdownPlacement('bottom-end'),
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