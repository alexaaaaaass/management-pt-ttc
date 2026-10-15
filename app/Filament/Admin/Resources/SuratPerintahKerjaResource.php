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
use Filament\Forms\Components\Wizard;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;


class SuratPerintahKerjaResource extends Resource
{
    protected static ?string $model = MasterSpk::class;
    protected static ?string $navigationGroup = 'Ppic';
    protected static ?string $navigationLabel = 'Master Spk';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Wizard::make([

                // ==========================================
                // 🔥 TAHAP 1: REFERENSI SALES ORDER
                // ==========================================
                Wizard\Step::make('Referensi Sales Order')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->description('Pilih Sales Order acuan dan tinjau ringkasan pesanan')
                    ->schema([
                        Forms\Components\Select::make('sales_order_id')
                            ->extraAttributes(['class' => 'z-50'])
                            ->label('No Sales Order')
                            ->relationship(
                                name: 'salesOrder',
                                titleAttribute: 'no_sales_order',
                                modifyQueryUsing: fn (Builder $query) => $query->where('order_type', 'SPK')
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->optionsLimit(20)
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $so = SalesOrder::with(['customer', 'itemable'])->find($state);

                                if (!$so) return;

                                // 🔹 data SO
                                $set('tanggal_po', $so->tanggal_po);
                                $set('qty', $so->qty);
                                $set('customer_name', $so->customer->nama_customer ?? '-');
                                $set('no_po_customer', $so->no_po_customer);
                                $set('tipe_pesanan', $so->tipe_pesanan);

                                // 🔥 data Finish Good
                                $fg = $so->itemable;

                                if ($fg instanceof \App\Models\FinishGoodItem) {
                                    $set('up_satu', $fg->up_satu);
                                    $set('up_dua', $fg->up_dua);
                                    $set('up_tiga', $fg->up_tiga);
                                    $set('ukuran_potong', $fg->ukuran_potong);
                                    $set('ukuran_cetak', $fg->ukuran_cetak);
                                    $set('spesifikasi_kertas', $fg->spesifikasi_kertas);
                                }
                            }),

                        // Auto fields hidden
                        Forms\Components\Hidden::make('customer_name'),
                        Forms\Components\Hidden::make('no_po_customer'),
                        Forms\Components\Hidden::make('qty'),
                        Forms\Components\Hidden::make('tipe_pesanan'),

                        // Detail Sales Order Card
                        Forms\Components\Section::make('Ringkasan Detail Sales Order')
                            ->extraAttributes([
                                'style' => 'border-left: 5px solid #3b82f6; border-radius: 10px; padding: 12px;',
                            ])
                            ->visible(fn (Get $get) => filled($get('sales_order_id')))
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
                                            ->content(function (Get $get) {
                                                $so = SalesOrder::with('itemable')->find($get('sales_order_id'));
                                                if (!$so || !$so->itemable) return '-';
                                                return $so->itemable->nama_barang
                                                    ?? $so->itemable->nama_master_item
                                                    ?? '-';
                                            }),

                                        Forms\Components\Placeholder::make('deskripsi')
                                            ->label('Deskripsi')
                                            ->content(function (Get $get) {
                                                $so = SalesOrder::with('itemable')->find($get('sales_order_id'));
                                                return $so?->itemable?->deskripsi ?? '-';
                                            }),
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
                                                if (!$so || is_null($so->toleransi_pengiriman)) return '-';
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
                            ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 2: RENCANA & PARAMETER SPK
                // ==========================================
                Wizard\Step::make('Rencana & Parameter SPK')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->description('Nomor SPK, instruksi kerja, target tanggal, dan status')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('no_spk')
                                ->label('No SPK')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Otomatis digenerate sistem'),

                            Forms\Components\TextInput::make('kode_ik')
                                ->label('Kode Instruksi Kerja (IK)')
                                ->placeholder('Contoh: IK-001/PRT')
                                ->required(),

                            Forms\Components\DatePicker::make('tanggal_po')
                                ->label('Tanggal PO / Referensi')
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('production_plan')
                                ->label('Rencana Produksi (Plan)')
                                ->placeholder('Target kuantiti produksi atau batch'),

                            Forms\Components\DatePicker::make('tanggal_estimasi_selesai')
                                ->label('Estimasi Tanggal Selesai'),

                            Forms\Components\Select::make('status')
                                ->label('Status SPK')
                                ->options([
                                    'ON PROCESS' => 'ON PROCESS',
                                    'FINISH' => 'FINISH',
                                ])
                                ->default('ON PROCESS')
                                ->required(),
                        ]),
                    ]),

                // ==========================================
                // 🔥 TAHAP 3: SPESIFIKASI TEKNIS CETAK
                // ==========================================
                Wizard\Step::make('Spesifikasi Teknis Cetak')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->description('Ukuran potong (UP), dimensi cetak, dan spesifikasi kertas')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('up_satu')
                                ->label('UP 1')
                                ->numeric()
                                ->placeholder('Contoh: 4'),

                            Forms\Components\TextInput::make('up_dua')
                                ->label('UP 2')
                                ->numeric(),

                            Forms\Components\TextInput::make('up_tiga')
                                ->label('UP 3')
                                ->numeric(),

                            Forms\Components\TextInput::make('ukuran_potong')
                                ->label('Ukuran Potong')
                                ->placeholder('Contoh: 650 x 1000 mm'),

                            Forms\Components\TextInput::make('ukuran_cetak')
                                ->label('Ukuran Cetak')
                                ->placeholder('Contoh: 640 x 980 mm'),

                            Forms\Components\Textarea::make('spesifikasi_kertas')
                                ->label('Spesifikasi Kertas / Material')
                                ->placeholder('Contoh: Ivory 250 GSM / Folio')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
}

