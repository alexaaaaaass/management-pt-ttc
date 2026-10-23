<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderConfirmationResource\Pages;
use App\Models\ApprovalForm;
use App\Models\ApprovalFormItem;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Services\Marketing\MarketingWorkflowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class OrderConfirmationResource extends Resource
{
    protected static ?string $model = OrderConfirmation::class;
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Order Confirmation';
    protected static ?string $modelLabel = 'Order Confirmation';
    protected static ?string $pluralModelLabel = 'Order Confirmations';

    public static function cleanNumeric(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        return (float) str_replace([',', ' '], '', (string) $value);
    }

    public static function formatCurrency(mixed $value): string
    {
        $num = static::cleanNumeric($value);
        return number_format($num, 0, '.', ',');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Order Confirmation Tabs')
                ->tabs([
                    // ==========================================
                    // 📋 TAB 1: INFORMASI DOKUMEN & CUSTOMER
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Informasi Dokumen')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Select::make('approval_form_id')
                                    ->label('Referensi Approval Form (Opsional)')
                                    ->relationship('approvalForm', 'form_no')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (!$state) return;
                                        $af = ApprovalForm::with(['customer', 'items.finishGoodItem'])->find($state);
                                        if ($af) {
                                            $set('customer_id', $af->customer_id);
                                            $set('order_id', $af->order_id);
                                            $set('lead_time', $af->lead_time ?: '21 Hari Kerja');
                                            if (!$get('doc_no') && $af->customer) {
                                                $set('doc_no', OrderConfirmation::generateDocNo($af->customer));
                                            }
                                            if ($af->customer) {
                                                $set('disetujui_customer', $af->customer->nama_customer);
                                            }

                                            // Otomatis tarik semua item pesanan dari Approval Form beserta Qty Pcs yang dipesan
                                            if ($af->items && $af->items->isNotEmpty()) {
                                                $newItems = [];
                                                foreach ($af->items as $item) {
                                                    $fgId = $item->finish_good_item_id;
                                                    if (!$fgId) {
                                                        $fgId = FinishGoodItem::where('nama_barang', $item->item_name)
                                                            ->where('customer_id', $af->customer_id)
                                                            ->value('id');
                                                    }

                                                    $newItems[] = [
                                                        'approval_form_item_id' => $item->id,
                                                        'finish_good_item_id'   => $fgId,
                                                        'item_name'             => $item->item_name,
                                                        'jenis'                 => $item->jenis,
                                                        'komponen'              => $item->komponen,
                                                        'isi'                   => $item->isi,
                                                        'kertas'                => $item->kertas,
                                                        'warna'                 => $item->warna,
                                                        'finishing'             => $item->finishing,
                                                        'qty'                   => (float) $item->qty,
                                                        'harga'                 => 0,
                                                        'total'                 => 0,
                                                        'satuan'                => $item->satuan ?: 'Pcs',
                                                    ];
                                                }
                                                $set('items', $newItems);
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'nama_customer')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (!$state) return;
                                        $cust = Customer::find($state);
                                        if ($cust) {
                                            if (!$get('doc_no')) {
                                                $set('doc_no', OrderConfirmation::generateDocNo($cust));
                                            }
                                            if (!$get('order_id')) {
                                                $set('order_id', ApprovalForm::generateOrderId($cust));
                                            }
                                            if (!$get('disetujui_customer')) {
                                                $set('disetujui_customer', $cust->nama_customer);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('doc_no')
                                    ->label('Doc No')
                                    ->placeholder('SC-26-VII-C006-003')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('order_id')
                                    ->label('Order ID')
                                    ->placeholder('OR-26-VII-C006-003')
                                    ->required(),

                                Forms\Components\DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\TextInput::make('lead_time')
                                    ->label('Lead Time')
                                    ->default('21 Hari Kerja')
                                    ->required(),
                            ]),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status Order')
                                    ->options([
                                        'draft'            => 'Draft (Penyusunan)',
                                        'sent_to_customer' => 'Terkirim ke Customer',
                                        'deal'             => 'Customer Deal (Disetujui)',
                                        'converted_to_so'  => 'Masuk Sales Order & SPK',
                                        'rejected'         => 'Ditolak / Batal',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                Forms\Components\Select::make('sales_order_id')
                                    ->label('Terkait Sales Order')
                                    ->relationship('salesOrder', 'no_sales_order')
                                    ->disabled()
                                    ->placeholder('Akan terisi otomatis saat Customer Deal'),
                            ]),

                            Forms\Components\Placeholder::make('design_preview')
                                ->label('Lampiran Desain dari Approval Form')
                                ->content(function (Get $get, ?OrderConfirmation $record) {
                                    $afId = $get('approval_form_id') ?: $record?->approval_form_id;
                                    if (!$afId) return null;

                                    $af = ApprovalForm::find($afId);
                                    if (!$af || !$af->file_design) {
                                        return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500 italic">Approval form ini belum memiliki lampiran file desain.</span>');
                                    }

                                    $url = asset('storage/' . $af->file_design);
                                    return new \Illuminate\Support\HtmlString('
                                        <div style="margin-top: 6px; padding: 12px 16px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; display: flex; align-items: center; gap: 16px;">
                                            <div style="width: 100px; height: 100px; min-width: 100px; max-width: 100px; min-height: 100px; max-height: 100px; flex-shrink: 0; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.2); background: #ffffff; padding: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.25);">
                                                <img src="' . e($url) . '" alt="Desain Approval Form" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3);">
                                                        Ref Approval Form: ' . e($af->form_no) . '
                                                    </span>
                                                </div>
                                                <p style="font-size: 12px; line-height: 1.45; color: #9ca3af; margin: 0 0 6px 0;">
                                                    Foto desain dari Approval Form otomatis dilampirkan pada lembar cetak Order Confirmation untuk customer.
                                                </p>
                                                <a href="' . e($url) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; color: #f59e0b; text-decoration: none;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 13px; height: 13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                    Lihat Gambar Penuh &nearr;
                                                </a>
                                            </div>
                                        </div>
                                    ');
                                })
                                ->visible(fn (Get $get, ?OrderConfirmation $record) => !empty($get('approval_form_id') ?: $record?->approval_form_id))
                                ->columnSpanFull(),
                        ]),

                    // ==========================================
                    // 💰 TAB 2: ITEM PESANAN, HARGA & TOTAL
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Item & Penawaran Harga')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->relationship('items')
                                ->label('Daftar Item & Harga Satuan')
                                ->addActionLabel('+ Tambah Item Penawaran')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $items = $get('items') ?? [];
                                    $subtotal = 0;
                                    foreach ($items as $item) {
                                        $subtotal += (static::cleanNumeric($item['qty'] ?? 0) * static::cleanNumeric($item['harga'] ?? 0));
                                    }
                                    $diskon = static::cleanNumeric($get('diskon'));
                                    $set('subtotal', static::formatCurrency($subtotal));
                                    $set('grand_total', static::formatCurrency(max(0, $subtotal - $diskon)));
                                })
                                ->schema([
                                    Forms\Components\Hidden::make('approval_form_item_id'),
                                    Forms\Components\Hidden::make('satuan')->default('Pcs'),

                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\Select::make('finish_good_item_id')
                                            ->label('Pilih dari Finish Good (opsional)')
                                            ->relationship('finishGoodItem', 'nama_barang')
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
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

                                                    // Ambil Qty Pcs dari Approval Form jika referensi Approval Form dipilih di Tab 1
                                                    $afId = $get('../../approval_form_id');
                                                    if ($afId) {
                                                        $afItem = ApprovalFormItem::where('approval_form_id', $afId)
                                                            ->where(function ($query) use ($fg) {
                                                                $query->where('finish_good_item_id', $fg->id)
                                                                    ->orWhere('item_name', $fg->nama_barang);
                                                            })
                                                            ->first();

                                                        if ($afItem) {
                                                            $set('approval_form_item_id', $afItem->id);
                                                            $set('qty', (float) $afItem->qty);
                                                            if ($afItem->satuan) {
                                                                $set('satuan', $afItem->satuan);
                                                            }
                                                            $harga = static::cleanNumeric($get('harga'));
                                                            $rowTotal = (float) $afItem->qty * $harga;
                                                            $set('total', static::formatCurrency($rowTotal));
                                                        }
                                                    }
                                                }
                                            }),

                                        Forms\Components\TextInput::make('item_name')
                                            ->label('Nama Item')
                                            ->placeholder('Contoh: barbara Metalize')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if (!$state) return;
                                                $afId = $get('../../approval_form_id');
                                                if ($afId && (!$get('qty') || (float)$get('qty') === 0.0)) {
                                                    $afItem = ApprovalFormItem::where('approval_form_id', $afId)
                                                        ->where('item_name', 'like', '%' . trim($state) . '%')
                                                        ->first();

                                                    if ($afItem) {
                                                        $set('approval_form_item_id', $afItem->id);
                                                        $set('qty', (float) $afItem->qty);
                                                        if ($afItem->satuan) {
                                                            $set('satuan', $afItem->satuan);
                                                        }
                                                        $harga = static::cleanNumeric($get('harga'));
                                                        $rowTotal = (float) $afItem->qty * $harga;
                                                        $set('total', static::formatCurrency($rowTotal));
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                    ]),

                                    Forms\Components\Grid::make(4)->schema([
                                        Forms\Components\TextInput::make('jenis')
                                            ->label('Jenis')
                                            ->placeholder('SKM / SKT'),

                                        Forms\Components\TextInput::make('komponen')
                                            ->label('Komponen')
                                            ->placeholder('Etiket / Inner'),

                                        Forms\Components\TextInput::make('isi')
                                            ->label('Isi (Batang)')
                                            ->placeholder('20'),

                                        Forms\Components\TextInput::make('kertas')
                                            ->label('Kertas')
                                            ->placeholder('Metalize / Ivory'),
                                    ]),

                                    Forms\Components\Grid::make(5)->schema([
                                        Forms\Components\TextInput::make('warna')
                                            ->label('Warna')
                                            ->placeholder('5'),

                                        Forms\Components\TextInput::make('finishing')
                                            ->label('Finishing')
                                            ->placeholder('Spot UV, Cutting')
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('qty')
                                            ->label('Qty (Pcs)')
                                            ->numeric()
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = static::cleanNumeric($get('qty'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                $rowTotal = $qty * $harga;
                                                $set('total', static::formatCurrency($rowTotal));

                                                $items = $get('../../items') ?? [];
                                                $subtotal = 0;
                                                foreach ($items as $item) {
                                                    $subtotal += (static::cleanNumeric($item['qty'] ?? 0) * static::cleanNumeric($item['harga'] ?? 0));
                                                }
                                                $diskon = static::cleanNumeric($get('../../diskon'));
                                                $set('../../subtotal', static::formatCurrency($subtotal));
                                                $set('../../grand_total', static::formatCurrency(max(0, $subtotal - $diskon)));
                                            })
                                            ->required(),

                                        Forms\Components\TextInput::make('harga')
                                            ->label('Harga (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                            ->stripCharacters(',')
                                            ->formatStateUsing(fn ($state) => static::formatCurrency($state))
                                            ->dehydrateStateUsing(fn ($state) => static::cleanNumeric($state))
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = static::cleanNumeric($get('qty'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                $rowTotal = $qty * $harga;
                                                $set('total', static::formatCurrency($rowTotal));

                                                $items = $get('../../items') ?? [];
                                                $subtotal = 0;
                                                foreach ($items as $item) {
                                                    $subtotal += (static::cleanNumeric($item['qty'] ?? 0) * static::cleanNumeric($item['harga'] ?? 0));
                                                }
                                                $diskon = static::cleanNumeric($get('../../diskon'));
                                                $set('../../subtotal', static::formatCurrency($subtotal));
                                                $set('../../grand_total', static::formatCurrency(max(0, $subtotal - $diskon)));
                                            })
                                            ->required(),
                                    ]),

                                    Forms\Components\Grid::make(1)->schema([
                                        Forms\Components\TextInput::make('total')
                                            ->label('Total Harga (Rp)')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                            ->stripCharacters(',')
                                            ->formatStateUsing(fn ($state) => static::formatCurrency($state))
                                            ->dehydrateStateUsing(fn ($state) => static::cleanNumeric($state))
                                            ->readOnly()
                                            ->dehydrated(),
                                    ]),
                                ])
                                ->columnSpanFull()
                                ->defaultItems(1),

                            // RINGKASAN BIAYA (SUBTOTAL, DISKON, GRAND TOTAL)
                            Forms\Components\Section::make('Ringkasan Biaya')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal (Rp)')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                            ->stripCharacters(',')
                                            ->formatStateUsing(fn ($state) => static::formatCurrency($state))
                                            ->dehydrateStateUsing(fn ($state) => static::cleanNumeric($state))
                                            ->readOnly()
                                            ->dehydrated()
                                            ->default(0),

                                        Forms\Components\TextInput::make('diskon')
                                            ->label('Diskon (Rp)')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                            ->stripCharacters(',')
                                            ->formatStateUsing(fn ($state) => static::formatCurrency($state))
                                            ->dehydrateStateUsing(fn ($state) => static::cleanNumeric($state))
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $subtotal = static::cleanNumeric($get('subtotal'));
                                                $diskon = static::cleanNumeric($get('diskon'));
                                                $set('grand_total', static::formatCurrency(max(0, $subtotal - $diskon)));
                                            }),

                                        Forms\Components\TextInput::make('grand_total')
                                            ->label('Total Akhir (Rp)')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input, ".", ",", 0)'))
                                            ->stripCharacters(',')
                                            ->formatStateUsing(fn ($state) => static::formatCurrency($state))
                                            ->dehydrateStateUsing(fn ($state) => static::cleanNumeric($state))
                                            ->readOnly()
                                            ->dehydrated()
                                            ->default(0),
                                    ]),
                                ])
                                ->compact(),
                        ]),

                    // ==========================================
                    // 📜 TAB 3: TERMS & KESEPAKATAN
                    // ==========================================
                    Forms\Components\Tabs\Tab::make('Terms & Conditions')
                        ->icon('heroicon-o-scale')
                        ->schema([
                            Forms\Components\Textarea::make('terms_conditions')
                                ->label('Terms & Conditions')
                                ->rows(5)
                                ->default(OrderConfirmation::defaultTermsConditions())
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('disetujui_customer')
                                    ->label('Disetujui Customer (Nama Terang)')
                                    ->placeholder('Nama Penanggung Jawab Customer')
                                    ->default(function (Forms\Get $get) {
                                        $afId = $get('approval_form_id');
                                        if ($afId) {
                                            $af = ApprovalForm::with('customer')->find($afId);
                                            if ($af?->customer) {
                                                return $af->customer->nama_customer;
                                            }
                                        }
                                        $custId = $get('customer_id');
                                        if ($custId) {
                                            $cust = Customer::find($custId);
                                            return $cust?->nama_customer;
                                        }
                                        return null;
                                    }),

                                Forms\Components\TextInput::make('diketahui_ttc')
                                    ->label('Diketahui TTC (Nama / PT)')
                                    ->default('PT. Teknografi Tri Cawanaska'),
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
                Tables\Columns\TextColumn::make('doc_no')
                    ->label('Doc No')
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

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total (Rp)')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'            => 'gray',
                        'sent_to_customer' => 'info',
                        'deal'             => 'success',
                        'converted_to_so'  => 'primary',
                        'rejected'         => 'danger',
                        default            => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'            => 'DRAFT',
                        'sent_to_customer' => 'TERKIRIM KE CUSTOMER',
                        'deal'             => 'CUSTOMER DEAL',
                        'converted_to_so'  => 'MASUK SALES ORDER',
                        'rejected'         => 'DITOLAK / BATAL',
                        default            => strtoupper($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'            => 'Draft',
                        'sent_to_customer' => 'Terkirim ke Customer',
                        'deal'             => 'Customer Deal',
                        'converted_to_so'  => 'Masuk Sales Order',
                        'rejected'         => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('preview_pdf')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn (OrderConfirmation $record) => route('order-confirmation.preview', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->url(fn (OrderConfirmation $record) => route('order-confirmation.download', $record)),

                    // ACTION DEAL -> LANJUT KE CREATE SALES ORDER
                    Tables\Actions\Action::make('deal_convert')
                        ->label('Deal -> Create SO')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Customer Deal & Lanjut Buat Sales Order')
                        ->modalDescription('Apakah Customer telah menyetujui Order Confirmation ini? Status Order Confirmation akan ditandai DEAL dan Anda akan langsung diarahkan ke form pembuatan Sales Order untuk melengkapi data komersial dan BOM.')
                        ->modalSubmitActionLabel('Ya, Lanjut ke Form Sales Order')
                        ->visible(fn (OrderConfirmation $record) => !in_array($record->status, ['converted_to_so', 'rejected']))
                        ->action(function (OrderConfirmation $record) {
                            if ($record->sales_order_id && $record->salesOrder) {
                                Notification::make()
                                    ->title('Sales Order Sudah Ada')
                                    ->body("Order Confirmation ini sudah terhubung ke Sales Order No: {$record->salesOrder->no_sales_order}. Mengarahkan ke halaman edit...")
                                    ->warning()
                                    ->send();

                                return redirect(SalesOrderResource::getUrl('edit', ['record' => $record->sales_order_id]));
                            }

                            $record->update(['status' => 'deal']);

                            Notification::make()
                                ->title('Status Order Confirmation: Customer Deal')
                                ->body('Silakan periksa dan lengkapi data Sales Order (mata uang, syarat pembayaran, toleransi, dll) sebelum disimpan.')
                                ->success()
                                ->send();

                            return redirect(SalesOrderResource::getUrl('create', ['oc_id' => $record->id]));
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
            'index'  => Pages\ListOrderConfirmations::route('/'),
            'create' => Pages\CreateOrderConfirmation::route('/create'),
            'edit'   => Pages\EditOrderConfirmation::route('/{record}/edit'),
        ];
    }
}
