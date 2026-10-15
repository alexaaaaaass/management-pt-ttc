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
use Filament\Forms\Set;

use App\Models\PenerimaanBarang;
use App\Models\User;

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
                    ->description('Pilih LPB acuan, nomor invoice vendor, karyawan, dan tanggal transaksi')
                    ->schema([
                        Forms\Components\Hidden::make('purchase_order_id'),
                        Forms\Components\Hidden::make('supplier_id'),

                        Grid::make(3)
                            ->schema([
                                Select::make('penerimaan_barang_id')
                                    ->label('Penerimaan Barang (LPB)')
                                    ->options(function (?PoBilling $record) {
                                        return PenerimaanBarang::with('purchaseOrder')
                                            ->where(function ($query) use ($record) {
                                                $query->whereDoesntHave('poBilling');
                                                if ($record && $record->penerimaan_barang_id) {
                                                    $query->orWhere('id', $record->penerimaan_barang_id);
                                                }
                                            })
                                            ->get()
                                            ->mapWithKeys(fn ($item) => [
                                                $item->id => $item->no_penerimaan . ' (PO: ' . ($item->purchaseOrder?->no_po ?? '-') . ')'
                                            ]);
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
                                            'purchaseOrder'
                                        ])->find($state);

                                        if (!$lpb) {
                                            return;
                                        }

                                        $items = [];
                                        $totalBarang = 0;

                                        foreach ($lpb->items as $item) {
                                            $harga = optional($item->purchaseOrderItem)->price ?? 0;
                                            $qty = $item->qty_terima ?? 0;
                                            $subtotal = $qty * $harga;
                                            $totalBarang += $subtotal;

                                            $items[] = [
                                                'item_name' => optional(optional($item->purchaseOrderItem)->item)->nama_master_item ?? 'Item',
                                                'qty' => $qty,
                                                'harga' => $harga,
                                                'diskon' => 0,
                                                'subtotal' => $subtotal,
                                            ];
                                        }

                                        $ppn = $totalBarang * 0.11;
                                        $ongkir = 0;
                                        $dp = 0;
                                        $grandTotal = $totalBarang + $ppn + $ongkir - $dp;

                                        $set('items', $items);
                                        $set('purchase_order_id', $lpb->purchase_order_id);
                                        $set('supplier_id', optional($lpb->purchaseOrder)->supplier_id);
                                        $set('total_barang', $totalBarang);
                                        $set('ppn', $ppn);
                                        $set('ongkir', $ongkir);
                                        $set('dp', $dp);
                                        $set('grand_total', $grandTotal);
                                    }),

                                Select::make('karyawan_id')
                                    ->label('Karyawan Penanggung Jawab')
                                    ->options(User::pluck('name', 'id'))
                                    ->searchable(),

                                TextInput::make('invoice_vendor')
                                    ->label('No Invoice Vendor / Tagihan')
                                    ->placeholder('Contoh: INV-SUP-2024/09'),
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
                                    ->relationship()
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->cloneable(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->live()
                                    ->itemLabel(fn (array $state): ?string => $state['item_name'] ?? 'Item')
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextInput::make('item_name')
                                                    ->label('Nama Item')
                                                    ->disabled()
                                                    ->dehydrated(),

                                                TextInput::make('qty')
                                                    ->label('Qty Terima')
                                                    ->numeric()
                                                    ->live(),

                                                TextInput::make('harga')
                                                    ->label('Harga Satuan')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->live(),

                                                TextInput::make('diskon')
                                                    ->label('Diskon (%)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->suffix('%')
                                                    ->live(),

                                                TextInput::make('subtotal')
                                                    ->label('Subtotal')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->prefix('Rp'),
                                            ]),
                                    ])
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $totalBarang = 0;

                                        foreach ($state as $key => $item) {
                                            $qty = (float) ($item['qty'] ?? 0);
                                            $harga = (float) ($item['harga'] ?? 0);
                                            $diskon = (float) ($item['diskon'] ?? 0);
                                            $subtotal = ($qty * $harga) - (($qty * $harga) * $diskon / 100);

                                            $state[$key]['subtotal'] = $subtotal;
                                            $totalBarang += $subtotal;
                                        }

                                        $ppn = $totalBarang * 0.11;
                                        $ongkir = (float) 0;
                                        $dp = (float) 0;
                                        $grandTotal = $totalBarang + $ppn + $ongkir - $dp;

                                        $set('items', $state);
                                        $set('total_barang', $totalBarang);
                                        $set('ppn', $ppn);
                                        $set('grand_total', $grandTotal);
                                    })
                                    ->columns(1),
                            ]),

                        Section::make('Rincian Total Tagihan Hutang')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('total_barang')
                                            ->label('Total Barang (DPP)')
                                            ->prefix('Rp')
                                            ->numeric(),

                                        TextInput::make('ppn')
                                            ->label('PPN (11%)')
                                            ->prefix('Rp')
                                            ->numeric(),

                                        TextInput::make('ongkir')
                                            ->label('Ongkos Kirim')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->live(),

                                        TextInput::make('dp')
                                            ->label('Uang Muka / DP')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->live(),

                                        TextInput::make('grand_total')
                                            ->label('Grand Total Tagihan')
                                            ->prefix('Rp')
                                            ->numeric()
                                            ->extraAttributes(['class' => 'font-bold text-primary-600']),
                                    ]),
                            ]),
                    ]),
            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Po Billing';
}

    public static function table(Table $table): Table
    {
        return $table
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
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Tagihan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_payment')
                    ->label('Dibayar')
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn ($record) => !app(\App\Services\Finance\PoBillingService::class)->canDelete($record))
                    ->using(fn ($record) => app(\App\Services\Finance\PoBillingService::class)->deleteBilling($record)),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoBillings::route('/'),
            'create' => Pages\CreatePoBilling::route('/create'),
            'edit' => Pages\EditPoBilling::route('/{record}/edit'),
        ];
    }
}