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
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function formatMoney(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return number_format((float) $value, 0, '.', ',');
        }
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
        return $cleaned !== '' ? number_format((float) $cleaned, 0, '.', ',') : null;
    }

    public static function cleanMoney(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) round($value);
        }
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);
        return $cleaned !== '' ? (int) $cleaned : null;
    }

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
                                    $set('order_confirmation_id', null);
                                    $set('order_confirmation_item_id', null);
                                }),

                            Forms\Components\Select::make('order_confirmation_id')
                                ->label('No. Order Confirmation (Opsional)')
                                ->placeholder('-- Pilih Order Confirmation jika ada --')
                                ->visible(fn(Get $get) => $get('order_type') == 'SPK')
                                ->options(function () {
                                    return \App\Models\OrderConfirmation::with(['customer', 'items'])
                                        ->latest('id')
                                        ->get()
                                        ->mapWithKeys(function ($oc) {
                                            $itemNames = $oc->items->pluck('item_name')->filter()->implode(', ');
                                            $cust = $oc->customer?->nama_customer ?? '-';
                                            $label = "{$oc->doc_no} - {$cust}" . ($itemNames ? " ({$itemNames})" : "");
                                            return [$oc->id => $label];
                                        });
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (! $state) {
                                        return;
                                    }

                                    $oc = \App\Models\OrderConfirmation::with(['customer', 'items.finishGoodItem.typeItem'])->find($state);
                                    if (! $oc) {
                                        return;
                                    }

                                    if ($oc->customer_id) {
                                        $set('customer_id', $oc->customer_id);
                                    }
                                    if ($oc->lead_time) {
                                        $set('lead_time', $oc->lead_time);
                                    }
                                    if ($oc->tanggal) {
                                        $set('tanggal_po', $oc->tanggal->format('Y-m-d'));
                                    }
                                    if ($oc->catatan) {
                                        $set('catatan', $oc->catatan);
                                    }
                                    if ($oc->diskon !== null) {
                                        $set('diskon', (float) $oc->diskon);
                                    }

                                    $formData = app(\App\Services\Sales\SalesOrderService::class)->prepareFormDataFromOrderConfirmation($oc);
                                    foreach ($formData as $field => $val) {
                                        $set($field, $val);
                                    }
                                }),

                            Forms\Components\Select::make('order_confirmation_item_id')
                                ->label('Pilih Item dari Order Confirmation')
                                ->placeholder('-- Pilih Item Penawaran --')
                                ->visible(function (Get $get) {
                                    $ocId = $get('order_confirmation_id');
                                    if (! $ocId || $get('order_type') !== 'SPK') return false;
                                    return \App\Models\OrderConfirmationItem::where('order_confirmation_id', $ocId)->count() > 1;
                                })
                                ->options(function (Get $get) {
                                    $ocId = $get('order_confirmation_id');
                                    if (! $ocId) return [];

                                    $oc = \App\Models\OrderConfirmation::with('items')->find($ocId);
                                    if (! $oc) return [];

                                    $progress = app(\App\Services\Marketing\MarketingWorkflowService::class)->getOrderConfirmationProgress($oc);
                                    $existingSoFgIds = $progress['existing_so_fg_ids'];

                                    return $oc->items
                                        ->mapWithKeys(function ($item) use ($existingSoFgIds) {
                                            $name = $item->item_name ?: 'Item';
                                            $qty = number_format((float) $item->qty, 0, ',', '.');
                                            $harga = number_format((float) $item->harga, 0, ',', '.');
                                            $isCreated = $item->finish_good_item_id && in_array($item->finish_good_item_id, $existingSoFgIds);
                                            $badge = $isCreated ? ' ✅ (Sudah Terbit SO)' : ' ⚠️ (Belum Terbit SO)';
                                            return [$item->id => "{$name} (Qty: {$qty} pcs @ Rp {$harga}){$badge}"];
                                        });
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if (! $state) return;
                                    $ocId = $get('order_confirmation_id');
                                    if (! $ocId) return;

                                    $oc = \App\Models\OrderConfirmation::with(['items.finishGoodItem.typeItem'])->find($ocId);
                                    if (! $oc) return;

                                    $formData = app(\App\Services\Sales\SalesOrderService::class)->prepareFormDataFromOrderConfirmation($oc, (int) $state);
                                    foreach ($formData as $field => $val) {
                                        $set($field, $val);
                                    }
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
                                        $currentProductName = trim((string) ($get('product_name') ?? ''));
                                        if ($currentProductName === '' && $item && strtoupper(trim((string) $item->nama_barang)) !== 'KERTAS IVORY') {
                                            $set('product_name', $item->nama_barang);
                                        }
                                        $set('material_item_id', null);
                                        $set('jenis', $item?->jenis);
                                        $set('komponen', $item?->komponen ?? $item?->typeItem?->nama_type_item);
                                        $set('isi', $item?->isi);
                                        $set('kertas', $item?->spesifikasi_kertas);
                                        $set('warna', $item?->warna);
                                        $set('finishing', $item?->finishing);

                                        // Otomatis tarik Bill of Material (BOM) sesuai porsi produksi
                                        static::syncFulfillmentAndBom($set, $get);
                                    } else {
                                        $item = \App\Models\MasterItem::find($state);

                                        $set('itemable_type', \App\Models\MasterItem::class);
                                        $set('itemable_id', $item?->id);
                                        $set('material_item_id', $item?->id);
                                        $set('kode_material', $item?->kode_master_item);
                                        $set('bom_items', []);
                                    }
                                }),

                            Forms\Components\TextInput::make('product_name')
                                ->label('Nama Item / Produk')
                                ->placeholder('Contoh: DAFI 16')
                                ->visible(fn(Get $get) => $get('order_type') == 'SPK'),

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
                                ->quantityMask(0)
                                ->live(debounce: 500)
                                ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                    if ($get('order_type') === 'SPK') {
                                        static::syncFulfillmentAndBom($set, $get, $record);
                                    }
                                })
                                ->rule(function ($get, $record) {
                                    if ($get('order_type') === 'MATERIAL') {
                                        return null;
                                    }
                                    
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        $itemableType = $get('itemable_type');
                                        $itemableId = $get('itemable_id');

                                        if ($itemableType !== \App\Models\FinishGoodItem::class || !$itemableId) return;

                                        $fulfillment = $get('fulfillment_type') ?? 'PRODUCTION';
                                        $qtyVal = (float) $value;
                                        $qtyFromStock = (float) ($get('qty_from_stock') ?? 0);

                                        $qtyToProduce = match ($fulfillment) {
                                            'FROM_STOCK'    => 0.0,
                                            'PARTIAL_STOCK' => max(0.0, $qtyVal - $qtyFromStock),
                                            default         => $qtyVal,
                                        };

                                        if ($qtyToProduce <= 0) {
                                            return;
                                        }

                                        $shortages = app(\App\Services\Inventory\StockService::class)->checkBomShortages(
                                            (int) $itemableId,
                                            $qtyToProduce
                                        );

                                        if (!empty($shortages)) {
                                            $first = $shortages[0];
                                            $fail("Stock tidak cukup untuk material: {$first['nama_material']} | Butuh: {$first['kebutuhan']} | Sisa: {$first['tersedia']}");
                                        }
                                    };
                                }),

                            Forms\Components\Placeholder::make('buffer_stock_info')
                                ->label('Status Buffer Stock Warehouse')
                                ->visible(fn (Get $get) => $get('order_type') === 'SPK' && ! empty($get('itemable_id')))
                                ->columnSpanFull()
                                ->content(function (Get $get, $record) {
                                    $fgId = (int) ($get('itemable_id') ?: $get('item_source') ?: 0);
                                    if (! $fgId) {
                                        return '-';
                                    }

                                    $stock = \App\Models\FinishGoodStock::where('finish_good_item_id', $fgId)->first();
                                    $onHand = (float) ($stock?->on_hand ?? 0);
                                    $reserved = (float) ($stock?->reserved ?? 0);
                                    $free = max(0.0, $onHand - $reserved);

                                    if ($record && (int) $record->itemable_id === $fgId) {
                                        $alreadyShipped = (float) $record->suratJalans()->sum('qty_pengiriman');
                                        $ownReserved = max(0.0, (float) ($record->qty_from_stock ?? 0) - $alreadyShipped);
                                        $free += $ownReserved;
                                        $reservedOther = max(0.0, $reserved - $ownReserved);
                                    } else {
                                        $reservedOther = $reserved;
                                    }

                                    $badgeColor = $free > 0
                                        ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300'
                                        : 'border-gray-600/40 bg-gray-800/40 text-gray-300';

                                    $statusText = $free > 0
                                        ? 'Tersedia Buffer Stock bebas siap pakai untuk langsung dipotong tanpa/kurangi SPK.'
                                        : 'Belum ada Buffer Stock bebas di gudang — pesanan perlu diproduksi via SPK.';

                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="rounded-lg border p-3 text-sm ' . $badgeColor . '">'
                                        . '<div class="font-semibold mb-1">' . $statusText . '</div>'
                                        . '<div class="flex flex-wrap gap-4 text-xs">'
                                        . '<span>Total Fisik (On Hand): <strong>' . number_format($onHand, 0, ',', '.') . ' pcs</strong></span>'
                                        . '<span>Reserved SO Lain: <strong>' . number_format($reservedOther, 0, ',', '.') . ' pcs</strong></span>'
                                        . '<span>Buffer Stock Bebas: <strong>' . number_format($free, 0, ',', '.') . ' pcs</strong></span>'
                                        . '</div></div>'
                                    );
                                }),

                            Forms\Components\Select::make('fulfillment_type')
                                ->label('Metode Pemenuhan Order')
                                ->visible(fn (Get $get) => $get('order_type') === 'SPK')
                                ->required(fn (Get $get) => $get('order_type') === 'SPK')
                                ->default('PRODUCTION')
                                ->native(false)
                                ->live()
                                ->options([
                                    'PRODUCTION'    => 'Produksi Baru (Buat SPK Penuh)',
                                    'FROM_STOCK'    => 'Ambil Full dari Buffer Stock Warehouse (Tanpa SPK)',
                                    'PARTIAL_STOCK' => 'Kombinasi: Sebagian Buffer Stock + Sebagian Produksi SPK',
                                ])
                                ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                    static::syncFulfillmentAndBom($set, $get, $record);
                                })
                                ->rule(function (Get $get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        if ($value !== 'FROM_STOCK') {
                                            return;
                                        }

                                        $fgId = (int) ($get('itemable_id') ?: $get('item_source') ?: 0);
                                        $qty = (float) ($get('qty') ?? 0);
                                        if (! $fgId || $qty <= 0) {
                                            return;
                                        }

                                        $free = static::getEffectiveFreeBufferForForm($fgId, $record);
                                        if ($free < $qty) {
                                            $fail("Buffer stock bebas hanya tersedia " . number_format($free, 0, ',', '.') . " pcs (kurang dari {$qty} pcs). Pilih Kombinasi atau Produksi Baru.");
                                        }
                                    };
                                }),

                            Forms\Components\TextInput::make('qty_from_stock')
                                ->label('Qty Diambil dari Buffer Stock (pcs)')
                                ->visible(fn (Get $get) => $get('order_type') === 'SPK' && $get('fulfillment_type') !== 'PRODUCTION')
                                ->numeric()
                                ->default(0)
                                ->dehydrated()
                                ->readOnly(fn (Get $get) => $get('fulfillment_type') === 'FROM_STOCK')
                                ->live(debounce: 400)
                                ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                    static::syncFulfillmentAndBom($set, $get, $record, true);
                                })
                                ->rule(function (Get $get, $record) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                        if (($get('fulfillment_type') ?? 'PRODUCTION') !== 'PARTIAL_STOCK') {
                                            return;
                                        }

                                        $fgId = (int) ($get('itemable_id') ?: $get('item_source') ?: 0);
                                        $qty = (float) ($get('qty') ?? 0);
                                        $val = (float) $value;

                                        if ($val > $qty) {
                                            $fail("Qty dari stock tidak boleh melebihi Jumlah Pesanan ({$qty}).");
                                            return;
                                        }

                                        if ($fgId) {
                                            $free = static::getEffectiveFreeBufferForForm($fgId, $record);
                                            if ($val > $free) {
                                                $fail("Qty melebihi Buffer Stock bebas tersedia (" . number_format($free, 0, ',', '.') . " pcs).");
                                            }
                                        }
                                    };
                                }),

                            Forms\Components\TextInput::make('qty_to_produce')
                                ->label('Qty Sisa Wajib Diproduksi via SPK (pcs)')
                                ->visible(fn (Get $get) => $get('order_type') === 'SPK' && $get('fulfillment_type') !== 'PRODUCTION')
                                ->numeric()
                                ->default(0)
                                ->readOnly()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('harga_pcs')
                                ->label('Harga pcs')
                                ->visible(fn(Get $get)=>$get('order_type')=='SPK')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                ->stripCharacters(',')
                                ->formatStateUsing(fn ($state) => static::formatMoney($state))
                                ->dehydrateStateUsing(fn ($state) => static::cleanMoney($state))
                                ->required(),

                            Forms\Components\TextInput::make('harga_kirim')
                                ->label('Harga kirim')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                ->stripCharacters(',')
                                ->formatStateUsing(fn ($state) => static::formatMoney($state))
                                ->dehydrateStateUsing(fn ($state) => static::cleanMoney($state)),

                            Forms\Components\TextInput::make('diskon')
                                ->label('Diskon')
                                ->prefix('Rp')
                                ->default(0)
                                ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                ->stripCharacters(',')
                                ->formatStateUsing(fn ($state) => static::formatMoney($state ?? 0) ?? '0')
                                ->dehydrateStateUsing(fn ($state) => static::cleanMoney($state) ?? 0),

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
                                        $qtyMaster = (float) ($state['qty_master'] ?? 0);
                                        $satuan = $state['satuan'] ?? '';
                                        if ($qtyMaster > 0) {
                                            return "{$nama} ({$qtyMaster} {$satuan})";
                                        }
                                        return "{$nama} [Bahan Penolong / Request di IMR]";
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
                                            ->formatStateUsing(function ($state) {
                                                $val = (float) ($state ?? 0);
                                                return $val > 0 ? (string) $val : 'Bahan Penolong (0)';
                                            })
                                            ->disabled(),

                                        Forms\Components\TextInput::make('qty_produksi')
                                            ->label('Total Qty Produksi')
                                            ->formatStateUsing(function ($state) {
                                                $val = (float) ($state ?? 0);
                                                return $val > 0 ? (string) $val : 'Request saat IMR';
                                            })
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
                                            ->quantityMask(0)
                                            ->required()
                                            ->live(),

                                        TextInput::make('harga')
                                            ->currencyMask(2)
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

                Tables\Columns\TextColumn::make('orderConfirmation.doc_no')
                    ->label('No OC')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => $state === 'SPK' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('fulfillment_type')
                    ->label('Sumber Pemenuhan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state, $record) => match (true) {
                        $record->order_type !== 'SPK' => '-',
                        $state === 'FROM_STOCK'       => 'Buffer Stock',
                        $state === 'PARTIAL_STOCK'    => 'Buffer (' . number_format((float) $record->qty_from_stock, 0, ',', '.') . ') + SPK (' . number_format((float) $record->qty_to_produce, 0, ',', '.') . ')',
                        default                       => 'Produksi SPK',
                    })
                    ->color(fn (?string $state, $record) => match (true) {
                        $record->order_type !== 'SPK' => 'gray',
                        $state === 'FROM_STOCK'       => 'success',
                        $state === 'PARTIAL_STOCK'    => 'warning',
                        default                       => 'info',
                    }),

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

                        return $record->product_name ?: ($record->itemable?->nama_barang ?? '-');
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'orderConfirmation',
                'customer',
                'itemable',
                'items.material',
                'suratJalans.items',
                'spk',
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

    /**
     * Sinkronisasi qty_from_stock, qty_to_produce, dan pratinjau BOM sesuai metode pemenuhan order.
     */
    protected static function syncFulfillmentAndBom(
        callable $set,
        callable $get,
        ?\App\Models\SalesOrder $record = null,
        bool $preferExistingQtyFromStock = false
    ): void {
        $fgId = (int) ($get('itemable_id') ?: $get('item_source') ?: 0);
        $qty = max(0.0, (float) ($get('qty') ?? 0));
        $mode = $get('fulfillment_type') ?: 'PRODUCTION';

        $effectiveFree = $fgId ? static::getEffectiveFreeBufferForForm($fgId, $record) : 0.0;

        if ($mode === 'FROM_STOCK') {
            $qtyFromStock = $qty;
            $qtyToProduce = 0.0;
        } elseif ($mode === 'PARTIAL_STOCK') {
            $rawFromStock = $preferExistingQtyFromStock
                ? (float) ($get('qty_from_stock') ?? 0)
                : min($qty, $effectiveFree);
            $qtyFromStock = min($qty, max(0.0, $rawFromStock));
            $qtyToProduce = max(0.0, $qty - $qtyFromStock);
        } else {
            $qtyFromStock = 0.0;
            $qtyToProduce = $qty;
        }

        $set('qty_from_stock', $qtyFromStock);
        $set('qty_to_produce', $qtyToProduce);

        if ($fgId && $qtyToProduce > 0) {
            $bom = app(\App\Services\Sales\SalesOrderService::class)->getBomPreview($fgId, $qtyToProduce);
            $set('bom_items', $bom);
        } else {
            $set('bom_items', []);
        }
    }

    /**
     * Hitung Buffer Stock bebas termasuk sisa alokasi milik SO yang sedang diedit.
     */
    protected static function getEffectiveFreeBufferForForm(int $fgId, ?\App\Models\SalesOrder $record = null): float
    {
        $free = app(\App\Services\Inventory\FinishGoodStockService::class)->getFreeBufferStock($fgId);

        if ($record && (int) $record->itemable_id === $fgId) {
            $alreadyShipped = (float) $record->suratJalans()->sum('qty_pengiriman');
            $ownReserved = max(0.0, (float) ($record->qty_from_stock ?? 0) - $alreadyShipped);
            $free += $ownReserved;
        }

        return $free;
    }
}