<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;


class PurchaseOrderResource extends Resource
    {
        protected static ?string $model = PurchaseOrder::class;
        protected static ?string $navigationGroup = 'Purchase';
        protected static ?int $navigationSort = 2;

        protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form->schema([
        Wizard::make([

            // ==========================================
            // 🔥 TAHAP 1: PURCHASE REQUEST & SUPPLIER
            // ==========================================
            Wizard\Step::make('Purchase Request & Supplier')
                ->icon('heroicon-o-document-magnifying-glass')
                ->description('Pilih nomor PR acuan, tanggal PO, tipe, dan supplier penyedia')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('purchase_request_id')
                            ->label('Purchase Request')
                            ->relationship(
                                'purchaseRequest',
                                'nomor_pr',
                                modifyQueryUsing: fn ($query) => $query->where('status', 'otorisasi')
                            )
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => 
                                $record->nomor_pr . ' - ' . $record->departemen->nama_departemen
                            )
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $pr = \App\Models\PurchaseRequest::with('items.item')->find($state);

                                if ($pr && $pr->items->count()) {
                                    $html = '
                        <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th class="p-3 border dark:border-gray-700">Item</th>
                                        <th class="p-3 border dark:border-gray-700">Qty</th>
                                        <th class="p-3 border dark:border-gray-700">Satuan</th>
                                        <th class="p-3 border dark:border-gray-700">ETA</th>
                                        <th class="p-3 border dark:border-gray-700">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900">';
                                    foreach ($pr->items as $item) {
                                        $html .= "
                                    <tr class='border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800'>
                                        <td class='p-3'>{$item->item->nama_master_item}</td>
                                        <td class='p-3'>{$item->qty}</td>
                                        <td class='p-3'>{$item->satuan}</td>
                                        <td class='p-3'>".($item->eta ?? '-')."</td>
                                        <td class='p-3'>".($item->catatan ?? '-')."</td>
                                    </tr>";
                                    }
                                    $html .= '
                                </tbody>
                            </table>
                        </div>';
                                    $set('pr_preview', $html);
                                } else {
                                    $set('pr_preview', '<p class="text-danger">Tidak ada item dalam PR ini</p>');
                                }
                            }),

                        Forms\Components\DatePicker::make('po_date')
                            ->label('Tanggal PO')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('po_type')
                            ->label('PO Type')
                            ->options([
                                'reguler' => 'Reguler',
                                'subcount' => 'Subcount',
                            ])
                            ->default('reguler')
                            ->required(),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                    Forms\Components\Placeholder::make('pr_preview')
                        ->label('Pratinjau Kebutuhan Item dari PR')
                        ->content(fn ($get) => new \Illuminate\Support\HtmlString(
                            $get('pr_preview') ?? '<p class="text-gray-400 italic text-sm">Pilih Purchase Request di atas untuk melihat rincian item kebutuhan.</p>'
                        ))
                        ->columnSpanFull(),
                ]),

            // ==========================================
            // 🔥 TAHAP 2: PENGIRIMAN & FINANSIAL
            // ==========================================
            Wizard\Step::make('Pengiriman & Finansial')
                ->icon('heroicon-o-truck')
                ->description('Perkiraan tanggal tiba (ETA), mata uang, PPN, ongkir, dan uang muka')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\DatePicker::make('eta')
                            ->label('Estimasi Tiba (ETA)'),

                        Forms\Components\Select::make('currency')
                            ->label('Mata Uang')
                            ->options([
                                'IDR' => 'IDR (Rupiah)',
                                'USD' => 'USD (Dollar)',
                            ])
                            ->default('IDR'),

                        Forms\Components\TextInput::make('ppn')
                            ->label('PPN (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),

                        Forms\Components\TextInput::make('ongkir')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        Forms\Components\TextInput::make('dp')
                            ->label('Uang Muka (DP)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                ]),

            // ==========================================
            // 🔥 TAHAP 3: RINCIAN ITEM PO & HARGA
            // ==========================================
            Wizard\Step::make('Rincian Item & Harga')
                ->icon('heroicon-o-shopping-bag')
                ->description('Tarik item dari PR, tentukan kuantiti order, harga beli, dan diskon')
                ->schema([
                    Forms\Components\Actions::make([
                        Action::make('getData')
                            ->label('Tarik Data Item dari PR')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->action(function ($get, $set, $livewire) {
                                if ($livewire->record) {
                                    return;
                                }

                                $prId = $get('purchase_request_id');
                                if (!$prId) return;

                                $pr = \App\Models\PurchaseRequest::with('items.item')->find($prId);
                                if (!$pr) return;

                                $items = $pr->items->map(function ($item) {
                                    return [
                                        'item_id' => $item->item_id,
                                        'nama_item' => $item->item->nama_master_item,
                                        'qty_pr' => $item->qty,
                                        'qty_po' => 0,
                                        'qty_konversi' => 0,
                                        'price' => 0,
                                        'discount' => 0,
                                        'total' => 0,
                                        'satuan' => $item->satuan,
                                        'eta' => $item->eta,
                                        'catatan' => $item->catatan,
                                    ];
                                })->toArray();

                                $set('items', []);
                                $set('items', $items);
                            })
                    ])->columnSpanFull(),

                    Forms\Components\Section::make('Purchase Order Items')
                        ->extraAttributes([
                            'style' => 'border-left: 5px solid #3b82f6; border-radius: 10px; padding: 12px;',
                        ])
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->dehydrated(true)
                                ->extraAttributes([
                                    'class' => 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-3 bg-white dark:bg-gray-900 shadow-sm'
                                ])
                                ->label('Daftar Item')
                                ->itemLabel(fn ($state) => $state['nama_item'] ?? 'Item')
                                ->schema([
                                    Forms\Components\Hidden::make('nama_item'),

                                    Forms\Components\Select::make('item_id')
                                        ->label('Item')
                                        ->options(\App\Models\MasterItem::pluck('nama_master_item', 'id'))
                                        ->disabled()
                                        ->dehydrated(),

                                    Forms\Components\TextInput::make('qty_pr')
                                        ->label('Qty PR')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(),

                                    Forms\Components\TextInput::make('qty_po')
                                        ->label('Qty PO')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $qtyPR = (int) ($get('qty_pr') ?? 0);
                                            $qtyPO = (int) ($state ?? 0);

                                            if ($qtyPO > $qtyPR) {
                                                $qtyPO = $qtyPR;
                                                $set('qty_po', $qtyPR);
                                            }

                                            $set('qty_konversi', $qtyPO);

                                            $price = (float) ($get('price') ?? 0);
                                            $discount = (float) ($get('discount') ?? 0);
                                            $total = ($qtyPO * $price) * (1 - ($discount / 100));

                                            $set('total', round($total, 2));
                                        }),

                                    Forms\Components\TextInput::make('qty_konversi')
                                        ->label('Qty After Conversion')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(),

                                    Forms\Components\TextInput::make('price')
                                        ->label('Price / Unit')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->dehydrated()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $qty = (int) ($get('qty_konversi') ?? 0);
                                            $price = (float) ($state ?? 0);
                                            $discount = (float) ($get('discount') ?? 0);
                                            $total = ($qty * $price) * (1 - ($discount / 100));

                                            $set('total', round($total, 2));
                                        }),

                                    Forms\Components\TextInput::make('discount')
                                        ->label('Discount (%)')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $qty = (int) ($get('qty_konversi') ?? 0);
                                            $price = (float) ($get('price') ?? 0);
                                            $discount = (float) ($state ?? 0);
                                            $total = ($qty * $price) * (1 - ($discount / 100));

                                            $set('total', round($total, 2));
                                        }),

                                    Forms\Components\TextInput::make('total')
                                        ->label('Total')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->disabled()
                                        ->dehydrated()
                                        ->afterStateHydrated(function ($state, callable $set) {
                                            $items = collect($state)->map(function ($item) {
                                                $qty = (int) ($item['qty_konversi'] ?? 0);
                                                $price = (float) ($item['price'] ?? 0);
                                                $discount = (float) ($item['discount'] ?? 0);
                                                $item['total'] = ($qty * $price) * (1 - ($discount / 100));
                                                return $item;
                                            });
                                            $set('items', $items->toArray());
                                        })
                                        ->formatStateUsing(fn ($state) => 
                                            $state ? 'Rp ' . number_format($state, 0, ',', '.') : 'Rp 0'
                                        ),

                                    Forms\Components\TextInput::make('satuan')
                                        ->disabled()
                                        ->dehydrated(),

                                    Forms\Components\DatePicker::make('eta'),

                                    Forms\Components\Textarea::make('catatan'),
                                ])
                                ->columns(3)
                                ->defaultItems(0)
                                ->collapsible()
                                ->cloneable(false)
                                ->reorderable(false),
                        ]),

                    Forms\Components\Placeholder::make('subtotal')
                        ->label('Subtotal Keseluruhan')
                        ->reactive()
                        ->content(function ($get) {
                            $items = $get('items') ?? [];
                            $subtotal = collect($items)->sum(function ($item) {
                                return (float) str_replace(',', '', $item['total'] ?? 0);
                            });

                            return new \Illuminate\Support\HtmlString(
                                '<div class="text-right text-xl font-bold text-green-600 dark:text-green-400">
                                    Total: Rp ' . number_format($subtotal, 0, ',', '.') . '
                                </div>'
                            );
                        })
                        ->columnSpanFull(),
                ]),
        ])
        ->skippable()
        ->columnSpanFull(),
    ]);
}

