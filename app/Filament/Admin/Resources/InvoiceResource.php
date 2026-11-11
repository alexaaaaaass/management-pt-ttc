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
use Illuminate\Database\Eloquent\Builder;

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
                        ->description('Pilih Surat Jalan yang sudah siap kirim / sudah terbit Surat Jalan dan belum ditagihkan')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('surat_jalan_id')
                                        ->label('Pilih Surat Jalan')
                                        ->placeholder('Pilih Surat Jalan yang siap kirim / belum ditagihkan...')
                                        ->relationship(
                                            name: 'suratJalan',
                                            titleAttribute: 'kode_surat_jalan',
                                            modifyQueryUsing: function (Builder $query, ?Invoice $record) {
                                                return $query
                                                    ->with([
                                                        'salesOrder.customer',
                                                        'salesOrder.itemable',
                                                        'salesOrder.items.material',
                                                        'spk',
                                                    ])
                                                    ->where(function (Builder $q) use ($record) {
                                                        // Hanya tampilkan Surat Jalan yang sudah terbit & siap kirim (qty > 0)
                                                        // dan BELUM selesai dibuatkan Invoice
                                                        $q->where(function (Builder $activeQuery) {
                                                            $activeQuery
                                                                ->whereDoesntHave('invoice')
                                                                ->whereHas('salesOrder')
                                                                ->where(function (Builder $qtyQuery) {
                                                                    $qtyQuery->where('qty_pengiriman', '>', 0)
                                                                        ->orWhereHas('items', fn (Builder $iq) => $iq->where('qty_kirim', '>', 0));
                                                                });
                                                        });

                                                        // Saat Edit Invoice, tetap tampilkan Surat Jalan milik Invoice ini
                                                        if ($record && $record->surat_jalan_id) {
                                                            $q->orWhere('surat_jalans.id', $record->surat_jalan_id);
                                                        }
                                                    });
                                            }
                                        )
                                        ->getOptionLabelFromRecordUsing(function (SuratJalan $record): string {
                                            $so = $record->salesOrder;
                                            $noSo = $so?->no_sales_order ?? '-';
                                            $customer = $so?->customer?->nama_customer ?? '-';
                                            $produk = $so?->product_name
                                                ?: ($so?->itemable?->nama_barang ?? '-');
                                            $qty = $record->qty_pengiriman > 0
                                                ? $record->qty_pengiriman
                                                : $record->items()->sum('qty_kirim');

                                            return sprintf(
                                                '%s — %s | %s (%s - %s pcs)',
                                                $record->kode_surat_jalan,
                                                $noSo,
                                                $customer,
                                                $produk,
                                                number_format((float) $qty, 0, ',', '.')
                                            );
                                        })
                                        ->disabled(fn (string $operation) => $operation === 'edit')
                                        ->searchable(['kode_surat_jalan'])
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $sj = SuratJalan::with([
                                                'salesOrder.customer',
                                                'salesOrder.itemable',
                                                'salesOrder.items.material',
                                                'salesOrder.orderConfirmation',
                                                'items.salesOrderItem',
                                            ])->find($state);

                                            if (! $sj) {
                                                $set('preview_items', []);
                                                return;
                                            }

                                            $so = $sj->salesOrder;

                                            // Set Preview Items untuk MULTI_MATERIAL
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

                                            $uangMuka = $so?->orderConfirmation?->dp_nominal ?? ($so?->uang_muka ?? 0);
                                            $set('uang_muka', $uangMuka);

                                            // Autofill hidden fields
                                            $set('customer_id', $so?->customer_id);
                                            $set('sales_order_id', $so?->id);
                                            $set('no_po_customer', $so?->no_po_customer ?? '-');
                                        }),

                                    TextInput::make('kode_invoice')
                                        ->label('Kode Invoice')
                                        ->default(fn () => app(\App\Services\Finance\InvoiceService::class)->generateKodeInvoice())
                                        ->disabled()
                                        ->dehydrated()
                                        ->required(),
                                ]),

                            Section::make('Informasi Order & Pengiriman Terkait')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Placeholder::make('customer_info')
                                                ->label('Customer')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::with('salesOrder.customer')->find($get('surat_jalan_id'));
                                                    return $sj?->salesOrder?->customer?->nama_customer ?? '-';
                                                }),

                                            Placeholder::make('so_info')
                                                ->label('No. Sales Order')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::with('salesOrder')->find($get('surat_jalan_id'));
                                                    return $sj?->salesOrder?->no_sales_order ?? '-';
                                                }),

                                            Placeholder::make('po_info')
                                                ->label('No. PO Customer')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::with('salesOrder')->find($get('surat_jalan_id'));
                                                    return $sj?->salesOrder?->no_po_customer ?: '-';
                                                }),

                                            Placeholder::make('produk_info')
                                                ->label('Produk / Barang')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::with(['salesOrder.itemable', 'salesOrder.items.material'])->find($get('surat_jalan_id'));
                                                    if (! $sj || ! $sj->salesOrder) {
                                                        return '-';
                                                    }
                                                    $so = $sj->salesOrder;
                                                    return match ($so->mode) {
                                                        'SPK' => $so->product_name ?: ($so->itemable?->nama_barang ?? '-'),
                                                        'SINGLE_MATERIAL' => $so->items->first()?->material?->nama_master_item ?? '-',
                                                        'MULTI_MATERIAL' => $so->items->pluck('material.nama_master_item')->filter()->implode(', '),
                                                        default => '-',
                                                    };
                                                }),

                                            Placeholder::make('qty_kirim_info')
                                                ->label('Qty Kirim (Siap Tagih)')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::find($get('surat_jalan_id'));
                                                    if (! $sj) {
                                                        return '-';
                                                    }
                                                    $qty = $sj->qty_pengiriman > 0 ? $sj->qty_pengiriman : $sj->items()->sum('qty_kirim');
                                                    return number_format((float) $qty, 0, ',', '.') . ' pcs';
                                                }),

                                            Placeholder::make('harga_info')
                                                ->label('Harga / PCS')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::with('salesOrder')->find($get('surat_jalan_id'));
                                                    return 'Rp ' . number_format((float) ($sj?->salesOrder?->harga_pcs ?? 0), 0, ',', '.');
                                                }),

                                            Placeholder::make('sj_date')
                                                ->label('Tgl Kirim Surat Jalan')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::find($get('surat_jalan_id'));
                                                    return $sj && $sj->tanggal_surat_jalan
                                                        ? \Carbon\Carbon::parse($sj->tanggal_surat_jalan)->format('d F Y')
                                                        : '-';
                                                }),

                                            Placeholder::make('driver_info')
                                                ->label('Driver / Ekspedisi')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::find($get('surat_jalan_id'));
                                                    if (! $sj) {
                                                        return '-';
                                                    }
                                                    return trim(($sj->driver ?? '') . ($sj->transportasi ? ' (' . $sj->transportasi . ')' : '')) ?: '-';
                                                }),

                                            Placeholder::make('kendaraan_info')
                                                ->label('No. Polisi')
                                                ->content(function (Get $get) {
                                                    $sj = SuratJalan::find($get('surat_jalan_id'));
                                                    return $sj?->no_polisi ?: '-';
                                                }),
                                        ]),
                                ])
                                ->visible(fn (Get $get) => filled($get('surat_jalan_id'))),

                            Section::make('Detail Material')
                                ->visible(function (Get $get) {
                                    $sj = SuratJalan::with('salesOrder.items')->find($get('surat_jalan_id'));
                                    return $sj && $sj->salesOrder && $sj->salesOrder->mode === 'MULTI_MATERIAL';
                                })
                                ->schema([
                                    Repeater::make('preview_items')
                                        ->label('Rincian Item (Multi Material)')
                                        ->dehydrated(false)
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columns(4)
                                        ->schema([
                                            TextInput::make('material')
                                                ->label('Nama Material')
                                                ->disabled(),

                                            TextInput::make('qty')
                                                ->label('Qty Dikirim')
                                                ->disabled(),

                                            TextInput::make('harga')
                                                ->label('Harga Satuan')
                                                ->prefix('Rp')
                                                ->disabled(),

                                            Placeholder::make('subtotal')
                                                ->label('Subtotal')
                                                ->content(fn (Get $get) =>
                                                    'Rp ' . number_format(
                                                        ((float) ($get('qty') ?? 0)) * ((float) ($get('harga') ?? 0)),
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

                            Grid::make(4)
                                ->schema([
                                    TextInput::make('subtotal')
                                        ->label('Subtotal (DPP)')
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                                        ->stripCharacters('.')
                                        ->readOnly()
                                        ->default(0)
                                        ->dehydrated(true),

                                    Select::make('tipe_pajak_helper')
                                        ->label('Tipe Pajak (PPN / Non-PPN)')
                                        ->options([
                                            'NON_PPN' => 'Non-PPN (0% — Tanpa Faktur Pajak)',
                                            'PPN_11'  => 'PPN 11% (Pakai Faktur Pajak)',
                                        ])
                                        ->default('NON_PPN')
                                        ->dehydrated(false)
                                        ->live()
                                        ->afterStateHydrated(function (Select $component, Get $get) {
                                            $component->state(((float) ($get('ppn') ?? 0)) > 0 ? 'PPN_11' : 'NON_PPN');
                                        })
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set('ppn', $state === 'PPN_11' ? 11 : 0);
                                        }),

                                    TextInput::make('ppn')
                                        ->label('Tarif PPN (%)')
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->suffix('%')
                                        ->default(0)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set('tipe_pajak_helper', ((float) $state) > 0 ? 'PPN_11' : 'NON_PPN');
                                        }),

                                    TextInput::make('diskon')
                                        ->label('Diskon (%)')
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
                                        ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                                        ->stripCharacters('.')
                                        ->default(0)
                                        ->live(),

                                    TextInput::make('uang_muka')
                                        ->label('Uang Muka (DP)')
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('$money($input, ",", ".", 0)'))
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
                                        ->extraAttributes(['class' => 'text-lg font-bold', 'style' => 'color: #007bff; font-size: 1.5rem;'])
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

                                    Placeholder::make('uang_muka_view')
                                        ->label('Uang Muka (DP)')
                                        ->extraAttributes(['class' => 'text-lg font-bold', 'style' => 'color: #16a34a; font-size: 1.5rem;'])
                                        ->content(function (Get $get) {
                                            $uangMuka = (float) str_replace(['.', ','], '', (string) ($get('uang_muka') ?? 0));
                                            return 'Rp ' . number_format($uangMuka, 0, ',', '.');
                                        }),

                                    Placeholder::make('sisa_tagihan_view')
                                        ->label('Sisa Tagihan (Setelah DP)')
                                        ->extraAttributes(['class' => 'text-lg font-bold', 'style' => 'color: #dc2626; font-size: 1.5rem;'])
                                        ->content(function (Get $get) {
                                            $subtotal = (float) ($get('subtotal') ?? 0);
                                            $diskon = (float) ($get('diskon') ?? 0);
                                            $ppn = (float) ($get('ppn') ?? 0);
                                            $ongkir = (float) ($get('ongkir') ?? 0);
                                            $uangMuka = (float) str_replace(['.', ','], '', (string) ($get('uang_muka') ?? 0));

                                            $diskonNominal = ($subtotal * $diskon) / 100;
                                            $ppnNominal = ($subtotal * $ppn) / 100;
                                            $grandTotal = $subtotal - $diskonNominal + $ppnNominal + $ongkir;
                                            $sisa = max(0, $grandTotal - $uangMuka);

                                            return 'Rp ' . number_format($sisa, 0, ',', '.');
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
            ->defaultSort('tanggal_invoice', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_invoice')
                    ->label('No. Invoice')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->getStateUsing(fn ($record) => $record->order_id ?: ($record->salesOrder?->no_po ?? '-'))
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_display')
                    ->label('Customer & Order')
                    ->getStateUsing(fn ($record) => $record->customer?->nama_customer ?? $record->suratJalan?->salesOrder?->customer?->nama_customer ?? '-')
                    ->description(fn ($record) => $record->salesOrder?->product_name ?: ($record->catatan ?: null))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('customer', fn ($q) => $q->where('nama_customer', 'like', "%{$search}%"))
                            ->orWhere('order_id', 'like', "%{$search}%")
                            ->orWhere('catatan', 'like', "%{$search}%");
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tanggal_invoice')
                    ->label('Tgl. Invoice')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pajak_status')
                    ->label('Pajak (PPN)')
                    ->badge()
                    ->getStateUsing(fn ($record) => ((float) ($record->ppn ?? 0)) > 0 ? 'PPN ' . round((float) $record->ppn, 1) . '%' : 'NON-PPN')
                    ->description(function ($record) {
                        $ppnVal = (float) ($record->ppn ?? 0);
                        if ($ppnVal <= 0) {
                            return 'Tanpa Faktur';
                        }
                        $subtotal = (float) ($record->subtotal ?? 0);
                        $diskonNominal = ($subtotal * (float) ($record->diskon ?? 0)) / 100;
                        $ppnNominal = (($subtotal - $diskonNominal) * $ppnVal) / 100;
                        return 'Rp ' . number_format($ppnNominal, 0, ',', '.');
                    })
                    ->color(fn ($state) => str_starts_with($state, 'PPN') ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Revenue (Tagihan)')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Revenue')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('total_payment')
                    ->label('Sudah Dibayar')
                    ->money('IDR')
                    ->alignRight()
                    ->color('success')
                    ->getStateUsing(fn ($record) => (float) ($record->total_payment > 0 ? $record->total_payment : (($record->uang_muka ?? 0) + $record->payments()->where('status', 'SUCCESS')->sum('nominal_bayar'))))
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Dibayar')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label('Outstanding (Sisa)')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn ($record) => $record->sisa_tagihan <= 0 ? 'success' : 'danger')
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Outstanding')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pajak_type')
                    ->label('Filter Jenis Pajak')
                    ->options([
                        'ppn'     => 'Khusus PPN (11%)',
                        'non_ppn' => 'Khusus Non-PPN (0%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'ppn') {
                            return $query->where('ppn', '>', 0);
                        }
                        if ($data['value'] === 'non_ppn') {
                            return $query->where(function ($q) {
                                $q->whereNull('ppn')->orWhere('ppn', '<=', 0);
                            });
                        }
                        return $query;
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
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->url(fn ($record) => route('invoice.preview', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('invoice.download', $record)),

                    Tables\Actions\Action::make('detail')
                        ->label('Detail Invoice')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => static::getUrl('view', [
                            'record' => $record,
                        ])),

                    Tables\Actions\EditAction::make()
                        ->label('Edit'),

                    Tables\Actions\Action::make('toggle_ppn')
                        ->label(fn ($record) => ((float) ($record->ppn ?? 0)) > 0 ? 'Ubah ke Non-PPN (0%)' : 'Ubah ke PPN 11%')
                        ->icon(fn ($record) => ((float) ($record->ppn ?? 0)) > 0 ? 'heroicon-o-no-symbol' : 'heroicon-o-receipt-percent')
                        ->color(fn ($record) => ((float) ($record->ppn ?? 0)) > 0 ? 'gray' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => ((float) ($record->ppn ?? 0)) > 0 ? 'Ubah Invoice ke Non-PPN' : 'Terapkan PPN 11% pada Invoice')
                        ->modalDescription('Perubahan ini akan otomatis menghitung ulang Grand Total & Sisa Tagihan serta menyinkronkan Jurnal Akuntansi (Piutang & Utang PPN Keluaran).')
                        ->action(function ($record) {
                            $isPpn = ((float) ($record->ppn ?? 0)) > 0;
                            $record->ppn = $isPpn ? 0 : 11;
                            app(\App\Services\Finance\InvoiceService::class)->syncInvoiceFinancials($record);
                            $record->save();
                            app(\App\Services\Finance\TreasuryService::class)->syncInvoice($record);

                            \Filament\Notifications\Notification::make()
                                ->title($isPpn ? 'Status diubah ke Non-PPN (0%)' : 'PPN 11% berhasil diterapkan!')
                                ->body('Grand Total dan Jurnal Keuangan telah disinkronkan.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->hidden(fn ($record) =>
                            ! app(\App\Services\Finance\InvoiceService::class)
                                ->canDelete($record)
                        ),
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
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'suratJalan.salesOrder.customer',
                'payments.metodeBayar',
                'payments.coa',
                'payments.karyawan',
            ]);
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
