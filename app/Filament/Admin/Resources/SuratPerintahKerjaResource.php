<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;
use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Table;
use App\Models\SalesOrder;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;


class SuratPerintahKerjaResource extends Resource
{
    protected static ?string $model = MasterSpk::class;
    protected static ?string $navigationGroup = 'Ppic';
    protected static ?string $navigationLabel = 'Master Spk';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\TextInput::make('no_spk')
                ->label('No SPK')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('kode_ik')
                ->required(),

            // 🔥 SELECT SALES ORDER
           Forms\Components\Select::make('sales_order_id')
           ->extraAttributes([
    'class' => 'z-50'
])
    ->label('No Sales Order')
    ->relationship('salesOrder', 'no_sales_order')
    ->searchable()
    ->preload()
    ->native(false)
    ->optionsLimit(20)
    ->reactive()
    ->required()
   ->afterStateUpdated(function ($state, Set $set) {

    $so = SalesOrder::with(['customer', 'itemable'])->find($state);

    if (!$so) return;

    // 🔹 data SO
    $set('tanggal_po', $so->tanggal_po);
    $set('qty', $so->qty);
    $set('customer_name', $so->customer->nama_customer ?? '-');
    $set('no_po_customer', $so->no_po_customer);
    $set('tipe_pesanan', $so->tipe_pesanan);

    // 🔥 data Finish Good
    $fg = $so->itemable;

    if ($fg instanceof \App\Models\FinishGoodItem) {

        $set('up_satu', $fg->up_satu);
        $set('up_dua', $fg->up_dua);
        $set('up_tiga', $fg->up_tiga);
        $set('ukuran_potong', $fg->ukuran_potong);
        $set('ukuran_cetak', $fg->ukuran_cetak);
        $set('spesifikasi_kertas', $fg->spesifikasi_kertas);
    }
}),

            // 🔥 AUTO FIELD (HIDDEN / DISPLAY ONLY)
            Forms\Components\Hidden::make('customer_name'),
            Forms\Components\Hidden::make('no_po_customer'),
            Forms\Components\Hidden::make('qty'),
            Forms\Components\Hidden::make('tipe_pesanan'),

        Forms\Components\DatePicker::make('tanggal_po')
    ->disabled()
    ->dehydrated(), // 🔥 INI KUNCINYA

            Forms\Components\TextInput::make('production_plan'),

            Forms\Components\Select::make('status')
    ->options([
        'ON PROCESS' => 'ON PROCESS',
        'FINISH' => 'FINISH',
    ])
    ->default('ON PROCESS')
    ->required(),

            Forms\Components\DatePicker::make('tanggal_estimasi_selesai'),

            Forms\Components\Section::make('Spesifikasi Produk')
    ->schema([

        Forms\Components\TextInput::make('up_satu')
            ->label('UP 1')
            ->numeric(),

        Forms\Components\TextInput::make('up_dua')
            ->label('UP 2')
            ->numeric(),

        Forms\Components\TextInput::make('up_tiga')
            ->label('UP 3')
            ->numeric(),

        Forms\Components\TextInput::make('ukuran_potong')
            ->label('Ukuran Potong'),

        Forms\Components\TextInput::make('ukuran_cetak')
            ->label('Ukuran Cetak'),

        Forms\Components\Textarea::make('spesifikasi_kertas')
            ->label('Spesifikasi Kertas')
            ->rows(3),
    ])
    ->columns(2),

