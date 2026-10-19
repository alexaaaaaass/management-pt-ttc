<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([

                // ==========================================
                // 🔥 TAHAP 1: INFORMASI ORDER & CUSTOMER
                // ==========================================
                Wizard\Step::make('Informasi Order & Customer')
                    ->icon('heroicon-o-document-text')
                    ->description('Tipe pesanan, pilihan produk, data customer, dan referensi PO')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Hidden::make('material_item_id'),
                            Forms\Components\Hidden::make('order_confirmation_id'),
                            
                            Forms\Components\Select::make('order_type')
                                ->label('Order Type')
                                ->options([
                                    'SPK'      => 'Produksi (SPK)',
                                    'MATERIAL' => 'Penjualan Material',
                                ])
                                ->default('SPK')
                                ->required()
                                ->live()
                                ->native(false)
                                ->afterStateUpdated(function (callable $set) {
                                    // Reset item jika tipe order berubah agar tidak cross-data
                                    $set('item_source', null);
                                    $set('itemable_type', null);
                                    $set('itemable_id', null);
                                    $set('kode_material', null);
                                    $set('bom_items', []);
                                }),     

                            Forms\Components\Select::make('item_source')
                                ->visible(fn(Get $get) => $get('order_type') == 'SPK')
                                ->label('Pilih Item')
                                ->searchable()
                                ->native(false)
                                ->preload()
                                ->required()
                                ->afterStateHydrated(function ($state, callable $set, $record) {
                                    if (!$record) return;
                                    // Cukup set ID aslinya karena pembeda sudah ada di order_type
                                    $set('item_source', $record->itemable_id);
                                })
                                ->options(function (callable $get) {
                                    if ($get('order_type') === 'SPK') {
                                        return \App\Models\FinishGoodItem::pluck('nama_barang', 'id');
                                    }
                                    return \App\Models\MasterItem::pluck('nama_master_item', 'id');
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (!$state) {
                                        $set('itemable_id', null);
                                        $set('kode_material', null);
                                        $set('bom_items', []);
                                        return;
                                    }

                                    if ($get('order_type') === 'SPK') {
                                        $item = \App\Models\FinishGoodItem::with('typeItem')->find($state);

                                        $set('itemable_type', \App\Models\FinishGoodItem::class);
                                        $set('itemable_id', $item?->id);
                                        $set('customer_id', $item?->customer_id);
                                        $set('kode_material', $item?->kode_material_produk);
                                        $set('material_item_id', null);
                                        $set('jenis', $item?->jenis);
                                        $set('komponen', $item?->komponen ?? $item?->typeItem?->nama_type_item);
                                        $set('isi', $item?->isi);
                                        $set('kertas', $item?->spesifikasi_kertas);
                                        $set('warna', $item?->warna);
                                        $set('finishing', $item?->finishing);

                                        // Otomatis tarik Bill of Material (BOM)
                                        $qtyPesanan = (float) ($get('qty') ?? 0);
                                        $bom = app(\App\Services\Sales\SalesOrderService::class)->getBomPreview((int) $state, $qtyPesanan);
                                        $set('bom_items', $bom);
                                    } else {
                                        $item = \App\Models\MasterItem::find($state);

                                        $set('itemable_type', \App\Models\MasterItem::class);
                                        $set('itemable_id', $item?->id);
                                        $set('material_item_id', $item?->id);
                                        $set('kode_material', $item?->kode_master_item);
                                        $set('bom_items', []);
                                    }
                                }),

                            Forms\Components\Hidden::make('itemable_type'),
                            Forms\Components\Hidden::make('itemable_id'),
                            Forms\Components\Hidden::make('finish_good_item_id'),
                            Forms\Components\Hidden::make('kode_material')
                                ->visible(fn(Get $get)=>$get('order_type')=='SPK'),

                            Forms\Components\Select::make('customer_id')
                                ->label('Customer')
                                ->relationship('customer', 'nama_customer')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required(),

                            Forms\Components\TextInput::make('no_sales_order')
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated()
                                ->placeholder('Otomatis jika dikosongkan'),

                            Forms\Components\TextInput::make('no_po_customer'),

                            Forms\Components\DatePicker::make('tanggal_po')
                                ->default(now()),

                            Forms\Components\TextInput::make('tipe_pesanan')
                                ->placeholder('Contoh: Repeat Order / Baru'),

                            Forms\Components\TextInput::make('lead_time')
                                ->label('Lead Time')
                                ->default('21 Hari Kerja')
                                ->placeholder('Contoh: 21 Hari Kerja'),

                            Forms\Components\Select::make('dipesan_via')
                                ->options([
                                    'whatsapp' => 'WhatsApp',
                                    'email' => 'Email',
                                    'sosmed' => 'Sosmed',
                                ])
                                ->native(false),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 2: KUANTITAS & KETENTUAN KOMERSIAL
                // ==========================================
                Wizard\Step::make('Kuantitas & Komersial')
                    ->icon('heroicon-o-currency-dollar')
                    ->description('Jumlah pesanan, harga satuan, diskon, dan toleransi pengiriman')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('qty')
                                ->visible(fn(Get $get) => $get('order_type') == 'SPK')
                                ->required(fn(Get $get) => $get('order_type') == 'SPK')
                                ->label('Jumlah Pesanan')
                                ->numeric()
                                ->live(debounce: 500)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $fgId = $get('item_source');
                                    if ($fgId && $get('order_type') === 'SPK') {
                                        $bom = app(\App\Services\Sales\SalesOrderService::class)->getBomPreview((int) $fgId, (float) ($state ?? 0));
                                        $set('bom_items', $bom);
                                    }
                                })
                                ->rule(function ($get) {
                                    if ($get('order_type') === 'MATERIAL') {
                                        return null;
                                    }
                                    
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $itemableType = $get('itemable_type');
                                        $itemableId = $get('itemable_id');

                                        if ($itemableType !== \App\Models\FinishGoodItem::class || !$itemableId) return;

                                        $shortages = app(\App\Services\Inventory\StockService::class)->checkBomShortages(
                                            (int) $itemableId,
                                            (float) $value
                                        );

                                        if (!empty($shortages)) {
                                            $first = $shortages[0];
                                            $fail("Stock tidak cukup untuk material: {$first['nama_material']} | Butuh: {$first['kebutuhan']} | Sisa: {$first['tersedia']}");
                                        }
                                    };
                                }),

                            Forms\Components\TextInput::make('harga_pcs')
                                ->visible(fn(Get $get)=>$get('order_type')=='SPK')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', $state) : null),

                            Forms\Components\TextInput::make('harga_kirim')
                                ->numeric()
                                ->prefix('Rp')
                                ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', $state) : null),

                            Forms\Components\TextInput::make('diskon')
                                ->label('Diskon')
                                ->numeric()
                                ->prefix('Rp')
                                ->default(0)
                                ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
                                ->dehydrateStateUsing(fn ($state) => $state ? (int) str_replace('.', '', $state) : 0),

                            Forms\Components\Select::make('mata_uang')
                                ->options([
                                    'rupiah' => 'Rupiah',
                                    'usd' => 'USD',
                                    'yuan' => 'Yuan',
                                ])
                                ->default('rupiah')
                                ->native(false),

                            Forms\Components\TextInput::make('syarat_pembayaran')
                                ->placeholder('Contoh: 30 Hari setelah invoice'),

                            Forms\Components\TextInput::make('toleransi_pengiriman')
                                ->numeric()
                                ->suffix('%')
                                ->minValue(0)
                                ->maxValue(100),

                            Forms\Components\Select::make('klaim_kertas')
                                ->options([
                                    'claim' => 'Claim',
                                    'not_claim' => 'Not Claim',
                                ])
                                ->native(false),

                            Forms\Components\Textarea::make('catatan')
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('catatan_colour_range')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 3: SPESIFIKASI & MATERIAL (BOM)
                // ==========================================
                Wizard\Step::make('Spesifikasi & Material (BOM)')
                    ->icon('heroicon-o-queue-list')
                    ->description('Spesifikasi cetak, preview BOM pesanan, atau daftar material jual')
                    ->schema([
                        Forms\Components\Section::make('Spesifikasi Produk (Order Confirmation)')
                            ->description('Spesifikasi teknis kemasan rokok/cetak untuk dicantumkan pada preview PDF Order Confirmation.')
                            ->collapsible()
                            ->collapsed(false)
                            ->visible(fn(Get $get) => $get('order_type') == 'SPK')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('jenis')
                                        ->label('Jenis')
                                        ->placeholder('Contoh: SKM, SKT, SPM'),

                                    Forms\Components\TextInput::make('komponen')
                                        ->label('Komponen')
                                        ->placeholder('Contoh: Etiket, Inner Frame'),

                                    Forms\Components\TextInput::make('isi')
                                        ->label('Isi')
                                        ->placeholder('Contoh: 20, 16, 12'),

                                    Forms\Components\TextInput::make('kertas')
                                        ->label('Kertas')
                                        ->placeholder('Contoh: Metalize, Ivory 230gr'),

                                    Forms\Components\TextInput::make('warna')
                                        ->label('Warna')
                                        ->placeholder('Contoh: 5, 4+1'),

                                    Forms\Components\TextInput::make('finishing')
                                        ->label('Finishing')
                                        ->placeholder('Contoh: Spot UV, Cutting, Emboss')
                                        ->columnSpan(3),
                                ]),
                            ]),

                        Forms\Components\Actions::make([
                            Action::make('getBom')
                                ->label('Get Bill Of Material')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('success')
                                ->action(function ($get, $set) {
                                    $id = $get('item_source') ?: $get('itemable_id');
                                    if (! $id || $get('order_type') !== 'SPK') {
                                        $set('bom_items', []);
                                        return;
                                    }

                                    $qtyPesanan = (float) ($get('qty') ?? 0);
                                    $bom = app(\App\Services\Sales\SalesOrderService::class)->getBomPreview((int) $id, $qtyPesanan);
                                    $set('bom_items', $bom);
                                })
                        ])
                        ->visible(fn(Get $get) => $get('order_type') == 'SPK')
                        ->columnSpanFull(),

                        Forms\Components\Section::make('Bill of Material')
                            ->extraAttributes([
                                'class' => 'border-l-4 border-green-500 bg-green-50 dark:bg-gray-900 rounded-xl p-4'
                            ])
                            ->visible(fn ($get) => $get('order_type') === 'SPK')
                            ->schema([
                                Forms\Components\Placeholder::make('bom_empty_notice')
                                    ->hiddenLabel()
                                    ->content(new \Illuminate\Support\HtmlString('
                                        <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg text-amber-800 dark:text-amber-200 text-xs">
                                            ⚠️ <strong>Perhatian:</strong> Item Finish Good ini belum memiliki komponen Bill of Material (BOM). Silakan lengkapi data BOM pada master Finish Good jika diperlukan.
                                        </div>
                                    '))
                                    ->visible(fn (Get $get) => empty($get('bom_items')) && (!empty($get('item_source')) || !empty($get('itemable_id')))),

                                Forms\Components\Repeater::make('bom_items')
                                    ->live()
                                    ->label(function ($get) {
                                        $id = $get('item_source') ?: $get('itemable_id');
                                        if (!$id || $get('order_type') !== 'SPK') return 'Bill Of Material';
                                        
                                        $item = \App\Models\FinishGoodItem::find($id);
                                        return $item ? 'Daftar Bill Of Material (BOM) - ' . $item->nama_barang : 'Bill Of Material';
                                    })
                                    ->itemLabel(function ($state) {
                                        $nama = $state['nama_material'] ?? 'Material';
                                        $qtyMaster = $state['qty_master'] ?? 0;
                                        $satuan = $state['satuan'] ?? '';
                                        return "{$nama} ({$qtyMaster} {$satuan})";
                                    })
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_material')
                                            ->label('Nama Bahan / Material')
                                            ->disabled()
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('departemen')
                                            ->label('Departemen')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('qty_master')
                                            ->label('Qty BOM (Per Pcs)')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('qty_produksi')
                                            ->label('Total Qty Produksi')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('satuan')
                                            ->label('Satuan')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('waste')
                                            ->label('Waste (%)')
                                            ->numeric()
                                            ->disabled(),
                                    ])
                                    ->columns(7)
                                    ->defaultItems(0)
                                    ->collapsed(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->dehydrated(false),
                            ])
                            ->columnSpanFull(),

                        Section::make('Daftar Material')
                            ->visible(fn(Get $get) => $get('order_type') == 'MATERIAL')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship('items')
                                    ->defaultItems(1)
                                    ->schema([
                                        Select::make('master_item_id')
                                            ->label('Material')
                                            ->relationship(
                                                'material',
                                                'nama_master_item'
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),

                                        TextInput::make('qty')
                                            ->numeric()
                                            ->required()
                                            ->live(),

                                        TextInput::make('harga')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->required()
                                            ->live(),

                                        Placeholder::make('subtotal')
                                            ->label('Subtotal')
                                            ->content(function (Get $get) {
                                                $qty = (float) ($get('qty') ?? 0);
                                                $harga = str_replace(
                                                    '.',
                                                    '',
                                                    (string) ($get('harga') ?? 0)
                                                );
                                                return 'Rp ' . number_format(
                                                    $qty * (float) $harga,
                                                    0,
                                                    ',',
                                                    '.'
                                                );
                                            }),
                                    ])
                                    ->columns(4)
                            ]),
                    ]),
            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Sales Order';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('no_sales_order')
                    ->label('No SO')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => $state === 'SPK' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('customer.nama_customer')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('item_display')
                    ->label('Produk / Material')
                    ->badge()
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        if ($record->items->count()) {
                            return $record->items
                                ->pluck('material.nama_master_item')
                                ->filter()
                                ->implode(', ');
                        }

                        return $record->itemable?->nama_barang ?? '-';
                    }),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty Pesanan')
                    ->numeric()
                    ->getStateUsing(fn ($record) => $record->order_qty),

                Tables\Columns\TextColumn::make('status_pengiriman')
                    ->label('Status Pengiriman')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->fulfillment['status'])
                    ->color(fn ($record) => $record->fulfillment['color']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_type')
                    ->label('Tipe Pesanan')
                    ->options([
                        'SPK'      => 'Produksi (SPK)',
                        'MATERIAL' => 'Penjualan Material',
                    ]),

                Tables\Filters\SelectFilter::make('status_pengiriman')
                    ->label('Status Pengiriman')
                    ->options([
                        'BELUM DIKIRIM'    => 'Belum Dikirim',
                        'DIKIRIM SEBAGIAN' => 'Dikirim Sebagian',
                        'SELESAI'          => 'Selesai Dikirim',
                    ])
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereFulfillmentStatus($data['value']);
                        }
                    }),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Sales\SalesOrderService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Sales\SalesOrderService::class)->canModify($record) ? 'Sales Order terkunci karena sudah memiliki Surat Jalan atau SPK' : null),
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => route('sales-order.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('sales-order.download', $record)),
                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Sales\SalesOrderService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Sales\SalesOrderService::class)->canModify($record) ? 'Tidak dapat dihapus karena sudah memiliki Surat Jalan atau SPK' : null),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit'   => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}