<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountOutResource\Pages;
use App\Models\Karyawan;
use App\Models\MasterItem;
use App\Models\MasterSatuan;
use App\Models\MasterSpk;
use App\Models\MasterSupplier;
use App\Models\SubcountOut;
use App\Services\Subcontract\SubcontractService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubcountOutResource extends Resource
{
    protected static ?string $model = SubcountOut::class;

    protected static ?string $navigationGroup = 'Subcount';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static function vendorOptions(): array
    {
        return MasterSupplier::query()
            ->orderByRaw("CASE WHEN LOWER(COALESCE(jenis_supplier, '')) LIKE '%subcount%' OR LOWER(COALESCE(jenis_supplier, '')) LIKE '%maklo%' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (MasterSupplier $supplier): array {
                $label = $supplier->nama_supplier;
                if (filled($supplier->jenis_supplier)) {
                    $label .= ' — ' . $supplier->jenis_supplier;
                }

                return [$supplier->id => $label];
            })
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengiriman Subcount')
                    ->description('Pilih vendor maklon/subcount, jenis proses pengerjaan, dan jadwal pengiriman.')
                    ->icon('heroicon-o-truck')
                    ->iconColor('primary')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #3b82f6 !important;',
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('surat_jalan_subcount')
                            ->label('No. Surat Jalan Subcount')
                            ->disabled()
                            ->placeholder('Terisi otomatis setelah disimpan')
                            ->dehydrated(false)
                            ->hiddenOn('create'),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Vendor Subcount / Maklon')
                            ->options(fn () => static::vendorOptions())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih vendor subcount / maklon'),

                        Forms\Components\Select::make('jenis_proses')
                            ->label('Jenis Proses Pengerjaan')
                            ->options(SubcountOut::JENIS_PROSES_OPTIONS)
                            ->default('FULL_MAKLON')
                            ->searchable()
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_subcount')
                            ->label('Tanggal Kirim')
                            ->default(now())
                            ->required()
                            ->live(),

                        Forms\Components\DatePicker::make('tanggal_target_selesai')
                            ->label('Target Selesai / Kembali')
                            ->minDate(fn (Get $get) => $get('tanggal_subcount'))
                            ->placeholder('Estimasi kembali dari vendor'),

                        Forms\Components\TextInput::make('admin_produksi')
                            ->label('PIC / Penanggung Jawab')
                            ->default(fn () => auth()->user()?->name)
                            ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                            ->placeholder('Nama PIC pengiriman')
                            ->required(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan / Instruksi Pengerjaan')
                            ->rows(2)
                            ->placeholder('Catatan spesifikasi proses untuk vendor (opsional)...')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Daftar Item Dikirim ke Vendor')
                    ->description('Pilih sumber item dari SPK Subcount/Maklon atau Material Produksi.')
                    ->icon('heroicon-o-cube')
                    ->iconColor('warning')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #f59e0b !important;',
                    ])
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Item Subcount Out')
                            ->addActionLabel('+ Tambah Item Subcount')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->schema([
                                Forms\Components\Radio::make('sumber_item')
                                    ->label('Sumber Item')
                                    ->options([
                                        'spk' => 'SPK (Subcount / Maklon)',
                                        'material' => 'Master Item (Material Produksi)',
                                    ])
                                    ->default('spk')
                                    ->inline()
                                    ->live()
                                    ->columnSpan(3)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('spk_id', null);
                                        $set('item_id', null);
                                        $set('nama_produk', null);
                                        $set('satuan_id', null);
                                        $set('qty', '0');
                                    })
                                    ->required(),

                                Forms\Components\Select::make('spk_id')
                                    ->label('No SPK (Subcount / Maklon)')
                                    ->placeholder('Pilih SPK bertipe Subcount / Maklon')
                                    ->options(function () {
                                        return MasterSpk::query()
                                            ->subcount()
                                            ->with(['salesOrder.customer', 'salesOrder.itemable'])
                                            ->orderByDesc('id')
                                            ->get()
                                            ->mapWithKeys(function (MasterSpk $spk): array {
                                                $cust = $spk->salesOrder?->customer?->nama_customer ?? '-';
                                                $prod = $spk->salesOrder?->itemable?->nama_barang
                                                    ?? ($spk->salesOrder?->product_name ?? '-');

                                                return [$spk->id => "{$spk->no_spk} — {$cust} · {$prod}"];
                                            })
                                            ->all();
                                    })
                                    ->helperText('Hanya menampilkan SPK dengan tipe pengerjaan SUBCOUNT / MAKLON.')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->visible(fn (Get $get) => ($get('sumber_item') ?: 'spk') === 'spk')
                                    ->required(fn (Get $get) => ($get('sumber_item') ?: 'spk') === 'spk')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            $set('nama_produk', null);
                                            $set('satuan_id', null);
                                            $set('qty', '0');

                                            return;
                                        }

                                        $spk = MasterSpk::with('salesOrder.itemable.satuan')->find($state);

                                        $set(
                                            'nama_produk',
                                            $spk?->salesOrder?->itemable?->nama_barang
                                                ?? $spk?->salesOrder?->product_name
                                        );
                                        $set('satuan_id', $spk?->salesOrder?->itemable?->satuan_id);

                                        $orderQty = (float) ($spk?->order_qty ?: ($spk?->salesOrder?->qty ?? 0));
                                        $set('qty', $orderQty > 0 ? (MoneyHelper::format($orderQty, 0) ?? '0') : '0');
                                    }),

                                Forms\Components\Select::make('item_id')
                                    ->label('Material Produksi')
                                    ->options(fn () => MasterItem::latest('id')->pluck('nama_master_item', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2)
                                    ->visible(fn (Get $get) => $get('sumber_item') === 'material')
                                    ->required(fn (Get $get) => $get('sumber_item') === 'material')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            $set('nama_produk', null);
                                            $set('satuan_id', null);

                                            return;
                                        }

                                        $item = MasterItem::with('satuan')->find($state);
                                        if (! $item) {
                                            return;
                                        }

                                        $set('nama_produk', $item->nama_master_item);
                                        $set('satuan_id', $item->satuan_id);
                                    }),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty Dikirim')
                                    ->default('0')
                                    ->placeholder('0')
                                    ->quantityMask(0)
                                    ->required(),

                                Forms\Components\Select::make('satuan_id')
                                    ->label('Satuan')
                                    ->options(fn () => MasterSatuan::latest('id')->pluck('nama_satuan', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('catatan')
                                    ->label('Catatan Item')
                                    ->placeholder('Spesifikasi / warna / ukuran (opsional)')
                                    ->columnSpan(2),

                                Forms\Components\Hidden::make('nama_produk')
                                    ->dehydrated(),
                            ])
                            ->columns(3),
                    ]),

                Forms\Components\Section::make('Penandatangan PDF (Opsional)')
                    ->description('Isi jika ingin mencantumkan nama Supervisor / Admin Mainstore di kolom tanda tangan surat jalan.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('supervisor')
                            ->label('Supervisor')
                            ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                            ->placeholder('Opsional untuk tanda tangan PDF'),

                        Forms\Components\TextInput::make('admin_mainstore')
                            ->label('Admin Mainstore')
                            ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                            ->placeholder('Opsional untuk tanda tangan PDF'),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('surat_jalan_subcount')
                    ->label('No. SJ Subcount')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tanggal_subcount')
                    ->label('Tgl Kirim')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_target_selesai')
                    ->label('Target Kembali')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable()
                    ->color(function (SubcountOut $record): ?string {
                        if (! $record->tanggal_target_selesai || $record->status_penerimaan === 'SELESAI') {
                            return null;
                        }

                        return $record->tanggal_target_selesai->isPast() ? 'danger' : null;
                    }),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Vendor Subcount')
                    ->weight('bold')
                    ->description(fn (SubcountOut $record): ?string => $record->jenis_proses ? $record->jenis_proses_label : null)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('progress_qty')
                    ->label('Diterima / Dikirim')
                    ->getStateUsing(function (SubcountOut $record): string {
                        return number_format($record->total_received_qty, 0, ',', '.')
                            . ' / '
                            . number_format($record->total_sent_qty, 0, ',', '.');
                    })
                    ->alignRight()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('status_penerimaan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SELESAI' => 'SELESAI',
                        'PARTIAL' => 'PARTIAL',
                        default => 'DI VENDOR',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'SELESAI' => 'success',
                        'PARTIAL' => 'info',
                        default => 'warning',
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('admin_produksi')
                    ->label('PIC')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Vendor Subcount')
                    ->options(fn () => static::vendorOptions())
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('jenis_proses')
                    ->label('Jenis Proses')
                    ->options(SubcountOut::JENIS_PROSES_OPTIONS),

                Tables\Filters\SelectFilter::make('status_penerimaan')
                    ->label('Status Penerimaan')
                    ->options([
                        'BELUM_SELESAI' => 'Belum Selesai (Di Vendor / Partial)',
                        'DI_VENDOR' => 'Di Vendor (Belum Kembali)',
                        'PARTIAL' => 'Partial (Kembali Sebagian)',
                        'SELESAI' => 'Selesai (Sudah Kembali Semua)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'] ?? null;
                        if (blank($status)) {
                            return $query;
                        }

                        $sentExpr = 'COALESCE((SELECT SUM(qty) FROM subcount_out_items WHERE subcount_out_items.subcount_out_id = subcount_outs.id), 0)';
                        $recvExpr = 'COALESCE((SELECT SUM(qty_diterima) FROM subcount_in_items WHERE subcount_in_items.subcount_out_id = subcount_outs.id), 0)';

                        return match ($status) {
                            'SELESAI' => $query->whereRaw("{$sentExpr} > 0 AND {$recvExpr} >= {$sentExpr}"),
                            'PARTIAL' => $query->whereRaw("{$recvExpr} > 0 AND {$recvExpr} < {$sentExpr}"),
                            'DI_VENDOR' => $query->whereRaw("{$recvExpr} <= 0"),
                            'BELUM_SELESAI' => $query->whereRaw("{$recvExpr} < {$sentExpr}"),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('preview')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => route('subcount-out.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('subcount-out.download', $record)),
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => ! app(SubcontractService::class)->canDeleteSubcountOut($record))
                        ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountOut($record)),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplier', 'items', 'subcountInItems']);
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
