<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransFakturResource\Pages;
use App\Models\TransFaktur;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransFakturResource extends Resource
{
    protected static ?string $model = TransFaktur::class;
     protected static ?string $navigationGroup = 'Finance';
protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make('Informasi Transaksi')
                ->schema([

                    Grid::make(3)
    ->schema([

       Select::make('purchase_order_id')
    ->label('Purchase Order')
    ->relationship(
        'purchaseOrder',
        'no_po'
    )
    ->searchable()
    ->preload()
    ->live()
    ->required()

    ->afterStateUpdated(function (
        $state,
        Forms\Set $set
    ) {

        $po = \App\Models\PurchaseOrder::with(
            'items.item'
        )->find($state);

        if (!$po) {
            return;
        }

        $items = [];

        foreach ($po->items as $item) {

            $qty =
                (float) ($item->qty_po ?? 0);

            $harga =
                (float) ($item->price ?? 0);

            $diskon =
                (float) ($item->discount ?? 0);

            $total =
                ($qty * $harga)
                - $diskon;

            $items[] = [

                'deskripsi' =>
                    $item->item?->nama_master_item
                    ?? '-',

                'qty' =>
                    $qty,

                'unit' =>
                    $item->satuan ?? '-',

                'harga_satuan' =>
                    $harga,

                'diskon' =>
                    $diskon,

                'total' =>
                    $total,
            ];
        }

        $set('items', $items);

        $subtotal = collect($items)
            ->sum('total');

        $ppn = $subtotal * 0.11;

        $grandTotal =
            $subtotal + $ppn;

        $set('subtotal', $subtotal);

        $set('ppn', $ppn);

        $set('grand_total', $grandTotal);
    }),

        Select::make('pic_id')
            ->label('PIC')
            ->relationship(
                'pic',
                'nama'
            )
            ->searchable()
            ->preload(),

        TextInput::make('gudang')
            ->label('Gudang'),

        TextInput::make('no_faktur'),

        TextInput::make('no_invoice'),

        DatePicker::make(
            'tanggal_transaksi'
        )
            ->default(now())
            ->required(),
    ]),
                ]),

            Section::make('Rincian Barang')
    ->schema([

        Forms\Components\Repeater::make('items')
            ->relationship()
            ->schema([

                Grid::make(6)
                    ->schema([

                        TextInput::make('deskripsi')
                            ->label('Deskripsi')
                            ->readOnly()
                            ->required(),

                        TextInput::make('qty')
                            ->numeric()
                            ->readOnly()
                            ->required(),

                        TextInput::make('unit')
                            ->readOnly(),

                        TextInput::make('harga_satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->required(),

                        TextInput::make('diskon')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (
                                $state,
                                $get,
                                $set
                            ) {

                                $qty =
                                    (float) $get('qty');

                                $harga =
                                    (float) $get('harga_satuan');

                                $diskon =
                                    (float) $state;

                                $total =
                                    ($qty * $harga)
                                    - $diskon;

                                $set('total', $total);
                            }),

                        TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ]),
            ])

            ->addable(false)
            ->deletable(false)
            ->reorderable(false)

            ->live()

            ->afterStateUpdated(function (
                $state,
                Forms\Set $set
            ) {

                $subtotal = collect($state)
                    ->sum('total');

                $ppn = $subtotal * 0.11;

                $grandTotal =
                    $subtotal + $ppn;

                $set('subtotal', $subtotal);

                $set('ppn', $ppn);

                $set(
                    'grand_total',
                    $grandTotal
                );
            }),
    ]),

            Section::make('Summary')
    ->schema([

        TextInput::make('subtotal')
            ->label('Subtotal')
            ->readOnly()
            ->prefix('Rp')
            ->formatStateUsing(fn ($state) =>
                number_format($state, 0, ',', '.')
            ),

        TextInput::make('ppn')
            ->label('PPN')
            ->readOnly()
            ->prefix('Rp')
            ->formatStateUsing(fn ($state) =>
                number_format($state, 0, ',', '.')
            ),

        TextInput::make('grand_total')
        
            ->label('Grand Total')
            ->readOnly()
            ->prefix('Rp')
            ->formatStateUsing(fn ($state) =>
                number_format($state, 0, ',', '.')
            ),
    ]),
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Trans Faktur';
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make(
                'purchaseOrder.no_po'
            )
                ->label('PO'),

            Tables\Columns\TextColumn::make(
                'no_invoice'
            ),

            Tables\Columns\TextColumn::make(
                'tanggal_transaksi'
            )
                ->date(),

            Tables\Columns\TextColumn::make(
                'grand_total'
            )
                ->money('IDR'),

        ])

        ->actions([

            Tables\Actions\Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn ($record) =>
                    static::getUrl('view', [
                        'record' => $record
                    ])
                ),

            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTransFakturs::route('/'),
            'create' => Pages\CreateTransFaktur::route('/create'),
            'edit' => Pages\EditTransFaktur::route('/{record}/edit'),
            'view' => Pages\ViewTransFaktur::route('/{record}'),
        ];
    }
}