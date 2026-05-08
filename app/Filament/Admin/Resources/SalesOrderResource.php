<?php
namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

use Filament\Forms\Components\Actions\Action;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Grid::make(3)->schema([

       Forms\Components\Select::make('item_source')
       ->afterStateHydrated(function ($state, callable $set, $record) {

    if (!$record) return;

    if ($record->itemable_type === \App\Models\FinishGoodItem::class) {
        $set('item_source', 'fg-' . $record->itemable_id);
    }

    if ($record->itemable_type === \App\Models\MasterItem::class) {
        $set('item_source', 'mi-' . $record->itemable_id);
    }
})
    ->label('Pilih Item')
    ->searchable()
    ->native(false)
    ->preload()
    ->options(
        collect()
            ->merge(
                \App\Models\FinishGoodItem::select('id', 'nama_barang', 'customer_id', 'kode_material_produk')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        'fg-' . $item->id => $item->nama_barang . ' (Finish Good)'
                    ])
            )
            ->merge(
                \App\Models\MasterItem::select('id', 'nama_master_item')
                    ->get()
                    ->mapWithKeys(fn ($item) => [
                        'mi-' . $item->id => $item->nama_master_item . ' (Master Item)'
                    ])
            )
            ->toArray()
    )
    ->live()
   ->afterStateUpdated(function ($state, callable $set) {

    if (!$state) return;

    [$type, $id] = explode('-', $state);

    if ($type === 'fg') {

        $item = \App\Models\FinishGoodItem::find($id);

        if ($item) {
            $set('itemable_type', \App\Models\FinishGoodItem::class);
            $set('itemable_id', $item->id);
            $set('customer_id', $item->customer_id);
            $set('kode_material', $item->kode_material_produk);
        }
    }

    if ($type === 'mi') {

        $item = \App\Models\MasterItem::find($id);

        if ($item) {
            $set('itemable_type', \App\Models\MasterItem::class);
            $set('itemable_id', $item->id);
            $set('kode_material', null);
        }
    }

    // reset BOM tiap ganti item
    $set('bom_items', []);
}),

Forms\Components\Hidden::make('itemable_type'),
Forms\Components\Hidden::make('itemable_id'),
Forms\Components\Hidden::make('finish_good_item_id'),
Forms\Components\Hidden::make('kode_material'),

            Forms\Components\Select::make('customer_id')
    ->label('Customer')
    ->relationship('customer', 'nama_customer')
    ->searchable()
    ->preload()
    ->native(false)
    ->required(),
                Forms\Components\TextInput::make('no_sales_order')
                    ->disabled(),

                Forms\Components\TextInput::make('no_po_customer'),