public static function getNavigationLabel(): string
{
    return 'Master Spk';
}

   public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            Tables\Columns\TextColumn::make('no_spk')
                ->label('No SPK')
                ->searchable()
                ->sortable()
                ->copyable()
                ->weight('bold'),

            Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                ->label('No Sales Order')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('salesOrder.customer.nama_customer')
                ->label('Customer')
                ->searchable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Nama Produk')
                ->getStateUsing(function ($record) {
                    return $record->salesOrder?->itemable?->nama_barang
                        ?? $record->salesOrder?->itemable?->nama_master_item
                        ?? '-';
                })
                ->searchable(),

            Tables\Columns\TextColumn::make('order_qty')
                ->label('Qty Order')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('total_good_packed')
                ->label('Packing Baik')
                ->numeric()
                ->badge()
                ->color('info')
                ->sortable(),

            Tables\Columns\TextColumn::make('total_shipped')
                ->label('Terkirim')
                ->numeric()
                ->badge()
                ->color('gray')
                ->sortable(),

            Tables\Columns\TextColumn::make('on_hand_stock')
                ->label('Siap Kirim')
                ->numeric()
                ->badge()
                ->color(fn ($state) => $state <= 0 ? 'danger' : 'success')
                ->sortable(),

            Tables\Columns\TextColumn::make('completion_rate')
                ->label('Progres')
                ->formatStateUsing(fn ($state) => $state . '%')
                ->badge()
                ->color(fn ($state) => $state >= 100 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'ON PROCESS',
                    'success' => 'FINISH',
                ]),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'ON PROCESS' => 'ON PROCESS',
                    'FINISH'     => 'FINISH',
                ]),
        ])
        ->actions([
            ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status !== 'FINISH'),

                Tables\Actions\Action::make('finish')
                    ->label('Selesaikan SPK (FINISH)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penyelesaian SPK')
                    ->modalDescription('Apakah Anda yakin ingin menandai SPK ini telah selesai produksi?')
                    ->action(function ($record) {
                        try {
                            app(\App\Services\Production\ProductionService::class)->completeSpk($record);
                            \Filament\Notifications\Notification::make()
                                ->title('SPK Berhasil Diselesaikan')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menyelesaikan SPK')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status !== 'FINISH'),

                Tables\Actions\Action::make('reopen')
                    ->label('Buka Kembali SPK')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Buka Kembali SPK')
                    ->modalDescription('Kembalikan status SPK menjadi ON PROCESS untuk pengerjaan lanjutan?')
                    ->action(function ($record) {
                        app(\App\Services\Production\ProductionService::class)->reopenSpk($record);
                        \Filament\Notifications\Notification::make()
                            ->title('Status SPK dikembalikan ke ON PROCESS')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'FINISH'),

                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(\App\Services\Production\ProductionService::class)->deleteSpk($record)),
            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->tooltip('Aksi SPK'),
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
            'index'  => Pages\ListSuratPerintahKerjas::route('/'),
            'create' => Pages\CreateSuratPerintahKerja::route('/create'),
            'view'   => Pages\ViewSuratPerintahKerja::route('/{record}'),
            'edit'   => Pages\EditSuratPerintahKerja::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'packagings',
                'suratJalans',
                'salesOrder.itemable',
                'salesOrder.customer',
                'pracetaks',
                'printings',
                'finishings',
            ]);
    }
}