            // ===============================
            // 🔥 DETAIL SALES ORDER (KAYAK UI KAMU)
            // ===============================
            Forms\Components\Section::make('Detail Sales Order')
              ->extraAttributes([
        'style' => '
            border-left: 5px solid #3b82f6;
            border-radius: 10px;
            padding: 12px;
        ',
    ])
                ->schema([

                    Forms\Components\Grid::make(2)->schema([

                        // 🔹 KIRI
                        Forms\Components\Grid::make(1)->schema([

                            Forms\Components\Placeholder::make('so_number')
                                ->label('No Sales Order')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->no_sales_order ?? '-'
                                ),

                            Forms\Components\Placeholder::make('po_customer')
                                ->label('No PO Customer')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->no_po_customer ?? '-'
                                ),

                            Forms\Components\Placeholder::make('customer')
                                ->label('Customer')
                                ->content(function (Get $get) {
                                    $so = SalesOrder::with('customer')->find($get('sales_order_id'));
                                    return $so?->customer?->nama_customer ?? '-';
                                }),

                           Forms\Components\Placeholder::make('nama_barang')
    ->label('Nama Barang')
    ->content(function (Get $get) {
        $so = SalesOrder::with('itemable')->find($get('sales_order_id'));

        if (!$so || !$so->itemable) {
            return '-';
        }

        return $so->itemable->nama_barang
            ?? $so->itemable->nama_master_item
            ?? '-';
    }),
Forms\Components\Placeholder::make('deskripsi')
    ->label('Deskripsi')
    ->content(function (Get $get) {
        $so = SalesOrder::with('itemable')->find($get('sales_order_id'));

        return $so?->itemable?->deskripsi ?? '-';
    }),

                         ]),

                        // 🔹 KANAN
                        Forms\Components\Grid::make(1)->schema([

                            Forms\Components\Placeholder::make('qty_display')
                                ->label('Jumlah Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->qty ?? '-'
                                ),

                           Forms\Components\Placeholder::make('toleransi')
    ->label('Toleransi')
    ->content(function (Get $get) {
        $so = SalesOrder::find($get('sales_order_id'));

        if (!$so || is_null($so->toleransi_pengiriman)) {
            return '-';
        }
        return $so->toleransi_pengiriman . ' %';
    }),
                            Forms\Components\Placeholder::make('tipe')
                                ->label('Tipe Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->tipe_pesanan ?? '-'
                                ),
                            Forms\Components\Placeholder::make('tanggal')
                                ->label('Tanggal Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->tanggal_po ?? '-'
                             ),
                        ]),
                    ]),
                ])
                ->visible(fn (Get $get) => filled($get('sales_order_id'))),
        ]);
}

public static function getNavigationLabel(): string
{
    return 'Master Spk';
}

   public static function table(Table $table): Table
{
    return $table
     ->defaultSort('created_at', 'desc')
        ->columns([
    Tables\Columns\TextColumn::make('no_spk')
        ->searchable(),

    Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
        ->label('No Sales Order'),
   Tables\Columns\TextColumn::make('nama_barang')
    ->label('Nama Barang')
    ->getStateUsing(function ($record) {

        return $record->salesOrder?->itemable?->nama_barang
            ?? $record->salesOrder?->itemable?->nama_master_item
            ?? '-';
    })
    ->searchable(),

    // 🔥 KOLOM BARU
    Tables\Columns\TextColumn::make('salesOrder.qty')
        ->label('Jumlah Order')
        ->numeric()
        ->sortable(),

       Tables\Columns\TextColumn::make('on_hand_stock')
    ->label('On Hand Stock')
    ->getStateUsing(function ($record) {

        // 🔹 total barang hasil packaging
        $totalPackaging = $record->packagings
            ->sum('total_satuan_penuh');

        // 🔹 total barang terkirim
        $totalPengiriman = $record->suratJalans
            ->sum('qty_pengiriman');

        // 🔥 stock sisa
        return $totalPackaging - $totalPengiriman;
    })
    ->badge()
    ->color(function ($state) {

        if ($state <= 0) {
            return 'danger';
        }

        if ($state <= 100) {
            return 'warning';
        }

        return 'success';
    })
    ->sortable(),

    Tables\Columns\BadgeColumn::make('status')
        ->colors([
            'warning' => 'ON PROCESS',
            'success' => 'FINISH',
        ]),
])
     ->actions([
    ActionGroup::make([
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
          Tables\Actions\ViewAction::make()
            ->icon('heroicon-o-eye'),
        Tables\Actions\Action::make('finish')
            ->label('Mark as FINISH')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->action(fn ($record) => $record->update([
                'status' => 'FINISH'
            ]))
            ->visible(fn ($record) => $record->status !== 'FINISH'),
    ])
    ->icon('heroicon-m-ellipsis-vertical') // 🔥 icon 3 titik
    ->tooltip('Actions')
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
        'index' => Pages\ListSuratPerintahKerjas::route('/'),
        'create' => Pages\CreateSuratPerintahKerja::route('/create'),
        'view' => Pages\ViewSuratPerintahKerja::route('/{record}'),
        'edit' => Pages\EditSuratPerintahKerja::route('/{record}/edit'),
    ];
}

public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with([
            'packagings',
            'suratJalans',
            'salesOrder.itemable',
        ]);
}
}