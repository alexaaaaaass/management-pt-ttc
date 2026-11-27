<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubcountInResource\Pages;
use App\Models\FinishGoodItem;
use App\Models\Karyawan;
use App\Models\MasterItem;
use App\Models\MasterSupplier;
use App\Models\SubcountIn;
use App\Models\SubcountOut;
use App\Services\Subcontract\SubcontractService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Database\Eloquent\Builder;

class SubcountInResource extends Resource
{
    protected static ?string $model = SubcountIn::class;

    protected static ?string $navigationGroup = 'Subcount';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Section::make('Informasi Penerimaan Subcount')
                            ->description('Pilih surat jalan Subcount Out yang diterima kembali dari vendor, lalu lengkapi surat jalan vendor.')
                            ->icon('heroicon-o-inbox-arrow-down')
                            ->iconColor('primary')
                            ->extraAttributes([
                                'style' => 'border-left: 5px solid #3b82f6 !important;',
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => fn (Get $get): int => filled($get('subcount_out_id')) ? 2 : 3,
                            ])
                            ->columns(2)
                            ->schema([
                                Select::make('subcount_out_id')
                                    ->label('Surat Jalan Subcount Out')
                                    ->placeholder('Pilih Subcount Out yang masih memiliki sisa...')
                                    ->columnSpanFull()
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->options(function (?SubcountIn $record): array {
                                        $currentOutId = $record?->resolved_subcount_out?->id;

                                        return SubcountOut::with(['supplier', 'items', 'subcountInItems'])
                                            ->latest('id')
                                            ->get()
                                            ->filter(function (SubcountOut $out) use ($currentOutId): bool {
                                                return $out->outstanding_qty > 0
                                                    || ($currentOutId && (int) $out->id === (int) $currentOutId);
                                            })
                                            ->mapWithKeys(function (SubcountOut $out): array {
                                                $vendor = $out->supplier?->nama_supplier ?? 'Tanpa Vendor';
                                                $proses = $out->jenis_proses ? " · {$out->jenis_proses_label}" : '';
                                                $sisa = number_format($out->outstanding_qty, 0, ',', '.');

                                                return [
                                                    $out->id => "{$out->surat_jalan_subcount} — {$vendor}{$proses} (Sisa: {$sisa})",
                                                ];
                                            })
                                            ->all();
                                    })
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            $set('supplier_id', null);
                                            $set('items', []);

                                            return;
                                        }

                                        $subcountOut = SubcountOut::with(['supplier', 'items.item', 'items.spk.salesOrder.itemable'])
                                            ->find($state);

                                        if (! $subcountOut) {
                                            return;
                                        }

                                        $set('supplier_id', $subcountOut->supplier_id);

                                        $service = app(SubcontractService::class);
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

                                            $isSpk = $item->sumber_item === 'spk' || ! empty($item->spk_id);
                                            $fgId = $isSpk ? ($item->spk?->salesOrder?->itemable_id ?? null) : null;

                                            $items[] = [
                                                'subcount_out_id' => $subcountOut->id,
                                                'spk_id' => $item->spk_id,
                                                'item_asal_id' => $item->item_id,
                                                'nama_item_asal' => $item->item?->nama_master_item ?? $item->nama_produk ?? ($item->spk?->no_spk ?? '-'),
                                                'qty_dikirim' => $item->qty,
                                                'qty_diterima' => $sisa,
                                                'tipe_hasil' => $isSpk ? 'finish_good' : 'material',
                                                'item_hasil_id' => null,
                                                'finish_good_item_id' => $fgId,
                                                'keterangan' => null,
                                            ];
                                        }

