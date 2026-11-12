<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountInResource\Pages;
use App\Models\FinishGoodItem;
use App\Models\MasterItem;
use App\Models\SubcountIn;
use App\Models\SubcountOut;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class SubcountInResource extends Resource
{
    protected static ?string $model = SubcountIn::class;
    protected static ?string $navigationGroup = 'Subcount';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make('Informasi')
                ->schema([

                    TextInput::make('no_subcount')
                        ->disabled(),

                    DatePicker::make('tanggal_subcount')
                        ->required(),

                    TextInput::make('surat_jalan_pengiriman')
                        ->required(),

                    TextInput::make('admin_produksi')
                        ->required(),

                    TextInput::make('supervisor')
                        ->required(),

                    TextInput::make('admin_mainstore')
                        ->required(),

                    Textarea::make('keterangan')
                        ->columnSpanFull(),

                ])
                ->columns(2),

            Section::make('Pilih Subcount Out')
                ->schema([

                    Select::make('subcount_out_id')
                        ->label('Surat Jalan Subcount Out')
                        ->options(function () {
                            return SubcountOut::with('items')->get()->mapWithKeys(function ($out) {
                                $sisa = app(\App\Services\Subcontract\SubcontractService::class)->calculateOutstandingQty($out->id);
                                $label = $out->surat_jalan_subcount . ' (Sisa Belum Diterima: ' . number_format($sisa, 0, ',', '.') . ')';
                                return [$out->id => $label];
                            });
                        })
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            $subcountOut = SubcountOut::with(['items.item', 'items.spk.salesOrder.itemable'])->find($state);

                            if (!$subcountOut) {
                                return;
                            }

                            $service = app(\App\Services\Subcontract\SubcontractService::class);
                            $items = [];

                            foreach ($subcountOut->items as $item) {
                                $sisa = $service->calculateOutstandingQty(
                                    $subcountOut->id,
                                    $item->item_id ? (int) $item->item_id : null,
                                    $item->spk_id ? (int) $item->spk_id : null
                                );
                                if ($sisa <= 0) {
                                    continue;
                                }

                                $isSpk = $item->sumber_item === 'spk' || !empty($item->spk_id);
                                $fgId = $isSpk ? ($item->spk?->salesOrder?->itemable_id ?? null) : null;

                                $items[] = [
                                    'subcount_out_id'     => $subcountOut->id,
                                    'spk_id'              => $item->spk_id,
                                    'item_asal_id'        => $item->item_id,
                                    'nama_item_asal'      => $item->item?->nama_master_item ?? $item->nama_produk ?? ($item->spk?->no_spk ?? '-'),
                                    'qty_dikirim'         => $item->qty,
                                    'qty_diterima'        => $sisa,
                                    'tipe_hasil'          => $isSpk ? 'finish_good' : 'material',
                                    'item_hasil_id'       => null,
                                    'finish_good_item_id' => $fgId,
                                    'keterangan'          => null,
                                ];
                            }

                            $set('items', $items);
                        })

                ]),

            Section::make('Item Subcount In')
                ->schema([

                    Repeater::make('items')
                        ->schema([

                            Hidden::make('subcount_out_id'),

                            Hidden::make('spk_id'),

                            Hidden::make('item_asal_id'),

                            TextInput::make('nama_item_asal')
                                ->label('Item / SPK Asal')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('qty_dikirim')
                                ->quantityMask(0)
                                ->disabled()
                                ->dehydrated(),

                            Radio::make('tipe_hasil')
                                ->label('Tipe Barang Diterima')
                                ->options([
                                    'material'    => 'Material Produksi (Master Item)',
                                    'finish_good' => 'Barang Jadi (Finish Good)',
                                ])
                                ->default('material')
                                ->inline()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if ($state === 'material') {
                                        $set('finish_good_item_id', null);
                                    } else {
                                        $set('item_hasil_id', null);
                                    }
                                }),

                            Select::make('item_hasil_id')
                                ->label('Material Hasil (Master Item)')
                                ->options(
                                    fn () => MasterItem::pluck('nama_master_item', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get) => ($get('tipe_hasil') ?: 'material') === 'material')
                                ->required(fn (Get $get) => ($get('tipe_hasil') ?: 'material') === 'material'),

                            Select::make('finish_good_item_id')
                                ->label('Barang Jadi (Finish Good)')
                                ->options(
                                    fn () => FinishGoodItem::pluck('nama_barang', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get) => $get('tipe_hasil') === 'finish_good')
                                ->required(fn (Get $get) => $get('tipe_hasil') === 'finish_good')
                                ->helperText('Otomatis menambah stok di Finish Good Stocks'),

                            TextInput::make('qty_diterima')
                                ->quantityMask(0)
                                ->required(),

                            TextInput::make('keterangan'),

                        ])
                        ->columns(3)

                ])

        ]);
}
    public static function getNavigationLabel(): string
{
    return 'Subcount In';
}

    public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')

        ->columns([

            Tables\Columns\TextColumn::make('id')
                ->label('No')
                ->sortable(),

            Tables\Columns\TextColumn::make('no_subcount')
                ->label('No Subcount')
                ->searchable()
                ->copyable()
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal_subcount')
                ->label('Tanggal')
                ->date('d-m-Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('surat_jalan_pengiriman')
                ->label('Surat Jalan')
                ->searchable(),

            Tables\Columns\TextColumn::make('supervisor')
                ->searchable(),

            Tables\Columns\TextColumn::make('items_count')
                ->label('Jumlah Item/SPK')
                ->counts('items')
                ->badge()
                ->color('info'),

            Tables\Columns\TextColumn::make('total_qty')
                ->label('Total Qty Diterima')
                ->getStateUsing(
                    fn ($record) =>
                        $record->items->sum('qty_diterima')
                )
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->since(),

        ])

        ->filters([

        ])

        ->actions([

           Tables\Actions\ActionGroup::make([

    Tables\Actions\ViewAction::make(),

    Tables\Actions\EditAction::make(),

    Tables\Actions\Action::make('preview_pdf')
        ->label('Preview PDF')
        ->icon('heroicon-o-eye')
        ->url(fn ($record) => route(
            'subcount-in.preview',
            $record
        ))
        ->openUrlInNewTab(),

    Tables\Actions\Action::make('download_pdf')
        ->label('Download PDF')
        ->icon('heroicon-o-arrow-down-tray')
        ->url(fn ($record) => route(
            'subcount-in.download',
            $record
        )),

    Tables\Actions\DeleteAction::make()
        ->using(fn ($record) => app(\App\Services\Subcontract\SubcontractService::class)->deleteSubcountIn($record)),

])
->icon('heroicon-m-ellipsis-vertical')

        ])

        ->bulkActions([

            Tables\Actions\BulkActionGroup::make([

                Tables\Actions\DeleteBulkAction::make(),

            ])

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
            'index' => Pages\ListSubcountIns::route('/'),
            'create' => Pages\CreateSubcountIn::route('/create'),
            'edit' => Pages\EditSubcountIn::route('/{record}/edit'),
        ];
    }
}