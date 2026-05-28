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

            Section::make('Tagihan')

                ->schema([

                  Forms\Components\Hidden::make(
            'purchase_order_id'
        ),

        Forms\Components\Hidden::make(
            'supplier_id'
        ),

                    Grid::make(3)
                        ->schema([

                            Select::make(
                                'penerimaan_barang_id'
                            )
                                ->label(
                                    'Penerimaan Barang (LPB)'
                                )

                                ->options(

                                    PenerimaanBarang::with(
                                        'purchaseOrder'
                                    )

                                    ->get()

                                    ->mapWithKeys(
                                        fn ($item) => [

                                            $item->id =>

                                                $item->no_penerimaan .

                                                ' (PO: ' .

                                                optional(
                                                    $item->purchaseOrder
                                                )->no_po .

                                                ')'
                                        ]
                                    )
                                )

                                ->searchable()

                                ->live()

                                ->afterStateUpdated(function (
                                    $state,
                                    Set $set
                                ) {

                                    $lpb =
                                        PenerimaanBarang::with([

                                            'items.purchaseOrderItem.item',
                                            'purchaseOrder'

                                        ])->find($state);

                                    if (!$lpb) {
                                        return;
                                    }

                                    $items = [];

                                    $totalBarang = 0;

                                    foreach (
                                        $lpb->items
                                        as $item
                                    ) {

                                        $harga =
                                            optional(
                                                $item->purchaseOrderItem
                                            )->price
                                            ?? 0;

                                        $qty =
                                            $item->qty_terima
                                            ?? 0;

                                        $subtotal =
                                            $qty * $harga;

                                        $totalBarang += $subtotal;

                                        $items[] = [

                                            'item_name' =>

                                                optional(
                                                    optional(
                                                        $item->purchaseOrderItem
                                                    )->item
                                                )->nama_master_item

                                                ?? 'Item',

                                            'qty' => $qty,

                                            'harga' => $harga,

                                            'diskon' => 0,

                                            'subtotal' => $subtotal,
                                        ];
                                    }

                                    $ppn =
                                        $totalBarang * 0.11;

                                    $ongkir = 0;

                                    $dp = 0;

                                    $grandTotal =

                                        $totalBarang
                                        + $ppn
                                        + $ongkir
                                        - $dp;

                                    $set('items', $items);

                                    $set(
                                        'purchase_order_id',
                                        $lpb->purchase_order_id
                                    );

                                    $set(
                                        'supplier_id',
                                        optional(
                                            $lpb->purchaseOrder
                                        )->supplier_id
                                    );

                                    $set(
                                        'total_barang',
                                        $totalBarang
                                    );

                                    $set('ppn', $ppn);

                                    $set('ongkir', $ongkir);

                                    $set('dp', $dp);

                                    $set(
                                        'grand_total',
                                        $grandTotal
                                    );
                                }),

                            Select::make('karyawan_id')
                                ->label('Karyawan')

                                ->options(
                                    User::pluck(
                                        'name',
                                        'id'
                                    )
                                )

                                ->searchable(),

                            TextInput::make(
                                'invoice_vendor'
                            ),
                        ]),

                    Grid::make(3)
                        ->schema([

                            TextInput::make('periode')
                                ->default(now()->year),

                            DatePicker::make(
                                'tanggal_transaksi'
                            ),

                            DatePicker::make(
                                'tanggal_jatuh_tempo'
                            ),
                        ]),

                    Textarea::make('keterangan'),

                ]),

            Section::make('Item Details')

                ->schema([

                    Repeater::make('items')

                        ->relationship()

                        ->collapsible()

                        ->collapsed()

                        ->cloneable(false)

                        ->addable(false)

                        ->deletable(false)

                        ->reorderable(false)

                        ->live()

                        ->itemLabel(fn (
                            array $state
                        ): ?string =>

                            $state['item_name']
                            ?? 'Item'
                        )

                        ->schema([

                            Grid::make(5)
                                ->schema([

                                   TextInput::make('item_name')
    ->disabled()
    ->dehydrated(),

                                    TextInput::make(
                                        'qty'
                                    )
                                        ->numeric()
                                        ->live(),

                                    TextInput::make(
                                        'harga'
                                    )
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->live(),

                                    TextInput::make(
                                        'diskon'
                                    )
                                        ->numeric()
                                        ->default(0)
                                        ->suffix('%')
                                        ->live(),

                                    TextInput::make(
    'subtotal'
)
    ->disabled()
    ->dehydrated()
    ->prefix('Rp'),

                                ]),
                        ])

                        ->afterStateUpdated(function (
                            $state,
                            Set $set
                        ) {

                            $totalBarang = 0;

                            foreach (
                                $state as $key => $item
                            ) {

                                $qty =
                                    (float) (
                                        $item['qty']
                                        ?? 0
                                    );

                                $harga =
                                    (float) (
                                        $item['harga']
                                        ?? 0
                                    );

                                $diskon =
                                    (float) (
                                        $item['diskon']
                                        ?? 0
                                    );

                                $subtotal =

                                    ($qty * $harga)

                                    -

                                    (
                                        ($qty * $harga)
                                        * $diskon
                                        / 100
                                    );

                                $state[$key]['subtotal']
                                    = $subtotal;

                                $totalBarang += $subtotal;
                            }

                            $ppn =
                                $totalBarang * 0.11;

                            $ongkir =
                                (float) 0;

                            $dp =
                                (float) 0;

                            $grandTotal =

                                $totalBarang
                                + $ppn
                                + $ongkir
                                - $dp;

                            $set('items', $state);

                            $set(
                                'total_barang',
                                $totalBarang
                            );

                            $set('ppn', $ppn);

                            $set(
                                'grand_total',
                                $grandTotal
                            );
                        })

                        ->columns(1),

                    Grid::make(2)
                        ->schema([

                            TextInput::make(
                                'total_barang'
                            )
                                ->numeric(),

                            TextInput::make('ppn')
                                ->numeric(),

                            TextInput::make('ongkir')
                                ->numeric()
                                ->live(),

                            TextInput::make('dp')
                                ->numeric()
                                ->live(),

                            TextInput::make(
                                'grand_total'
                            )
                                ->numeric(),
                        ]),
                ])
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

    Tables\Columns\TextColumn::make(
        'kode_tagihan'
    )
        ->searchable(),

    Tables\Columns\TextColumn::make(
        'penerimaanBarang.no_penerimaan'
    )
        ->label('LPB'),

    Tables\Columns\TextColumn::make(
        'purchaseOrder.no_po'
    )
        ->label('PO'),

    Tables\Columns\TextColumn::make(
        'supplier.nama_supplier'
    ),

    Tables\Columns\TextColumn::make(
        'grand_total'
    )
        ->money('IDR'),

    Tables\Columns\TextColumn::make(
        'tanggal_transaksi'
    )
        ->date(),

])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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