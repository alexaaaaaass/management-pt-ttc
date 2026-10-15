<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PenerimaanBarangResource\Pages;
use App\Models\PenerimaanBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

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

    // 🔥 reset dulu
    $set('items', []);

    if (!$state) return;

    $po = \App\Models\PurchaseOrder::with([
        'items.item.satuan',
    ])->find($state);

    if (!$po) return;

    $items = $po->items->map(function ($item) {

       return [

    'purchase_order_item_id'=>$item->id,

    'nama_item_display'=>
        ($item->item->kode_material ?? '-')
        .' - '.
        ($item->item->nama_master_item ?? '-'),

    'qty_po_display'=>
        $item->qty_po.' | '.$item->item->satuan->nama_satuan,

    'qty_received'=>$item->qty_received,

    'qty_sebelumnya_display'=>
        $item->qty_received.' | '.$item->item->satuan->nama_satuan,

    'qty_terima'=>0,

    'catatan_po_display'=>$item->catatan,

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
                        
                        Forms\Components\Hidden::make('qty_received'),

                        Forms\Components\Textarea::make('catatan_pengiriman')
                            ->label('Catatan Pengiriman')
                            ->columnSpanFull(),

                    ])->columns(3),

            Forms\Components\Section::make('Data Item Purchase Order')
             ->extraAttributes([
        'style' => '
            border-left: 5px solid #3b82f6;
            border-radius: 10px;
            padding: 12px;
        ',
    ])
    ->schema([

      Forms\Components\Repeater::make('items')
       ->relationship()
      ->statePath('items')
      ->itemLabel(function ($state) {
    return $state['nama_item_display'] ?? 'Item';
})
      ->key('penerimaan_items')
    ->schema([

        Forms\Components\Hidden::make('purchase_order_item_id')
    ->dehydrated(true)
    ->required(),

        Forms\Components\TextInput::make('nama_item_display')
            ->label('Type | Kode - Nama Item')
            ->disabled()
            ->dehydrated(false),

     Forms\Components\TextInput::make(
    'qty_sebelumnya_display'
)
    ->label(
        'Qty | Satuan Penerimaan Sebelumnya'
    ),

       Forms\Components\TextInput::make('qty_po_display')
    ->label('Qty | Satuan Purchase Order')
            ->disabled()
            ->dehydrated(false),

      Forms\Components\TextInput::make('qty_terima')
    ->label('Qty Penerimaan')
    ->numeric()
    ->required()
    ->live()
    ->afterStateUpdated(function ($state, $get, $set) {

        $qtyPO = (float) explode('|', $get('qty_po_display'))[0];

        $received = (float) ($get('qty_received') ?? 0);

        $remaining = $qtyPO - $received;

        if ($state > $remaining) {

            $set('qty_terima', $remaining);

        }

    })
    ->helperText(function ($get){

        $qtyPO = (float) explode('|', $get('qty_po_display'))[0];

        $received = (float) ($get('qty_received') ?? 0);

        return 'Sisa yang boleh diterima : '.($qtyPO-$received);

    }),

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

            ])
          ->actions([

    ActionGroup::make([

        Action::make('preview')

            ->label('Preview PDF')

            ->icon('heroicon-o-eye')

            ->url(fn($record)=>

                route('pb.preview',$record)

            )

            ->openUrlInNewTab(),

      Action::make('download')
    ->label('Download PDF')
    ->icon('heroicon-o-arrow-down-tray')
    ->url(fn (PenerimaanBarang $record) => route('pb.download', [
        'id' => $record->id,
    ])),

        Tables\Actions\EditAction::make()
            ->disabled(fn ($record) => ! app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->canModify($record))
            ->tooltip(fn ($record) => ! app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->canModify($record) ? 'Penerimaan Barang terkunci karena sudah memiliki Retur Eksternal' : null),

        Tables\Actions\DeleteAction::make()
            ->disabled(fn ($record) => ! app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->canModify($record))
            ->tooltip(fn ($record) => ! app(\App\Services\PurchaseOrder\PenerimaanBarangService::class)->canModify($record) ? 'Tidak dapat dihapus karena sudah memiliki Retur Eksternal terkait' : null),

    ])

    ->icon('heroicon-o-ellipsis-vertical')

    ->tooltip('Actions')

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