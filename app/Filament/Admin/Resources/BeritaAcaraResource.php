<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BeritaAcaraResource\Pages;
use App\Models\BeritaAcara;
use App\Models\Invoice;
use App\Models\SuratJalan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BeritaAcaraResource extends Resource
{
    protected static ?string $model = BeritaAcara::class;

    protected static ?string $navigationGroup = 'Dispatch';

    protected static ?string $navigationLabel = 'Berita Acara Complaint';

    protected static ?string $modelLabel = 'Berita Acara';

    protected static ?string $pluralModelLabel = 'Berita Acara';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Complaint')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('no_ba')
                            ->label('No. BA')
                            ->default(fn () => BeritaAcara::generateNoBA())
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('tipe')
                            ->options([
                                'complaint_produksi' => 'Complaint Produksi',
                                'rapat' => 'Rapat',
                            ])
                            ->default('complaint_produksi')
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('surat_jalan_id')
                            ->label('Surat Jalan')
                            ->relationship('suratJalan', 'kode_surat_jalan')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => self::syncFromSuratJalan($state, $set)),
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'kode_invoice')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => self::syncFromInvoice($state, $set)),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'nama_customer')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('no_surat_jalan_invoice')
                            ->label('Referensi Dokumen')
                            ->helperText('Terisi otomatis dari Surat Jalan/Invoice yang dipilih.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('item_product')
                            ->label('Item / Produk')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'proses' => 'Proses',
                                'selesai' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\FileUpload::make('foto_bukti')
                            ->label('Foto Bukti')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->disk('public')
                            ->directory('berita-acara')
                            ->visibility('public')
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Rincian Barang dan Penolakan')
                    ->description('Catat jumlah barang dan bukti untuk setiap item yang ditolak.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('Rincian Item')
                            ->orderColumn('urutan')
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->collapsible()
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('item_product')
                                    ->label('Item')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('qty_kedatangan')
                                    ->label('Kedatangan')
                                    ->quantityMask(2)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('qty_penerimaan')
                                    ->label('Penerimaan')
                                    ->quantityMask(2)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('qty_penolakan')
                                    ->label('Penolakan')
                                    ->quantityMask(2)
                                    ->minValue(0),
                                Forms\Components\Textarea::make('catatan_penerimaan')
                                    ->label('Catatan Penerimaan / Rejection')
                                    ->rows(3)
                                    ->columnSpan(2),
                                Forms\Components\FileUpload::make('foto_referensi')
                                    ->label('Foto Referensi')
                                    ->image()
                                    ->disk('public')
                                    ->directory('berita-acara/referensi')
                                    ->visibility('public')
                                    ->openable(),
                                Forms\Components\FileUpload::make('foto_fisik')
                                    ->label('Foto Fisik Datang')
                                    ->image()
                                    ->disk('public')
                                    ->directory('berita-acara/fisik')
                                    ->visibility('public')
                                    ->openable(),
                            ]),
                    ]),
                Forms\Components\Section::make('Kronologi dan Penanganan Solusi')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('kronologi')
                            ->label('Kronologi Komplain')
                            ->rows(3)
                            ->placeholder('Jelaskan kronologi komplain dari pengiriman hingga diterima...'),
                        Forms\Components\Textarea::make('analisa_produksi')
                            ->label('Analisa Produksi / Akar Masalah')
                            ->rows(3)
                            ->placeholder('Analisa tim QC/teknis mengenai penyebab masalah...'),
                        Forms\Components\Select::make('jenis_penyelesaian')
                            ->label('Pilihan Solusi / Tindakan')
                            ->options([
                                'sortir_ulang'   => '🔍 Sortir Ulang / Perbaikan di Pabrik (Sorting & Touch Up)',
                                'ganti_barang'   => '🔄 Cetak Ulang / Ganti Barang Baru (Reprint / Rework)',
                                'potong_tagihan' => '💰 Potong Tagihan / Credit Note (Bayar Sesuai Penerimaan Saja)',
                                'retur_total'    => '📦 Retur Total (Batal Pengiriman / Kembali Semua)',
                                'lainnya'        => '📝 Lainnya / Kesepakatan Khusus',
                            ])
                            ->placeholder('-- Pilih Bentuk Penyelesaian --')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('tindakan_solusi')
                            ->label('Rincian Tindakan / Solusi')
                            ->rows(3)
                            ->placeholder('Penjelasan rincian solusi (jadwal kirim ulang, komitmen diskon invoice, dll)...'),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan penting lainnya bila ada...'),
                    ]),
                Forms\Components\Section::make('Rapat dan Persetujuan')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('peserta')->rows(3),
                        Forms\Components\Textarea::make('agenda')->rows(3),
                        Forms\Components\TextInput::make('ttd_qc')->label('Tanda Tangan QC')->maxLength(255),
                        Forms\Components\TextInput::make('ttd_customer')->label('Tanda Tangan Customer')->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_ba')
                    ->label('No. BA')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.nama_customer')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('suratJalan.kode_surat_jalan')
                    ->label('Surat Jalan')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('invoice.kode_invoice')
                    ->label('Invoice')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('item_product')
                    ->label('Item / Produk')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jenis_penyelesaian')
                    ->label('Solusi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'sortir_ulang'   => 'Sortir Ulang',
                        'ganti_barang'   => 'Ganti Barang',
                        'potong_tagihan' => 'Potong Tagihan',
                        'retur_total'    => 'Retur Total',
                        'lainnya'        => 'Lainnya',
                        default          => $state ? ucfirst(str_replace('_', ' ', $state)) : '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'sortir_ulang'   => 'info',
                        'ganti_barang'   => 'warning',
                        'potong_tagihan' => 'danger',
                        'retur_total'    => 'gray',
                        default          => 'secondary',
                    })
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'selesai' => 'success',
                        'proses' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_penyelesaian')
                    ->label('Pilihan Solusi')
                    ->options([
                        'sortir_ulang'   => 'Sortir Ulang / Perbaikan',
                        'ganti_barang'   => 'Cetak Ulang / Ganti Barang',
                        'potong_tagihan' => 'Potong Tagihan (Credit Note)',
                        'retur_total'    => 'Retur Total',
                        'lainnya'        => 'Lainnya',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'proses' => 'Proses',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('print')
                        ->label('Cetak BA')
                        ->icon('heroicon-o-printer')
                        ->url(fn (BeritaAcara $record): string => route('berita-acaras.pdf', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make(),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('primary')
                    ->button()
                    ->dropdownPlacement('bottom-end'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeritaAcaras::route('/'),
            'create' => Pages\CreateBeritaAcara::route('/create'),
            'edit' => Pages\EditBeritaAcara::route('/{record}/edit'),
        ];
    }

    private static function syncFromSuratJalan($state, callable $set): void
    {
        $suratJalan = $state
            ? SuratJalan::with(['invoice', 'salesOrder.itemable', 'salesOrder.items.material', 'items.material'])->find($state)
            : null;

        $invoice = $suratJalan?->invoice;

        $set('invoice_id', $invoice?->id);
        $set('customer_id', $invoice?->customer_id ?? $suratJalan?->salesOrder?->customer_id);
        $set('no_surat_jalan_invoice', self::documentReference($suratJalan, $invoice));
        $set('item_product', self::productName($suratJalan));
    }

    private static function syncFromInvoice($state, callable $set): void
    {
        $invoice = $state
            ? Invoice::with(['suratJalan.invoice', 'suratJalan.salesOrder.itemable', 'suratJalan.salesOrder.items.material', 'suratJalan.items.material', 'salesOrder.itemable', 'salesOrder.items.material'])->find($state)
            : null;

        $suratJalan = $invoice?->suratJalan;

        $set('surat_jalan_id', $suratJalan?->id);
        $set('customer_id', $invoice?->customer_id ?? $suratJalan?->salesOrder?->customer_id ?? $invoice?->salesOrder?->customer_id);
        $set('no_surat_jalan_invoice', self::documentReference($suratJalan, $invoice));
        $set('item_product', self::productName($suratJalan) ?: ($invoice?->salesOrder?->product_name ?? ''));
    }

    private static function documentReference(?SuratJalan $suratJalan, ?Invoice $invoice): string
    {
        return collect([$suratJalan?->kode_surat_jalan, $invoice?->kode_invoice])
            ->filter()
            ->unique()
            ->implode(' / ');
    }

    private static function productName(?SuratJalan $suratJalan): string
    {
        if (! $suratJalan) {
            return '';
        }

        $items = $suratJalan->items
            ->map(fn ($item) => $item->material?->nama_master_item)
            ->filter()
            ->unique();

        if ($items->isNotEmpty()) {
            return $items->implode(', ');
        }

        return $suratJalan->salesOrder?->product_name ?? '';
    }
}
