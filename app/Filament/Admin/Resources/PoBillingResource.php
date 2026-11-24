<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PoBillingResource\Pages;
use App\Models\PoBilling;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{
    Wizard,
    Section,
    Grid,
    Select,
    TextInput,
    DatePicker,
    Textarea,
    Repeater
};
use Filament\Forms\Get;
use Filament\Forms\Set;

use App\Models\PenerimaanBarang;
use App\Models\User;
use App\Support\MoneyHelper;

class PoBillingResource extends Resource
{
    protected static ?string $model = PoBilling::class;
     protected static ?string $navigationGroup = 'Finance';
     protected static ?int $navigationSort = 11;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Wizard::make([

                // ==========================================
                // 🔥 TAHAP 1: PENERIMAAN BARANG & INFO TAGIHAN
                // ==========================================
                Wizard\Step::make('Penerimaan Barang & Info Tagihan')
                    ->icon('heroicon-o-document-text')
                    ->description('Pilih LPB acuan, cek supplier terkait, nomor invoice vendor, karyawan, dan tanggal transaksi')
                    ->schema([
                        Forms\Components\Hidden::make('purchase_order_id'),
                        Forms\Components\Hidden::make('supplier_id'),

                        Grid::make(2)
                            ->schema([
                                Select::make('penerimaan_barang_id')
                                    ->label('Penerimaan Barang (LPB)')
                                    ->options(function (?PoBilling $record) {
                                        return PenerimaanBarang::with(['purchaseOrder.supplier', 'purchaseOrder.purchaseRequest'])
                                            ->where(function ($query) use ($record) {
                                                $query->whereDoesntHave('poBilling');
                                                if ($record && $record->penerimaan_barang_id) {
                                                    $query->orWhere('id', $record->penerimaan_barang_id);
                                                }
                                            })
                                            ->orderByDesc('created_at')
                                            ->orderByDesc('id')
                                            ->get()
                                            ->mapWithKeys(function ($item) {
                                                $supplierName = $item->purchaseOrder?->supplier?->nama_supplier ?? 'Tanpa Supplier';
                                                $poNo = $item->purchaseOrder?->no_po ?? '-';
                                                $prNo = $item->purchaseOrder?->purchaseRequest?->nomor_pr;
                                                $ref = $prNo ? "PO: {$poNo} | PR: {$prNo}" : "PO: {$poNo}";

                                                return [
                                                    $item->id => "{$item->no_penerimaan} — {$supplierName} ({$ref})",
                                                ];
                                            });
                                    })
                                    ->disabled(fn (string $operation) => $operation === 'edit')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (
                                        $state,
                                        Set $set
                                    ) {
                                        $lpb = PenerimaanBarang::with([
                                            'items.purchaseOrderItem.item',
                                            'purchaseOrder.supplier',
                                        ])->find($state);

                                        if (!$lpb) {
                                            $set('supplier_name_display', null);
                                            $set('supplier_id', null);
                                            $set('purchase_order_id', null);

                                            return;
                                        }

                                        $items = [];
                                        $totalBarang = 0;

                                        foreach ($lpb->items as $item) {
                                            $harga = (float) (optional($item->purchaseOrderItem)->price ?? 0);
                                            $qty = (float) ($item->qty_terima ?? 0);
                                            $diskon = (float) (optional($item->purchaseOrderItem)->discount ?? 0);
                                            $subtotal = round(($qty * $harga) - (($qty * $harga) * $diskon / 100), 2);
                                            $totalBarang += $subtotal;

                                            $items[(string) \Illuminate\Support\Str::uuid()] = [
                                                'item_id' => optional($item->purchaseOrderItem)->item_id,
                                                'item_name' => optional(optional($item->purchaseOrderItem)->item)->nama_master_item ?? 'Item',
                                                'qty' => MoneyHelper::format($qty, 2) ?? '0',
                                                'harga' => MoneyHelper::format($harga, 2) ?? '0',
                                                'diskon' => $diskon,
                                                'subtotal' => MoneyHelper::format($subtotal, 2) ?? '0',
                                            ];
                                        }

                                        $ppnRate = $lpb->purchaseOrder && $lpb->purchaseOrder->ppn !== null
                                            ? (float) $lpb->purchaseOrder->ppn
                                            : 11.0;
                                        $ppn = round($totalBarang * ($ppnRate / 100), 2);
                                        $ongkir = (float) ($lpb->purchaseOrder?->ongkir ?? 0);
                                        $dp = (float) ($lpb->purchaseOrder?->dp ?? 0);
                                        $grandTotal = max(0.0, round($totalBarang + $ppn + $ongkir - $dp, 2));

                                        $set('items', $items);
                                        $set('purchase_order_id', $lpb->purchase_order_id);
                                        $set('supplier_id', optional($lpb->purchaseOrder)->supplier_id);
                                        $set('supplier_name_display', $lpb->purchaseOrder?->supplier?->nama_supplier ?? '-');
                                        $set('total_barang', MoneyHelper::format($totalBarang, 2) ?? '0');
                                        $set('ppn', MoneyHelper::format($ppn, 2) ?? '0');
                                        $set('ongkir', MoneyHelper::format($ongkir, 2) ?? '0');
                                        $set('dp', MoneyHelper::format($dp, 2) ?? '0');
                                        $set('grand_total', MoneyHelper::format($grandTotal, 2) ?? '0');
                                    }),

                                TextInput::make('supplier_name_display')
                                    ->label('Supplier / Vendor Tertagih')
                                    ->placeholder('Otomatis terisi dari LPB / PO')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('karyawan_id')
                                    ->label('Karyawan Penanggung Jawab')
                                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('invoice_vendor')
                                    ->label('No Invoice Vendor / Tagihan')
                                    ->placeholder('Contoh: INV-SUP-2026/09'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('periode')
                                    ->label('Periode Tahun')
                                    ->default(now()->year),

                                DatePicker::make('tanggal_transaksi')
                                    ->label('Tanggal Transaksi')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('tanggal_jatuh_tempo')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->default(now()->addDays(30)),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Catatan Tagihan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // ==========================================
                // 🔥 TAHAP 2: RINCIAN ITEM & TOTAL PEMBAYARAN
                // ==========================================
                Wizard\Step::make('Rincian Item & Total Pembayaran')
                    ->icon('heroicon-o-calculator')
                    ->description('Rincian item barang dari LPB, kuantiti, harga satuan, PPN, dan grand total')
                    ->schema([
                        Section::make('Item Details (LPB)')
                            ->schema([
                                Repeater::make('items')
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->cloneable(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->itemLabel(fn (array $state): ?string => $state['item_name'] ?? 'Item')
                                    ->schema([
                                        Forms\Components\Hidden::make('item_id')->dehydrated(),
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('item_name')
                                                    ->label('Nama Item')
                                                    ->disabled()
                                                    ->dehydrated(),

                                                TextInput::make('qty')
                                                    ->label('Qty Terima')
                                                    ->quantityMask(2)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateItemRowAndTotals($set, $get)),

                                                TextInput::make('harga')
                                                    ->label('Harga Satuan')
                                                    ->prefix('Rp')
                                                    ->currencyMask(2)
                                                    ->placeholder('0.00')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateItemRowAndTotals($set, $get)),

                                                TextInput::make('diskon')
                                                    ->label('Diskon (%)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->suffix('%')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Set $set, Get $get) => static::recalculateItemRowAndTotals($set, $get)),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->currencyMask(2)
                                                    ->prefix('Rp'),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),

                        Section::make('Rincian Total Tagihan Hutang')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('total_barang')
                                            ->label('Total Barang (DPP)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('ppn')
                                            ->label('PPN')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->disabled()
                                            ->dehydrated(),

                                        TextInput::make('ongkir')
                                            ->label('Ongkos Kirim')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $totalBarang = (float) (MoneyHelper::clean($get('total_barang')) ?? 0);
                                                $ppn = (float) (MoneyHelper::clean($get('ppn')) ?? 0);
                                                $ongkir = (float) (MoneyHelper::clean($state) ?? 0);
                                                $dp = (float) (MoneyHelper::clean($get('dp')) ?? 0);
                                                $grandTotal = max(0.0, round($totalBarang + $ppn + $ongkir - $dp, 2));
                                                $set('grand_total', MoneyHelper::format($grandTotal, 2) ?? '0');
                                            }),

                                        TextInput::make('dp')
                                            ->label('Uang Muka / DP')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->helperText('Otomatis mengurangi Total Bruto (DPP + PPN + Ongkir)')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $totalBarang = (float) (MoneyHelper::clean($get('total_barang')) ?? 0);
                                                $ppn = (float) (MoneyHelper::clean($get('ppn')) ?? 0);
                                                $ongkir = (float) (MoneyHelper::clean($get('ongkir')) ?? 0);
                                                $dp = (float) (MoneyHelper::clean($state) ?? 0);
                                                $grandTotal = max(0.0, round($totalBarang + $ppn + $ongkir - $dp, 2));
                                                $set('grand_total', MoneyHelper::format($grandTotal, 2) ?? '0');
                                            }),

                                        TextInput::make('grand_total')
                                            ->label('Grand Total Tagihan (Setelah DP)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->disabled()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-primary-600']),
                                    ]),

                                Forms\Components\Placeholder::make('kalkulasi_ringkasan')
                                    ->label('Rincian Perhitungan')
                                    ->content(function (Get $get): \Illuminate\Support\HtmlString {
                                        $dpp = (float) (MoneyHelper::clean($get('total_barang')) ?? 0);
                                        $ppn = (float) (MoneyHelper::clean($get('ppn')) ?? 0);
                                        $ongkir = (float) (MoneyHelper::clean($get('ongkir')) ?? 0);
                                        $dp = (float) (MoneyHelper::clean($get('dp')) ?? 0);
                                        $bruto = round($dpp + $ppn + $ongkir, 2);
                                        $netto = max(0.0, round($bruto - $dp, 2));

                                        $fmt = fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');

                                        $dpPart = $dp > 0
                                            ? ' − <span style="color:#f59e0b;font-weight:600;">DP ' . $fmt($dp) . '</span>'
                                            : '';

                                        return new \Illuminate\Support\HtmlString(
                                            '<div style="border-radius:0.5rem;border:1px solid rgba(148,163,184,0.28);background:rgba(148,163,184,0.08);padding:0.75rem 1rem;font-size:0.8125rem;line-height:1.6;">'
                                            . '<div>DPP <strong>' . $fmt($dpp) . '</strong> + PPN <strong>' . $fmt($ppn) . '</strong> + Ongkir <strong>' . $fmt($ongkir) . '</strong> = Total Bruto <strong>' . $fmt($bruto) . '</strong></div>'
                                            . '<div style="margin-top:0.25rem;">Total Bruto <strong>' . $fmt($bruto) . '</strong>' . $dpPart . ' = <span style="font-size:0.9375rem;font-weight:700;color:#10b981;">Tagihan Netto ' . $fmt($netto) . '</span></div>'
                                            . '</div>'
                                        );
                                    })
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
}

    protected static function recalculateItemRowAndTotals(Set $set, Get $get): void
    {
        $qty = (float) (MoneyHelper::clean($get('qty')) ?? 0);
        $harga = (float) (MoneyHelper::clean($get('harga')) ?? 0);
        $diskon = (float) ($get('diskon') ?? 0);
        $rowSubtotal = round(($qty * $harga) - (($qty * $harga) * $diskon / 100), 2);
        $set('subtotal', MoneyHelper::format($rowSubtotal, 2) ?? '0');

        $items = $get('../../items') ?? [];
        $totalBarang = 0.0;
        foreach ($items as $item) {
            $iqty = (float) (MoneyHelper::clean($item['qty'] ?? 0) ?? 0);
            $iharga = (float) (MoneyHelper::clean($item['harga'] ?? 0) ?? 0);
            $idiskon = (float) ($item['diskon'] ?? 0);
            $totalBarang += round(($iqty * $iharga) - (($iqty * $iharga) * $idiskon / 100), 2);
        }

        $poId = $get('../../purchase_order_id');
        $poPpn = $poId ? \App\Models\PurchaseOrder::whereKey($poId)->value('ppn') : null;
        $ppnRate = $poPpn !== null ? (float) $poPpn : 11.0;
        $ppn = round($totalBarang * ($ppnRate / 100), 2);
        $ongkir = (float) (MoneyHelper::clean($get('../../ongkir')) ?? 0);
        $dp = (float) (MoneyHelper::clean($get('../../dp')) ?? 0);
        $grandTotal = max(0.0, round($totalBarang + $ppn + $ongkir - $dp, 2));

        $set('../../total_barang', MoneyHelper::format($totalBarang, 2) ?? '0');
        $set('../../ppn', MoneyHelper::format($ppn, 2) ?? '0');
        $set('../../grand_total', MoneyHelper::format($grandTotal, 2) ?? '0');
    }

    public static function getNavigationLabel(): string
{
    return 'Billing of Purchase Order / Penagihan';
}

    public static function getModelLabel(): string
    {
        return 'Billing of Purchase Order / Penagihan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Billing of Purchase Order / Penagihan';
    }

    public static function table(Table $table): Table
    {
        return $table
           ->defaultSort('created_at', 'desc')
           ->columns([
                Tables\Columns\TextColumn::make('kode_tagihan')
                    ->label('Kode Tagihan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('penerimaanBarang.no_penerimaan')
                    ->label('LPB')
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchaseOrder.no_po')
                    ->label('PO')
                    ->description(fn (PoBilling $record): ?string => $record->purchaseOrder?->purchaseRequest?->nomor_pr
                        ? 'PR: ' . $record->purchaseOrder->purchaseRequest->nomor_pr
                        : null)
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Tagihan (Netto)')
                    ->money('IDR', locale: 'id')
                    ->description(function (PoBilling $record): ?string {
                        $dp = (float) ($record->dp ?? 0);
                        if ($dp <= 0) {
                            return null;
                        }
                        $bruto = round((float) $record->total_barang + (float) $record->ppn + (float) $record->ongkir, 2);

                        return 'Bruto Rp ' . number_format($bruto, 0, ',', '.') . ' − DP Rp ' . number_format($dp, 0, ',', '.');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('dp')
                    ->label('DP')
                    ->money('IDR', locale: 'id')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_payment')
                    ->label('Dibayar (Pelunasan)')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label('Sisa Tagihan')
                    ->money('IDR', locale: 'id'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Paid' => 'success',
                        'Partial' => 'warning',
                        'Unpaid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->label('Tgl Transaksi')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('bayar_tagihan')
                        ->label('Bayar Tagihan')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (PoBilling $record): bool => (float) $record->sisa_tagihan > 0)
                        ->url(fn (PoBilling $record): string => PoBillPayResource::getUrl('create', ['po_billing_id' => $record->id])),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => !app(\App\Services\Finance\PoBillingService::class)->canDelete($record))
                        ->using(fn ($record) => app(\App\Services\Finance\PoBillingService::class)->deleteBilling($record)),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('bottom-end'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'penerimaanBarang',
            'purchaseOrder.purchaseRequest',
            'supplier',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoBillings::route('/'),
            'create' => Pages\CreatePoBilling::route('/create'),
            'edit' => Pages\EditPoBilling::route('/{record}/edit'),
        ];
    }
}