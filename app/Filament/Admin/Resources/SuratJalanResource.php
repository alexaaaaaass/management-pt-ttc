<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratJalanResource\Pages;
use App\Models\SuratJalan;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;

class SuratJalanResource extends Resource
{
    protected static ?string $model = SuratJalan::class;

    protected static ?string $navigationGroup = 'Dispatch';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Surat Jalan';
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Tambah Surat Jalan')
                    ->schema([

                        Forms\Components\Select::make('sales_order_id')
                            ->label('Pilih Sales Order')
                            ->relationship('salesOrder', 'no_sales_order')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                           ->afterStateUpdated(function ($state, Set $set) {

                            $so = SalesOrder::with('customer')->find($state);

                            if (!$so) {
                                return;
                            }

                            // alamat customer
                            $set(
                                'alamat_tujuan',
                                $so->customer->alamat ?? '-'
                            );

                            // 🔥 AUTO PILIH SPK
                            $spk = \App\Models\MasterSpk::where(
                                'sales_order_id',
                                $so->id
                            )->first();

                            if ($spk) {
                                $set('spk_id', $spk->id);
                            }
                        }),

                        Forms\Components\Select::make('spk_id')
                            ->label('Pilih SPK (Opsional)')
                            ->relationship('spk', 'no_spk')
                            ->searchable()
                            ->preload(),

                            Forms\Components\Section::make('Informasi Stock')
    ->schema([

        Forms\Components\Placeholder::make('stock_produk')
            ->label('On Hand Stock')
            ->content(function (Get $get) {

                $spk = \App\Models\MasterSpk::find(
                    $get('spk_id')
                );

                if (!$spk) {
                    return '-';
                }

                return number_format(
                    $spk->on_hand_stock
                );
            }),

        Forms\Components\Placeholder::make('nama_produk')
            ->label('Nama Produk')
            ->content(function (Get $get) {

                $spk = \App\Models\MasterSpk::with(
                    'salesOrder.itemable'
                )->find($get('spk_id'));

                return
                    $spk?->salesOrder?->itemable?->nama_barang
                    ?? '-';
            }),

        Forms\Components\Placeholder::make('qty_order')
            ->label('Qty Order')
            ->content(function (Get $get) {

                $spk = \App\Models\MasterSpk::with(
                    'salesOrder'
                )->find($get('spk_id'));

                return number_format(
                    $spk?->salesOrder?->qty ?? 0
                );
            }),

    ])
    ->columns(3)
    ->visible(fn (Get $get) => filled($get('spk_id'))),

                        Forms\Components\TextInput::make('qty_pengiriman')
    ->numeric()
    ->required()
    ->rule(function (Get $get) {

        return function (
            string $attribute,
            $value,
            \Closure $fail
        ) use ($get) {

            $spk = \App\Models\MasterSpk::find(
                $get('spk_id')
            );

            if (!$spk) {
                return;
            }

            if ($value > $spk->on_hand_stock) {

                $fail(
                    'Qty pengiriman melebihi stock tersedia.'
                );
            }
        };
    }),
    Forms\Components\Placeholder::make('sisa_stock')
    ->label('Sisa Stock Setelah Kirim')
    ->content(function (Get $get) {

        $spk = \App\Models\MasterSpk::find(
            $get('spk_id')
        );

        if (!$spk) {
            return '-';
        }

        $qtyKirim = (int) $get('qty_pengiriman');

        return number_format(
            $spk->on_hand_stock - $qtyKirim
        );
    }),

                        Forms\Components\DatePicker::make('tanggal_surat_jalan')
                            ->default(now())
                            ->required(),

                        Forms\Components\Textarea::make('alamat_tujuan')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('transportasi')
                            ->required(),

                        Forms\Components\TextInput::make('no_polisi')
                            ->required(),

                        Forms\Components\TextInput::make('driver')
                            ->required(),

                        Forms\Components\TextInput::make('pengirim')
                            ->required(),

                        Forms\Components\Textarea::make('keterangan')
                            ->default('-')
                            ->columnSpanFull(),

                    ])
                    ->columns(2),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('kode_surat_jalan')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                    ->label('No SO')
                    ->searchable(),

                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Produk')
                    ->getStateUsing(fn ($record) =>

                        $record->salesOrder?->itemable?->nama_barang
                        ?? '-'
                    ),

                Tables\Columns\TextColumn::make('qty_pengiriman')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('transportasi'),

                Tables\Columns\TextColumn::make('driver'),

                Tables\Columns\TextColumn::make('tanggal_surat_jalan')
                    ->date(),
            ])

            ->actions([

                Tables\Actions\ActionGroup::make([

                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\EditAction::make(),

                    Tables\Actions\DeleteAction::make(),

                ])

            ])

            ->bulkActions([

                Tables\Actions\DeleteBulkAction::make(),

            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [

            'index' => Pages\ListSuratJalans::route('/'),

            'create' => Pages\CreateSuratJalan::route('/create'),

            'edit' => Pages\EditSuratJalan::route('/{record}/edit'),

            'view' => Pages\ViewSuratJalan::route('/{record}'),
        ];
    }
}