                                        $set('items', $items);
                                    }),

                                Hidden::make('supplier_id')
                                    ->dehydrated(),

                                TextInput::make('no_subcount')
                                    ->label('No. Subcount In')
                                    ->disabled()
                                    ->placeholder('Terisi otomatis setelah disimpan')
                                    ->hiddenOn('create'),

                                DatePicker::make('tanggal_subcount')
                                    ->label('Tanggal Terima')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('surat_jalan_pengiriman')
                                    ->label('No. Surat Jalan Vendor')
                                    ->placeholder('Nomor surat jalan pengiriman dari vendor')
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                TextInput::make('admin_produksi')
                                    ->label('PIC Penerima / Admin Produksi')
                                    ->default(fn () => auth()->user()?->name)
                                    ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                                    ->placeholder('Nama PIC penerima barang')
                                    ->required(),

                                Textarea::make('keterangan')
                                    ->label('Keterangan Penerimaan')
                                    ->rows(2)
                                    ->placeholder('Catatan kondisi barang atau penerimaan (opsional)...')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Ringkasan Subcount Out')
                            ->description('Detail vendor & sisa kiriman aktif.')
                            ->icon('heroicon-o-building-storefront')
                            ->iconColor('success')
                            ->extraAttributes([
                                'style' => 'border-left: 5px solid #10b981 !important;',
                            ])
                            ->columnSpan(['default' => 1, 'lg' => 1])
                            ->visible(fn (Get $get) => filled($get('subcount_out_id')))
                            ->schema([
                                Placeholder::make('summary_vendor')
                                    ->label('Vendor Subcount')
                                    ->extraAttributes(['class' => 'text-base font-semibold'])
                                    ->content(function (Get $get): string {
                                        $out = SubcountOut::with('supplier')->find($get('subcount_out_id'));

                                        return $out?->supplier?->nama_supplier ?? '-';
                                    }),

                                Placeholder::make('summary_proses')
                                    ->label('Jenis Proses')
                                    ->extraAttributes(['class' => 'text-sm font-medium'])
                                    ->content(function (Get $get): string {
                                        $out = SubcountOut::find($get('subcount_out_id'));

                                        return $out?->jenis_proses_label ?? '-';
                                    }),

                                Placeholder::make('summary_schedule')
                                    ->label('Tgl Kirim / Target Kembali')
                                    ->extraAttributes(['class' => 'text-sm tabular-nums'])
                                    ->content(function (Get $get): string {
                                        $out = SubcountOut::find($get('subcount_out_id'));
                                        if (! $out) {
                                            return '-';
                                        }

                                        $kirim = $out->tanggal_subcount?->format('d/m/Y') ?? '-';
                                        $target = $out->tanggal_target_selesai?->format('d/m/Y') ?? '-';

                                        return "{$kirim}  →  Target: {$target}";
                                    }),

                                Placeholder::make('summary_sent')
                                    ->label('Total Dikirim')
                                    ->extraAttributes(['class' => 'text-base font-bold tabular-nums'])
                                    ->content(function (Get $get): string {
                                        $out = SubcountOut::with('items')->find($get('subcount_out_id'));

                                        return number_format($out?->total_sent_qty ?? 0, 0, ',', '.');
                                    }),

                                Placeholder::make('summary_outstanding')
                                    ->label('Sisa Belum Diterima')
                                    ->extraAttributes([
                                        'class' => 'text-base font-bold tabular-nums',
                                        'style' => 'color: #10b981;',
                                    ])
                                    ->content(function (Get $get): string {
                                        $out = SubcountOut::with(['items', 'subcountInItems'])->find($get('subcount_out_id'));

                                        return number_format($out?->outstanding_qty ?? 0, 0, ',', '.');
                                    }),
                            ]),
                    ]),

                Section::make('Item Subcount In')
                    ->description('Verifikasi tipe barang hasil (Material / Finish Good) dan quantity yang diterima.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconColor('warning')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #f59e0b !important;',
                    ])
                    ->schema([
                        Repeater::make('items')
                            ->label('Daftar Item Diterima')
                            ->addActionLabel('+ Tambah Item Penerimaan')
                            ->schema([
                                Hidden::make('subcount_out_id'),
                                Hidden::make('spk_id'),
                                Hidden::make('item_asal_id'),

                                TextInput::make('nama_item_asal')
                                    ->label('Item / SPK Asal')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('qty_dikirim')
                                    ->label('Qty Dikirim')
                                    ->quantityMask(0)
                                    ->disabled()
                                    ->dehydrated(),

                                Radio::make('tipe_hasil')
                                    ->label('Tipe Barang Diterima')
                                    ->options([
                                        'material' => 'Material Produksi (Master Item)',
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
                                    ->options(fn () => MasterItem::latest('id')->pluck('nama_master_item', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => ($get('tipe_hasil') ?: 'material') === 'material')
                                    ->required(fn (Get $get) => ($get('tipe_hasil') ?: 'material') === 'material'),

                                Select::make('finish_good_item_id')
                                    ->label('Barang Jadi (Finish Good)')
                                    ->options(fn () => FinishGoodItem::latest('id')->pluck('nama_barang', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => $get('tipe_hasil') === 'finish_good')
                                    ->required(fn (Get $get) => $get('tipe_hasil') === 'finish_good')
                                    ->helperText('Otomatis menambah stok di Finish Good Stocks'),

                                TextInput::make('qty_diterima')
                                    ->label('Qty Diterima')
                                    ->quantityMask(0)
                                    ->required(),

                                TextInput::make('keterangan')
                                    ->label('Catatan Item')
                                    ->placeholder('Catatan hasil pengerjaan (opsional)'),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Penandatangan PDF (Opsional)')
                    ->description('Isi jika ingin mencantumkan nama Supervisor / Admin Mainstore di kolom tanda tangan bukti penerimaan.')
                    ->collapsed()
                    ->schema([
                        TextInput::make('supervisor')
                            ->label('Supervisor')
                            ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                            ->placeholder('Opsional untuk tanda tangan PDF'),

                        TextInput::make('admin_mainstore')
                            ->label('Admin Mainstore')
                            ->datalist(fn () => Karyawan::latest('id')->pluck('nama')->all())
                            ->placeholder('Opsional untuk tanda tangan PDF'),
                    ])
                    ->columns(2),
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
                Tables\Columns\TextColumn::make('no_subcount')
                    ->label('No. Subcount In')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_subcount')
                    ->label('Tgl Terima')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subcount_out_no')
                    ->label('No. SJ Subcount Out')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (SubcountIn $record): string => $record->resolved_subcount_out?->surat_jalan_subcount ?? '-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('subcountOut', fn (Builder $q) => $q->where('surat_jalan_subcount', 'like', "%{$search}%"))
                            ->orWhereHas('items.subcountOut', fn (Builder $q) => $q->where('surat_jalan_subcount', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('vendor_name')
                    ->label('Vendor Subcount')
                    ->weight('bold')
                    ->getStateUsing(fn (SubcountIn $record): string => $record->resolved_supplier?->nama_supplier ?? '-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('supplier', fn (Builder $q) => $q->where('nama_supplier', 'like', "%{$search}%"))
                            ->orWhereHas('subcountOut.supplier', fn (Builder $q) => $q->where('nama_supplier', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('surat_jalan_pengiriman')
                    ->label('No. SJ Vendor')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Diterima')
                    ->getStateUsing(fn (SubcountIn $record): string => number_format((float) $record->items->sum('qty_diterima'), 0, ',', '.'))
                    ->badge()
                    ->color('success')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('admin_produksi')
                    ->label('PIC')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Vendor Subcount')
                    ->options(fn () => MasterSupplier::query()
                        ->orderByRaw("CASE WHEN LOWER(COALESCE(jenis_supplier, '')) LIKE '%subcount%' OR LOWER(COALESCE(jenis_supplier, '')) LIKE '%maklo%' THEN 0 ELSE 1 END")
                        ->orderByDesc('id')
                        ->pluck('nama_supplier', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        $supplierId = (int) $data['value'];

                        return $query->where(function (Builder $q) use ($supplierId) {
                            $q->where('supplier_id', $supplierId)
                                ->orWhereHas('subcountOut', fn (Builder $oq) => $oq->where('supplier_id', $supplierId));
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => route('subcount-in.preview', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('subcount-in.download', $record)),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountIn($record)),
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
        return parent::getEloquentQuery()->with(['subcountOut.supplier', 'supplier', 'items.subcountOut.supplier']);
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
