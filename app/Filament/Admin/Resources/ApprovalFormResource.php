<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApprovalFormResource\Pages;
use App\Models\ApprovalForm;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Services\Marketing\MarketingWorkflowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApprovalFormResource extends Resource
{
    protected static ?string $model = ApprovalForm::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Approval Form';
    protected static ?string $modelLabel = 'Approval Form';
    protected static ?string $pluralModelLabel = 'Approval Forms';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Approval Form Tabs')
                ->tabs([
                    // ==========================================
                    // 📋 TAB 1: INFORMASI DOKUMEN & CUSTOMER
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Informasi Dokumen')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'nama_customer')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (!$state) return;
                                        $cust = Customer::find($state);
                                        if ($cust) {
                                            if (!$get('form_no')) {
                                                $set('form_no', ApprovalForm::generateFormNo($cust));
                                            }
                                            if (!$get('order_id')) {
                                                $set('order_id', ApprovalForm::generateOrderId($cust));
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('form_no')
                                    ->label('Form No')
                                    ->placeholder('AF-26-IV-C005-003')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('order_id')
                                    ->label('Order ID')
                                    ->placeholder('OR-26-IV-C005-003')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\TextInput::make('lead_time')
                                    ->label('Lead Time')
                                    ->placeholder('Contoh: 21 Hari Kerja'),

                                Forms\Components\Select::make('status')
                                    ->label('Status Dokumen')
                                    ->options([
                                        'draft'              => 'Draft (Penyusunan)',
                                        'reviewed_marketing' => 'Reviewed Marketing',
                                        'approved_produksi'  => 'Approved Produksi',
                                        'converted_to_oc'    => 'Converted to Order Confirmation',
                                        'cancelled'          => 'Batal',
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),
                        ]),

                    // ==========================================
                    // 📦 TAB 2: SPESIFIKASI ITEM & CHECKLIST TEKNIS
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Spesifikasi Produk & Teknis')
                        ->icon('heroicon-o-swatch')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship('items')
                                ->label('Daftar Spesifikasi Produk & Teknis')
                                ->addActionLabel('+ Tambah Spesifikasi Item')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\Select::make('finish_good_item_id')
                                            ->label('Pilih dari Finish Good (opsional)')
                                            ->relationship('finishGoodItem', 'nama_barang')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if (!$state) return;
                                                $fg = FinishGoodItem::find($state);
                                                if ($fg) {
                                                    $set('item_name', $fg->nama_barang);
                                                    $set('jenis', $fg->jenis);
                                                    $set('komponen', $fg->komponen);
                                                    $set('isi', $fg->isi);
                                                    $set('kertas', $fg->spesifikasi_kertas);
                                                    $set('warna', $fg->warna);
                                                    $set('finishing', $fg->finishing);
                                                }
                                            }),

                                        Forms\Components\TextInput::make('item_name')
                                            ->label('Nama Item')
                                            ->placeholder('Contoh: PDW88 Sliding Luar')
                                            ->required()
                                            ->columnSpan(2),
                                    ]),

                                    Forms\Components\Grid::make(4)->schema([
                                        Forms\Components\TextInput::make('jenis')
                                            ->label('Jenis')
                                            ->placeholder('Contoh: SKT / SKM'),

                                        Forms\Components\TextInput::make('komponen')
                                            ->label('Komponen')
                                            ->placeholder('Contoh: Etiket / Inner / Sliding'),

                                        Forms\Components\TextInput::make('isi')
                                            ->label('Isi (Batang)')
                                            ->placeholder('Contoh: 20'),

                                        Forms\Components\TextInput::make('kertas')
                                            ->label('Kertas')
                                            ->placeholder('Contoh: Metalize / Ivory'),
                                    ]),

                                    Forms\Components\Grid::make(4)->schema([
                                        Forms\Components\TextInput::make('warna')
                                            ->label('Warna')
                                            ->placeholder('Contoh: 5'),

                                        Forms\Components\TextInput::make('finishing')
                                            ->label('Finishing')
                                            ->placeholder('Spot UV, Emboss, Lem, Cutting')
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('qty')
                                            ->label('Qty (Pcs)')
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                    ]),

                                    Forms\Components\Section::make('Checklist Kesiapan Teknis')
                                        ->description('Centang checklist internal berikut sebelum ACC produksi')
                                        ->schema([
                                            Forms\Components\Grid::make(4)->schema([
                                                Forms\Components\Checkbox::make('cek_desain')
                                                    ->label('1. Desain Siap'),
                                                Forms\Components\Checkbox::make('cek_plate')
                                                    ->label('2. Plate Siap'),
                                                Forms\Components\Checkbox::make('cek_produksi')
                                                    ->label('3. Produksi Siap'),
                                                Forms\Components\Checkbox::make('cek_qc')
                                                    ->label('4. QC Pass'),
                                            ]),
                                        ])
                                        ->compact(),
                                ])
                                ->columnSpanFull()
                                ->defaultItems(1),
                        ]),

                    // ==========================================
                    // ✍️ TAB 3: CATATAN & PERSETUJUAN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Catatan & Persetujuan')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            Forms\Components\Textarea::make('catatan_produksi')
                                ->label('Catatan Produksi')
                                ->rows(4)
                                ->default("Perhatikan warna cetak\nFinishing harus Presisi\nQC Sebelum pengemasan")
                                ->columnSpanFull(),

                            Forms\Components\FileUpload::make('file_design')
                                ->label('Upload Foto / Mockup Desain Etiket (Opsional)')
                                ->helperText('Unggah foto/desain prove cetak dari customer jika ada. Gambar ini akan otomatis muncul pada lembar cetak Approval Form.')
                                ->image()
                                ->directory('approval-forms/designs')
                                ->disk('public')
                                ->maxSize(10240)
                                ->imagePreviewHeight('250')
                                ->downloadable()
                                ->openable()
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('disetujui_marketing')
                                    ->label('Disetujui Marketing (Nama Terang)')
                                    ->placeholder('Nama Penanggung Jawab Marketing'),

                                Forms\Components\TextInput::make('disetujui_produksi')
                                    ->label('Disetujui Produksi (Nama Terang)')
                                    ->placeholder('Nama Penanggung Jawab Produksi'),
                            ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('form_no')
                    ->label('Form No')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.nama_customer')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jml Item')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('bom_status')
                    ->label('Kesiapan BOM')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'complete' => 'success',
                        'partial'  => 'info',
                        'pending'  => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'complete' => 'BOM LENGKAP',
                        'partial'  => 'BOM SEBAGIAN',
                        'pending'  => 'BOM BELUM DIATUR',
                        default    => 'BELUM ADA ITEM',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'              => 'gray',
                        'reviewed_marketing' => 'info',
                        'approved_produksi'  => 'warning',
                        'converted_to_oc'    => 'success',
                        'cancelled'          => 'danger',
                        default              => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'              => 'DRAFT',
                        'reviewed_marketing' => 'REVIEWED MARKETING',
                        'approved_produksi'  => 'APPROVED PRODUKSI',
                        'converted_to_oc'    => 'TERBIT ORDER CONFIRMATION',
                        'cancelled'          => 'BATAL',
                        default              => strtoupper($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'              => 'Draft',
                        'reviewed_marketing' => 'Reviewed Marketing',
                        'approved_produksi'  => 'Approved Produksi',
                        'converted_to_oc'    => 'Converted to OC',
                        'cancelled'          => 'Batal',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (ApprovalForm $record) => route('approval-form.preview', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->url(fn (ApprovalForm $record) => route('approval-form.download', $record)),

                    // 🔥 ACTION SET BOM FINISH GOOD OLEH OPERATOR / PPIC
                    Tables\Actions\Action::make('set_bom')
                        ->label('Set BOM Finish Good')
                        ->icon('heroicon-o-queue-list')
                        ->color('primary')
                        ->tooltip('Rancang atau buka Bill of Materials di Finish Good berdasarkan Approval Form ini')
                        ->action(function (ApprovalForm $record) {
                            $service = app(MarketingWorkflowService::class);
                            $firstItem = $record->items->first();

                            if (!$firstItem) {
                                Notification::make()
                                    ->title('Tidak Ada Item')
                                    ->body('Silakan tambahkan minimal 1 item spesifikasi terlebih dahulu pada Approval Form ini.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $fg = $service->createOrLinkFinishGoodFromApprovalFormItem($firstItem);

                            Notification::make()
                                ->title('Master Finish Good Siap!')
                                ->body("Produk '{$fg->nama_barang}' berhasil disiapkan. Silakan atur kebutuhan bahan baku (BOM) pada Tahap 4.")
                                ->success()
                                ->send();

                            return redirect(FinishGoodItemResource::getUrl('edit', ['record' => $fg]));
                        }),

                    Tables\Actions\Action::make('generate_oc')
                        ->label('Generate Order Confirmation')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Generate Order Confirmation')
                        ->modalDescription('Apakah Anda yakin ingin membuat Order Confirmation dari Approval Form ini? Seluruh data spesifikasi item akan otomatis di-copy.')
                        ->modalSubmitActionLabel('Ya, Terbitkan OC')
                        ->action(function (ApprovalForm $record) {
                            $service = app(MarketingWorkflowService::class);
                            $oc = $service->generateOrderConfirmationFromApprovalForm($record);

                            Notification::make()
                                ->title('Order Confirmation Berhasil Dibuat!')
                                ->body("Dokumen No: {$oc->doc_no} berhasil dibuat. Silakan lengkapi harga satuan.")
                                ->success()
                                ->send();

                            return redirect(OrderConfirmationResource::getUrl('edit', ['record' => $oc]));
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Menu Aksi'),
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
            'index'  => Pages\ListApprovalForms::route('/'),
            'create' => Pages\CreateApprovalForm::route('/create'),
            'edit'   => Pages\EditApprovalForm::route('/{record}/edit'),
        ];
    }
}
