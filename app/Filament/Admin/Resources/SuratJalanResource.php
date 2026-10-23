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
                        ->description('Pilih Sales Order acuan, cek stock, dan tentukan kuantiti kirim')
                        ->schema([
                            Forms\Components\Select::make('sales_order_id')
                                ->label('Sales Order')
                                ->relationship('salesOrder', 'no_sales_order')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (!$state) {
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

                                    if ($mode === 'SPK') {
                                        $set('items', []);
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
                                        ->label('Stock Tersedia')
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
                                ->numeric()
                                ->required()
                                ->live()
                                ->rule(function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $so = self::getSalesOrder($get('sales_order_id'));
                                        if (!$so) return;

                                        // Validasi Sisa Order (memperhitungkan form edit)
                                        $qtySudahKirim = $so->suratJalans()
                                            ->whereKeyNot(request()->route('record'))
                                            ->sum('qty_pengiriman');

                                        $sisaOrder = $so->order_qty - $qtySudahKirim;

                                        if ($value > $sisaOrder) {
                                            $fail("Qty pengiriman melebihi outstanding order ({$sisaOrder}).");
                                            return;
                                        }

                                        // Validasi Stock Tersedia
                                        $stockTersedia = $so->current_stock;
                                        if ($value > $stockTersedia) {
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
                                                ->numeric()
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
                'salesOrder.itemable',
                'salesOrder.items.material',
                'items.material',
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
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                    ->label('No SO')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_spk')
                    ->label('No SPK')
                    ->getStateUsing(function ($record) {
                        return \App\Models\MasterSpk::where('sales_order_id', $record->sales_order_id)->value('no_spk') ?? '-';
                    }),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Produk')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        return $record->salesOrder?->product_name ?? '-'; // Menggunakan helper model
                    }),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty Pengiriman')
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

                Tables\Columns\TextColumn::make('transportasi'),

                Tables\Columns\TextColumn::make('driver'),

                Tables\Columns\TextColumn::make('tanggal_surat_jalan')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-document')
                        ->color('warning')
                        ->url(fn ($record) => route('surat-jalan.download', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('surat-jalan.download', $record)),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record) ? 'Surat Jalan terkunci karena sudah memiliki Invoice' : null),
                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record))
                        ->tooltip(fn ($record) => ! app(\App\Services\Dispatch\SuratJalanService::class)->canModify($record) ? 'Tidak dapat dihapus karena sudah memiliki Invoice' : null),
                ])
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