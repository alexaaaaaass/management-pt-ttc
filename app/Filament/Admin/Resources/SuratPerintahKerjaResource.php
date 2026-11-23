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
                                modifyQueryUsing: function (Builder $query, ?MasterSpk $record = null) {
                                    $query->with(['customer', 'itemable', 'orderConfirmation.items'])
                                        ->where('order_type', 'SPK');

                                    if (\Illuminate\Support\Facades\Schema::hasTable('sales_orders') && \Illuminate\Support\Facades\Schema::hasColumn('sales_orders', 'fulfillment_type')) {
                                        $query->where(fn (Builder $q) => $q->whereNull('fulfillment_type')->orWhere('fulfillment_type', '!=', 'FROM_STOCK'));
                                    }

                                    // Pada mode Create hanya tampilkan SO yang belum memiliki SPK; pada Edit tetap tampilkan SO milik record ini
                                    $query->where(function (Builder $q) use ($record) {
                                        $q->whereDoesntHave('spk');
                                        if ($record?->sales_order_id) {
                                            $q->orWhere('id', $record->sales_order_id);
                                        }
                                    });

                                    return $query->orderByDesc('created_at')->orderByDesc('id');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(function (SalesOrder $so): string {
                                $cust = $so->customer?->nama_customer ?? '-';
                                $prod = $so->product_name ?: '-';
                                $qty = number_format((float) ($so->qty ?? 0), 0, ',', '.');

                                return "{$so->no_sales_order} — {$cust} · {$prod} ({$qty} pcs)";
                            })
                            ->searchable(['no_sales_order', 'no_po_customer', 'product_name'])
                            ->preload()
                            ->native(false)
                            ->optionsLimit(30)
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $so = SalesOrder::with(['customer', 'itemable', 'orderConfirmation.items'])->find($state);

                                if (!$so) return;

                                $qtyToProduce = ($so->fulfillment_type === 'PARTIAL_STOCK' && (float) $so->qty_to_produce > 0)
                                    ? (float) $so->qty_to_produce
                                    : (float) $so->qty;

                                // 🔹 data SO
                                $set('tanggal_po', $so->tanggal_po);
                                $set('qty', $qtyToProduce);
                                $set('customer_name', $so->customer->nama_customer ?? '-');
                                $set('no_po_customer', $so->no_po_customer);
                                $set('tipe_pesanan', $so->tipe_pesanan);

                                // 🔥 Spesifikasi Teknis Cetak: Prioritas 1 dari Kalkulasi HPP Order Confirmation, Fallback ke FinishGoodItem
                                $specs = app(\App\Services\Production\ProductionService::class)
                                    ->resolveTechnicalSpecsFromSalesOrder($so);

                                $set('up_satu', $specs['up_satu']);
                                $set('up_dua', $specs['up_dua']);
                                $set('up_tiga', $specs['up_tiga']);
                                $set('ukuran_potong', $specs['ukuran_potong']);
                                $set('ukuran_cetak', $specs['ukuran_cetak']);
                                $set('spesifikasi_kertas', $specs['spesifikasi_kertas']);
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
                                            ->label('Jumlah Wajib Produksi SPK')
                                            ->content(function (Get $get) {
                                                $so = SalesOrder::find($get('sales_order_id'));
                                                if (! $so) {
                                                    return '-';
                                                }
                                                if ($so->fulfillment_type === 'PARTIAL_STOCK') {
                                                    return number_format((float) $so->qty_to_produce, 0, ',', '.')
                                                        . ' pcs (Total SO: ' . number_format((float) $so->qty, 0, ',', '.')
                                                        . ' | Buffer Stock: ' . number_format((float) $so->qty_from_stock, 0, ',', '.') . ' pcs)';
                                                }

                                                return $so->qty ?? '-';
                                            }),

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
                                ->placeholder('Target kuantiti produksi atau tulis SUBCOUNT / MAKLON')
                                ->helperText('Jika dikerjakan vendor luar, tulis "SUBCOUNT" atau otomatis terdeteksi setelah dibuatkan Subcount Out.'),

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
                    ->description('Susunan UP (Repeat × Lane), ukuran potong die-cut, area cetak, dan spesifikasi kertas')
                    ->schema([
                        Forms\Components\Placeholder::make('oc_spec_info')
                            ->hiddenLabel()
                            ->visible(fn (Get $get) => filled($get('sales_order_id')))
                            ->content(function (Get $get) {
                                $so = SalesOrder::with(['orderConfirmation.items', 'itemable'])->find($get('sales_order_id'));
                                if (! $so) {
                                    return null;
                                }
                                $specs = app(\App\Services\Production\ProductionService::class)
                                    ->resolveTechnicalSpecsFromSalesOrder($so);

                                if ($specs['source'] === 'order_confirmation') {
                                    $docNo = e($specs['oc_doc_no'] ?: '-');

                                    return new \Illuminate\Support\HtmlString(
                                        '<div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-left: 4px solid #10b981; border-radius: 8px; padding: 10px 14px; font-size: 12px;">'
                                        . '<strong style="color: #10b981;">Terisi Otomatis dari Kalkulasi HPP Order Confirmation (' . $docNo . ')</strong><br>'
                                        . '<span style="opacity: 0.85;">Nilai UP, Ukuran Potong (Die-Cut), Ukuran Cetak (Web × Repeat), dan Spesifikasi Kertas ditarik dari hasil kalkulasi Flexo OC. Anda tetap dapat menyesuaikan secara manual bila diperlukan.</span>'
                                        . '</div>'
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-left: 4px solid #f59e0b; border-radius: 8px; padding: 10px 14px; font-size: 12px;">'
                                    . '<strong style="color: #f59e0b;">Sales Order belum terhubung dengan Kalkulasi HPP Order Confirmation</strong><br>'
                                    . '<span style="opacity: 0.85;">Data menggunakan fallback dari Master Finish Good (jika ada). Silakan isi atau sesuaikan spesifikasi teknis cetak di bawah ini secara manual.</span>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('up_satu')
                                ->label('UP 1 — Total UP / Repeat')
                                ->numeric()
                                ->placeholder('Contoh: 4')
                                ->helperText('Total etiket per 1 putaran cylinder (UP Repeat × UP Lebar).'),

                            Forms\Components\TextInput::make('up_dua')
                                ->label('UP 2 — Arah Repeat (Keliling)')
                                ->numeric()
                                ->placeholder('Contoh: 2')
                                ->helperText('Jumlah susunan etiket sepanjang keliling repeat cylinder.'),

                            Forms\Components\TextInput::make('up_tiga')
                                ->label('UP 3 — Arah Lebar (Jalur / Lane)')
                                ->numeric()
                                ->placeholder('Contoh: 2')
                                ->helperText('Jumlah jalur / baris etiket melintang lebar web kertas.'),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('ukuran_potong')
                                ->label('Ukuran Potong / Jadi Etiket (Die-Cut)')
                                ->placeholder('Contoh: 178 × 119 mm')
                                ->helperText('Ukuran jadi / potong akhir etiket (Panjang × Lebar dalam mm).'),

                            Forms\Components\TextInput::make('ukuran_cetak')
                                ->label('Ukuran Cetak (Lebar Web × Repeat Cylinder)')
                                ->placeholder('Contoh: 264 × 412.8 mm')
                                ->helperText('Area cetak 1 repeat mesin Flexo (Lebar Bahan/Web × Repeat dalam mm).'),
                        ]),

                        Forms\Components\Textarea::make('spesifikasi_kertas')
                            ->label('Spesifikasi Kertas / Material')
                            ->placeholder('Contoh: Ivory 230 GSM | Lebar Web: 26.4 cm | Est. Kebutuhan: 5,716 m / 347.1 kg')
                            ->helperText('Jenis kertas, gramatur, lebar web roll, dan estimasi kebutuhan dari Order Confirmation.')
                            ->rows(3)
                            ->columnSpanFull(),
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

            Tables\Columns\TextColumn::make('execution_mode')
                ->label('Tipe Pengerjaan')
                ->badge()
                ->icon(fn ($record) => $record->is_subcount ? 'heroicon-o-truck' : 'heroicon-o-cog-6-tooth')
                ->color(fn ($record) => $record->is_subcount ? 'info' : 'success')
                ->tooltip(function ($record): ?string {
                    if (!$record->is_subcount) {
                        return 'Dikerjakan di mesin produksi internal TTC';
                    }
                    $suppliers = $record->subcountOutItems
                        ->map(fn ($it) => $it->subcountOut?->supplier?->nama_supplier)
                        ->filter()
                        ->unique()
                        ->implode(', ');
                    return $suppliers ? "Maklon / Subcount ke: {$suppliers}" : 'Ditandai Subcount / Maklon';
                }),

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

            Tables\Columns\TextColumn::make('pracetak_status_label')
                ->label('Pra Cetak')
                ->badge()
                ->getStateUsing(function (MasterSpk $record): string {
                    $latest = $record->pracetaks->sortByDesc('id')->first();
                    if (! $latest) {
                        return 'Belum Proses';
                    }

                    return match ($latest->status) {
                        'design', 'trial_design' => 'A. Trial Design',
                        'prove_print'            => 'B. Prove Print',
                        'plat_making'            => 'C. Plate Making',
                        'selesai'                => 'Siap (' . ($latest->lokasi_simpan_plate ?: 'Rak') . ')',
                        'handed_over'            => 'Di Printing',
                        default                  => ucfirst((string) $latest->status),
                    };
                })
                ->color(function (MasterSpk $record): string {
                    $latest = $record->pracetaks->sortByDesc('id')->first();
                    if (! $latest) {
                        return 'gray';
                    }

                    return match ($latest->status) {
                        'design', 'trial_design' => 'warning',
                        'prove_print'            => 'info',
                        'plat_making'            => 'primary',
                        'selesai', 'handed_over' => 'success',
                        default                  => 'gray',
                    };
                }),

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
            Tables\Filters\SelectFilter::make('execution_mode')
                ->label('Tipe Pengerjaan')
                ->options([
                    'subcount' => 'Subcount / Maklon',
                    'in_house' => 'Produksi Internal',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $val = $data['value'] ?? null;
                    if ($val === 'subcount') {
                        return $query->where(function ($q) {
                            $q->whereHas('subcountOutItems')
                              ->orWhere('production_plan', 'like', '%SUBCOUNT%')
                              ->orWhere('production_plan', 'like', '%MAKLON%');
                        });
                    }
                    if ($val === 'in_house') {
                        return $query->whereDoesntHave('subcountOutItems')
                            ->where(function ($q) {
                                $q->whereNull('production_plan')
                                  ->orWhere(function ($pq) {
                                      $pq->where('production_plan', 'not like', '%SUBCOUNT%')
                                         ->where('production_plan', 'not like', '%MAKLON%');
                                  });
                            });
                    }
                    return $query;
                }),
        ])
        ->actions([
            ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status !== 'FINISH'),

                Tables\Actions\Action::make('open_pracetak')
                    ->label(fn (MasterSpk $record) => $record->pracetaks->isNotEmpty() ? 'Buka Proses Pra Cetak' : 'Buat Tugas Pra Cetak')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('info')
                    ->action(function (MasterSpk $record) {
                        $existing = $record->pracetaks()->latest('id')->first();
                        if ($existing) {
                            return redirect(PracetakProcessResource::getUrl('edit', ['record' => $existing]));
                        }

                        // Cek jika ada Pre-Order Pra Cetak dari Approval Form terkait yang belum dihubungkan ke SPK
                        $afId = $record->salesOrder?->orderConfirmation?->approval_form_id;
                        if ($afId) {
                            $preOrder = \App\Models\PracetakProcess::where('approval_form_id', $afId)
                                ->whereNull('spk_id')
                                ->latest('id')
                                ->first();

                            if ($preOrder) {
                                $resolved = app(\App\Services\Pracetak\PracetakCostingService::class)->resolveEstimatedPlateFromSpk($record);
                                $preOrder->update([
                                    'spk_id'                => $record->id,
                                    'order_confirmation_id' => $resolved['order_confirmation_id'],
                                    'estimasi_biaya_plate'  => $resolved['estimasi_biaya_plate'],
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('Pre-Order Pra Cetak Terhubung ke SPK')
                                    ->body("Proses Trial/Prove sebelumnya otomatis disambungkan ke SPK {$record->no_spk}. Siap lanjut ke Tahap C (Plate Making).")
                                    ->success()
                                    ->send();

                                return redirect(PracetakProcessResource::getUrl('edit', ['record' => $preOrder]));
                            }
                        }

                        return redirect(PracetakProcessResource::getUrl('create', ['spk_id' => $record->id]));
                    }),

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
            ->label('Aksi')
            ->icon('heroicon-m-ellipsis-vertical')
            ->size('sm')
            ->color('primary')
            ->button()
            ->dropdownPlacement('bottom-end'),
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
                'subcountOutItems.subcountOut.supplier',
                'subcountInItems',
            ]);
    }
}