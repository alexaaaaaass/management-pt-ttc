<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ImrFinishResource\Pages;
use App\Models\ImrFinish;
use App\Models\MasterItem;
use App\Models\MasterSpk;
use App\Models\MaterialStock;
use App\Services\MaterialMovement\RecordMaterialMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ImrFinishResource extends Resource
{
    protected static ?string $model = ImrFinish::class;

    protected static ?string $navigationGroup = 'Finishing';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationLabel(): string
    {
        return 'IMR Finish';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi IMR Finishing')
                    ->description('Data utama permintaan material divisi Finishing.')
                    ->schema([
                        Forms\Components\TextInput::make('no_imr')
                            ->label('Nomor IMR')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Nomor IMR dibuat otomatis setelah disimpan'),

                        Forms\Components\DatePicker::make('tanggal_request')
                            ->label('Tanggal Request')
                            ->required()
                            ->native(false)
                            ->default(now()),

                        Forms\Components\Select::make('spk_id')
                            ->label('Surat Perintah Kerja')
                            ->placeholder('Pilih SPK')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->relationship('spk', 'no_spk'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan IMR')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('loadBom')
                        ->label('Load Material dari SPK / BOM')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($get, $set) {
                            $spkId = $get('spk_id');
                            if (!$spkId) {
                                Notification::make()
                                    ->title('Pilih SPK Terlebih Dahulu')
                                    ->body('Silakan tentukan nomor SPK sebelum memuat material.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $spk = MasterSpk::with([
                                'salesOrder.itemable.materials.material.satuan',
                                'salesOrder.itemable.materials.departemen',
                            ])->find($spkId);

                            if (!$spk || !$spk->salesOrder || !$spk->salesOrder->itemable) {
                                Notification::make()
                                    ->title('Data BOM Tidak Ditemukan')
                                    ->body('SPK yang dipilih belum memiliki relasi Finish Good atau resep BOM.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $items = [];
                            foreach ($spk->salesOrder->itemable->materials as $bom) {
                                $totalBom = (float) $bom->qty * (float) $spk->salesOrder->qty;
                                $items[] = [
                                    'item_id'       => $bom->master_item_id,
                                    'nama_item'     => $bom->material?->nama_master_item,
                                    'satuan'        => optional($bom->material?->satuan)->nama_satuan,
                                    'total_pesanan' => $totalBom,
                                    'qty_request'   => 0,
                                    'qty_input'     => 0,
                                    'qty_approved'  => 0,
                                    'catatan'       => null,
                                ];
                            }

                            $set('imr_items', $items);

                            Notification::make()
                                ->title('Material BOM Berhasil Dimuat')
                                ->body(count($items) . ' material resep berhasil dimasukkan ke daftar request.')
                                ->success()
                                ->send();
                        }),
                ]),

                Forms\Components\Section::make('Daftar Material Request')
                    ->description('Daftar material yang diminta ke gudang (BOM produk atau bahan penolong finishing seperti lem, pita, plastik shrink, kardus, dll).')
                    ->schema([
                        Forms\Components\Repeater::make('imr_items')
                            ->label('')
                            ->default([])
                            ->collapsible()
                            ->cloneable(false)
                            ->reorderable(false)
                            ->addActionLabel('Tambah Material Lain (Bahan Penolong / Kemasan)')
                            ->itemLabel(function (array $state): ?string {
                                $nama = $state['nama_item'] ?? null;
                                if (!$nama && !empty($state['item_id'])) {
                                    $item = MasterItem::find($state['item_id']);
                                    $nama = $item?->nama_master_item;
                                }
                                $nama = $nama ?: '-';
                                $bom = (float) ($state['total_pesanan'] ?? 0);
                                $qty = (float) ($state['qty_input'] ?? ($state['qty_request'] ?? 0));
                                $satuan = $state['satuan'] ?? '';

                                if ($bom > 0) {
                                    return "{$nama} (BOM: {$bom} {$satuan} | Req: {$qty} {$satuan})";
                                }
                                return "{$nama} [Bahan Penolong] (Req: {$qty} {$satuan})";
                            })
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Material')
                                    ->options(MasterItem::pluck('nama_master_item', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $item = MasterItem::with('satuan')->find($state);
                                            if ($item) {
                                                $set('nama_item', $item->nama_master_item);
                                                $set('satuan', $item->satuan?->nama_satuan ?? '');
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('nama_item')
                                    ->label('Nama Item')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('total_pesanan')
                                    ->label('Qty BOM')
                                    ->formatStateUsing(function ($state) {
                                        $val = (float) ($state ?? 0);
                                        return $val > 0 ? (string) $val : 'Bahan Penolong (Bebas Request)';
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Hidden::make('qty_request')
                                    ->default(0)
                                    ->dehydrated(),

                                Forms\Components\TextInput::make('qty_input')
                                    ->label('Qty Request')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->live(debounce: 500)
                                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                        $req = (float) ($get('qty_request') ?? 0);
                                        if ($req > 0 && !$state) {
                                            $set('qty_input', $req);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $bom = (float) ($get('total_pesanan') ?? 0);
                                        $qty = max(0, (float) $state);

                                        if ($bom > 0 && $qty > $bom) {
                                            $qty = $bom;
                                            Notification::make()
                                                ->title('Melebihi Kebutuhan BOM')
                                                ->body("Bahan baku tidak boleh melebihi total pesanan BOM ({$bom}).")
                                                ->warning()
                                                ->send();
                                        }

                                        $set('qty_input', $qty);
                                        $set('qty_request', $qty);
                                    })
                                    ->helperText(function (callable $get) {
                                        $bom = (float) ($get('total_pesanan') ?? 0);
                                        $satuan = $get('satuan') ?? '';
                                        if ($bom > 0) {
                                            return "Bahan Baku: Maksimal request sesuai BOM ({$bom} {$satuan}).";
                                        }
                                        return "Bahan Penolong: Bebas input request sesuai kebutuhan aktual.";
                                    }),

                                Forms\Components\Textarea::make('catatan')
                                    ->label('Catatan Item')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(5),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_imr')
                    ->label('No IMR')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tanggal_request')
                    ->label('Tanggal Request')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_request')
                    ->label('Qty Request')
                    ->getStateUsing(fn($record) => $record->items->sum('qty_request')),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'Pending'        => 'warning',
                        'Approved'       => 'success',
                        'Partial'        => 'info',
                        'Issued'         => 'primary',
                        'Partial Issued' => 'indigo',
                        'Rejected'       => 'danger',
                        'Closed'         => 'gray',
                        default          => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending'        => 'Pending',
                        'Approved'       => 'Approved',
                        'Partial'        => 'Partial',
                        'Issued'         => 'Issued',
                        'Partial Issued' => 'Partial Issued',
                        'Rejected'       => 'Rejected',
                        'Closed'         => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn(ImrFinish $record) => $record->status === 'Pending')
                        ->requiresConfirmation()
                        ->modalWidth('4xl')
                        ->modalHeading('Approve IMR Finishing')
                        ->modalDescription('Masukkan jumlah material yang disetujui untuk dialokasikan dari gudang.')
                        ->form(function (ImrFinish $record) {
                            $record->loadMissing(['items.item.stock', 'items.item.satuan']);
                            $schema = [];
                            foreach ($record->items as $index => $item) {
                                $stockOnHand = $item->item?->stock?->on_hand ?? 0;
                                $schema[] = Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\Placeholder::make("item_{$index}")
                                            ->label('Material')
                                            ->content($item->item?->nama_master_item ?? '-'),

                                        Forms\Components\Placeholder::make("stock_{$index}")
                                            ->label('Stock On-Hand')
                                            ->content($stockOnHand . ' ' . ($item->satuan ?: '')),

                                        Forms\Components\Placeholder::make("req_{$index}")
                                            ->label('Qty Request')
                                            ->content($item->qty_request . ' ' . ($item->satuan ?: '')),

                                        Forms\Components\TextInput::make("items.{$index}.qty_approved")
                                            ->label('Qty Approve')
                                            ->numeric()
                                            ->required()
                                            ->default($item->qty_request)
                                            ->minValue(0)
                                            ->maxValue($item->qty_request),
                                    ]);
                            }
                            return $schema;
                        })
                        ->action(function (ImrFinish $record, array $data) {
                            try {
                                DB::transaction(function () use ($record, $data) {
                                    $record->loadMissing('items');
                                    foreach ($record->items as $index => $item) {
                                        $qtyApproved = (float) ($data['items'][$index]['qty_approved'] ?? $item->qty_request);
                                        $item->update(['qty_approved' => $qtyApproved]);

                                        $stock = MaterialStock::where('item_id', $item->item_id)->lockForUpdate()->first();
                                        if ($stock) {
                                            $stock->increment('allocation', $qtyApproved);
                                            app(RecordMaterialMovement::class)->handle([
                                                'item_id'        => $item->item_id,
                                                'reference_type' => 'IMR_FINISH',
                                                'reference_id'   => $record->id,
                                                'movement_type'  => 'APPROVE',
                                                'qty'            => $qtyApproved,
                                                'stock_before'   => $stock->on_hand,
                                                'stock_after'    => $stock->on_hand,
                                                'remarks'        => "Approve IMR Finish {$record->no_imr}",
                                            ]);
                                        }
                                    }
                                    $record->update(['status' => 'Approved']);
                                });

                                Notification::make()
                                    ->title('IMR Finishing Berhasil Disetujui')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Persetujuan Gagal')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(ImrFinish $record) => $record->status === 'Pending')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Permintaan IMR Finishing')
                        ->modalDescription('Apakah Anda yakin ingin menolak permintaan material ini?')
                        ->action(function (ImrFinish $record) {
                            $record->update(['status' => 'Rejected']);
                            Notification::make()
                                ->title('IMR Finishing Berhasil Ditolak')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\Action::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'Pending'        => 'Pending',
                                    'Approved'       => 'Approved',
                                    'Partial'        => 'Partial',
                                    'Issued'         => 'Issued',
                                    'Partial Issued' => 'Partial Issued',
                                    'Rejected'       => 'Rejected',
                                    'Closed'         => 'Closed',
                                ])
                                ->default(fn(ImrFinish $record) => $record->status)
                                ->required(),
                        ])
                        ->action(function (ImrFinish $record, array $data) {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Status Berhasil Diperbarui')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'spk',
                'departemen',
                'items.item',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListImrFinishes::route('/'),
            'create' => Pages\CreateImrFinish::route('/create'),
            'edit'   => Pages\EditImrFinish::route('/{record}/edit'),
        ];
    }
}