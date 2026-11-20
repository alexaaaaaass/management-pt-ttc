<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakProcessResource\Pages;
use App\Models\MasterSpk;
use App\Models\PracetakProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PracetakProcessResource extends Resource
{
    protected static ?string $model = PracetakProcess::class;
    protected static ?string $navigationGroup = 'Pra Cetak';
    protected static ?string $navigationLabel = 'Pra Cetak';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralLabel = 'Proses Pra Cetak';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('AlurPraCetak')
                ->tabs([
                    // ==========================================
                    // 1. DATA SPK & DETAIL PESANAN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Data SPK & Pesanan')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('spk_id')
                                    ->label('Nomor SPK')
                                    ->relationship('spk', 'no_spk')
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $spk = MasterSpk::with([
                                            'salesOrder.customer',
                                            'salesOrder.itemable.materials.item',
                                            'salesOrder.itemable.materials.departemen'
                                        ])->find($state);

                                        if (!$spk || !$spk->salesOrder || !$spk->salesOrder->itemable) {
                                            $set('materials_preview', []);
                                            return;
                                        }

                                        $materials = $spk->salesOrder->itemable->materials ?? collect();
                                        $data = $materials->map(function ($mat) use ($spk) {
                                            $qtySo = $spk->salesOrder->qty ?? 0;
                                            return [
                                                'nama_material' => $mat->item->nama_master_item ?? '-',
                                                'qty' => $qtySo * $mat->qty,
                                                'departemen' => $mat->departemen->nama_departemen ?? '-',
                                            ];
                                        })->toArray();

                                        $set('materials_preview', $data);
                                    })
                                    ->required(),

                                Forms\Components\Select::make('ket_spk')
                                    ->label('Jenis Pekerjaan SPK')
                                    ->options([
                                        'reguler' => 'Reguler (Internal)',
                                        'subcount' => 'Subcount (Pekerjaan Luar)',
                                    ])
                                    ->default('reguler')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('nama_plat')
                                    ->label('Nama / Judul Plat')
                                    ->placeholder('Contoh: Box Kemasan 250gr - Depan & Belakang')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Target Qty Plat (Lembar)')
                                    ->placeholder('Jumlah lembar plat')
                                    ->numeric()
                                    ->default(4)
                                    ->required(),
                            ]),

                            Forms\Components\Select::make('status')
                                ->label('Status Tahapan Pra Cetak')
                                ->options([
                                    'design' => 'A. Trial Design (Pengecekan File)',
                                    'prove_print' => 'B. Prove Print (Sampel Cetak)',
                                    'plat_making' => 'C. Plate Making (CTP)',
                                    'selesai' => 'D. Selesai & Tersimpan di Rak',
                                ])
                                ->default('design')
                                ->required(),

                            // Panel Detail Sales Order
                            Forms\Components\Section::make('Detail Sales Order & Spesifikasi Produk')
                                ->extraAttributes([
                                    'style' => 'border-left: 5px solid #3b82f6; border-radius: 10px; padding: 12px; background-color: rgba(59, 130, 246, 0.05);',
                                ])
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Grid::make(1)->schema([
                                            Forms\Components\Placeholder::make('no_so')
                                                ->label('No. Sales Order')
                                                ->content(fn ($get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->no_sales_order ?? '-'),

                                            Forms\Components\Placeholder::make('no_po')
                                                ->label('No. PO Customer')
                                                ->content(fn ($get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->no_po_customer ?? '-'),

                                            Forms\Components\Placeholder::make('customer')
                                                ->label('Customer')
                                                ->content(function ($get) {
                                                    $spk = MasterSpk::with('salesOrder.customer')->find($get('spk_id'));
                                                    return $spk?->salesOrder?->customer?->nama_customer ?? '-';
                                                }),

                                            Forms\Components\Placeholder::make('nama_barang')
                                                ->label('Nama Barang')
                                                ->content(function ($get) {
                                                    $spk = MasterSpk::with('salesOrder.itemable')->find($get('spk_id'));
                                                    return $spk?->salesOrder?->itemable?->nama_barang
                                                        ?? $spk?->salesOrder?->itemable?->nama_master_item
                                                        ?? '-';
                                                }),
                                        ]),

                                        Forms\Components\Grid::make(1)->schema([
                                            Forms\Components\Placeholder::make('qty_order')
                                                ->label('Jumlah Pesanan Cetak')
                                                ->content(fn ($get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->qty ?? '-'),

                                            Forms\Components\Placeholder::make('toleransi')
                                                ->label('Toleransi Pengiriman')
                                                ->content(function ($get) {
                                                    $spk = MasterSpk::with('salesOrder')->find($get('spk_id'));
                                                    return $spk?->salesOrder?->toleransi_pengiriman ? $spk->salesOrder->toleransi_pengiriman . ' %' : '-';
                                                }),

                                            Forms\Components\Placeholder::make('tipe')
                                                ->label('Tipe Pesanan')
                                                ->content(fn ($get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->tipe_pesanan ?? '-'),

                                            Forms\Components\Placeholder::make('tanggal')
                                                ->label('Tanggal Order')
                                                ->content(fn ($get) => optional(MasterSpk::with('salesOrder')->find($get('spk_id')))?->salesOrder?->tanggal_po ?? '-'),
                                        ]),
                                    ]),
                                ])
                                ->visible(fn ($get) => filled($get('spk_id'))),
                        ]),

                    // ==========================================
                    // (A) TRIAL DESIGN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('A. Trial Design')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('status_file')
                                    ->label('Ketersediaan File Desain')
                                    ->options([
                                        'ada' => '✅ Ada File Desain',
                                        'tidak_ada' => '❌ Belum Ada File',
                                    ])
                                    ->default('ada'),

                                Forms\Components\Select::make('status_kesiapan_file')
                                    ->label('Kesiapan File Siap Cetak')
                                    ->options([
                                        'siap_cetak' => '✅ File Siap Cetak (Layout OK)',
                                        'perlu_edit' => '⚠️ Perlu Edit / Penyesuaian Bleed & Margin',
                                        'revisi_customer' => '🔄 Menunggu Revisi dari Customer',
                                    ])
                                    ->default('siap_cetak'),
                            ]),

                            Forms\Components\TextInput::make('toleransi_hasil')
                                ->label('Toleransi Hasil Desain')
                                ->placeholder('Contoh: Bleed 3mm, margin potong 5mm, warna CMYK convert OK, font outlined'),

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
                                ->reactive(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('bahan_prove_print')
                                    ->label('Print di Bahan Tertentu')
                                    ->placeholder('Contoh: Art Paper 150g, Duplex 310g, Samson Kraft, HVS 80g')
                                    ->visible(fn ($get) => (bool) $get('is_prove_print')),

                                Forms\Components\Select::make('status_prove_print')
                                    ->label('Status Approval Prove Print')
                                    ->options([
                                        'menunggu_acc' => '⏳ Menunggu ACC / Approval',
                                        'acc' => '✅ Sudah ACC (Disetujui Naik Cetak)',
                                        'revisi' => '🔄 Revisi Proofing / Warna Kurang Pas',
                                    ])
                                    ->default('menunggu_acc')
                                    ->visible(fn ($get) => (bool) $get('is_prove_print')),
                            ]),
                        ]),

                    // ==========================================
                    // (C) PLATE MAKING
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('C. Plate Making')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Forms\Components\TextInput::make('jumlah_warna_plate')
                                ->label('Jumlah Warna Plate')
                                ->numeric()
                                ->default(4)
                                ->helperText('Contoh: 4 warna (CMYK), 1 warna (Black saja), atau 5 warna (+ Spot Color)'),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('operator_id')
                                    ->label('Operator Pra Cetak')
                                    ->relationship('operator', 'nama_operator')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('mesin_id')
                                    ->label('Mesin Pra Cetak / Output CTP')
                                    ->relationship('mesin', 'nama_mesin')
                                    ->searchable()
                                    ->preload(),
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
                                ->placeholder('Penyebab kerusakan plate (misal: baret, over-burn, ganti revisi teks dadakan)...')
                                ->rows(2),
                        ]),

                    // ==========================================
                    // (D) PENYIMPANAN MATERIAL & ARSIP
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('D. Penyimpanan & Arsip')
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
                    ->label('No SPK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Tahapan Alur')
                    ->colors([
                        'warning' => 'design',
                        'info'    => 'prove_print',
                        'primary' => 'plat_making',
                        'success' => 'selesai',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'design' => 'A. Trial Design',
                        'prove_print' => 'B. Prove Print',
                        'plat_making' => 'C. Plate Making',
                        'selesai' => 'D. Selesai & Simpan',
                        default => ucfirst((string) $state),
                    }),

                Tables\Columns\TextColumn::make('nama_plat')
                    ->label('Nama Plat')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jumlah_warna_plate')
                    ->label('Jml Warna')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} Warna" : '-'),

                Tables\Columns\TextColumn::make('hasil_baik')
                    ->label('Baik (Lbr)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hasil_rusak')
                    ->label('Rusak (Lbr)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lokasi_simpan_plate')
                    ->label('Lokasi Rak')
                    ->placeholder('Belum disimpan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('operator.nama_operator')
                    ->label('Operator')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('tgl_entry')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                // Quick Action Alur
                Tables\Actions\Action::make('toProve')
                    ->label('Ke Prove Print')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'design')
                    ->action(fn ($record) => $record->update(['status' => 'prove_print'])),

                Tables\Actions\Action::make('toPlat')
                    ->label('Ke Plate Making')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('primary')
                    ->visible(fn ($record) => in_array($record->status, ['design', 'prove_print']))
                    ->action(fn ($record) => $record->update(['status' => 'plat_making'])),

                Tables\Actions\Action::make('toSelesai')
                    ->label('Selesai & Simpan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'plat_making')
                    ->form([
                        Forms\Components\TextInput::make('hasil_baik')
                            ->label('Hasil Plate Baik (Lembar)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('hasil_rusak')
                            ->label('Kerusakan Plate Cetak')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('lokasi_simpan_plate')
                            ->label('Lokasi Rak Penyimpanan Plate')
                            ->placeholder('Contoh: Rak A-02')
                            ->required(),

                        Forms\Components\DatePicker::make('tgl_entry')
                            ->label('Tanggal Selesai')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('operator_id')
                            ->label('Operator Pra Cetak')
                            ->relationship('operator', 'nama_operator')
                            ->required(),

                        Forms\Components\Select::make('mesin_id')
                            ->label('Mesin CTP')
                            ->relationship('mesin', 'nama_mesin')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            ...$data,
                            'status' => 'selesai',
                        ]);
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPracetakProcesses::route('/'),
            'create' => Pages\CreatePracetakProcess::route('/create'),
            'edit' => Pages\EditPracetakProcess::route('/{record}/edit'),
        ];
    }
}
