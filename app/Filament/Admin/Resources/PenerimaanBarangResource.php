<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PenerimaanBarangResource\Pages;
use App\Models\PenerimaanBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenerimaanBarangResource extends Resource
{
    protected static ?string $model = PenerimaanBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $navigationLabel = 'Penerimaan Barang';

    protected static ?string $modelLabel = 'Penerimaan Barang';

    protected static ?string $pluralModelLabel = 'Penerimaan Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Form Penerimaan Barang')
                    ->schema([

                      Forms\Components\Select::make('purchase_order_id')
    ->label('ID Purchase Order')
    ->relationship('purchaseOrder', 'no_po')
    ->preload()
    ->live()
    ->afterStateUpdated(function ($state, callable $set) {

        if (!$state) {
            $set('items', []);
            return;
        }

        $po = \App\Models\PurchaseOrder::with([
            'items.item.satuan',
            'items'
        ])->find($state);

        if (!$po) {
            $set('items', []);
            return;
        }

        $items = $po->items->map(function ($item) {

            return [
                'purchase_order_item_id' => $item->id,

                'nama_item_display' =>
                    ($item->item->kode_material ?? '-') .
                    ' - ' .
                    ($item->item->nama_master_item ?? '-'),

                'qty_po_display' =>
                    number_format($item->qty, 2) .
                    ' | ' .
                    ($item->item->satuan->nama_satuan ?? '-'),

                'qty_sebelumnya_display' =>
                    '0 | ' .
                    ($item->item->satuan->nama_satuan ?? '-'),

                'qty_terima' => 0,

                'catatan_po_display' =>
                    $item->catatan ?? '-',

                'catatan_item' => null,
                'tgl_exp' => null,
                'no_lot' => null,
            ];
        })->toArray();

        $set('items', $items);
    })
    ->required(),

                        Forms\Components\TextInput::make('no_surat_jalan')
                            ->label('No. Surat Jalan Kirim')
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_terima')
                            ->label('Tgl. Terima Barang')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('nopol_kendaraan')
                            ->label('Nopol Kendaraan'),

                        Forms\Components\TextInput::make('nama_pengirim')
                            ->label('Nama Pengirim'),

                        Forms\Components\Textarea::make('catatan_pengiriman')
                            ->label('Catatan Pengiriman')
                            ->columnSpanFull(),

                    ])->columns(3),

            Forms\Components\Section::make('Data Item Purchase Order')
    ->schema([

      Forms\Components\Repeater::make('items')
    ->relationship('items')
    ->schema([

        Forms\Components\Hidden::make('purchase_order_item_id'),

        Forms\Components\TextInput::make('nama_item_display')
            ->label('Type | Kode - Nama Item')
            ->disabled()
            ->dehydrated(false),

        Forms\Components\TextInput::make('qty_po_display')
            ->label('Qty | Satuan Purchase Order')
            ->disabled()
            ->dehydrated(false),

        Forms\Components\TextInput::make('qty_sebelumnya_display')
            ->label('Qty | Satuan Penerimaan Sebelumnya')
            ->disabled()
            ->dehydrated(false),

        Forms\Components\TextInput::make('qty_terima')
            ->label('Qty | Satuan Penerimaan')
            ->numeric()
            ->required(),

        Forms\Components\TextInput::make('catatan_po_display')
            ->label('Catatan PO')
            ->disabled()
            ->dehydrated(false),

        Forms\Components\Textarea::make('catatan_item')
            ->label('Catatan Item'),

        Forms\Components\DatePicker::make('tgl_exp')
            ->label('Tgl.Exp'),

        Forms\Components\TextInput::make('no_lot')
            ->label('No.LOT'),

    ])
    ->columns(4)
    ->columnSpanFull()
    ->addable(false)
    ->deletable(false)
    ->reorderable(false)
    ->collapsible()

    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('no_penerimaan')
                    ->label('No Penerimaan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchaseOrder.no_po')
                    ->label('No Purchase Order')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_terima')
                    ->label('Tanggal Terima')
                    ->date(),

                Tables\Columns\TextColumn::make('nama_pengirim')
                    ->label('Pengirim'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerimaanBarangs::route('/'),
            'create' => Pages\CreatePenerimaanBarang::route('/create'),
            'edit' => Pages\EditPenerimaanBarang::route('/{record}/edit'),
        ];
    }
}