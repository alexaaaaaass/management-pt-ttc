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
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form->schema([
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
                            return;
                        }

                        if ($get('order_type') === 'SPK') {
                            $item = \App\Models\FinishGoodItem::find($state);

                            $set('itemable_type', \App\Models\FinishGoodItem::class);
                            $set('itemable_id', $item?->id);
                            $set('customer_id', $item?->customer_id);
                            $set('kode_material', $item?->kode_material_produk);
                            $set('material_item_id', null);
                        } else {
                            $item = \App\Models\MasterItem::find($state);

                            $set('itemable_type', \App\Models\MasterItem::class);
                            $set('itemable_id', $item?->id);
                            $set('material_item_id', $item?->id);
                            $set('kode_material', $item?->kode_master_item);
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
                    ->disabled()
                    ->placeholder('Otomatis setelah disimpan'),

                Forms\Components\TextInput::make('no_po_customer'),

                Forms\Components\TextInput::make('qty')
                ->visible(fn(Get $get) => $get('order_type') == 'SPK')
    ->required(fn(Get $get) => $get('order_type') == 'SPK')
                    ->label('Jumlah Pesanan')
                    ->numeric()
                    ->required()
                    ->live() // Diperlukan agar tombol BOM bisa langsung membaca Qty terbaru
                    ->rule(function ($get) {
                        if ($get('order_type') === 'MATERIAL') {
                            return null;
                        }
                        
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $itemableType = $get('itemable_type');
                            $itemableId = $get('itemable_id');

                            if ($itemableType !== \App\Models\FinishGoodItem::class) return;

                            $finishGood = \App\Models\FinishGoodItem::with('materials')->find($itemableId);
                            if (!$finishGood) return;

                            foreach ($finishGood->materials as $bom) {
                                $kebutuhan = $value * $bom->qty;
                                $stock = \App\Models\MaterialStock::where('item_id', $bom->master_item_id)->first();
                                $available = $stock ? ($stock->on_hand - $stock->allocation) : 0;

                                if ($kebutuhan > $available) {
                                    $fail("Stock tidak cukup untuk material: "
                                        . ($bom->material->nama_master_item ?? '-')
                                        . " | Butuh: $kebutuhan | Sisa: $available");
                                    return;
                                }
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

                Forms\Components\Select::make('mata_uang')
                    ->options([
                        'rupiah' => 'Rupiah',
                        'usd' => 'USD',
                        'yuan' => 'Yuan',
                    ])
                    ->default('rupiah')
                    ->native(false),

                Forms\Components\TextInput::make('syarat_pembayaran'),

                Forms\Components\DatePicker::make('tanggal_po'),

                Forms\Components\Select::make('klaim_kertas')
                    ->options([
                        'claim' => 'Claim',
                        'not_claim' => 'Not Claim',
                    ])
                    ->native(false),

                Forms\Components\Select::make('dipesan_via')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'sosmed' => 'Sosmed',
                    ])
                    ->native(false),

                Forms\Components\TextInput::make('tipe_pesanan'),

                Forms\Components\TextInput::make('toleransi_pengiriman')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),

                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('catatan_colour_range')
                    ->columnSpanFull(),
            ]),

            Forms\Components\Actions::make([
                Action::make('getBom')
                    ->label('Get Bill Of Material')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($get, $set) {
                        $id = $get('item_source');
                        if (!$id || $get('order_type') !== 'SPK') {
                            $set('bom_items', []);
                            return;
                        }

                        $qtyPesanan = floatval($get('qty') ?? 0);
                        $item = \App\Models\FinishGoodItem::with([
                            'materials.material',
                            'materials.departemen'
                        ])->find($id);

                        if (!$item) return;

                        $bom = [];
                        foreach ($item->materials as $mat) {
                            $qtyBom = $qtyPesanan * floatval($mat->qty);
                            $bom[] = [
                                'nama_material' => $mat->material?->nama_master_item,
                                'departemen'    => $mat->departemen?->nama_departemen,
                                'qty_master'    => floatval($mat->qty),
                                'qty_produksi'  => round($qtyBom, 2),
                                'satuan'        => strtoupper($mat->material?->satuan?->nama_satuan ?? ''),
                                'waste'         => $mat->waste ?? 0,
                            ];
                        }
                        $set('bom_items', $bom);
                    })
            ])->columnSpanFull(),

            Forms\Components\Section::make('Bill of Material')
                ->extraAttributes([
                    'class' => 'border-l-4 border-green-500 bg-green-50 dark:bg-gray-900 rounded-xl p-4'
                ])
                ->visible(fn ($get) => $get('order_type') === 'SPK')
                ->schema([
                    Forms\Components\Repeater::make('bom_items')
                        ->live()
                        ->label(function ($get) {
                            $id = $get('item_source');
                            if (!$id || $get('order_type') !== 'SPK') return 'Bill Of Material';
                            
                            $item = \App\Models\FinishGoodItem::find($id);
                            return $item ? 'Bill Of Material - ' . $item->nama_barang : 'Bill Of Material';
                        })
                        ->itemLabel(function ($state) {
                            $nama = $state['nama_material'] ?? 'Material';
                            $qtyMaster = $state['qty_master'] ?? 0;
                            $satuan = $state['satuan'] ?? '';
                            return "{$nama} ({$qtyMaster} {$satuan})";
                        })
                        ->schema([
                            Forms\Components\TextInput::make('nama_material')
                                ->label('Material')
                                ->disabled(),

                            Forms\Components\TextInput::make('departemen')
                                ->label('Departemen')
                                ->disabled(),

                            Forms\Components\TextInput::make('satuan')
                                ->label('Satuan')
                                ->disabled(),

                            Forms\Components\TextInput::make('waste')
                                ->label('Waste')
                                ->numeric()
                                ->disabled(),

                            Forms\Components\TextInput::make('qty_master')
                                ->label('Qty BOM (Per Pcs)')
                                ->disabled(),

                            Forms\Components\TextInput::make('qty_produksi')
                                ->label('Total Qty Produksi')
                                ->disabled(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible()
                        ->addable(false)   // User tidak boleh nambah manual
                        ->dehydrated(false), // Tidak disimpan ke database sales_orders
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

    ])
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
                Tables\Columns\TextColumn::make('no_sales_order')->searchable(),
              Tables\Columns\TextColumn::make('item_display')
    ->label('Item')
    ->badge()
    ->separator(',')
    ->getStateUsing(function ($record) {

        if ($record->items->count()) {
            return $record->items
                ->pluck('material.nama_master_item')
                ->filter()
                ->implode(',');
        }

        return $record->itemable?->nama_barang;
    }),
                Tables\Columns\TextColumn::make('customer.nama_customer')->label('Customer'),
                Tables\Columns\TextColumn::make('kode_material')->label('Kode Material'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview_pdf')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('sales-order.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('sales-order.download', $record)),
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