<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountInResource\Pages;
use App\Models\MasterItem;
use App\Models\SubcountIn;
use App\Models\SubcountOut;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
                        ->options(
                            SubcountOut::pluck(
                                'surat_jalan_subcount',
                                'id'
                            )
                        )
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {

                            $subcountOut = SubcountOut::with('items.item')
                                ->find($state);

                            if (!$subcountOut) {
                                return;
                            }

                            $items = [];

                            foreach ($subcountOut->items as $item) {

                                $items[] = [

                                    'subcount_out_id' => $subcountOut->id,

                                    'item_asal_id' => $item->item_id,

                                    'nama_item_asal' =>
                                        $item->item?->nama_master_item,

                                    'qty_dikirim' => $item->qty,

                                    'qty_diterima' => $item->qty,

                                    'item_hasil_id' => null,

                                    'keterangan' => null,
                                ];
                            }

                            $set('items', $items);
                        })

                ]),

            Section::make('Item Subcount In')
                ->schema([

                    Repeater::make('items')
                        ->relationship()
                        ->schema([

                            Hidden::make('subcount_out_id'),

                            Hidden::make('item_asal_id'),

                            TextInput::make('nama_item_asal')
                                ->label('Material Asal')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('qty_dikirim')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(),

                            Select::make('item_hasil_id')
                                ->label('Material Hasil')
                                ->options(
                                    MasterItem::pluck(
                                        'nama_master_item',
                                        'id'
                                    )
                                )
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('qty_diterima')
                                ->numeric()
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
            'index' => Pages\ListSubcountIns::route('/'),
            'create' => Pages\CreateSubcountIn::route('/create'),
            'edit' => Pages\EditSubcountIn::route('/{record}/edit'),
        ];
    }
}