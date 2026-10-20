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
                Forms\Components\Section::make('Kronologi dan Penanganan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('kronologi')->rows(4),
                        Forms\Components\Textarea::make('analisa_produksi')->label('Analisa Produksi')->rows(4),
                        Forms\Components\Textarea::make('tindakan_solusi')->label('Tindakan / Solusi')->rows(4),
                        Forms\Components\Textarea::make('catatan')->rows(4),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'proses' => 'Proses',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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