Forms\Components\TextInput::make('qty')
    ->numeric()
    ->required()
    ->rule(function ($get) {
        return function (string $attribute, $value, \Closure $fail) use ($get) {

            $itemableType = $get('itemable_type');
            $itemableId = $get('itemable_id');

            if ($itemableType !== \App\Models\FinishGoodItem::class) return;

            $finishGood = \App\Models\FinishGoodItem::with('materials')->find($itemableId);

            if (!$finishGood) return;

            foreach ($finishGood->materials as $bom) {

                $kebutuhan = $value * $bom->qty;

                $stock = \App\Models\MaterialStock::where(
                    'item_id',
                    $bom->master_item_id
                )->first();

                $available = $stock
                    ? ($stock->on_hand - $stock->allocation)
                    : 0;

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
    ->numeric()
    ->prefix('Rp')
    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
    ->dehydrateStateUsing(fn ($state) => str_replace('.', '', $state)),

Forms\Components\TextInput::make('harga_kirim')
    ->numeric()
    ->prefix('Rp')
    ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') : null)
    ->dehydrateStateUsing(fn ($state) => str_replace('.', '', $state)),

           Forms\Components\Select::make('mata_uang')
    ->options([
        'rupiah' => 'Rupiah',
        'usd' => 'USD',
        'yuan' => 'Yuan',
    ])
    ->default('rupiah'),

                Forms\Components\TextInput::make('syarat_pembayaran'),

                Forms\Components\DatePicker::make('tanggal_po'),

                Forms\Components\Select::make('klaim_kertas')
                    ->options([
                        'claim' => 'Claim',
                        'not_claim' => 'Not Claim',
                    ])
                    ->required(),

                Forms\Components\Select::make('dipesan_via')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                        'sosmed' => 'Sosmed',
                    ]),

                Forms\Components\TextInput::make('tipe_pesanan'),

                Forms\Components\TextInput::make('toleransi_pengiriman')
    ->numeric()
    ->suffix('%')
    ->minValue(0)
    ->maxValue(100),

                Forms\Components\Textarea::make('catatan'),

                Forms\Components\Textarea::make('catatan_colour_range'),
            ]),
            Forms\Components\Actions::make([
    Action::make('getBom')
        ->label('Get Bill Of Material')
        ->icon('heroicon-o-arrow-down-tray')
        ->color('success')
        ->action(function ($get, $set) {

            $state = $get('item_source');

            if (!$state) return;

            [$type, $id] = explode('-', $state);

            if ($type !== 'fg') {
                $set('bom_items', []);
                return;
            }

                $item = \App\Models\FinishGoodItem::with([
            'materials.material',
            'materials.departemen'
        ])->find($id);

        $bom = [];

        foreach ($item->materials as $mat) {
            $bom[] = [
                'nama_material' => optional($mat->material)->nama_master_item ?? '-',
                'departemen' => optional($mat->departemen)->nama_departemen ?? '-',
                'qty' => $mat->qty ?? 0,
                'satuan' => optional($mat->material?->satuan)->nama_satuan ?? '-',
                'waste' => $mat->waste ?? 0,
                'keterangan' => $mat->keterangan ?? '-',
            ];
        }

        $set('bom_items', []);
        $set('bom_items', $bom);
        })
]),
Forms\Components\Section::make('Bill of Material')
->extraAttributes([
    'class' => 'border-l-4 border-green-500 bg-green-50 dark:bg-gray-900 rounded-xl p-4'
])
    ->schema([

        Forms\Components\Repeater::make('bom_items')
              ->label(function ($get) {
        $state = $get('item_source');

        if (!$state) return 'Bill Of Material';

        [$type, $id] = explode('-', $state);

        if ($type !== 'fg') return 'Bill Of Material';

        $item = \App\Models\FinishGoodItem::find($id);

        return $item 
            ? 'Bill Of Material - ' . $item->nama_barang
            : 'Bill Of Material';
    })
        ->itemLabel(function ($state) {
    $nama = $state['nama_material'] ?? 'Material';
    $qty = $state['qty'] ?? 0;
    $satuan = $state['satuan'] ?? '';

    return "{$nama} ({$qty} {$satuan})";
})
            ->schema([

               Forms\Components\TextInput::make('nama_material')
            ->label('Material')
            ->disabled(),


                 Forms\Components\TextInput::make('departemen')
            ->label('Departemen')
            ->disabled(),

        Forms\Components\TextInput::make('qty')
            ->numeric()
            ->disabled(),

        Forms\Components\TextInput::make('satuan')
            ->disabled(),

        Forms\Components\TextInput::make('waste')
            ->numeric()
            ->disabled(),
              Forms\Components\Textarea::make('keterangan')
            ->disabled(),

            ])
            ->columns(3)
            ->defaultItems(0)
            ->collapsible()
            ->dehydrated(false), // 🔥 penting: jangan disimpan ke DB

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
    ->getStateUsing(function ($record) {
        return $record->itemable?->nama_barang
            ?? $record->itemable?->nama_master_item
            ?? '-';
    }),
                Tables\Columns\TextColumn::make('customer.nama_customer')->label('Customer'),
                Tables\Columns\TextColumn::make('qty'),
                Tables\Columns\TextColumn::make('harga_pcs')
    ->money('IDR'),

Tables\Columns\TextColumn::make('harga_kirim')
    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}