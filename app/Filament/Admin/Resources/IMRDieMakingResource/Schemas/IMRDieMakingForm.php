<?php

namespace App\Filament\Admin\Resources\IMRDieMakingResource\Schemas;

use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\MasterSpk;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class IMRDieMakingForm
{
    public static function defaultDepartemenId(): ?int
    {
        return MasterDepartemen::query()
            ->where('kode', 'DIEM')
            ->orWhere('nama_departemen', 'like', '%Die Making%')
            ->orWhere('nama_departemen', 'like', '%Diemaking%')
            ->value('id');
    }

    public static function make(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi IMR Die Making')
                ->description('Permintaan material (Internal Material Request) dari divisi Die Making ke Gudang berdasarkan SPK.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('no_imr')
                        ->label('Nomor IMR')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('Auto Generate (IMRD-YYYYMM-XXXX)'),

                    Forms\Components\DatePicker::make('tanggal_request')
                        ->label('Tanggal Request')
                        ->required()
                        ->native(false)
                        ->default(now()),

                    Forms\Components\Select::make('spk_id')
                        ->label('Surat Perintah Kerja (SPK)')
                        ->placeholder('Pilih SPK (Terbaru di Atas)')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->relationship(
                            name: 'spk',
                            titleAttribute: 'no_spk',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->with(['salesOrder.customer', 'salesOrder.itemable'])
                                ->orderByDesc('created_at')
                                ->orderByDesc('id')
                        )
                        ->getOptionLabelFromRecordUsing(function (MasterSpk $spk): string {
                            $cust = $spk->salesOrder?->customer?->nama_customer ?? '-';
                            $prod = $spk->salesOrder?->product_name
                                ?: ($spk->salesOrder?->itemable?->nama_barang ?? '-');

                            return "{$spk->no_spk} — {$cust} · {$prod}";
                        }),

                    Forms\Components\Select::make('departemen_id')
                        ->label('Departemen Peminta')
                        ->options(fn () => MasterDepartemen::query()
                            ->where('status', 1)
                            ->orderBy('nama_departemen')
                            ->pluck('nama_departemen', 'id')
                            ->all())
                        ->default(fn () => self::defaultDepartemenId())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Placeholder::make('spk_bom_info')
                        ->label('Ringkasan SPK & BOM Produk')
                        ->visible(fn (Get $get) => filled($get('spk_id')))
                        ->columnSpanFull()
                        ->content(function (Get $get): HtmlString {
                            $spk = MasterSpk::with([
                                'salesOrder.customer',
                                'salesOrder.itemable.materials',
                            ])->find($get('spk_id'));

                            if (! $spk) {
                                return new HtmlString('-');
                            }

                            $cust = e($spk->salesOrder?->customer?->nama_customer ?? '-');
                            $produk = e($spk->salesOrder?->itemable?->nama_barang ?? ($spk->salesOrder?->product_name ?? '-'));
                            $orderQty = number_format((int) $spk->order_qty, 0, ',', '.');
                            $bomCount = $spk->salesOrder?->itemable?->materials?->count() ?? 0;

                            return new HtmlString(
                                '<div style="padding:8px 12px;border-radius:8px;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.1);font-size:12px;color:#60a5fa;">'
                                . "Customer: <strong>{$cust}</strong> · Produk: <strong>{$produk}</strong> · Target Order: <strong>{$orderQty} pcs</strong> · Material di BOM: <strong>{$bomCount} item</strong>. "
                                . 'Klik <strong>Load Material dari SPK</strong> di bawah atau tambah material lain secara manual.'
                                . '</div>'
                            );
                        }),
                ]),

            Forms\Components\Section::make('Daftar Material Request Die Making')
                ->description('Muat daftar material dari BOM SPK lalu isi Qty Request, atau tambahkan material pendukung Die Making lainnya (misal foil, pisau pond, creasing rule, spons/rubber) langsung dari Master Item.')
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('loadBom')
                            ->label('Load Material dari SPK')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->action(function (Get $get, Set $set): void {
                                $spkId = $get('spk_id');

                                if (! $spkId) {
                                    Notification::make()
                                        ->title('SPK belum dipilih')
                                        ->body('Pilih Surat Perintah Kerja terlebih dahulu sebelum memuat material BOM.')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $spk = MasterSpk::with([
                                    'salesOrder.itemable.materials.material.satuan',
                                    'salesOrder.itemable.materials.material.stock',
                                    'salesOrder.itemable.materials.departemen',
                                ])->find($spkId);

                                if (! $spk || ! $spk->salesOrder || ! $spk->salesOrder->itemable) {
                                    Notification::make()
                                        ->title('Data SPK tidak lengkap')
                                        ->body('SPK atau produk Finish Good terkait tidak ditemukan.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $materials = $spk->salesOrder->itemable->materials;

                                if ($materials->isEmpty()) {
                                    Notification::make()
                                        ->title('BOM Produk Kosong')
                                        ->body('Belum ada material di BOM produk SPK ini. Anda tetap bisa menambahkan material secara manual lewat tombol "Tambah Material Lain".')
                                        ->warning()
                                        ->send();

                                    return;
                                }

                                $items = [];

                                foreach ($materials as $bom) {
                                    if (! $bom->material) {
                                        continue;
                                    }

                                    $available = (float) ($bom->material->stock?->available ?? 0);
                                    $satuan = $bom->material->satuan?->nama_satuan ?? '-';

                                    $items[] = [
                                        'item_id'       => $bom->master_item_id,
                                        'nama_item'     => $bom->material->nama_master_item ?? '-',
                                        'department'    => $bom->departemen?->nama_departemen ?? 'BOM SPK',
                                        'satuan'        => $satuan,
                                        'stok_gudang'   => number_format($available, 2, ',', '.') . ' ' . $satuan,
                                        'total_pesanan' => round((float) $bom->qty * (float) $spk->salesOrder->qty, 4),
                                        'qty_approved'  => 0,
                                        'qty_request'   => 0,
                                        'qty_input'     => 0,
                                        'catatan'       => null,
                                    ];
                                }

                                $set('imr_items', $items);

                                Notification::make()
                                    ->title('Material BOM berhasil dimuat')
                                    ->body(count($items) . ' material dimuat dari SPK. Silakan isi Qty Request pada material yang dibutuhkan Die Making.')
                                    ->success()
                                    ->send();
                            }),
                    ]),

                    Forms\Components\Repeater::make('imr_items')
                        ->label('Daftar Material')
                        ->default([])
                        ->collapsible()
                        ->collapsed()
                        ->cloneable(false)
                        ->addable(true)
                        ->addActionLabel('Tambah Material Lain (Non-BOM)')
                        ->deletable(true)
                        ->reorderable(false)
                        ->itemLabel(function (array $state): ?string {
                            $nama = $state['nama_item'] ?? null;
                            if (empty($nama) && ! empty($state['item_id'])) {
                                $nama = MasterItem::whereKey($state['item_id'])->value('nama_master_item');
                            }
                            $nama = $nama ?: 'Pilih Material';
                            $qty = max(0, (float) ($state['qty_input'] ?? $state['qty_request'] ?? 0));
                            $qtyFormatted = MoneyHelper::format($qty, 2) ?? '0';
                            $satuan = trim((string) ($state['satuan'] ?? ''));

                            return $satuan !== ''
                                ? "{$nama} ({$qtyFormatted} {$satuan})"
                                : "{$nama} ({$qtyFormatted})";
                        })
                        ->schema([
                            Forms\Components\Hidden::make('nama_item'),
                            Forms\Components\Hidden::make('qty_request')->default(0),

                            Forms\Components\Select::make('item_id')
                                ->label('Material')
                                ->options(fn () => MasterItem::query()
                                    ->orderBy('nama_master_item')
                                    ->get()
                                    ->mapWithKeys(fn (MasterItem $item) => [
                                        $item->id => ($item->kode_master_item ? "[{$item->kode_master_item}] " : '') . $item->nama_master_item,
                                    ])
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                    if (! $state) {
                                        return;
                                    }
                                    $item = MasterItem::with(['satuan', 'stock'])->find($state);
                                    if (! $item) {
                                        return;
                                    }
                                    $satuan = $item->satuan?->nama_satuan ?? '-';
                                    $available = (float) ($item->stock?->available ?? 0);

                                    $set('nama_item', $item->nama_master_item);
                                    $set('satuan', $satuan);
                                    $set('stok_gudang', number_format($available, 2, ',', '.') . ' ' . $satuan);
                                    if (blank($get('department'))) {
                                        $set('department', 'Tambahan Manual');
                                    }
                                    if (blank($get('total_pesanan'))) {
                                        $set('total_pesanan', 0);
                                    }
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('department')
                                ->label('Sumber / Dept BOM')
                                ->default('Tambahan Manual')
                                ->disabled()
                                ->dehydrated(false),

                            Forms\Components\TextInput::make('stok_gudang')
                                ->label('Stok Tersedia Gudang')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('-'),

                            Forms\Components\TextInput::make('satuan')
                                ->label('Satuan')
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('total_pesanan')
                                ->label('Qty BOM SPK')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('qty_approved')
                                ->label('Qty Approved')
                                ->numeric()
                                ->default(0)
                                ->disabled()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('qty_input')
                                ->label('Qty Request')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set): void {
                                    $qty = max(0, (float) $state);
                                    $set('qty_input', $qty);
                                    $set('qty_request', $qty);
                                })
                                ->helperText('Isi > 0 pada material yang diminta ke Gudang.'),

                            Forms\Components\Textarea::make('catatan')
                                ->label('Catatan (Opsional)')
                                ->placeholder('Contoh: Foil emas hotprint 1 roll / pisau pond cadangan')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
