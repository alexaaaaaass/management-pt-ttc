<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

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
                Wizard::make([

                    // ==========================================
                    // 🔥 TAHAP 1: SURAT JALAN & DETAIL ORDER
                    // ==========================================
                    Wizard\Step::make('Surat Jalan & Detail Order')
                        ->icon('heroicon-o-document-text')
                        ->description('Pilih Surat Jalan terkirim dan tinjau item pengiriman')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('surat_jalan_id')
                                        ->label('Surat Jalan')
                                        ->relationship(
                                            'suratJalan',
                                            'kode_surat_jalan',
                                            fn ($query, ?Invoice $record) => $query->where(function ($q) use ($record) {
                                                $q->whereDoesntHave('invoice');
                                                if ($record && $record->surat_jalan_id) {
                                                    $q->orWhere('id', $record->surat_jalan_id);
                                                }
                                            })
                                        )
                                        ->disabled(fn (string $operation) => $operation === 'edit')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $sj = SuratJalan::with([
                                                'salesOrder.customer',
                                                'salesOrder.itemable',
                                                'salesOrder.items.material',
                                                'items.salesOrderItem',
                                            ])->find($state);

                                            if (!$sj) {
                                                $set('preview_items', []);
                                                return;
                                            }

                                            $so = $sj->salesOrder;

                                            // Set Preview Items
                                            if ($so && $so->mode === 'MULTI_MATERIAL') {
                                                $rows = [];
                                                foreach ($so->items as $item) {
                                                    $rows[] = [
                                                        'material' => $item->material?->nama_master_item,
                                                        'qty'      => $item->qty,
                                                        'harga'    => $item->harga,
                                                    ];
                                                }
                                                $set('preview_items', $rows);
                                            } else {
                                                $set('preview_items', []);
                                            }

                                            // Perhitungan Subtotal via InvoiceService
                                            $subtotal = app(\App\Services\Finance\InvoiceService::class)->calculateSubtotal($sj);

                                            $set('subtotal', $subtotal);

                                            $set('ongkir', $so?->harga_kirim ?? 0);

                                            /*
                                            |--------------------------------------------------------------------------
                                            | Hidden data
                                            |--------------------------------------------------------------------------
                                            */
                                            $set('customer_id', $so?->customer_id);
                                            $set('sales_order_id', $so?->id);
                                            $set('no_po_customer', $so?->no_po_customer);
                                        }),

                                    TextInput::make('kode_invoice')
                                        ->label('Kode Invoice')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->default('AUTO'),
                                ]),

                            Section::make('Informasi Order Terkait')
                                ->schema([
                                    Placeholder::make('customer_info')
                                        ->label('Customer')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::with('salesOrder.customer')
                                                ->find($get('surat_jalan_id'));
                                            return $sj?->salesOrder?->customer?->nama_customer ?? '-';
                                        }),

                                    Placeholder::make('sales_order_info')
                                        ->label('Sales Order')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::with('salesOrder')
                                                ->find($get('surat_jalan_id'));
                                            return $sj?->salesOrder?->no_sales_order ?? '-';
                                        }),

                                    Placeholder::make('po_customer_info')
                                        ->label('PO Customer')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::with('salesOrder')
                                                ->find($get('surat_jalan_id'));
                                            return $sj?->salesOrder?->no_po_customer ?? '-';
                                        }),

                                    Placeholder::make('produk_info')
                                        ->label('Produk')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::with([
                                                'salesOrder.itemable',
                                                'salesOrder.items.material',
                                            ])->find($get('surat_jalan_id'));

                                            if (! $sj) {
                                                return '-';
                                            }

                                            $so = $sj->salesOrder;

                                            if (! $so) {
                                                return '-';
                                            }

                                            return match ($so->mode) {
                                                'SPK' =>
                                                    $so->itemable?->nama_barang ?? '-',
                                                'SINGLE_MATERIAL' =>
                                                    $so->items->first()?->material?->nama_master_item ?? '-',
                                                'MULTI_MATERIAL' =>
                                                    $so->items->pluck('material.nama_master_item')->filter()->implode(', '),
                                                default => '-',
                                            };
                                        }),

                                    Placeholder::make('qty_kirim_info')
                                        ->label('Qty Kirim')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::find($get('surat_jalan_id'));
                                            return number_format($sj?->qty_pengiriman ?? 0);
                                        }),

                                    Placeholder::make('harga_info')
                                        ->label('Harga / PCS')
                                        ->content(function (Get $get) {
                                            $sj = SuratJalan::with('salesOrder')
                                                ->find($get('surat_jalan_id'));
                                            return 'Rp ' . number_format($sj?->salesOrder?->harga_pcs ?? 0, 0, ',', '.');
                                        }),
                                ])
                                ->columns(3)
                                ->visible(fn(Get $get) => filled($get('surat_jalan_id'))),

                            // Section Detail Material untuk Mode MULTI_MATERIAL
                            Section::make('Detail Material')
                                ->visible(function (Get $get) {
                                    $sj = SuratJalan::with('salesOrder.items')->find($get('surat_jalan_id'));
                                    if (!$sj || !$sj->salesOrder) {
                                        return false;
                                    }
                                    return $sj->salesOrder->mode === 'MULTI_MATERIAL';
                                })
                                ->schema([
                                    Repeater::make('preview_items')
                                        ->dehydrated(false)
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columns(4)
                                        ->schema([
                                            TextInput::make('material')
                                                ->disabled(),

                                            TextInput::make('qty')
                                                ->disabled(),

                                            TextInput::make('harga')
                                                ->prefix('Rp')
                                                ->disabled(),

                                            Placeholder::make('subtotal')
                                                ->content(fn(Get $get) =>
                                                    'Rp ' . number_format(
                                                        ((float)($get('qty') ?? 0)) * ((float)($get('harga') ?? 0)),
                                                        0,
                                                        ',',
                                                        '.'
                                                    )
                                                ),
                                        ]),
                                ]),

                            Forms\Components\Hidden::make('customer_id'),
                            Forms\Components\Hidden::make('sales_order_id'),
                            Forms\Components\Hidden::make('no_po_customer'),
                        ]),

                    // ==========================================
                    // 🔥 TAHAP 2: BIAYA, DISKON & TOTAL TAGIHAN
                    // ==========================================
                    Wizard\Step::make('Biaya & Tagihan')
                        ->icon('heroicon-o-banknotes')
                        ->description('Tanggal invoice, jatuh tempo, diskon, PPN, dan rincian nominal tagihan')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('tanggal_invoice')
                                        ->label('Tanggal Invoice')
                                        ->default(now())
                                        ->required(),

                                    DatePicker::make('tanggal_jatuh_tempo')
                                        ->label('Tanggal Jatuh Tempo')
                                        ->default(now()->addDays(30))
                                        ->required(),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('subtotal')
                                        ->label('Subtotal (DPP)')
                                        ->prefix('Rp')
                                        ->mask(\Filament\Support\RawJs::make('$money($input, ",", ".", 0)'))
                                        ->stripCharacters('.')
                                        ->readOnly()
                                        ->default(0)
                                        ->dehydrated(true),

                                    TextInput::make('diskon')
                                        ->label('Diskon (%)')
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->suffix('%')
                                        ->default(0)
                                        ->live(),

                                    TextInput::make('ppn')
                                        ->label('PPN (%)')
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->suffix('%')
                                        ->default(0)
                                        ->live(),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    TextInput::make('ongkir')
                                        ->label('Ongkos Kirim')
                                        ->prefix('Rp')
                                        ->mask(\Filament\Support\RawJs::make('$money($input, ",", ".", 0)'))
                                        ->stripCharacters('.')
                                        ->default(0)
                                        ->live(),

                                    TextInput::make('uang_muka')
                                        ->label('Uang Muka (DP)')
                                        ->prefix('Rp')
                                        ->mask(\Filament\Support\RawJs::make('$money($input, ",", ".", 0)'))
                                        ->stripCharacters('.')
                                        ->default(0)
                                        ->live(),
                                ]),

                            Section::make('Ringkasan Biaya Tagihan')
                                ->schema([
                                    Placeholder::make('subtotal_view')
                                        ->label('Subtotal')
                                        ->content(function (Get $get) {
                                            return 'Rp ' . number_format((float) ($get('subtotal') ?? 0), 0, ',', '.');
                                        }),

                                    Placeholder::make('diskon_view')
                                        ->label('Diskon')
                                        ->content(function (Get $get) {
                                            $subtotal = (float) ($get('subtotal') ?? 0);
                                            $diskonPersen = (float) ($get('diskon') ?? 0);
                                            $diskonNominal = ($subtotal * $diskonPersen) / 100;
                                            return $diskonPersen . '% = Rp ' . number_format($diskonNominal, 0, ',', '.');
                                        }),

                                    Placeholder::make('ppn_view')
                                        ->label('PPN')
                                        ->content(function (Get $get) {
                                            $subtotal = (float) ($get('subtotal') ?? 0);
                                            $ppnPersen = (float) ($get('ppn') ?? 0);
                                            $ppnNominal = ($subtotal * $ppnPersen) / 100;
                                            return $ppnPersen . '% = Rp ' . number_format($ppnNominal, 0, ',', '.');
                                        }),

                                    Placeholder::make('grand_total_view')
                                        ->label('Grand Total Tagihan')
                                        ->extraAttributes(['class' => 'text-lg font-bold', 'style' => 'color: #007bff; font-size: 2.25rem;'])
                                        ->content(function (Get $get) {
                                            $subtotal = (float) ($get('subtotal') ?? 0);
                                            $diskon = (float) ($get('diskon') ?? 0);
                                            $ppn = (float) ($get('ppn') ?? 0);
                                            $ongkir = (float) ($get('ongkir') ?? 0);

                                            $diskonNominal = ($subtotal * $diskon) / 100;
                                            $ppnNominal = ($subtotal * $ppn) / 100;
                                            $grandTotal = $subtotal - $diskonNominal + $ppnNominal + $ongkir;

                                            return 'Rp ' . number_format($grandTotal, 0, ',', '.');
                                        }),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->skippable()
                ->columnSpanFull(),
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

            Tables\Columns\TextColumn::make('suratJalan.kode_surat_jalan'),

            Tables\Columns\TextColumn::make('suratJalan.salesOrder.customer.nama_customer')
                ->label('Customer'),

            Tables\Columns\TextColumn::make('grand_total')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Paid' => 'success',
                    'Partial' => 'warning',
                    'Unpaid' => 'danger',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('status_pembayaran')
                ->label('Status Pembayaran')
                ->badge()
                ->getStateUsing(fn ($record) =>
                    $record->sisa_tagihan <= 0 ? 'Lunas' : 'Belum Lunas'
                )
                ->color(fn ($record) =>
                    $record->sisa_tagihan <= 0 ? 'success' : 'danger'
                ),

            Tables\Columns\TextColumn::make('tanggal_invoice')
                ->date('d/m/Y'),
        ])
        ->actions([
            Tables\Actions\Action::make('detail')
                ->label('Detail')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) => static::getUrl('view', [
                    'record' => $record,
                ])),

            Tables\Actions\EditAction::make(),

            Tables\Actions\DeleteAction::make()
                ->hidden(fn ($record) =>
                    ! app(\App\Services\Finance\InvoiceService::class)
                        ->canDelete($record)
                ),
        ]);
}

    public static function getRelations(): array
    {
        return [];
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