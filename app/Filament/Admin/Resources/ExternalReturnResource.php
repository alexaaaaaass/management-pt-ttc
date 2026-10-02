<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExternalReturnResource\Pages;
use App\Models\ExternalReturn;
use App\Models\PenerimaanBarang;
use App\Services\ExternalReturn\ExternalReturnService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ExternalReturnResource extends Resource
{
    protected static ?string $model = ExternalReturn::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Section::make('Form External Return')
                ->schema([

                    Forms\Components\Select::make('penerimaan_barang_id')
                        ->label('No. Laporan Penerimaan Barang')
                        ->relationship('penerimaanBarang', 'no_penerimaan')
                        ->searchable()
                        ->preload()
                        ->live()
                       ->afterStateUpdated(function ($state, callable $set) {

                            // Reset data ketika LPB berubah
                            $set('supplier_display', null);
                            $set('supplier_id', null);
                            $set('tanggal_terima_display', null);
                            $set('surat_jalan_display', null);
                            $set('items', []);

                            if (!$state) {
                                return;
                            }

                            $pb = PenerimaanBarang::with([
                                'purchaseOrder.supplier',
                                'items.purchaseOrderItem.item.satuan',
                            ])->find($state);

                            if (!$pb) {
                                return;
                            }

                            // Isi informasi header
                            $set(
                                'supplier_display',
                                optional($pb->purchaseOrder->supplier)->nama_supplier
                            );

                            $set(
                                'supplier_id',
                                optional($pb->purchaseOrder)->supplier_id
                            );

                            $set(
                                'tanggal_terima_display',
                                $pb->tanggal_terima
                            );

                            $set(
                                'surat_jalan_display',
                                $pb->no_surat_jalan
                            );

                            // Isi item return
                            $items = $pb->items->map(function ($row) {

                                return [

                                    'penerimaan_barang_item_id' => $row->id,

                                    'nama_item' =>
                                        ($row->purchaseOrderItem->item->kode_material ?? '-')
                                        .' - '.
                                        ($row->purchaseOrderItem->item->nama_master_item ?? '-'),

                                    'qty_terima' => $row->qty_terima,

                                    'satuan' =>
                                        $row->purchaseOrderItem->item->satuan->nama_satuan ?? '-',

                                    'qty_return' => 0,

                                    'catatan' => null,

                                ];

                            })->toArray();

                            $set('items', $items);

                        })
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_return')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('supplier_display')
                        ->label('Supplier')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('tanggal_terima_display')
                        ->label('Tanggal Terima')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('surat_jalan_display')
                        ->label('No Surat Jalan')
                        ->disabled()
                        ->dehydrated(false),

                    Forms\Components\Textarea::make('alasan')
                        ->columnSpanFull(),

                ])
                ->columns(2),

            Forms\Components\Section::make('Item Return')
                ->schema([

                    Forms\Components\Repeater::make('items')
                     ->relationship()
                        ->schema([

                          Forms\Components\Hidden::make('penerimaan_barang_item_id')
                            ->dehydrated(true)
                            ->required(),

                            Forms\Components\TextInput::make('nama_item')
                                ->label('Item')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('qty_terima')
                                ->label('Qty Terima')
                                ->disabled()
                                ->suffix(fn ($get) => $get('satuan'))
                                ->dehydrated(false),

                            Forms\Components\Hidden::make('satuan')
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('qty_return')
                                ->label('Qty Return')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(function ($state, $get, $set) {

                                    if ($state > $get('qty_terima')) {

                                        $set(
                                            'qty_return',
                                            $get('qty_terima')
                                        );

                                    }

                                })
                                ->helperText(function ($get) {

                                    return 'Maksimal Return : '
                                        .$get('qty_terima');

                                }),

                            Forms\Components\Textarea::make('catatan')
                                ->rows(2),

                        ])
                        ->columns(4)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),

                ]),

        ]);
}

    public static function getNavigationLabel(): string
{
    return 'External Return';
}

  public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->columns([

            Tables\Columns\TextColumn::make('no_return')
                ->label('No Return')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('penerimaanBarang.no_penerimaan')
                ->label('No LPB')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('penerimaanBarang.purchaseOrder.no_po')
                ->label('No PO')
                ->searchable(),

            Tables\Columns\TextColumn::make('penerimaanBarang.purchaseOrder.supplier.nama_supplier')
                ->label('Supplier')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal_return')
                ->label('Tanggal Return')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('items_count')
                ->label('Total Item')
                ->counts('items'),

            Tables\Columns\TextColumn::make('total_qty_return')
                ->label('Qty Return')
                ->state(function ($record) {

                    return $record->items->sum('qty_return');

                })
                ->badge()
                ->color('warning'),

          Tables\Columns\BadgeColumn::make('status')
    ->colors([
        'gray'=>'draft',
        'success'=>'approved',
        'danger'=>'cancel'
    ]),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i'),

        ])

        ->actions([

            Tables\Actions\ActionGroup::make([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('preview')
                    ->label('Preview PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route(
                        'external-return.preview',
                        $record
                    ))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route(
                        'external-return.download',
                        $record
                    )),

                Tables\Actions\Action::make('approve')

    ->label('Approve')

    ->icon('heroicon-o-check')

    ->color('success')

    ->visible(fn($record)=>
        $record->status=='draft'
    )

    ->requiresConfirmation()

    ->action(function($record){

        app(
            ExternalReturnService::class
        )->approve($record);

    }),   
    
    Tables\Actions\Action::make('cancel')

    ->label('Cancel')

    ->icon('heroicon-o-x-circle')

    ->color('danger')

    ->visible(fn($record)=>
        $record->status=='approved'
    )

    ->requiresConfirmation()

    ->action(function($record){

        app(
            ExternalReturnService::class
        )->cancel($record);

    }),

             Tables\Actions\DeleteAction::make()

    ->visible(fn($record)=>
        $record->status=='draft'
    ),

            ]),

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
            'index' => Pages\ListExternalReturns::route('/'),
            'create' => Pages\CreateExternalReturn::route('/create'),
            'edit' => Pages\EditExternalReturn::route('/{record}/edit'),
        ];
    }
}