<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakProcessResource\Pages;
use App\Models\ApprovalForm;
use App\Models\MasterMaterialPlate;
use App\Models\MasterSpk;
use App\Models\MesinPraCetak;
use App\Models\PracetakMaterialUsage;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use App\Services\Pracetak\PracetakCostingService;
use App\Services\Pracetak\PracetakStockService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PracetakProcessResource extends Resource
{
    protected static ?string $model = PracetakProcess::class;
    protected static ?string $navigationGroup = 'Pra Cetak';
    protected static ?string $navigationLabel = 'Proses Pra Cetak';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralLabel = 'Proses Pra Cetak';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function recalculateUsageRow(Get $get, Set $set, bool $autoComputeSheet = true): void
    {
        $plateId = $get('master_material_plate_id');
        $plate = $plateId ? MasterMaterialPlate::with('stock')->find($plateId) : null;

        $jumlahPlate = max(1, (int) ($get('jumlah_plate') ?? 1));
        $panjangMm = max(0, (float) ($get('panjang_potong_mm') ?? 0));
        $lebarMm = max(0, (float) ($get('lebar_potong_mm') ?? 0));

        $fraction = app(PracetakStockService::class)->calculateSheetFraction(
            $plate,
            $panjangMm,
            $lebarMm,
            $jumlahPlate
        );

        $set('luas_terpakai_cm2', $fraction['luas_terpakai_cm2']);

        $qtySheet = $autoComputeSheet
            ? $fraction['qty_sheet_terpakai']
            : max(0, (float) ($get('qty_sheet_terpakai') ?? 0));

        if ($autoComputeSheet) {
            $set('qty_sheet_terpakai', $qtySheet);
        }

        $unitPrice = 0.0;
        if ($plate) {
            $avgStock = (float) ($plate->stock?->harga_rata_rata ?? 0);
            $unitPrice = $avgStock > 0 ? $avgStock : (float) $plate->harga_beli;
        }

        $set('harga_satuan_snapshot', MoneyHelper::format(round($unitPrice, 2), 2) ?? '0');
        $set('total_biaya', MoneyHelper::format(round($qtySheet * $unitPrice, 2), 2) ?? '0');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('AlurPraCetak')
                ->tabs([
                    // ==========================================
                    // 1. DATA SPK / PRE-ORDER & DETAIL PESANAN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Data SPK & Pesanan')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('spk_id')
                                    ->label('Nomor SPK (Standar Alur Baku)')
                                    ->relationship('spk', 'no_spk')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('Wajib diisi sebelum masuk Tahap C (Plate Making / Potong Stok). Kosongkan hanya untuk Pre-Order Trial/Prove.')
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $spk = MasterSpk::find($state);
                                        if (! $spk) {
                                            return;
                                        }

                                        $resolved = app(PracetakCostingService::class)->resolveEstimatedPlateFromSpk($spk);
                                        $set('approval_form_id', $resolved['approval_form_id']);
                                        $set('order_confirmation_id', $resolved['order_confirmation_id']);
                                        $set('finish_good_item_id', $resolved['finish_good_item_id']);
                                        if (! $get('nama_plat')) {
                                            $set('nama_plat', $resolved['nama_plat']);
                                        }
                                        $set('jumlah_warna_plate', $resolved['jumlah_warna_plate']);
                                        $set('qty', $resolved['jumlah_warna_plate']);
                                        $set('estimasi_biaya_plate', MoneyHelper::format($resolved['estimasi_biaya_plate'], 2) ?? '0');
                                        $set('is_repeat_order', $resolved['is_repeat_order']);
                                        if ($resolved['plate_archive_id']) {
                                            $set('plate_archive_id', $resolved['plate_archive_id']);
                                        }
                                    }),

                                Forms\Components\Select::make('approval_form_id')
                                    ->label('Referensi Approval Form (Opsional / Pre-Order)')
                                    ->relationship('approvalForm', 'form_no')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state || $get('spk_id')) {
                                            return;
                                        }
                                        $af = ApprovalForm::with('items')->find($state);
                                        $firstItem = $af?->items?->first();
                                        if ($firstItem) {
                                            if (! $get('nama_plat')) {
                                                $set('nama_plat', $firstItem->item_name);
                                            }
                                            if ($firstItem->finish_good_item_id) {
                                                $set('finish_good_item_id', $firstItem->finish_good_item_id);
                                            }
                                            if ($firstItem->warna && preg_match('/\d+/', (string) $firstItem->warna, $m)) {
                                                $set('jumlah_warna_plate', max(1, (int) $m[0]));
                                                $set('qty', max(1, (int) $m[0]));
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('ket_spk')
                                    ->label('Jenis Pekerjaan SPK')
                                    ->options([
                                        'reguler'  => 'Reguler (Internal)',
                                        'subcount' => 'Subcount (Pekerjaan Luar)',
                                    ])
                                    ->default('reguler')
                                    ->required(),
                            ]),

                            Forms\Components\Hidden::make('order_confirmation_id'),
                            Forms\Components\Hidden::make('finish_good_item_id'),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('nama_plat')
                                    ->label('Nama / Judul Plat')
                                    ->placeholder('Contoh: Etiket Pandawa 230gr - 4 Warna')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Target Qty Plat (Lembar)')
                                    ->numeric()
                                    ->default(4)
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Tahapan Pra Cetak')
                                    ->options([
                                        'design'      => 'A. Trial Design (Pengecekan File)',
                                        'prove_print' => 'B. Prove Print (Sampel Cetak)',
                                        'plat_making' => 'C. Plate Making (CTP & Potong Stok)',
                                        'selesai'     => 'D. Selesai & Tersimpan di Rak',
                                        'handed_over' => 'E. Diserahkan ke Printing',
                                    ])
                                    ->default('design')
                                    ->required(),

                                Forms\Components\Toggle::make('is_repeat_order')
                                    ->label('Repeat Order (Pakai Plate Arsip Rak)?')
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Aktifkan jika produk memakai ulang plate jadi dari rak penyimpanan.'),

                                Forms\Components\TextInput::make('estimasi_biaya_plate')
                                    ->label('Estimasi Biaya Plate (dari OC)')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->default(0)
                                    ->helperText('Acuan budget dari Kalkulator HPP Order Confirmation'),
                            ]),

                            Forms\Components\Select::make('plate_archive_id')
                                ->label('Arsip Plate Jadi Terkait (Untuk Repeat Order)')
                                ->options(function () {
                                    return PracetakPlateArchive::with('customer')
                                        ->whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])
                                        ->orderByDesc('id')
                                        ->get()
                                        ->mapWithKeys(fn (PracetakPlateArchive $a) => [$a->id => $a->display_label]);
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (! $state) {
                                        return;
                                    }
                                    $archive = PracetakPlateArchive::find($state);
                                    if ($archive) {
                                        $set('lokasi_simpan_plate', $archive->lokasi_rak);
                                        $set('material_pendukung', $archive->material_pendukung);
                                        $set('toleransi_rusak_hilang', $archive->toleransi_rusak_hilang);
                                        $set('jumlah_warna_plate', $archive->jumlah_warna);
                                        $set('detail_warna_plate', $archive->detail_warna);
                                    }
                                })
                                ->visible(fn (Get $get) => (bool) $get('is_repeat_order') || filled($get('plate_archive_id'))),

                            // Panel Detail Sales Order & Estimasi
                            Forms\Components\Section::make('Detail Sales Order & Spesifikasi Produk')
                                ->extraAttributes([
                                    'style' => 'border-left: 4px solid #3b82f6; border-radius: 10px; padding: 12px; background-color: rgba(59, 130, 246, 0.05);',
                                ])
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Grid::make(1)->schema([
                                            Forms\Components\Placeholder::make('no_so')
                                                ->label('No. Sales Order')
                                                ->content(fn (Get $get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->no_sales_order ?? '-'),

                                            Forms\Components\Placeholder::make('customer')
                                                ->label('Customer')
                                                ->content(function (Get $get) {
                                                    $spk = MasterSpk::with('salesOrder.customer')->find($get('spk_id'));
                                                    if ($spk?->salesOrder?->customer) {
                                                        return $spk->salesOrder->customer->nama_customer;
                                                    }
                                                    $af = ApprovalForm::with('customer')->find($get('approval_form_id'));

                                                    return $af?->customer?->nama_customer ?? '-';
                                                }),

                                            Forms\Components\Placeholder::make('nama_barang')
                                                ->label('Nama Barang')
                                                ->content(function (Get $get) {
                                                    $spk = MasterSpk::with('salesOrder.itemable')->find($get('spk_id'));

                                                    return $spk?->salesOrder?->product_name
                                                        ?? $spk?->salesOrder?->itemable?->nama_barang
                                                        ?? $spk?->salesOrder?->itemable?->nama_master_item
                                                        ?? '-';
                                                }),
                                        ]),

                                        Forms\Components\Grid::make(1)->schema([
                                            Forms\Components\Placeholder::make('qty_order')
                                                ->label('Jumlah Pesanan Cetak')
                                                ->content(fn (Get $get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->qty
                                                    ? number_format((float) MasterSpk::with('salesOrder')->find($get('spk_id'))->salesOrder->qty, 0, ',', '.') . ' Pcs'
                                                    : '-'),

                                            Forms\Components\Placeholder::make('spec_warna')
                                                ->label('Spesifikasi Kertas & Warna')
                                                ->content(function (Get $get) {
                                                    $so = MasterSpk::with('salesOrder.itemable')->find($get('spk_id'))?->salesOrder;
                                                    if (! $so) {
                                                        return '-';
                                                    }
                                                    $kertas = $so->kertas ?: ($so->itemable?->spesifikasi_kertas ?? '-');
                                                    $warna = $so->warna ?: ($so->itemable?->warna ?? '-');

                                                    return "{$kertas} | Warna: {$warna}";
                                                }),

                                            Forms\Components\Placeholder::make('oc_ref')
                                                ->label('Order Confirmation Acuan')
                                                ->content(fn (Get $get) => optional(MasterSpk::with('salesOrder.orderConfirmation')->find($get('spk_id')))?->salesOrder?->orderConfirmation?->doc_no ?? '-'),
                                        ]),
                                    ]),
                                ])
                                ->visible(fn (Get $get) => filled($get('spk_id')) || filled($get('approval_form_id'))),
                        ]),

                    // ==========================================
                    // (A) TRIAL DESIGN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('A. Trial Design')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            Forms\Components\Placeholder::make('af_design_preview')
                                ->label('Lampiran File Desain dari Approval Form')
                                ->content(function (Get $get) {
                                    $afId = $get('approval_form_id');
                                    if (! $afId && $get('spk_id')) {
                                        $afId = MasterSpk::with('salesOrder.orderConfirmation')->find($get('spk_id'))?->salesOrder?->orderConfirmation?->approval_form_id;
                                    }
                                    $af = $afId ? ApprovalForm::find($afId) : null;
                                    if (! $af || ! $af->file_design) {
                                        return new HtmlString('<span class="text-xs text-gray-500 italic">Belum ada lampiran file desain dari Approval Form.</span>');
                                    }
                                    $url = asset('storage/' . $af->file_design);

                                    return new HtmlString('
                                        <div style="padding: 10px 14px; border: 1px solid rgba(59,130,246,0.3); border-radius: 10px; display: flex; align-items: center; gap: 14px; background: rgba(59,130,246,0.05);">
                                            <img src="' . e($url) . '" alt="Desain" style="width: 72px; height: 72px; object-fit: contain; background: #fff; border-radius: 6px; padding: 3px; border: 1px solid rgba(0,0,0,0.1);">
                                            <div>
                                                <div style="font-size: 12px; font-weight: 700;">Ref Approval Form: ' . e($af->form_no) . '</div>
                                                <a href="' . e($url) . '" target="_blank" style="font-size: 12px; color: #3b82f6; font-weight: 600;">Buka File Desain Penuh &nearr;</a>
                                            </div>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('status_file')
                                    ->label('Ketersediaan File Desain')
                                    ->options([
                                        'ada'       => 'Ada File Desain',
                                        'tidak_ada' => 'Belum Ada File',
                                    ])
                                    ->default('ada'),

                                Forms\Components\Select::make('status_kesiapan_file')
                                    ->label('Kesiapan File Siap Cetak')
                                    ->options([
                                        'siap_cetak'      => 'File Siap Cetak (Layout OK)',
                                        'perlu_edit'      => 'Perlu Edit / Penyesuaian Bleed & Margin',
                                        'revisi_customer' => 'Menunggu Revisi dari Customer',
                                    ])
                                    ->default('siap_cetak'),
                            ]),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('toleransi_hasil')
                                    ->label('Toleransi Hasil Desain')
                                    ->placeholder('Contoh: Bleed 3mm, margin potong 5mm, warna CMYK convert OK, font outlined'),

                                Forms\Components\TextInput::make('actual_design_cost')
                                    ->label('Biaya Setting / Jasa Desain Aktual (Rp)')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->default(0)
                                    ->helperText('Isi jika ada biaya edit/redraw file desain di luar standar'),
                            ]),

                            Forms\Components\Textarea::make('catatan_design')
                                ->label('Catatan Preflight / Trial Design')
                                ->placeholder('Catatan teknis operator tentang file desain ini...')
                                ->rows(3),
                        ]),

                    // ==========================================
                    // (B) PROVE PRINT
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('B. Prove Print')
                        ->icon('heroicon-o-printer')
                        ->schema([
                            Forms\Components\Toggle::make('is_prove_print')
                                ->label('Lakukan Proses Prove Print (Sampel Cetak)?')
                                ->default(true)
                                ->live(),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('bahan_prove_print')
                                    ->label('Print di Bahan Tertentu')
                                    ->placeholder('Contoh: Art Paper 150g, Ivory 230g, Metalize')
                                    ->visible(fn (Get $get) => (bool) $get('is_prove_print')),

                                Forms\Components\Select::make('status_prove_print')
                                    ->label('Status Approval Prove Print')
                                    ->options([
                                        'menunggu_acc' => 'Menunggu ACC / Approval',
                                        'acc'          => 'Sudah ACC (Disetujui Naik Cetak)',
                                        'revisi'       => 'Revisi Proofing / Warna Kurang Pas',
                                    ])
                                    ->default('menunggu_acc')
                                    ->visible(fn (Get $get) => (bool) $get('is_prove_print')),

                                Forms\Components\TextInput::make('actual_prove_cost')
                                    ->label('Biaya Aktual Prove Print (Rp)')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->default(0)
                                    ->helperText('Biaya bahan sampel & cetak prove')
                                    ->visible(fn (Get $get) => (bool) $get('is_prove_print')),
                            ]),

                            Forms\Components\FileUpload::make('arsip_proses_prove')
                                ->label('Arsip Bukti Prove Print (Foto / PDF ACC)')
                                ->directory('pracetak/prove-archives')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                                ->maxSize(5120)
                                ->downloadable()
                                ->openable()
                                ->visible(fn (Get $get) => (bool) $get('is_prove_print')),
                        ]),

                    // ==========================================
                    // (C) PLATE MAKING & PEMOTONGAN STOK PREPRESS
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('C. Plate Making & Stok')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('jumlah_warna_plate')
                                    ->label('Jumlah Warna Plate')
                                    ->numeric()
                                    ->default(4)
                                    ->helperText('Contoh: 4 warna (CMYK), atau 6 warna (+ Spot Gold & Varnish)'),

                                Forms\Components\TextInput::make('detail_warna_plate')
                                    ->label('Rincian Warna / Separasi Plate')
                                    ->placeholder('Contoh: Cyan, Magenta, Yellow, Black, Pantone 871 Gold, Spot UV'),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('operator_id')
                                    ->label('Operator Pra Cetak')
                                    ->relationship('operator', 'nama_operator')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_operator')
                                            ->label('Nama Operator Pra Cetak')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Select::make('mesin_id')
                                    ->label('Mesin Pra Cetak / Output CTP')
                                    ->relationship('mesin', 'nama_mesin')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nama_mesin')->required()->maxLength(255),
                                        Forms\Components\TextInput::make('jenis_mesin')->required()->maxLength(255),
                                        Forms\Components\TextInput::make('kapasitas')->maxLength(100),
                                        Forms\Components\Select::make('proses')
                                            ->options([
                                                'ctp'          => 'CTP',
                                                'setting_film' => 'Setting Film',
                                                'plate_making' => 'Plate Making',
                                            ])
                                            ->default('ctp')
                                            ->required(),
                                        Forms\Components\TextInput::make('tarif_per_plate')
                                            ->label('Tarif Proses / Plate (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0),
                                        Forms\Components\Hidden::make('status')->default('aktif'),
                                    ])
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $mesin = MesinPraCetak::find($state);
                                        if ($mesin && (float) $mesin->tarif_per_plate > 0 && (float) (MoneyHelper::clean($get('actual_process_cost')) ?? 0) <= 0) {
                                            $plateCount = max(
                                                (int) (($get('hasil_baik') ?? 0) + ($get('hasil_rusak') ?? 0)),
                                                (int) ($get('jumlah_warna_plate') ?? 4)
                                            );
                                            $set('actual_process_cost', MoneyHelper::format(round((float) $mesin->tarif_per_plate * $plateCount, 2), 2) ?? '0');
                                        }
                                    }),

                                Forms\Components\TextInput::make('actual_process_cost')
                                    ->label('Biaya Proses CTP / Solvent / Mesin (Rp)')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->default(0)
                                    ->helperText('Otomatis dari tarif mesin atau isi manual'),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\DatePicker::make('tgl_entry')
                                    ->label('Tanggal Pengerjaan Plat')
                                    ->default(now()),

                                Forms\Components\TextInput::make('hasil_baik')
                                    ->label('Hasil Plate Baik (Lembar)')
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\TextInput::make('hasil_rusak')
                                    ->label('Kerusakan Plate Cetak (Afkir/Rusak)')
                                    ->numeric()
                                    ->default(0),
                            ]),

                            Forms\Components\Textarea::make('catatan_kerusakan_plate')
                                ->label('Catatan Kerusakan Plate Cetak')
                                ->placeholder('Penyebab kerusakan plate (misal: baret, over-burn, revisi teks dadakan)...')
                                ->rows(2),

                            // ================================================
                            // REPEATER PEMAKAIAN BAHAN MENTAH PLATE (POTONG STOK)
                            // ================================================
                            Forms\Components\Section::make('Pemakaian Bahan Mentah Plate (Potong Stok Prepress — Fraksi Sheet / Utuh)')
                                ->description('Tambahkan bahan mentah plate yang digunakan untuk produksi atau remake rusak. Luas cm² & fraksi sheet dihitung otomatis namun dapat disesuaikan manual ke sheet utuh (misal 1.00). Simpan lalu klik "Posting Potong Stok" untuk memotong saldo stok prepress & mencatat Ledger OUT.')
                                ->schema([
                                    Forms\Components\Repeater::make('materialUsages')
                                        ->relationship('materialUsages')
                                        ->label('Daftar Pemakaian Material Plate')
                                        ->addActionLabel('+ Tambah Pemakaian Material Plate')
                                        ->defaultItems(0)
                                        ->collapsible()
                                        ->itemLabel(function (array $state): ?string {
                                            $plateName = ! empty($state['master_material_plate_id'])
                                                ? (MasterMaterialPlate::find($state['master_material_plate_id'])?->nama_material ?? 'Material Plate')
                                                : 'Item Pemakaian Baru';
                                            $qty = number_format((float) ($state['qty_sheet_terpakai'] ?? 0), 4, '.', ',');
                                            $biaya = MoneyHelper::format($state['total_biaya'] ?? 0, 2) ?? '0';
                                            $status = strtoupper((string) ($state['status'] ?? 'draft'));

                                            return "[{$status}] {$plateName} — {$qty} Sheet (Rp {$biaya})";
                                        })
                                        ->deleteAction(
                                            fn (Forms\Components\Actions\Action $action) => $action
                                                ->hidden(function (array $arguments, Forms\Components\Repeater $component): bool {
                                                    $itemData = $component->getItemState($arguments['item']);

                                                    return ($itemData['status'] ?? 'draft') === 'posted';
                                                })
                                        )
                                        ->schema([
                                            Forms\Components\Hidden::make('status')->default('draft'),

                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\Select::make('master_material_plate_id')
                                                    ->label('Material Plate Mentah')
                                                    ->options(function () {
                                                        return MasterMaterialPlate::with('stock')
                                                            ->where('status', 'aktif')
                                                            ->orderBy('nama_material')
                                                            ->get()
                                                            ->mapWithKeys(function (MasterMaterialPlate $p) {
                                                                $stok = number_format((float) ($p->stock?->stok_sheet_utuh ?? 0), 2, '.', ',');
                                                                $harga = (float) ($p->stock?->harga_rata_rata ?? 0) > 0
                                                                    ? (float) $p->stock->harga_rata_rata
                                                                    : (float) $p->harga_beli;

                                                                return [
                                                                    $p->id => "{$p->kode_material} · {$p->nama_material} ({$p->ukuran_sheet} | Stok: {$stok} sh | Rp " . number_format($harga, 0, ',', '.') . '/sh)',
                                                                ];
                                                            });
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft')
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateUsageRow($get, $set, true))
                                                    ->columnSpan(2),

                                                Forms\Components\Select::make('jenis_pemakaian')
                                                    ->label('Jenis Pemakaian')
                                                    ->options([
                                                        'produksi'     => 'Produksi Plate Normal',
                                                        'remake_rusak' => 'Remake / Ganti Plate Rusak',
                                                        'prove'        => 'Keperluan Prove Print',
                                                    ])
                                                    ->default('produksi')
                                                    ->required()
                                                    ->live()
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft'),
                                            ]),

                                            Forms\Components\Grid::make(4)->schema([
                                                Forms\Components\TextInput::make('jumlah_plate')
                                                    ->label('Jml Plate (Warna)')
                                                    ->numeric()
                                                    ->default(4)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft')
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateUsageRow($get, $set, true)),

                                                Forms\Components\TextInput::make('panjang_potong_mm')
                                                    ->label('Panjang Potong (mm)')
                                                    ->numeric()
                                                    ->default(432.8)
                                                    ->helperText('Repeat + margin')
                                                    ->live(onBlur: true)
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft')
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateUsageRow($get, $set, true)),

                                                Forms\Components\TextInput::make('lebar_potong_mm')
                                                    ->label('Lebar Potong (mm)')
                                                    ->numeric()
                                                    ->default(280)
                                                    ->helperText('Lebar web + margin')
                                                    ->live(onBlur: true)
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft')
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateUsageRow($get, $set, true)),

                                                Forms\Components\TextInput::make('luas_terpakai_cm2')
                                                    ->label('Total Luas (cm²)')
                                                    ->numeric()
                                                    ->readOnly()
                                                    ->dehydrated(),
                                            ]),

                                            Forms\Components\Grid::make(3)->schema([
                                                Forms\Components\TextInput::make('qty_sheet_terpakai')
                                                    ->label('Qty Sheet Dipotong (Desimal / Utuh)')
                                                    ->numeric()
                                                    ->step('0.0001')
                                                    ->required()
                                                    ->helperText('Otomatis dari luas cm²; ubah manual misal ke 1.00 jika potong 1 sheet penuh')
                                                    ->live(onBlur: true)
                                                    ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft')
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateUsageRow($get, $set, false)),

                                                Forms\Components\TextInput::make('harga_satuan_snapshot')
                                                    ->label('Harga Rata-rata / Sheet (Rp)')
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                    ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                    ->dehydrated(),

                                                Forms\Components\TextInput::make('total_biaya')
                                                    ->label('Total Biaya Material (Rp)')
                                                    ->prefix('Rp')
                                                    ->readOnly()
                                                    ->formatStateUsing(fn ($state) => MoneyHelper::format($state, 2) ?? '0')
                                                    ->dehydrateStateUsing(fn ($state) => MoneyHelper::clean($state) ?? 0)
                                                    ->dehydrated(),
                                            ]),

                                            Forms\Components\TextInput::make('alasan_kerusakan')
                                                ->label('Alasan Remake / Kerusakan Plate')
                                                ->placeholder('Contoh: Plate warna Cyan baret saat cuci / revisi teks customer')
                                                ->visible(fn (Get $get) => $get('jenis_pemakaian') === 'remake_rusak')
                                                ->disabled(fn (Get $get) => ($get('status') ?? 'draft') !== 'draft'),
                                        ])
                                        ->columnSpanFull(),
                                ]),

                            // Kartu Ringkasan Actual Cost & Variance
                            Forms\Components\Placeholder::make('cost_variance_summary')
                                ->label('Ringkasan Actual Costing & Variance vs Estimasi Order Confirmation')
                                ->content(function (?PracetakProcess $record, Get $get) {
                                    $est = (float) (MoneyHelper::clean($get('estimasi_biaya_plate')) ?? $record?->estimasi_biaya_plate ?? 0);
                                    $mat = (float) ($record?->actual_material_cost ?? 0);
                                    $proc = (float) (MoneyHelper::clean($get('actual_process_cost')) ?? $record?->actual_process_cost ?? 0);
                                    $des = (float) (MoneyHelper::clean($get('actual_design_cost')) ?? $record?->actual_design_cost ?? 0);
                                    $prv = (float) (MoneyHelper::clean($get('actual_prove_cost')) ?? $record?->actual_prove_cost ?? 0);
                                    $actualTotal = $mat + $proc + $des + $prv;
                                    $variance = $est > 0 ? ($est - $actualTotal) : 0;
                                    $varColor = $variance >= 0 ? '#10b981' : '#ef4444';
                                    $varLabel = $est <= 0 ? 'Belum ada acuan OC' : ($variance >= 0 ? 'Hemat / Dalam Budget' : 'Overbudget (Cek Remake/Waste)');

                                    return new HtmlString('
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; padding: 12px; border-radius: 10px; border: 1px solid rgba(156,163,175,0.25); background: rgba(156,163,175,0.06);">
                                            <div>
                                                <div style="font-size: 11px; color: #6b7280;">Estimasi Budget OC</div>
                                                <div style="font-size: 15px; font-weight: 700; color: #3b82f6;">Rp ' . (MoneyHelper::format($est, 2) ?? '0') . '</div>
                                            </div>
                                            <div>
                                                <div style="font-size: 11px; color: #6b7280;">Material Plate Posted (OUT)</div>
                                                <div style="font-size: 15px; font-weight: 700;">Rp ' . (MoneyHelper::format($mat, 2) ?? '0') . '</div>
                                            </div>
                                            <div>
                                                <div style="font-size: 11px; color: #6b7280;">Total Biaya Aktual Plate</div>
                                                <div style="font-size: 15px; font-weight: 700; color: #f59e0b;">Rp ' . (MoneyHelper::format($actualTotal, 2) ?? '0') . '</div>
                                            </div>
                                            <div>
                                                <div style="font-size: 11px; color: #6b7280;">Variance (' . e($varLabel) . ')</div>
                                                <div style="font-size: 15px; font-weight: 800; color: ' . $varColor . ';">' . ($variance >= 0 ? '+' : '-') . 'Rp ' . (MoneyHelper::format(abs($variance), 2) ?? '0') . '</div>
                                            </div>
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),
                        ]),

                    // ==========================================
                    // (D) PENYIMPANAN MATERIAL, ARSIP & SERAH TERIMA
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('D. Penyimpanan & Serah Terima')
                        ->icon('heroicon-o-archive-box')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('lokasi_simpan_plate')
                                    ->label('Lokasi Rak Penyimpanan Plate')
                                    ->placeholder('Contoh: Rak A-03, Box Arsip 12 (untuk Repeat Order)'),

                                Forms\Components\TextInput::make('toleransi_rusak_hilang')
                                    ->label('Toleransi Rusak / Hilang')
                                    ->placeholder('Contoh: Maksimal 1 plate toleransi per 100.000 cetak'),
                            ]),

                            Forms\Components\Textarea::make('material_pendukung')
                                ->label('Penyimpanan Plate, Arsip, dan Material Pendukung (Finishing)')
                                ->placeholder('Catatan lokasi penyimpanan pisau pond, master film spot UV, master emboss/hotprint...')
                                ->rows(3),

                            Forms\Components\Section::make('Status Serah Terima ke Divisi Printing (1 Pintu via IMR Printing)')
                                ->description('Serah terima plate ke divisi Printing diproses secara terpusat melalui IMR Printing → Warehouse Pengeluaran Material agar seluruh pengeluaran gudang tercatat 1 pintu.')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('handed_over_to')
                                        ->label('Diserahkan Kepada (Operator / Shift Printing)')
                                        ->placeholder('Otomatis terisi saat Gudang memproses IMR Printing')
                                        ->disabled(),

                                    Forms\Components\DateTimePicker::make('handed_over_at')
                                        ->label('Waktu Serah Terima')
                                        ->disabled(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK / Pre-Order')
                    ->formatStateUsing(fn ($state, PracetakProcess $record) => $state ?: ('PRE-' . ($record->approvalForm?->form_no ?: $record->id)))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Tahapan Alur')
                    ->colors([
                        'warning' => fn ($state) => in_array($state, ['design', 'trial_design'], true),
                        'info'    => 'prove_print',
                        'primary' => 'plat_making',
                        'success' => 'selesai',
                        'gray'    => 'handed_over',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'design', 'trial_design' => 'A. Trial Design',
                        'prove_print'            => 'B. Prove Print',
                        'plat_making'            => 'C. Plate Making',
                        'selesai'                => 'D. Tersimpan di Rak',
                        'handed_over'            => 'Diserahkan Printing',
                        default                  => ucfirst((string) $state),
                    }),

                Tables\Columns\TextColumn::make('nama_plat')
                    ->label('Nama Plat')
                    ->description(fn (PracetakProcess $record) => $record->is_repeat_order ? 'Repeat Order (Arsip)' : 'Plate Baru')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('jumlah_warna_plate')
                    ->label('Warna')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} Warna" : '-'),

                Tables\Columns\TextColumn::make('hasil_baik')
                    ->label('Baik / Rusak')
                    ->formatStateUsing(fn ($state, PracetakProcess $record) => ((int) $state) . ' / ' . ((int) ($record->hasil_rusak ?? 0)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_total_cost')
                    ->label('Biaya Aktual Plate')
                    ->money('IDR')
                    ->description(function (PracetakProcess $record) {
                        if ((float) $record->estimasi_biaya_plate <= 0) {
                            return 'Est. OC: -';
                        }
                        $var = (float) $record->cost_variance;
                        $sign = $var >= 0 ? '+' : '';

                        return 'Est: Rp ' . number_format((float) $record->estimasi_biaya_plate, 0, ',', '.') . " ({$sign}" . number_format($var, 0, ',', '.') . ')';
                    })
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('lokasi_simpan_plate')
                    ->label('Lokasi Rak')
                    ->placeholder('Belum disimpan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('operator.nama_operator')
                    ->label('Operator')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tgl_entry')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'design'      => 'A. Trial Design',
                        'prove_print' => 'B. Prove Print',
                        'plat_making' => 'C. Plate Making',
                        'selesai'     => 'D. Tersimpan di Rak',
                        'handed_over' => 'Diserahkan ke Printing',
                    ]),

                Tables\Filters\TernaryFilter::make('is_repeat_order')
                    ->label('Tipe Order')
                    ->trueLabel('Repeat Order (Arsip)')
                    ->falseLabel('Plate Baru'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toProve')
                        ->label('Lanjut ke B. Prove Print')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->visible(fn (PracetakProcess $record) => in_array($record->status, ['design', 'trial_design'], true))
                        ->action(function (PracetakProcess $record) {
                            $record->update(['status' => 'prove_print']);
                            Notification::make()->title('Berpindah ke Tahap B: Prove Print')->info()->send();
                        }),

                    Tables\Actions\Action::make('toPlat')
                        ->label('Lanjut ke C. Plate Making')
                        ->icon('heroicon-o-cpu-chip')
                        ->color('primary')
                        ->visible(fn (PracetakProcess $record) => in_array($record->status, ['design', 'trial_design', 'prove_print'], true))
                        ->action(function (PracetakProcess $record) {
                            if (! $record->spk_id) {
                                Notification::make()
                                    ->title('SPK Belum Terhubung')
                                    ->body('Hubungkan Nomor SPK terlebih dahulu pada form Edit sebelum memulai Tahap C (Plate Making & Potong Stok).')
                                    ->warning()
                                    ->send();

                                return;
                            }
                            $record->update(['status' => 'plat_making']);
                            Notification::make()->title('Berpindah ke Tahap C: Plate Making')->success()->send();
                        }),

                    Tables\Actions\Action::make('postUsages')
                        ->label('Posting Potong Stok Plate')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Posting Pemakaian Material Plate (OUT)')
                        ->modalDescription('Seluruh baris pemakaian material plate berstatus Draft pada proses ini akan memotong saldo Stok Material Plate Prepress dan dicatat permanen ke Histori Material Plate (Ledger OUT). Lanjutkan?')
                        ->visible(fn (PracetakProcess $record) => $record->has_unposted_usages)
                        ->action(function (PracetakProcess $record) {
                            try {
                                $count = app(PracetakStockService::class)->postAllDraftUsages($record, auth()->id());
                                Notification::make()
                                    ->title("Berhasil Posting {$count} Baris Pemakaian Plate")
                                    ->body('Stok Material Plate telah dipotong otomatis dan biaya aktual telah diperbarui.')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Gagal Posting Pemakaian Plate')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('toSelesai')
                        ->label('Selesai & Simpan ke Rak')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (PracetakProcess $record) => $record->status === 'plat_making')
                        ->form([
                            Forms\Components\TextInput::make('hasil_baik')
                                ->label('Hasil Plate Baik (Lembar)')
                                ->numeric()
                                ->default(fn (PracetakProcess $record) => $record->hasil_baik ?: ($record->jumlah_warna_plate ?: 4))
                                ->required(),

                            Forms\Components\TextInput::make('hasil_rusak')
                                ->label('Kerusakan Plate Cetak (Rusak/Afkir)')
                                ->numeric()
                                ->default(fn (PracetakProcess $record) => $record->hasil_rusak ?: 0)
                                ->required(),

                            Forms\Components\TextInput::make('lokasi_simpan_plate')
                                ->label('Lokasi Rak Penyimpanan Plate')
                                ->placeholder('Contoh: Rak A-02')
                                ->default(fn (PracetakProcess $record) => $record->lokasi_simpan_plate)
                                ->required(),

                            Forms\Components\DatePicker::make('tgl_entry')
                                ->label('Tanggal Selesai')
                                ->default(now())
                                ->required(),

                            Forms\Components\Select::make('operator_id')
                                ->label('Operator Pra Cetak')
                                ->relationship('operator', 'nama_operator')
                                ->default(fn (PracetakProcess $record) => $record->operator_id)
                                ->required(),

                            Forms\Components\Select::make('mesin_id')
                                ->label('Mesin CTP')
                                ->relationship('mesin', 'nama_mesin')
                                ->default(fn (PracetakProcess $record) => $record->mesin_id)
                                ->required(),
                        ])
                        ->action(function (PracetakProcess $record, array $data) {
                            $record->update([
                                ...$data,
                                'status' => 'selesai',
                            ]);

                            $costing = app(PracetakCostingService::class);
                            $costing->recalculateProcessCost($record);
                            $archive = $costing->syncPlateArchive($record->fresh());

                            Notification::make()
                                ->title('Plate Selesai & Terdaftar di Arsip Rak')
                                ->body("Arsip {$archive->kode_arsip} tersimpan di {$archive->lokasi_rak} dan siap digunakan untuk Printing maupun Repeat Order.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('createImrForHandover')
                        ->label('Ajukan IMR Printing (Serah Terima)')
                        ->icon('heroicon-o-document-text')
                        ->color('primary')
                        ->visible(fn (PracetakProcess $record) => $record->status === 'selesai' && filled($record->spk_id))
                        ->url(fn (PracetakProcess $record) => IMRPrintingResource::getUrl('create', ['spk_id' => $record->spk_id])),

                    Tables\Actions\Action::make('detailKerusakan')
                        ->label('Detail Kerusakan & Biaya Plate')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->modalHeading(fn (PracetakProcess $record) => 'Detail Kerusakan & Biaya Plate — ' . ($record->nama_plat ?: 'Pra Cetak'))
                        ->modalWidth('3xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(function (PracetakProcess $record) {
                            $summary = app(PracetakCostingService::class)->calculateProcessDamageSummary($record);

                            return view('filament.pracetak.damage-detail-modal', [
                                'process' => $record,
                                'summary' => $summary,
                            ]);
                        }),

                    Tables\Actions\EditAction::make(),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('primary')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['spk', 'approvalForm', 'operator', 'mesin', 'materialUsages.masterPlate', 'generatedArchive', 'plateArchive']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPracetakProcesses::route('/'),
            'create' => Pages\CreatePracetakProcess::route('/create'),
            'edit'   => Pages\EditPracetakProcess::route('/{record}/edit'),
        ];
    }
}
