<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountOutResource\Pages;
use App\Models\SubcountOut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class SubcountOutResource extends Resource
{
    protected static ?string $model = SubcountOut::class;
     protected static ?string $navigationGroup = 'Subcount';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Section::make('Informasi Subcount')
                ->schema([

                   Forms\Components\TextInput::make('surat_jalan_subcount')
    ->label('No. Surat Jalan Subcount')
    ->disabled()
    ->placeholder('Terisi otomatis setelah disimpan')
    ->dehydrated(false),

                    Forms\Components\DatePicker::make('tanggal_subcount')
                        ->required(),

                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier','nama_supplier')
                        ->required(),

                    Forms\Components\TextInput::make('admin_produksi')
                        ->required(),

                    Forms\Components\TextInput::make('supervisor')
                        ->required(),

                    Forms\Components\TextInput::make('admin_mainstore')
                        ->required(),

                    Forms\Components\Textarea::make('keterangan')
                        ->columnSpanFull(),

                ])
                ->columns(3),

            Forms\Components\Section::make('Form Items Subcount Out')
                ->schema([

                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([

                          Forms\Components\Radio::make('sumber_item')
                            ->options([
                                'spk' => 'SPK',
                                'material' => 'Master Item (Material Production)',
                            ])
                            ->inline()
                            ->live()
                            ->required(),

                                                Forms\Components\Select::make('spk_id')
                            ->label('No SPK')
                            ->relationship('spk', 'no_spk')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('sumber_item') === 'spk')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {

                                $spk = \App\Models\MasterSpk::with(
                                    'salesOrder.itemable'
                                )->find($state);

                                $set(
                                    'nama_produk',
                                    $spk?->salesOrder?->itemable?->nama_barang
                                );
                            }),

                          Forms\Components\Select::make('item_id')
                                ->label('Material')
                                ->relationship('item', 'nama_master_item')
                                ->searchable()
                                ->preload()
                                ->visible(fn ($get) => $get('sumber_item') === 'material')
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {

                                    $item = \App\Models\MasterItem::with('satuan')
                                        ->find($state);

                                    if (!$item) {
                                        return;
                                    }

                                    $set('nama_produk', $item->nama_master_item);

                                    $set('satuan_id', $item->satuan_id);
                                }),

                            Forms\Components\TextInput::make('qty')
                                ->numeric()
                                ->required(),

                            Forms\Components\Select::make('satuan_id')
                                ->relationship('satuan','nama_satuan')
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('nama_produk')
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('catatan'),

                        ])
                        ->columns(3)

                ])

        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Subcount Out';
}

    public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')

        ->columns([

            Tables\Columns\TextColumn::make('id')
                ->label('No')
                ->sortable(),
            
                 Tables\Columns\TextColumn::make('surat_jalan_subcount')
        ->label('No. SJ Subcount')
        ->searchable()
        ->sortable()
        ->copyable(),


            Tables\Columns\TextColumn::make('tanggal_subcount')
                ->label('Tanggal')
                ->date('d-m-Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('supplier.nama_supplier')
                ->label('Supplier')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('admin_produksi')
                ->label('Admin Produksi')
                ->searchable(),

            Tables\Columns\TextColumn::make('supervisor')
                ->searchable(),

            Tables\Columns\TextColumn::make('admin_mainstore')
                ->label('Admin Mainstore')
                ->searchable(),

            Tables\Columns\TextColumn::make('items_count')
                ->label('Jumlah Item')
                ->counts('items')
                ->badge()
                ->color('info'),

            Tables\Columns\TextColumn::make('total_qty')
                ->label('Total Qty')
                ->getStateUsing(fn ($record) => $record->items->sum('qty'))
                ->badge()
                ->color('success'),


            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->since(),

        ])

        ->actions([

            Tables\Actions\ActionGroup::make([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),

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
            'index' => Pages\ListSubcountOuts::route('/'),
            'create' => Pages\CreateSubcountOut::route('/create'),
            'edit' => Pages\EditSubcountOut::route('/{record}/edit'),
        ];
    }
}