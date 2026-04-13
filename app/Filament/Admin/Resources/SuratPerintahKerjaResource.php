<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;
use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Table;
use App\Models\SalesOrder;



class SuratPerintahKerjaResource extends Resource
{
    protected static ?string $model = MasterSpk::class;
    protected static ?string $navigationGroup = 'Ppic';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\TextInput::make('no_spk')
                ->label('No SPK')
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('kode_ik')
                ->required(),

            // 🔥 SELECT SALES ORDER
            Forms\Components\Select::make('sales_order_id')
                ->label('No Sales Order')
                ->options(SalesOrder::pluck('no_sales_order', 'id'))
                ->reactive()
                ->afterStateUpdated(function ($state, Set $set) {

                    $so = SalesOrder::with('customer')->find($state);

                    if (!$so) return;

                    // 🔥 AUTO ISI FIELD
                    $set('tanggal_po', $so->tanggal_po);
                    $set('qty', $so->qty);
                    $set('customer_name', $so->customer->nama_customer ?? '-');
                    $set('no_po_customer', $so->no_po_customer);
                    $set('tipe_pesanan', $so->tipe_pesanan);
                }),

            // 🔥 AUTO FIELD (HIDDEN / DISPLAY ONLY)
            Forms\Components\Hidden::make('customer_name'),
            Forms\Components\Hidden::make('no_po_customer'),
            Forms\Components\Hidden::make('qty'),
            Forms\Components\Hidden::make('tipe_pesanan'),

        Forms\Components\DatePicker::make('tanggal_po')
    ->disabled()
    ->dehydrated(), // 🔥 INI KUNCINYA

            Forms\Components\TextInput::make('production_plan'),

            Forms\Components\DatePicker::make('tanggal_estimasi_selesai'),

            // ===============================
            // 🔥 DETAIL SALES ORDER (KAYAK UI KAMU)
            // ===============================
            Forms\Components\Section::make('Detail Sales Order')
                ->schema([

                    Forms\Components\Grid::make(2)->schema([

                        // 🔹 KIRI
                        Forms\Components\Grid::make(1)->schema([

                            Forms\Components\Placeholder::make('so_number')
                                ->label('No Sales Order')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->no_sales_order ?? '-'
                                ),

                            Forms\Components\Placeholder::make('po_customer')
                                ->label('No PO Customer')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->no_po_customer ?? '-'
                                ),

                            Forms\Components\Placeholder::make('customer')
                                ->label('Customer')
                                ->content(function (Get $get) {
                                    $so = SalesOrder::with('customer')->find($get('sales_order_id'));
                                    return $so?->customer?->nama_customer ?? '-';
                                }),

                            Forms\Components\Placeholder::make('nama_barang')
                                ->label('Nama Barang')
                                ->content(fn () => '-'),

                            Forms\Components\Placeholder::make('deskripsi')
                                ->label('Deskripsi')
                                ->content(fn () => '-'),
                        ]),

                        // 🔹 KANAN
                        Forms\Components\Grid::make(1)->schema([

                            Forms\Components\Placeholder::make('qty_display')
                                ->label('Jumlah Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->qty ?? '-'
                                ),

                           Forms\Components\Placeholder::make('toleransi')
    ->label('Toleransi')
    ->content(function (Get $get) {
        $so = SalesOrder::find($get('sales_order_id'));

        if (!$so || is_null($so->toleransi_pengiriman)) {
            return '-';
        }
        return $so->toleransi_pengiriman . ' %';
    }),
                            Forms\Components\Placeholder::make('tipe')
                                ->label('Tipe Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->tipe_pesanan ?? '-'
                                ),
                            Forms\Components\Placeholder::make('tanggal')
                                ->label('Tanggal Pesanan')
                                ->content(fn (Get $get) =>
                                    optional(SalesOrder::find($get('sales_order_id')))->tanggal_po ?? '-'
                             ),
                        ]),
                    ]),
                ])
                ->visible(fn (Get $get) => filled($get('sales_order_id'))),
        ]);
}

public static function getNavigationLabel(): string
{
    return 'Master Spk';
}

   public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('no_spk')->searchable(),

            Tables\Columns\TextColumn::make('kode_ik'),

            Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                ->label('No Sales Order'),

            Tables\Columns\TextColumn::make('production_plan'),

            Tables\Columns\TextColumn::make('tanggal_estimasi_selesai')
                ->date(),

            Tables\Columns\TextColumn::make('tanggal_po')
                ->date(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSuratPerintahKerjas::route('/'),
            'create' => Pages\CreateSuratPerintahKerja::route('/create'),
            'edit' => Pages\EditSuratPerintahKerja::route('/{record}/edit'),
        ];
    }
}