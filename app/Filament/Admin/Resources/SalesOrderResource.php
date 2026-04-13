<?php
namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
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

           Forms\Components\Select::make('item_source')
    ->label('Pilih Item')
   ->options(function () {

    $options = [];

    foreach (\App\Models\FinishGoodItem::select('id', 'nama_barang')->get() as $item) {
        $options['fg-' . $item->id] = $item->nama_barang . ' (Finish Good)';
    }

    foreach (\App\Models\MasterItem::select('id', 'nama_master_item')->get() as $item) {
        $options['mi-' . $item->id] = $item->nama_master_item . ' (Master Item)';
    }

    return $options;
})
    ->afterStateUpdated(function ($state, callable $set) {
    if (!$state) return;

    [$type, $id] = explode('-', $state);

    if ($type === 'fg') {
        $item = \App\Models\FinishGoodItem::find($id);

        if ($item) {
            $set('itemable_type', \App\Models\FinishGoodItem::class);
            $set('itemable_id', $item->id);
            $set('customer_id', $item->customer_id);

            // 🔥 AUTO ISI KODE MATERIAL
            $set('kode_material', $item->kode_material_produk);
        }
    }

    if ($type === 'mi') {
        $item = \App\Models\MasterItem::find($id);

        if ($item) {
            $set('itemable_type', \App\Models\MasterItem::class);
            $set('itemable_id', $item->id);

            // optional kosongkan
            $set('kode_material', null);
        }
    }
}),
Forms\Components\Hidden::make('itemable_type'),
Forms\Components\Hidden::make('itemable_id'),
Forms\Components\Hidden::make('finish_good_item_id'),
Forms\Components\Hidden::make('kode_material'),

            Forms\Components\Select::make('customer_id')
    ->label('Customer')
    ->options(\App\Models\Customer::pluck('nama_customer', 'id'))
    ->required(),
                Forms\Components\TextInput::make('no_sales_order')
                    ->disabled(),

                Forms\Components\TextInput::make('no_po_customer'),

                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->required(),

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
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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