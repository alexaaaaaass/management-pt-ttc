<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratJalanResource\Pages;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuratJalanResource extends Resource
{
    protected static ?string $model = SuratJalan::class;
    
    protected static ?string $navigationGroup = 'Dispatch';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Surat Jalan';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    protected static function getSalesOrder(?int $salesOrderId): ?SalesOrder
    {
        if (! $salesOrderId) {
            return null;
        }

        return SalesOrder::with([
            'customer',
            'itemable',
            'items.material',
            'items.suratJalanItems',
            'suratJalans',
        ])->find($salesOrderId);
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([

                    // ==========================================
                    // 🔥 TAHAP 1: SALES ORDER & KUANTITAS PENGIRIMAN
                    // ==========================================
                    Wizard\Step::make('Sales Order & Kuantitas')
                        ->icon('heroicon-o-shopping-bag')
                        ->description('Pilih Sales Order yang sudah siap kirim (ready), cek stock, dan tentukan kuantiti kirim')
                        ->schema([
                            Forms\Components\Select::make('sales_order_id')
                                ->label('Sales Order (Siap Kirim)')
                                ->placeholder('Pilih Sales Order yang sudah ready / siap kirim...')
                                ->relationship(
                                    name: 'salesOrder',
                                    titleAttribute: 'no_sales_order',
                                    modifyQueryUsing: function (Builder $query, ?SuratJalan $record) {
                                        $spkDelivered = "(SELECT COALESCE(SUM(sj.qty_pengiriman), 0) FROM surat_jalans sj WHERE sj.sales_order_id = sales_orders.id)";
                                        $spkPackedGood = "(SELECT COALESCE(SUM(pkg.grand_total), 0) FROM packagings pkg JOIN master_spk spk ON pkg.spk_id = spk.id WHERE spk.sales_order_id = sales_orders.id AND pkg.jenis_transfer != 'blokir')";
                                        $fgStockReady = "EXISTS (SELECT 1 FROM finish_good_stocks fgs WHERE fgs.finish_good_item_id = sales_orders.itemable_id AND fgs.on_hand > 0)";
                                        $materialOrder = "(SELECT COALESCE(SUM(soi.qty), 0) FROM sales_order_items soi WHERE soi.sales_order_id = sales_orders.id)";
                                        $materialDelivered = "(SELECT COALESCE(SUM(sji.qty_kirim), 0) FROM surat_jalan_items sji JOIN sales_order_items soi ON sji.sales_order_item_id = soi.id WHERE soi.sales_order_id = sales_orders.id)";
                                        $materialStockReady = "EXISTS (SELECT 1 FROM sales_order_items soi JOIN material_stocks ms ON ms.item_id = soi.master_item_id WHERE soi.sales_order_id = sales_orders.id AND ms.on_hand > 0)";

                                        return $query
                                            ->with(['customer', 'itemable', 'spk', 'items.material'])
                                            ->where(function (Builder $q) use ($record, $spkDelivered, $spkPackedGood, $fgStockReady, $materialOrder, $materialDelivered, $materialStockReady) {
                                                // Hanya tampilkan Sales Order yang SUDAH READY/SIAP KIRIM dan BELUM SELESAI DIKIRIM
                                                $q->where(function (Builder $readyAndIncomplete) use ($spkDelivered, $spkPackedGood, $fgStockReady, $materialOrder, $materialDelivered, $materialStockReady) {
                                                    // 1. Mode SPK: ada stok siap kirim dari FinishGoodStock / Packaging > 0 DAN belum selesai dikirim
                                                    $readyAndIncomplete->where(function (Builder $spkQuery) use ($spkDelivered, $spkPackedGood, $fgStockReady) {
                                                        $spkQuery->where(function (Builder $typeQ) {
                                                            $typeQ->where('sales_orders.order_type', 'SPK')
                                                                ->orWhereNotNull('sales_orders.itemable_id');
                                                        })
                                                        ->whereRaw("(($spkPackedGood) > ($spkDelivered) OR $fgStockReady)")
                                                        ->whereRaw("($spkDelivered) < COALESCE(sales_orders.qty, 0)");
                                                    })
                                                    // 2. Mode Material: stok tersedia > 0 DAN belum selesai dikirim
                                                    ->orWhere(function (Builder $matQuery) use ($materialOrder, $materialDelivered, $materialStockReady) {
                                                        $matQuery->where('sales_orders.order_type', '!=', 'SPK')
                                                            ->whereNull('sales_orders.itemable_id')
                                                            ->whereRaw($materialStockReady)
                                                            ->whereRaw("($materialDelivered) < ($materialOrder)");
                                                    });
                                                });

                                                // Saat Edit Surat Jalan, tetap tampilkan Sales Order milik Surat Jalan ini
                                                if ($record && $record->sales_order_id) {
                                                    $q->orWhere('sales_orders.id', $record->sales_order_id);
                                                }
                                            });
                                    }
                                )
                                ->getOptionLabelFromRecordUsing(function (SalesOrder $record): string {
                                    $customer = $record->customer?->nama_customer ?? '-';
                                    $produk = $record->product_name ?: '-';
                                    $siapKirim = (float) $record->current_stock;
                                    $sisaOrder = (float) $record->outstanding_qty;

                                    return sprintf(
                                        '%s — %s | %s (Siap Kirim: %s pcs | Sisa: %s pcs)',
                                        $record->no_sales_order,
                                        $customer,
                                        $produk,
                                        number_format($siapKirim, 0, ',', '.'),
                                        number_format($sisaOrder, 0, ',', '.')
                                    );
                                })
                                ->searchable(['no_sales_order'])
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (! $state) {
                                        $set('items', []);
                                        $set('order_type', null);
                                        return;
                                    }
                                    
                                    $so = self::getSalesOrder($state);
                                    if (! $so) {
                                        return;
                                    }

                                    // Langsung pakai accessor dari model SalesOrder
                                    $mode = $so->mode; 
                                    $set('order_type', $mode);

                                    if (! empty($so->customer?->alamat)) {
                                        $set('alamat_tujuan', $so->customer->alamat);
                                    }

                                    if ($mode === 'SPK') {
                                        $set('items', []);
                                        $defaultQty = min((float) $so->current_stock, (float) $so->outstanding_qty);
                                        if ($defaultQty > 0) {
                                            $set('qty_pengiriman', $defaultQty);
                                        }
                                        return;
                                    }

                                    // Generate rows untuk Repeater
                                    $rows = [];
                                    foreach ($so->items as $item) {
                                        // Hitung outstanding per item
                                        $terkirimItem = $item->suratJalanItems()->sum('qty_kirim');
                                        
                                        $rows[] = [
                                            'sales_order_item_id' => $item->id,
                                            'master_item_id'      => $item->master_item_id, 
                                            'material'            => $item->material?->nama_master_item ?? '-',
                                            'qty_order'           => $item->qty,
                                            'qty_outstanding'     => max(0, $item->qty - $terkirimItem), 
                                            'qty_kirim'           => 0,
                                        ];
                                    }
                                    $set('items', $rows);
                                }),

                            Forms\Components\Hidden::make('order_type')
                                ->default(fn (?SuratJalan $record) => $record?->salesOrder?->mode),

                            // ===== INFORMASI STOCK (Hanya untuk SPK & SINGLE_MATERIAL) =====
                            Forms\Components\Section::make('Informasi Ketersediaan Stock & Order')
                                ->visible(fn (Get $get) => in_array($get('order_type'), ['SPK', 'SINGLE_MATERIAL']))
                                ->columns(3)
                                ->schema([
                                    Forms\Components\Placeholder::make('nama_item')
                                        ->label('Item')
                                        ->content(function (Get $get) {
                                            $so = self::getSalesOrder($get('sales_order_id'));
                                            return $so?->product_name ?? '-';
                                        }),

                                    Forms\Components\Placeholder::make('stock')
                                        ->label('Stock Tersedia (Siap Kirim)')
                                        ->content(function (Get $get) {
                                            $so = self::getSalesOrder($get('sales_order_id'));
                                            return number_format($so?->current_stock ?? 0, 0, ',', '.');
                                        }),

                                    Forms\Components\Placeholder::make('qty_order')
                                        ->label('Qty Order')
                                        ->content(function (Get $get) {
                                            $so = self::getSalesOrder($get('sales_order_id'));
                                            return number_format($so?->order_qty ?? 0, 0, ',', '.');
                                        }),

                                    Forms\Components\Placeholder::make('qty_terkirim')
                                        ->label('Sudah Dikirim')
                                        ->content(function (Get $get) {
                                            $so = self::getSalesOrder($get('sales_order_id'));
                                            return number_format($so?->delivered_qty ?? 0, 0, ',', '.');
                                        }),

                                    Forms\Components\Placeholder::make('qty_outstanding')
                                        ->label('Sisa Pengiriman')
                                        ->content(function (Get $get) {
                                            $so = self::getSalesOrder($get('sales_order_id'));
                                            return number_format($so?->outstanding_qty ?? 0, 0, ',', '.');
                                        }),
                                ]),

                            // ===== INPUT QTY PENGIRIMAN (Hanya untuk SPK & SINGLE_MATERIAL) =====
                            Forms\Components\TextInput::make('qty_pengiriman')
                                ->label('Qty Pengiriman Kali Ini')
                                ->visible(fn (Get $get) => in_array($get('order_type'), ['SPK', 'SINGLE_MATERIAL']))
                                ->quantityMask(0)
                                ->required()
                                ->live()
                                ->rule(function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $so = self::getSalesOrder($get('sales_order_id'));
                                        if (!$so) return;

                                        $valClean = \App\Support\MoneyHelper::clean($value) ?? 0;

                                        // Validasi Sisa Order (memperhitungkan form edit)
                                        $qtySudahKirim = $so->suratJalans()
                                            ->whereKeyNot(request()->route('record'))
                                            ->sum('qty_pengiriman');

                                        $sisaOrder = $so->order_qty - $qtySudahKirim;

                                        if ($valClean > $sisaOrder) {
                                            $fail("Qty pengiriman melebihi outstanding order ({$sisaOrder}).");
                                            return;
                                         }

                                        // Validasi Stock Tersedia
                                        $stockTersedia = $so->current_stock;
                                        if ($valClean > $stockTersedia) {
                                            $fail("Qty melebihi stock produk/material yang tersedia ({$stockTersedia}).");
                                        }
                                    };
                                }),

                            // ===== REPEATER DETAIL MATERIAL (Hanya untuk MULTI_MATERIAL) =====
                            Forms\Components\Section::make('Detail Material')
                                ->visible(fn (Get $get) => $get('order_type') === 'MULTI_MATERIAL')
                                ->columnSpanFull()
                                ->schema([
                                    Forms\Components\Repeater::make('items')
                                        ->relationship('items')
                                        ->dehydrated()
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columns(4)
                                        ->schema([
                                            Forms\Components\Hidden::make('sales_order_item_id'),
                                            Forms\Components\Hidden::make('master_item_id'), 
                                            
                                            Forms\Components\TextInput::make('material')
                                                ->label('Material')
                                                ->disabled(),

                                            Forms\Components\TextInput::make('qty_order')
                                                ->label('Qty Order')
                                                ->disabled(),

                                            Forms\Components\TextInput::make('qty_outstanding')
                                                ->label('Outstanding')
                                                ->disabled(),

                                            Forms\Components\TextInput::make('qty_kirim')
                                                ->label('Qty Kirim')
                                                ->quantityMask(0)
                                                ->required()
                                                ->live(),
                                        ]),
                                ]),
                        ]),

                    // ==========================================
                    // 🔥 TAHAP 2: EKSPEDISI & DETAIL PENGIRIMAN
                    // ==========================================
                    Wizard\Step::make('Ekspedisi & Detail Pengiriman')
                        ->icon('heroicon-o-truck')
                        ->description('Tanggal kirim, alamat tujuan, kendaraan, driver, dan catatan pengiriman')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('tanggal_surat_jalan')
                                    ->label('Tanggal Surat Jalan')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\TextInput::make('transportasi')
                                    ->label('Jenis Transportasi / Ekspedisi')
                                    ->placeholder('Contoh: Truk Engkel, Pickup Box')
                                    ->required(),

                                Forms\Components\TextInput::make('no_polisi')
                                    ->label('Nomor Polisi')
                                    ->placeholder('Contoh: B 1234 CD')
                                    ->required(),

                                Forms\Components\TextInput::make('driver')
                                    ->label('Nama Driver / Supir')
                                    ->required(),

                                Forms\Components\TextInput::make('pengirim')
                                    ->label('Petugas Pengirim')
                                    ->required(),

                                Forms\Components\Textarea::make('alamat_tujuan')
                                    ->label('Alamat Tujuan Pengiriman')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan Tambahan')
                                    ->default('-')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ])
                ->skippable()
                ->columnSpanFull(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'salesOrder.customer',
                'salesOrder.itemable',
                'salesOrder.items.material',
                'salesOrder.spk',
                'spk',
                'items.material',
                'invoice',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_surat_jalan')
                    ->label('No. Surat Jalan')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                    ->label('No SO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('salesOrder.customer.nama_customer')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_spk')
                    ->label('No SPK')
                    ->getStateUsing(function ($record) {
                        return $record->spk?->no_spk ?? ($record->salesOrder?->spk?->no_spk ?? '-');
                    }),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Produk')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        return $record->salesOrder?->product_name ?? '-';
                    }),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty Kirim')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        $so = $record->salesOrder;
                        if (! $so) return '-';

                        if ($so->mode === 'SPK') {
                            return number_format($record->qty_pengiriman);
                        }

                        if ($record->items->count() <= 1) {
                            return number_format(optional($record->items->first())->qty_kirim);
                        }

                        return number_format($record->items->sum('qty_kirim'));
                    }),

                Tables\Columns\TextColumn::make('invoice.kode_invoice')
                    ->label('No. Invoice')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->default('Belum Invoiced'),

                Tables\Columns\TextColumn::make('transportasi')
                    ->label('Transportasi'),

                Tables\Columns\TextColumn::make('driver')
                    ->label('Driver'),

                Tables\Columns\TextColumn::make('tanggal_surat_jalan')
                    ->label('Tgl Kirim')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-document')
                        ->color('warning')
                        ->url(fn ($record) => route('suratjalan.preview', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('suratjalan.download', $record)),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record) ? 'Surat Jalan terkunci karena sudah memiliki Invoice' : null),
                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record) ? 'Tidak dapat dihapus karena sudah memiliki Invoice' : null),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('bottom-end'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuratJalans::route('/'),
            'create' => Pages\CreateSuratJalan::route('/create'),
            'edit'   => Pages\EditSuratJalan::route('/{record}/edit'),
            'view'   => Pages\ViewSuratJalan::route('/{record}'),
        ];
    }
}
