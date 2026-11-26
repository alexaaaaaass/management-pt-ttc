<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Filament\Admin\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\MasterItem;
use App\Models\MasterSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationGroup = 'Purchase';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?array $cachedSuppliers = null;

    protected static function getSupplierNameMap(?int $mustIncludeId = null): array
    {
        if (
            static::$cachedSuppliers === null
            || ($mustIncludeId && ! array_key_exists($mustIncludeId, static::$cachedSuppliers))
        ) {
            static::$cachedSuppliers = MasterSupplier::query()
                ->orderBy('nama_supplier')
                ->pluck('nama_supplier', 'id')
                ->all();
        }

        return static::$cachedSuppliers;
    }

    protected static function formatQtyNumber(float $value): string
    {
        $formatted = number_format($value, 2, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }

    /**
     * Render tabel pratinjau kebutuhan item PR (termasuk qty yang sudah di-PO, sisa, dan supplier rekomendasi).
     */
    public static function buildPrPreviewHtml(?int $prId, ?int $excludePoId = null, bool $isCreate = true): string
    {
        if (! $prId) {
            return '<p class="text-gray-400 italic text-sm">Pilih Purchase Request di atas untuk melihat rincian item kebutuhan.</p>';
        }

        $pr = PurchaseRequest::with(['items.item', 'purchaseOrders.supplier'])->find($prId);
        if (! $pr || $pr->items->isEmpty()) {
            return '<p class="text-red-500 text-sm">Tidak ada item dalam Purchase Request ini.</p>';
        }

        $service = app(PurchaseOrderService::class);
        $orderedMap = $service->getPrOrderedSummary($prId, $excludePoId);
        $itemIds = $pr->items->pluck('item_id')->filter()->all();
        $recSuppliers = $service->getRecommendedSuppliersForItems($itemIds);

        $rowsHtml = '';
        $hasRemaining = false;

        foreach ($pr->items as $item) {
            $itemId = (int) $item->item_id;
            $qtyPr = (float) $item->qty;
            $ordered = (float) ($orderedMap[$itemId] ?? 0);
            $sisa = max(0.0, round($qtyPr - $ordered, 4));

            if ($sisa > 0) {
                $hasRemaining = true;
            }

            $itemName = e($item->item?->nama_master_item ?? '-');
            $satuan = e($item->satuan ?? '-');
            $eta = e($item->eta ?? '-');
            $catatan = e($item->catatan ?? '-');
            $recName = isset($recSuppliers[$itemId])
                ? '<span style="display:inline-flex;align-items:center;padding:0.125rem 0.5rem;border-radius:0.375rem;font-size:0.75rem;font-weight:500;background:rgba(59,130,246,0.16);color:#60a5fa;border:1px solid rgba(59,130,246,0.3);">' . e($recSuppliers[$itemId]['name']) . '</span>'
                : '<span class="text-gray-400 text-xs">-</span>';

            $orderedCell = $ordered > 0
                ? '<span style="display:inline-flex;align-items:center;padding:0.125rem 0.5rem;border-radius:0.375rem;font-size:0.75rem;font-weight:500;background:rgba(245,158,11,0.16);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);">' . static::formatQtyNumber($ordered) . '</span>'
                : '<span class="text-gray-400">0</span>';

            $sisaCell = $sisa > 0
                ? '<span style="font-weight:600;color:#10b981;">' . static::formatQtyNumber($sisa) . '</span>'
                : '<span style="display:inline-flex;align-items:center;padding:0.125rem 0.5rem;border-radius:0.375rem;font-size:0.75rem;font-weight:500;background:rgba(148,163,184,0.16);color:#9ca3af;">Full PO</span>';

            $rowsHtml .= "
                <tr class='border-b dark:border-gray-700'>
                    <td class='p-3 font-medium'>{$itemName}</td>
                    <td class='p-3 text-right'>" . static::formatQtyNumber($qtyPr) . "</td>
                    <td class='p-3 text-right'>{$orderedCell}</td>
                    <td class='p-3 text-right'>{$sisaCell}</td>
                    <td class='p-3'>{$satuan}</td>
                    <td class='p-3'>{$recName}</td>
                    <td class='p-3 whitespace-nowrap'>{$eta}</td>
                    <td class='p-3'>{$catatan}</td>
                </tr>";
        }

        $existingPosHtml = '';
        $linkedPos = $pr->purchaseOrders->when($excludePoId, fn ($c) => $c->where('id', '!=', $excludePoId));
        if ($linkedPos->isNotEmpty()) {
            $badges = $linkedPos->map(function ($po) {
                $sup = $po->supplier?->nama_supplier ?? '-';

                return '<span style="display:inline-flex;align-items:center;padding:0.125rem 0.5rem;border-radius:0.25rem;font-size:0.75rem;background:rgba(148,163,184,0.14);border:1px solid rgba(148,163,184,0.28);">'
                    . e($po->no_po) . ' · ' . e($sup) . '</span>';
            })->implode(' ');

            $existingPosHtml = '<div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">'
                . '<span class="font-medium">PO Terkait Sebelumnya (' . $linkedPos->count() . '):</span> ' . $badges . '</div>';
        }

        $tipHtml = '';
        if ($isCreate) {
            if (! $hasRemaining) {
                $tipHtml = '<div style="margin-top:0.5rem;border-radius:0.5rem;border:1px solid rgba(245,158,11,0.35);background:rgba(245,158,11,0.12);padding:0.5rem 0.75rem;font-size:0.75rem;color:#fbbf24;">'
                    . 'Seluruh kebutuhan item pada PR ini sudah diterbitkan PO sepenuhnya.'
                    . '</div>';
            } else {
                $tipHtml = '<div style="margin-top:0.5rem;border-radius:0.5rem;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.12);padding:0.5rem 0.75rem;font-size:0.75rem;color:#60a5fa;">'
                    . '<strong>Multi-Supplier Aktif:</strong> Pada Tahap 3 Anda dapat memilih supplier berbeda di setiap baris item, atau klik tombol <strong>Split Supplier</strong> untuk memecah 1 material ke beberapa supplier. Sistem otomatis menerbitkan 1 nomor PO resmi per supplier.'
                    . '</div>';
            }
        }

        return '
            <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs uppercase">
                        <tr>
                            <th class="p-3 border-b dark:border-gray-700">Item</th>
                            <th class="p-3 border-b dark:border-gray-700 text-right">Qty PR</th>
                            <th class="p-3 border-b dark:border-gray-700 text-right">Sudah Di-PO</th>
                            <th class="p-3 border-b dark:border-gray-700 text-right">Sisa Qty</th>
                            <th class="p-3 border-b dark:border-gray-700">Satuan</th>
                            <th class="p-3 border-b dark:border-gray-700">Supplier Rekomendasi</th>
                            <th class="p-3 border-b dark:border-gray-700">ETA</th>
                            <th class="p-3 border-b dark:border-gray-700">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900">' . $rowsHtml . '</tbody>
                </table>
            </div>' . $existingPosHtml . $tipHtml;
    }

    /**
     * Tarik daftar item PR yang masih memiliki sisa kuota untuk mengisi repeater Tahap 3.
     */
    public static function buildPrRepeaterItems(int $prId, ?int $defaultSupplierId = null): array
    {
        $pr = PurchaseRequest::with(['items.item.materialPlate'])->find($prId);
        if (! $pr) {
            return [];
        }

        $service = app(PurchaseOrderService::class);
        $orderedMap = $service->getPrOrderedSummary($prId);
        $itemIds = $pr->items->pluck('item_id')->filter()->all();
        $recSuppliers = $service->getRecommendedSuppliersForItems($itemIds);

        $rows = [];
        foreach ($pr->items as $item) {
            $itemId = (int) $item->item_id;
            $qtyPr = (float) $item->qty;
            $alreadyOrdered = (float) ($orderedMap[$itemId] ?? 0);
            $sisaQty = max(0.0, round($qtyPr - $alreadyOrdered, 2));

            if ($sisaQty <= 0) {
                continue;
            }

            $refPrice = (float) ($item->item?->materialPlate?->harga_beli ?? 0);
            $resolvedSupplierId = $defaultSupplierId ?: ($recSuppliers[$itemId]['id'] ?? null);
            $lineTotal = round($sisaQty * $refPrice, 2);

            $rows[] = [
                'id'           => null,
                'item_id'      => $itemId,
                'nama_item'    => $item->item?->nama_master_item ?? 'Item',
                'supplier_id'  => $resolvedSupplierId,
                'qty_pr'       => $qtyPr,
                'qty_sisa_pr'  => $sisaQty,
                'qty_po'       => $sisaQty,
                'qty_konversi' => $sisaQty,
                'price'        => $refPrice > 0 ? (MoneyHelper::format($refPrice, 2) ?? '0') : '0',
                'discount'     => 0,
                'total'        => $lineTotal,
                'satuan'       => $item->satuan,
                'eta'          => $item->eta,
                'catatan'      => $item->catatan,
            ];
        }

        return $rows;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([

                // ==========================================
                // TAHAP 1: PURCHASE REQUEST & SUPPLIER
                // ==========================================
                Wizard\Step::make('Purchase Request & Supplier')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->description('Pilih nomor PR acuan, tanggal PO, tipe, dan supplier default (opsional)')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('purchase_request_id')
                                ->label('Purchase Request')
                                ->relationship(
                                    'purchaseRequest',
                                    'nomor_pr',
                                    modifyQueryUsing: fn ($query) => $query
                                        ->with(['departemen', 'items', 'purchaseOrders.items'])
                                        ->where('status', 'otorisasi')
                                        ->orderByDesc('created_at')
                                        ->orderByDesc('id')
                                )
                                ->searchable()
                                ->preload()
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated()
                                ->getOptionLabelFromRecordUsing(function ($record) {
                                    $dept = $record->departemen?->nama_departemen ?? '-';
                                    $totalReq = (float) $record->items->sum('qty');
                                    $totalOrd = (float) $record->purchaseOrders->flatMap->items->sum('qty_po');
                                    $sisa = max(0.0, round($totalReq - $totalOrd, 2));
                                    $statusTag = $sisa <= 0
                                        ? ' (Sudah Full PO)'
                                        : ($totalOrd > 0 ? ' (Sisa ' . static::formatQtyNumber($sisa) . ')' : '');

                                    return "{$record->nomor_pr} - {$dept}{$statusTag}";
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, callable $get, string $operation) {
                                    $prId = $state ? (int) $state : null;
                                    $set('pr_preview', static::buildPrPreviewHtml($prId, null, $operation === 'create'));

                                    if ($operation === 'create') {
                                        if ($prId) {
                                            $defaultSupplier = $get('supplier_id') ? (int) $get('supplier_id') : null;
                                            $set('items', static::buildPrRepeaterItems($prId, $defaultSupplier));
                                        } else {
                                            $set('items', []);
                                        }
                                    }
                                }),

                            Forms\Components\DatePicker::make('po_date')
                                ->label('Tanggal PO')
                                ->default(now())
                                ->required(),

                            Forms\Components\Select::make('po_type')
                                ->label('PO Type')
                                ->options([
                                    'reguler'  => 'Reguler',
                                    'subcount' => 'Subcount',
                                ])
                                ->default('reguler')
                                ->required(),

                            Forms\Components\Select::make('supplier_id')
                                ->label(fn (string $operation) => $operation === 'create' ? 'Supplier Default (Opsional)' : 'Supplier')
                                ->relationship('supplier', 'nama_supplier')
                                ->placeholder(fn (string $operation) => $operation === 'create' ? 'Kosongkan jika supplier beda-beda per item' : 'Pilih supplier')
                                ->helperText(fn (string $operation) => $operation === 'create'
                                    ? 'Opsional: otomatis mengisi supplier pada baris item yang masih kosong di Tahap 3. Setiap baris tetap bisa dipilih supplier berbeda.'
                                    : null)
                                ->searchable()
                                ->preload()
                                ->required(fn (string $operation) => $operation === 'edit')
                                ->live()
                                ->afterStateUpdated(function ($state, callable $get, callable $set, string $operation) {
                                    if ($operation !== 'create' || empty($state)) {
                                        return;
                                    }

                                    $items = $get('items') ?? [];
                                    if (empty($items)) {
                                        return;
                                    }

                                    $updated = false;
                                    foreach ($items as $key => $row) {
                                        if (empty($row['supplier_id'])) {
                                            $items[$key]['supplier_id'] = (int) $state;
                                            $updated = true;
                                        }
                                    }

                                    if ($updated) {
                                        $set('items', $items);
                                    }
                                }),
                        ]),

                        Forms\Components\Placeholder::make('pr_preview')
                            ->label('Pratinjau Kebutuhan Item dari PR')
                            ->content(fn ($get) => new HtmlString(
                                $get('pr_preview') ?? '<p class="text-gray-400 italic text-sm">Pilih Purchase Request di atas untuk melihat rincian item kebutuhan.</p>'
                            ))
                            ->columnSpanFull(),
                    ]),

                // ==========================================
                // TAHAP 2: PENGIRIMAN & FINANSIAL
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
                                ->default(11)
                                ->live(onBlur: true),

                            Forms\Components\TextInput::make('ongkir')
                                ->label('Ongkos Kirim')
                                ->prefix('Rp')
                                ->currencyMask(2)
                                ->default(0)
                                ->helperText(fn (string $operation) => $operation === 'create'
                                    ? 'Jika multi-supplier, Ongkir tiap PO di-set 0 agar tidak terduplikasi dan dapat disesuaikan di Edit PO.'
                                    : null)
                                ->live(onBlur: true),

                            Forms\Components\TextInput::make('dp')
                                ->label('Uang Muka (DP)')
                                ->prefix('Rp')
                                ->currencyMask(2)
                                ->default(0)
                                ->helperText(fn (string $operation) => $operation === 'create'
                                    ? 'Jika multi-supplier, DP tiap PO di-set 0 dan dapat disesuaikan di Edit PO masing-masing.'
                                    : null)
                                ->live(onBlur: true),
                        ]),
                    ]),

                // ==========================================
                // TAHAP 3: RINCIAN ITEM PO & HARGA
                // ==========================================
                Wizard\Step::make('Rincian Item & Harga')
                    ->icon('heroicon-o-shopping-bag')
                    ->description('Tentukan supplier, kuantiti order (bisa di-split), harga beli, dan diskon tiap baris')
                    ->schema([
                        Forms\Components\Actions::make([
                            Action::make('getData')
                                ->label('Tarik / Reset Item dari PR')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('primary')
                                ->visible(fn (string $operation) => $operation === 'create')
                                ->action(function ($get, $set, $livewire) {
                                    if ($livewire->record) {
                                        return;
                                    }

                                    $prId = $get('purchase_request_id');
                                    if (! $prId) {
                                        Notification::make()
                                            ->title('Pilih Purchase Request terlebih dahulu di Tahap 1')
                                            ->warning()
                                            ->send();

                                        return;
                                    }

                                    $defaultSupplier = $get('supplier_id') ? (int) $get('supplier_id') : null;
                                    $items = static::buildPrRepeaterItems((int) $prId, $defaultSupplier);

                                    if (empty($items)) {
                                        Notification::make()
                                            ->title('Tidak ada sisa item untuk di-PO')
                                            ->body('Seluruh kebutuhan qty pada PR ini sudah memiliki Purchase Order.')
                                            ->warning()
                                            ->send();
                                    }

                                    $set('items', []);
                                    $set('items', $items);
                                }),
                        ])->columnSpanFull(),

                        Forms\Components\Section::make('Purchase Order Items')
                            ->description(fn (string $operation) => $operation === 'create'
                                ? 'Pilih supplier pada setiap baris. Gunakan tombol Split Supplier di pojok kanan baris untuk membagi 1 material ke lebih dari 1 supplier.'
                                : 'Edit kuantiti, harga beli, dan diskon untuk Purchase Order ini.')
                            ->extraAttributes([
                                'style' => 'border-left: 5px solid #3b82f6; border-radius: 10px; padding: 12px;',
                            ])
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->dehydrated(true)
                                    ->extraAttributes([
                                        'class' => 'border border-gray-200 dark:border-gray-700 rounded-xl p-4 mb-3 bg-white dark:bg-gray-900 shadow-sm',
                                    ])
                                    ->label('Daftar Item')
                                    ->itemLabel(function (array $state, string $operation): string {
                                        $name = $state['nama_item'] ?? 'Item';
                                        $qty = (float) (MoneyHelper::clean($state['qty_po'] ?? 0) ?? 0);
                                        $satuan = $state['satuan'] ?? '';
                                        $total = (float) (MoneyHelper::clean($state['total'] ?? 0) ?? 0);

                                        $parts = [$name];
                                        if ($operation === 'create') {
                                            $supId = ! empty($state['supplier_id']) ? (int) $state['supplier_id'] : null;
                                            $supMap = static::getSupplierNameMap($supId);
                                            $supLabel = ($supId && isset($supMap[$supId])) ? $supMap[$supId] : 'Belum pilih supplier';
                                            $parts[] = "Supplier: {$supLabel}";
                                        }

                                        $qtyPart = static::formatQtyNumber($qty) . ($satuan ? " {$satuan}" : '');
                                        $totalPart = 'Rp ' . number_format($total, 0, ',', '.');
                                        $parts[] = "{$qtyPart} · {$totalPart}";

                                        return implode(' — ', $parts);
                                    })
                                    ->extraItemActions([
                                        Action::make('split_supplier')
                                            ->label('Split Supplier')
                                            ->tooltip('Pecah sebagian qty item ini ke supplier lain')
                                            ->icon('heroicon-m-arrows-pointing-out')
                                            ->color('info')
                                            ->visible(fn (string $operation): bool => $operation === 'create')
                                            ->action(function (array $arguments, Forms\Components\Repeater $component): void {
                                                $items = $component->getState();
                                                $sourceKey = $arguments['item'] ?? null;
                                                if ($sourceKey === null || ! isset($items[$sourceKey])) {
                                                    return;
                                                }

                                                $source = $items[$sourceKey];
                                                $itemId = (int) ($source['item_id'] ?? 0);
                                                $qtySisaPr = (float) (MoneyHelper::clean($source['qty_sisa_pr'] ?? $source['qty_pr'] ?? 0) ?? 0);

                                                $allocated = collect($items)
                                                    ->filter(fn ($row) => (int) ($row['item_id'] ?? 0) === $itemId)
                                                    ->sum(fn ($row) => (float) (MoneyHelper::clean($row['qty_po'] ?? 0) ?? 0));

                                                $unallocated = max(0.0, round($qtySisaPr - $allocated, 2));
                                                $newRowQty = $unallocated;

                                                if ($newRowQty <= 0) {
                                                    $sourceQty = (float) (MoneyHelper::clean($source['qty_po'] ?? 0) ?? 0);
                                                    if ($sourceQty >= 2) {
                                                        $half = floor($sourceQty / 2);
                                                        $remainingHalf = round($sourceQty - $half, 2);
                                                        $srcPrice = (float) (MoneyHelper::clean($source['price'] ?? 0) ?? 0);
                                                        $srcDisc = (float) ($source['discount'] ?? 0);

                                                        $items[$sourceKey]['qty_po'] = $remainingHalf;
                                                        $items[$sourceKey]['qty_konversi'] = $remainingHalf;
                                                        $items[$sourceKey]['total'] = round(($remainingHalf * $srcPrice) * (1 - ($srcDisc / 100)), 2);
                                                        $newRowQty = $half;
                                                    }
                                                }

                                                $newPrice = (float) (MoneyHelper::clean($source['price'] ?? 0) ?? 0);
                                                $newDisc = (float) ($source['discount'] ?? 0);

                                                $newRow = $source;
                                                $newRow['id'] = null;
                                                $newRow['supplier_id'] = null;
                                                $newRow['qty_po'] = $newRowQty;
                                                $newRow['qty_konversi'] = $newRowQty;
                                                $newRow['total'] = round(($newRowQty * $newPrice) * (1 - ($newDisc / 100)), 2);

                                                $newKey = (string) Str::uuid();
                                                $reordered = [];
                                                foreach ($items as $k => $v) {
                                                    $reordered[$k] = $v;
                                                    if ($k === $sourceKey) {
                                                        $reordered[$newKey] = $newRow;
                                                    }
                                                }

                                                $component->state($reordered);
                                            }),
                                    ])
                                    ->schema([
                                        Forms\Components\Hidden::make('id'),
                                        Forms\Components\Hidden::make('nama_item'),
                                        Forms\Components\Hidden::make('qty_sisa_pr'),

                                        Forms\Components\Select::make('item_id')
                                            ->label('Item')
                                            ->options(fn () => MasterItem::latest('id')->pluck('nama_master_item', 'id'))
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(fn (string $operation) => $operation === 'create' ? 2 : 3),

                                        Forms\Components\Select::make('supplier_id')
                                            ->label('Supplier Item Ini')
                                            ->options(fn () => static::getSupplierNameMap())
                                            ->searchable()
                                            ->preload()
                                            ->required(fn (string $operation) => $operation === 'create')
                                            ->visible(fn (string $operation) => $operation === 'create')
                                            ->placeholder('Pilih supplier...')
                                            ->live(),

                                        Forms\Components\TextInput::make('qty_pr')
                                            ->label('Qty PR')
                                            ->quantityMask(2)
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\TextInput::make('qty_po')
                                            ->label('Qty PO')
                                            ->quantityMask(2)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->helperText(function ($get, string $operation) {
                                                if ($operation !== 'create') {
                                                    return null;
                                                }
                                                $sisa = (float) (MoneyHelper::clean($get('qty_sisa_pr') ?? $get('qty_pr') ?? 0) ?? 0);

                                                return 'Sisa kuota PR item ini: ' . static::formatQtyNumber($sisa);
                                            })
                                            ->afterStateUpdated(function ($state, callable $get, callable $set, string $operation) {
                                                $maxQty = $operation === 'create'
                                                    ? (float) (MoneyHelper::clean($get('qty_sisa_pr') ?? $get('qty_pr') ?? 0) ?? 0)
                                                    : (float) (MoneyHelper::clean($get('qty_pr') ?? 0) ?? 0);
                                                $qtyPO = (float) (MoneyHelper::clean($state) ?? 0);

                                                if ($maxQty > 0 && $qtyPO > $maxQty) {
                                                    $qtyPO = $maxQty;
                                                    $set('qty_po', $maxQty);
                                                }

                                                $set('qty_konversi', $qtyPO);

                                                $price = (float) (MoneyHelper::clean($get('price')) ?? 0);
                                                $discount = (float) ($get('discount') ?? 0);
                                                $total = ($qtyPO * $price) * (1 - ($discount / 100));

                                                $set('total', round($total, 2));
                                            }),

                                        Forms\Components\TextInput::make('qty_konversi')
                                            ->label('Qty After Conversion')
                                            ->quantityMask(2)
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Price / Unit')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->placeholder('0.00')
                                            ->dehydrated()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $qty = (float) (MoneyHelper::clean($get('qty_konversi') ?? $get('qty_po')) ?? 0);
                                                $price = (float) (MoneyHelper::clean($state) ?? 0);
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
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                                $qty = (float) (MoneyHelper::clean($get('qty_konversi') ?? $get('qty_po')) ?? 0);
                                                $price = (float) (MoneyHelper::clean($get('price')) ?? 0);
                                                $discount = (float) ($state ?? 0);
                                                $total = ($qty * $price) * (1 - ($discount / 100));

                                                $set('total', round($total, 2));
                                            }),

                                        Forms\Components\TextInput::make('total')
                                            ->label('Total')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\TextInput::make('satuan')
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\DatePicker::make('eta'),

                                        Forms\Components\Textarea::make('catatan')
                                            ->rows(2),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addable(false)
                                    ->deletable(fn (string $operation) => $operation === 'create')
                                    ->collapsible()
                                    ->cloneable(false)
                                    ->reorderable(false),
                            ]),

                        Forms\Components\Placeholder::make('po_split_summary')
                            ->label('Ringkasan PO yang Akan Diterbitkan per Supplier')
                            ->visible(fn (string $operation) => $operation === 'create')
                            ->content(function ($get): HtmlString {
                                $items = $get('items') ?? [];
                                $defaultSupplierId = $get('supplier_id') ? (int) $get('supplier_id') : null;
                                $ppnPercent = (float) (MoneyHelper::clean($get('ppn') ?? 0) ?? 0);
                                $supMap = static::getSupplierNameMap();

                                // Cek validasi over-quota per item_id (jika ada split)
                                $allocatedByItem = [];
                                $quotaByItem = [];
                                $nameByItem = [];

                                $groups = [];
                                $missingSupplierRows = 0;

                                foreach ($items as $row) {
                                    $itemId = (int) ($row['item_id'] ?? 0);
                                    $qty = (float) (MoneyHelper::clean($row['qty_po'] ?? 0) ?? 0);
                                    if ($itemId <= 0 || $qty <= 0) {
                                        continue;
                                    }

                                    $allocatedByItem[$itemId] = ($allocatedByItem[$itemId] ?? 0.0) + $qty;
                                    $quotaByItem[$itemId] = (float) (MoneyHelper::clean($row['qty_sisa_pr'] ?? $row['qty_pr'] ?? 0) ?? 0);
                                    $nameByItem[$itemId] = $row['nama_item'] ?? "Item #{$itemId}";

                                    $supId = ! empty($row['supplier_id']) ? (int) $row['supplier_id'] : $defaultSupplierId;
                                    $price = (float) (MoneyHelper::clean($row['price'] ?? 0) ?? 0);
                                    $disc = (float) ($row['discount'] ?? 0);
                                    $lineTotal = round(($qty * $price) * (1 - ($disc / 100)), 2);

                                    if (! $supId) {
                                        $missingSupplierRows++;
                                        $groupKey = 0;
                                    } else {
                                        $groupKey = $supId;
                                    }

                                    if (! isset($groups[$groupKey])) {
                                        $groups[$groupKey] = [
                                            'name'     => $groupKey === 0 ? 'Belum Dipilih Supplier' : ($supMap[$groupKey] ?? "Supplier #{$groupKey}"),
                                            'is_valid' => $groupKey !== 0,
                                            'rows'     => 0,
                                            'qty'      => 0.0,
                                            'subtotal' => 0.0,
                                        ];
                                    }

                                    $groups[$groupKey]['rows']++;
                                    $groups[$groupKey]['qty'] += $qty;
                                    $groups[$groupKey]['subtotal'] += $lineTotal;
                                }

                                $warningsHtml = '';
                                foreach ($allocatedByItem as $itemId => $allocQty) {
                                    $quota = $quotaByItem[$itemId] ?? 0.0;
                                    if ($quota > 0 && round($allocQty, 4) > round($quota, 4) + 0.0001) {
                                        $warningsHtml .= '<div style="margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid rgba(239,68,68,0.4);background:rgba(239,68,68,0.14);padding:0.5rem 0.75rem;font-size:0.75rem;color:#f87171;">'
                                            . '<strong>Melebihi Kuota PR:</strong> Total Qty PO untuk <strong>' . e($nameByItem[$itemId]) . '</strong> (' . static::formatQtyNumber($allocQty) . ') melebihi sisa kebutuhan PR (' . static::formatQtyNumber($quota) . ').'
                                            . '</div>';
                                    }
                                }

                                if (empty($groups)) {
                                    return new HtmlString($warningsHtml . '<p class="text-sm text-gray-400 italic">Belum ada baris item dengan Qty PO &gt; 0.</p>');
                                }

                                $validPoCount = collect($groups)->where('is_valid', true)->count();
                                $headerBadge = $missingSupplierRows > 0
                                    ? '<span style="display:inline-flex;align-items:center;padding:0.25rem 0.625rem;border-radius:0.375rem;font-size:0.75rem;font-weight:600;background:rgba(239,68,68,0.16);color:#f87171;border:1px solid rgba(239,68,68,0.3);">' . $missingSupplierRows . ' baris belum memiliki supplier</span>'
                                    : '<span style="display:inline-flex;align-items:center;padding:0.25rem 0.625rem;border-radius:0.375rem;font-size:0.75rem;font-weight:600;background:rgba(16,185,129,0.16);color:#34d399;border:1px solid rgba(16,185,129,0.3);">Akan menerbitkan ' . $validPoCount . ' PO resmi</span>';

                                $rowsHtml = '';
                                foreach ($groups as $g) {
                                    $ppnAmt = $ppnPercent > 0 ? round($g['subtotal'] * ($ppnPercent / 100), 2) : 0.0;
                                    $estTotal = $g['subtotal'] + $ppnAmt;
                                    $nameBadge = $g['is_valid']
                                        ? '<span style="font-weight:500;">' . e($g['name']) . '</span>'
                                        : '<span style="font-weight:600;color:#f87171;">⚠ ' . e($g['name']) . '</span>';

                                    $rowsHtml .= '<tr style="border-bottom:1px solid rgba(148,163,184,0.2);">'
                                        . '<td style="padding:0.5rem 0.75rem;">' . $nameBadge . '</td>'
                                        . '<td style="padding:0.5rem 0.75rem;text-align:right;">' . $g['rows'] . ' baris (' . static::formatQtyNumber($g['qty']) . ' qty)</td>'
                                        . '<td style="padding:0.5rem 0.75rem;text-align:right;font-weight:500;">Rp ' . number_format($g['subtotal'], 0, ',', '.') . '</td>'
                                        . ($ppnPercent > 0 ? '<td style="padding:0.5rem 0.75rem;text-align:right;font-weight:600;color:#34d399;">Rp ' . number_format($estTotal, 0, ',', '.') . '</td>' : '')
                                        . '</tr>';
                                }

                                $ppnColHeader = $ppnPercent > 0
                                    ? '<th style="padding:0.5rem 0.75rem;text-align:right;">Est. Total (+PPN ' . static::formatQtyNumber($ppnPercent) . '%)</th>'
                                    : '';

                                return new HtmlString(
                                    $warningsHtml
                                    . '<div style="border-radius:0.75rem;border:1px solid rgba(148,163,184,0.25);overflow:hidden;background:rgba(148,163,184,0.06);">'
                                    . '<div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.75rem;background:rgba(148,163,184,0.12);border-bottom:1px solid rgba(148,163,184,0.22);">'
                                    . '<span style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Auto-Split per Supplier</span>'
                                    . $headerBadge
                                    . '</div>'
                                    . '<table style="width:100%;font-size:0.875rem;text-align:left;border-collapse:collapse;">'
                                    . '<thead style="font-size:0.75rem;opacity:0.75;border-bottom:1px solid rgba(148,163,184,0.22);">'
                                    . '<tr><th style="padding:0.5rem 0.75rem;">Supplier</th><th style="padding:0.5rem 0.75rem;text-align:right;">Cakupan Item</th><th style="padding:0.5rem 0.75rem;text-align:right;">Subtotal</th>' . $ppnColHeader . '</tr>'
                                    . '</thead>'
                                    . '<tbody>' . $rowsHtml . '</tbody>'
                                    . '</table>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('subtotal')
                            ->label('Subtotal Keseluruhan')
                            ->content(function ($get): HtmlString {
                                $items = $get('items') ?? [];
                                $subtotal = collect($items)->sum(function ($item) {
                                    $qty = (float) (MoneyHelper::clean($item['qty_konversi'] ?? $item['qty_po'] ?? 0) ?? 0);
                                    $price = (float) (MoneyHelper::clean($item['price'] ?? 0) ?? 0);
                                    $disc = (float) ($item['discount'] ?? 0);

                                    return round(($qty * $price) * (1 - ($disc / 100)), 2);
                                });

                                return new HtmlString(
                                    '<div class="text-right text-xl font-bold text-green-600 dark:text-green-400">
                                        Total Keseluruhan: Rp ' . number_format($subtotal, 0, ',', '.') . '
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
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('purchaseRequest.nomor_pr')
                    ->label('No PR')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_nominal')
                    ->label('Subtotal Item')
                    ->getStateUsing(fn (PurchaseOrder $record): float => (float) $record->items->sum('total'))
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('po_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'reguler'  => 'success',
                        'subcount' => 'warning',
                        default    => 'gray',
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
                    ->label('Tgl PO')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'nama_supplier')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('purchase_request_id')
                    ->label('No PR')
                    ->relationship('purchaseRequest', 'nomor_pr')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => route('purchase-order.preview', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn ($record) => route('purchase-order.download', $record)),

                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => ! app(PurchaseOrderService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(PurchaseOrderService::class)->canModify($record)
                            ? 'Tidak dapat dihapus karena sudah memiliki Penerimaan Barang, Tagihan Supplier, atau Faktur terkait'
                            : null)
                        ->using(fn ($record) => app(PurchaseOrderService::class)->deletePurchaseOrder($record)),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal'),
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['purchaseRequest.departemen', 'supplier', 'items']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