public static function getNavigationLabel(): string
{
    return 'Purchase Order';
}

        public static function table(Table $table): Table
    {
        return $table
         ->defaultSort('created_at', 'desc')
            ->columns([
            Tables\Columns\TextColumn::make('no_po')
    ->label('No PO')
    ->searchable()
    ->sortable(),

                    Tables\Columns\TextColumn::make('purchaseRequest.nomor_pr')
    ->label('No PR')
    ->searchable()
    ->sortable(),
     Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier'),

                    Tables\Columns\TextColumn::make('po_type')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'reguler' => 'success',
        'subcount' => 'warning',
        default => 'gray',
    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Partial'   => 'warning',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('po_date')
                    ->date(),

            ])
           ->actions([

    Tables\Actions\ActionGroup::make([

        Tables\Actions\EditAction::make(),

        Tables\Actions\Action::make('preview_pdf')
            ->label('Preview PDF')
            ->icon('heroicon-o-eye')
            ->url(fn ($record) =>
                route('purchase-order.preview', $record)
            )
            ->openUrlInNewTab(),

        Tables\Actions\Action::make('download_pdf')
            ->label('Download PDF')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn ($record) =>
                route('purchase-order.download', $record)
            ),

        Tables\Actions\DeleteAction::make()
            ->disabled(fn ($record) => ! app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->canModify($record))
            ->tooltip(fn ($record) => ! app(\App\Services\PurchaseOrder\PurchaseOrderService::class)->canModify($record) ? 'Tidak dapat dihapus karena sudah memiliki Penerimaan Barang atau Faktur terkait' : null),

    ])
    ->icon('heroicon-m-ellipsis-horizontal')

])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

        public static function getRelations(): array
        {
          return [
        RelationManagers\ItemsRelationManager::class,
    ];
        }

        public static function getPages(): array
        {
            return [
                'index' => Pages\ListPurchaseOrders::route('/'),
                'create' => Pages\CreatePurchaseOrder::route('/create'),
                'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            ];
        }
    }