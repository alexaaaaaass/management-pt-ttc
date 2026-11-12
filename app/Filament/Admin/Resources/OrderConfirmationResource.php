<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderConfirmationResource\Pages;
use App\Filament\Admin\Resources\OrderConfirmationResource\Schemas\FlexoHppModalSchema;
use App\Models\ApprovalForm;
use App\Models\ApprovalFormItem;
use App\Models\Customer;
use App\Models\FinishGoodItem;
use App\Models\OrderConfirmation;
use App\Services\Costing\FlexoHppCalculatorService;
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
use Illuminate\Database\Eloquent\Builder;

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
        return \App\Support\MoneyHelper::clean($value) ?? 0.0;
    }

    public static function formatCurrency(mixed $value, int $precision = 2): string
    {
        $num = static::cleanNumeric($value);
        return \App\Support\MoneyHelper::format($num, $precision) ?? '0';
    }

    public static function updateTotals(Get $get, Set $set, bool $isInsideRow = false): void
    {
        $prefix = $isInsideRow ? '../../' : '';
        $items = $get($prefix . 'items') ?? [];
        $subtotal = 0;
        foreach ($items as $item) {
            $qty = static::cleanNumeric($item['qty'] ?? 0);
            $harga = static::cleanNumeric($item['harga'] ?? 0);
            $subtotal += ($qty * $harga);
        }
        $diskon = static::cleanNumeric($get($prefix . 'diskon'));
        $set($prefix . 'subtotal', \App\Support\MoneyHelper::format($subtotal, 2) ?? '0');
        $set($prefix . 'grand_total', \App\Support\MoneyHelper::format(max(0, $subtotal - $diskon), 2) ?? '0');
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
                                        if (!$state) {
                                            $set('items', []);
                                            return;
                                        }
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

                                            // Reset item terlebih dahulu agar jumlah card persis sesuai item Approval Form
                                            $set('items', []);

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

                                                    $uuid = (string) \Illuminate\Support\Str::uuid();
                                                    $targetQty = (float) $item->qty ?: 100000;
                                                    $fg = $fgId ? FinishGoodItem::find($fgId) : null;
                                                    $panjang = $fg?->panjang ? (float) $fg->panjang : 178.0;
                                                    $lebar = $fg?->lebar ? (float) $fg->lebar : 119.0;
                                                    $gramatur = $fg?->gramasi ? (float) $fg->gramasi : FlexoHppCalculatorService::DEFAULT_GRAMATUR;

                                                    $calc = app(FlexoHppCalculatorService::class)->calculate([
                                                        'panjang_etiket_jadi' => $panjang,
                                                        'lebar_etiket_jadi' => $lebar,
                                                        'gramatur_kertas' => $gramatur,
                                                    ], $targetQty);

                                                    $calcPrice = (float) $calc['harga_jual_indikatif'] > 0 ? (float) $calc['harga_jual_indikatif'] : (float) $calc['hpp_per_etiket'];
                                                    $itemQty = (float) $item->qty;
                                                    $rowTotal = $itemQty * $calcPrice;

                                                    $newItems[$uuid] = [
                                                        'approval_form_item_id' => $item->id,
                                                        'finish_good_item_id'   => $fgId,
                                                        'item_name'             => $item->item_name,
                                                        'jenis'                 => $item->jenis,
                                                        'komponen'              => $item->komponen,
                                                        'isi'                   => $item->isi,
                                                        'kertas'                => $item->kertas,
                                                        'warna'                 => $item->warna,
                                                        'finishing'             => $item->finishing,
                                                        'qty'                   => $itemQty,
                                                        'harga'                 => \App\Support\MoneyHelper::format($calcPrice, 2) ?? '0',
                                                        'total'                 => \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0',
                                                        'satuan'                => $item->satuan ?: 'Pcs',
                                                        'hpp_per_pcs'           => $calc['hpp_per_etiket'],
                                                        'harga_jual_indikatif'  => $calc['harga_jual_indikatif'],
                                                        'total_biaya_order'     => $calc['total_biaya_order'],
                                                        'biaya_bahan'           => $calc['biaya_bahan'],
                                                        'biaya_proses'          => $calc['biaya_proses_cetak_total'],
                                                        'kebutuhan_kertas_kg'   => $calc['berat_bahan_teoritis'],
                                                        'kebutuhan_kertas_meter'=> $calc['total_panjang_bahan'],
                                                        'kalkulasi_hpp_data'    => $calc,
                                                    ];
                                                }
                                                $set('items', $newItems);
                                                static::updateTotals($get, $set);
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
                                ->itemLabel(function (array $state): ?string {
                                    if (empty($state['item_name'])) {
                                        return 'Item Penawaran';
                                    }
                                    $qtyVal = static::cleanNumeric($state['qty'] ?? 0);
                                    $hargaVal = static::cleanNumeric($state['harga'] ?? 0);
                                    $hppVal = static::cleanNumeric($state['hpp_per_pcs'] ?? 0);

                                    $label = "{$state['item_name']} — " . number_format($qtyVal, 0, '.', ',') . " Pcs";
                                    if ($hargaVal > 0) {
                                        $label .= " | Harga: Rp " . (\App\Support\MoneyHelper::format($hargaVal, 2) ?? '0') . "/pcs";
                                    }
                                    if ($hppVal > 0) {
                                        $label .= " (HPP: Rp " . (\App\Support\MoneyHelper::format($hppVal, 2) ?? '0') . " | Kertas: " . number_format((float)($state['kebutuhan_kertas_kg'] ?? 0), 1) . " kg)";
                                    }
                                    return $label;
                                })
                                ->collapsible()
                                ->addActionLabel('+ Tambah Item Penawaran')
                                ->deletable(true)
                                ->deleteAction(
                                    fn (Forms\Components\Actions\Action $action) => $action
                                        ->requiresConfirmation(false)
                                        ->tooltip('Hapus item penawaran')
                                )
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    static::updateTotals($get, $set);
                                })
                                ->schema([
                                    Forms\Components\Hidden::make('approval_form_item_id'),
                                    Forms\Components\Hidden::make('satuan')->default('Pcs'),
                                    Forms\Components\Hidden::make('hpp_per_pcs'),
                                    Forms\Components\Hidden::make('harga_jual_indikatif'),
                                    Forms\Components\Hidden::make('total_biaya_order'),
                                    Forms\Components\Hidden::make('biaya_bahan'),
                                    Forms\Components\Hidden::make('biaya_proses'),
                                    Forms\Components\Hidden::make('kebutuhan_kertas_kg'),
                                    Forms\Components\Hidden::make('kebutuhan_kertas_meter'),
                                    Forms\Components\Hidden::make('kalkulasi_hpp_data'),

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
                                                    $currentItemName = trim((string) ($get('item_name') ?? ''));
                                                    if ($currentItemName === '' && strtoupper(trim((string) $fg->nama_barang)) !== 'KERTAS IVORY') {
                                                        $set('item_name', $fg->nama_barang);
                                                    }
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
                                                            if (!empty($afItem->item_name) && strtoupper(trim($afItem->item_name)) !== 'KERTAS IVORY') {
                                                                $set('item_name', $afItem->item_name);
                                                            }
                                                            $set('qty', (float) $afItem->qty);
                                                            if ($afItem->satuan) {
                                                                $set('satuan', $afItem->satuan);
                                                            }
                                                        }
                                                    }

                                                    // Hitung otomatis kalkulasi HPP awal berdasarkan spesifikasi produk
                                                    $targetQty = (float) static::cleanNumeric($get('qty') ?? 100000);
                                                    if ($targetQty <= 0) $targetQty = 100000;

                                                    $panjang = $fg->panjang ? (float) $fg->panjang : 178.0;
                                                    $lebar = $fg->lebar ? (float) $fg->lebar : 119.0;
                                                    $gramatur = $fg->gramasi ? (float) $fg->gramasi : FlexoHppCalculatorService::DEFAULT_GRAMATUR;

                                                    $calculator = app(FlexoHppCalculatorService::class);
                                                    $calc = $calculator->calculate([
                                                        'panjang_etiket_jadi' => $panjang,
                                                        'lebar_etiket_jadi' => $lebar,
                                                        'gramatur_kertas' => $gramatur,
                                                    ], $targetQty);

                                                    $calc['target_order'] = $targetQty;
                                                    $set('kalkulasi_hpp_data', $calc);
                                                    $set('hpp_per_pcs', $calc['hpp_per_etiket']);
                                                    $set('harga_jual_indikatif', $calc['harga_jual_indikatif']);
                                                    $set('total_biaya_order', $calc['total_biaya_order']);
                                                    $set('biaya_bahan', $calc['biaya_bahan']);
                                                    $set('biaya_proses', $calc['biaya_proses_cetak_total']);
                                                    $set('kebutuhan_kertas_kg', $calc['berat_bahan_teoritis']);
                                                    $set('kebutuhan_kertas_meter', $calc['total_panjang_bahan']);

                                                    $currentHarga = static::cleanNumeric($get('harga'));
                                                    $priceToApply = ($currentHarga > 0) ? $currentHarga : (float) $calc['harga_jual_indikatif'];
                                                    $set('harga', \App\Support\MoneyHelper::format($priceToApply, 2) ?? '0');

                                                    $qty = static::cleanNumeric($get('qty'));
                                                    $rowTotal = $qty * $priceToApply;
                                                    $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');

                                                    static::updateTotals($get, $set, isInsideRow: true);
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
                                                        $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');
                                                        static::updateTotals($get, $set, isInsideRow: true);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                    ]),

                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('open_hpp_calculator')
                                            ->label(function (Get $get) {
                                                $name = trim((string) $get('item_name'));
                                                return $name !== '' ? "HPP Produk ( {$name} )" : 'HPP Produk';
                                            })
                                            ->button()
                                            ->color('warning')
                                            ->modalHeading(fn (Get $get) => 'Kalkulasi HPP & Kertas: ' . ($get('item_name') ?: 'Item Penawaran'))
                                            ->modalDescription('Kalkulasi spesifikasi layout repeat cylinder, kebutuhan bahan kertas (meter & kg), biaya proses cetak flexo, dan rekomendasi harga jual berdasarkan standar mesin Flexo Zonten TTC.')
                                            ->modalWidth('7xl')
                                            ->modalSubmitActionLabel('Simpan & Terapkan')
                                            ->fillForm(function (Get $get): array {
                                                $existing = $get('kalkulasi_hpp_data');
                                                $targetOrder = (float) static::cleanNumeric($get('qty') ?? 100000);
                                                if ($targetOrder <= 0) $targetOrder = 100000;

                                                if (!empty($existing) && is_array($existing)) {
                                                    $existing['target_order'] = $targetOrder;
                                                    if (!isset($existing['apply_harga_to_item'])) {
                                                        $existing['apply_harga_to_item'] = true;
                                                    }
                                                    return $existing;
                                                }

                                                $fgId = $get('finish_good_item_id');
                                                $fg = $fgId ? FinishGoodItem::find($fgId) : null;

                                                $panjang = $fg?->panjang ? (float) $fg->panjang : 178.0;
                                                $lebar = $fg?->lebar ? (float) $fg->lebar : 119.0;
                                                $gramatur = $fg?->gramasi ? (float) $fg->gramasi : FlexoHppCalculatorService::DEFAULT_GRAMATUR;

                                                $calculator = app(FlexoHppCalculatorService::class);
                                                $initialCalc = $calculator->calculate([
                                                    'panjang_etiket_jadi' => $panjang,
                                                    'lebar_etiket_jadi' => $lebar,
                                                    'lebihan_cetak_panjang' => FlexoHppCalculatorService::DEFAULT_ALLOWANCE_PANJANG,
                                                    'lebihan_cetak_lebar' => FlexoHppCalculatorService::DEFAULT_ALLOWANCE_LEBAR,
                                                    'lebihan_lebar_bahan' => FlexoHppCalculatorService::DEFAULT_ALLOWANCE_WEB,
                                                    'jumlah_etiket_sepanjang_repeat' => 2,
                                                    'repeat_cylinder' => FlexoHppCalculatorService::DEFAULT_REPEAT_CYLINDER,
                                                    'jumlah_etiket_sepanjang_lebar' => 2,
                                                    'waste_setting' => FlexoHppCalculatorService::DEFAULT_WASTE_SETTING,
                                                    'waste_tambahan_persen' => FlexoHppCalculatorService::DEFAULT_WASTE_PERSEN,
                                                    'gramatur_kertas' => $gramatur,
                                                    'harga_bahan_per_kg' => FlexoHppCalculatorService::DEFAULT_HARGA_BAHAN,
                                                    'kecepatan_cetak' => FlexoHppCalculatorService::DEFAULT_KECEPATAN_CETAK,
                                                    'waktu_setup' => FlexoHppCalculatorService::DEFAULT_WAKTU_SETUP,
                                                    'tarif_mesin_per_jam' => 0,
                                                    'biaya_tinta_total' => 0,
                                                    'biaya_plate' => 0,
                                                    'biaya_tenaga_kerja' => 0,
                                                    'biaya_overhead' => 0,
                                                    'margin_persen' => 0,
                                                ], $targetOrder);

                                                $initialCalc['target_order'] = $targetOrder;
                                                $initialCalc['apply_harga_to_item'] = true;
                                                return $initialCalc;
                                            })
                                            ->form(FlexoHppModalSchema::getFormSchema())
                                            ->action(function (array $data, Set $set, Get $get) {
                                                $targetOrder = (float) static::cleanNumeric($get('qty') ?? 100000);
                                                if ($targetOrder <= 0) $targetOrder = 100000;

                                                $calculator = app(FlexoHppCalculatorService::class);
                                                $calc = $calculator->calculate($data, $targetOrder);

                                                $hargaMode = $data['harga_mode'] ?? 'indikatif';
                                                $fixManual = static::cleanNumeric($data['fix_hpp_manual'] ?? 0);

                                                if ($hargaMode === 'custom' && $fixManual > 0) {
                                                    $calc['hpp_per_etiket'] = $fixManual;
                                                    $calc['harga_jual_indikatif'] = $fixManual;
                                                    $priceToApply = $fixManual;
                                                } elseif ($hargaMode === 'hpp') {
                                                    $calc['harga_jual_indikatif'] = $calc['hpp_per_etiket'];
                                                    $priceToApply = (float) $calc['hpp_per_etiket'];
                                                } else {
                                                    $priceToApply = ($calc['harga_jual_indikatif'] > 0)
                                                        ? (float) $calc['harga_jual_indikatif']
                                                        : (float) $calc['hpp_per_etiket'];
                                                }

                                                $fullData = array_merge($data, $calc);

                                                $set('kalkulasi_hpp_data', $fullData);
                                                $set('hpp_per_pcs', $calc['hpp_per_etiket']);
                                                $set('harga_jual_indikatif', $calc['harga_jual_indikatif']);
                                                $set('total_biaya_order', $calc['total_biaya_order']);
                                                $set('biaya_bahan', $calc['biaya_bahan']);
                                                $set('biaya_proses', $calc['biaya_proses_cetak_total']);
                                                $set('kebutuhan_kertas_kg', $calc['berat_bahan_teoritis']);
                                                $set('kebutuhan_kertas_meter', $calc['total_panjang_bahan']);

                                                $shouldApply = $data['apply_harga_to_item'] ?? true;
                                                if ($shouldApply && $priceToApply > 0) {
                                                    $set('harga', \App\Support\MoneyHelper::format($priceToApply, 2) ?? '0');
                                                    $qty = static::cleanNumeric($get('qty'));
                                                    $rowTotal = $qty * $priceToApply;
                                                    $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');

                                                    static::updateTotals($get, $set, isInsideRow: true);
                                                }

                                                Notification::make()
                                                    ->title('Kalkulasi HPP & Kertas Berhasil Disimpan')
                                                    ->body("HPP: Rp " . (\App\Support\MoneyHelper::format($calc['hpp_per_etiket'], 2) ?? '0') . " / pcs | Harga: Rp " . (\App\Support\MoneyHelper::format($priceToApply, 2) ?? '0') . " | Kertas: " . number_format($calc['berat_bahan_teoritis'], 2) . " kg")
                                                    ->success()
                                                    ->send();
                                            }),

                                        Forms\Components\Actions\Action::make('sync_harga_to_hpp')
                                            ->label(fn (Get $get) => 'Samakan Harga = HPP (Rp ' . (\App\Support\MoneyHelper::format($get('hpp_per_pcs'), 2) ?? '0') . ')')
                                            ->button()
                                            ->outlined()
                                            ->color('success')
                                            ->visible(function (Get $get): bool {
                                                $hpp = static::cleanNumeric($get('hpp_per_pcs'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                return $hpp > 0 && abs($hpp - $harga) > 0.01;
                                            })
                                            ->action(function (Get $get, Set $set) {
                                                $hpp = static::cleanNumeric($get('hpp_per_pcs'));
                                                if ($hpp <= 0) return;

                                                $set('harga', \App\Support\MoneyHelper::format($hpp, 2) ?? '0');
                                                $set('harga_jual_indikatif', $hpp);
                                                $qty = static::cleanNumeric($get('qty'));
                                                $rowTotal = $qty * $hpp;
                                                $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');
                                                static::updateTotals($get, $set, isInsideRow: true);
                                            }),

                                        Forms\Components\Actions\Action::make('fix_hpp_to_harga')
                                            ->label(fn (Get $get) => 'Jadikan Fix HPP = Harga (Rp ' . (\App\Support\MoneyHelper::format($get('harga'), 2) ?? '0') . ')')
                                            ->button()
                                            ->outlined()
                                            ->color('info')
                                            ->visible(function (Get $get): bool {
                                                $hpp = static::cleanNumeric($get('hpp_per_pcs'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                return $harga > 0 && abs($hpp - $harga) > 0.01;
                                            })
                                            ->action(function (Get $get, Set $set) {
                                                $harga = static::cleanNumeric($get('harga'));
                                                if ($harga <= 0) return;

                                                $set('hpp_per_pcs', $harga);
                                                $set('harga_jual_indikatif', $harga);
                                                $hppData = $get('kalkulasi_hpp_data');
                                                if (is_array($hppData)) {
                                                    $hppData['harga_mode'] = 'custom';
                                                    $hppData['fix_hpp_manual'] = $harga;
                                                    $hppData['hpp_per_etiket'] = $harga;
                                                    $hppData['harga_jual_indikatif'] = $harga;
                                                    $set('kalkulasi_hpp_data', $hppData);
                                                }
                                            }),
                                    ]),

                                    Forms\Components\Placeholder::make('hpp_summary_badge')
                                        ->hiddenLabel()
                                        ->content(function (Get $get) {
                                            $hpp = (float) ($get('hpp_per_pcs') ?? 0);
                                            $kertasKg = (float) ($get('kebutuhan_kertas_kg') ?? 0);
                                            $kertasMeter = (float) ($get('kebutuhan_kertas_meter') ?? 0);
                                            $hargaIndikatif = (float) ($get('harga_jual_indikatif') ?? 0);

                                            if ($hpp <= 0 && $kertasKg <= 0) {
                                                return new \Illuminate\Support\HtmlString('
                                                    <div style="margin-top: 4px; padding: 6px 12px; background: rgba(255, 255, 255, 0.03); border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: 8px; font-size: 11px; color: #9ca3af; display: flex; align-items: center; gap: 8px;">
                                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #6b7280;"></span>
                                                        <span>HPP & Kebutuhan Kertas belum dikalkulasi. Klik tombol <strong>Kalkulator HPP & Kertas</strong> di atas untuk menghitung otomatis.</span>
                                                    </div>
                                                ');
                                            }

                                            return new \Illuminate\Support\HtmlString('
                                                <div style="margin-top: 4px; padding: 8px 14px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; font-size: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                                        <span style="font-weight: 700; color: #10b981; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>📦 Kebutuhan Kertas:</span> ' . number_format($kertasKg, 2) . ' kg (' . number_format($kertasMeter, 0, '.', ',') . ' m)
                                                        </span>
                                                        <span style="font-weight: 700; color: #f59e0b; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>🏷️ HPP Pokok:</span> Rp ' . number_format($hpp, 2) . ' / pcs
                                                        </span>
                                                        <span style="font-weight: 700; color: #60a5fa; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>💡 Harga Indikatif:</span> Rp ' . number_format($hargaIndikatif, 2) . ' / pcs
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 10px; color: #10b981; font-weight: 600; background: rgba(16, 185, 129, 0.15); padding: 2px 8px; border-radius: 6px;">
                                                        ✓ Terkalkulasi Mesin Flexo
                                                    </span>
                                                </div>
                                            ');
                                        })
                                        ->columnSpanFull(),

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
                                            ->quantityMask(0)
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = static::cleanNumeric($get('qty'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                $rowTotal = $qty * $harga;
                                                $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');

                                                // Update HPP jika sudah pernah dikalkulasi dan bukan mode custom fix
                                                $hppData = $get('kalkulasi_hpp_data');
                                                if (!empty($hppData) && is_array($hppData) && $qty > 0) {
                                                    if (($hppData['harga_mode'] ?? 'indikatif') !== 'custom') {
                                                        $calculator = app(FlexoHppCalculatorService::class);
                                                        $recalc = $calculator->calculate($hppData, $qty);
                                                        $full = array_merge($hppData, $recalc);
                                                        $full['target_order'] = $qty;
                                                        $set('kalkulasi_hpp_data', $full);
                                                        $set('hpp_per_pcs', $recalc['hpp_per_etiket']);
                                                        $set('harga_jual_indikatif', $recalc['harga_jual_indikatif']);
                                                        $set('total_biaya_order', $recalc['total_biaya_order']);
                                                        $set('biaya_bahan', $recalc['biaya_bahan']);
                                                        $set('biaya_proses', $recalc['biaya_proses_cetak_total']);
                                                        $set('kebutuhan_kertas_kg', $recalc['berat_bahan_teoritis']);
                                                        $set('kebutuhan_kertas_meter', $recalc['total_panjang_bahan']);
                                                    }
                                                }

                                                static::updateTotals($get, $set, isInsideRow: true);
                                            })
                                            ->required(),

                                        Forms\Components\TextInput::make('harga')
                                            ->label('Harga (Rp/pcs)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $qty = static::cleanNumeric($get('qty'));
                                                $harga = static::cleanNumeric($get('harga'));
                                                $rowTotal = $qty * $harga;
                                                $set('total', \App\Support\MoneyHelper::format($rowTotal, 2) ?? '0');

                                                static::updateTotals($get, $set, isInsideRow: true);
                                            })
                                            ->required(),
                                    ]),

                                    Forms\Components\Grid::make(1)->schema([
                                        Forms\Components\TextInput::make('total')
                                            ->label('Total Harga (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->readOnly()
                                            ->dehydrated(),
                                    ]),
                                ])
                                ->columnSpanFull()
                                ->defaultItems(0),

                            // RINGKASAN BIAYA (SUBTOTAL, DISKON, GRAND TOTAL)
                            Forms\Components\Section::make('Ringkasan Biaya')
                                ->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\TextInput::make('subtotal')
                                            ->label('Subtotal (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->readOnly()
                                            ->dehydrated()
                                            ->default(0),

                                        Forms\Components\TextInput::make('diskon')
                                            ->label('Diskon (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                static::updateTotals($get, $set);
                                            }),

                                        Forms\Components\TextInput::make('grand_total')
                                            ->label('Total Akhir (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
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
                        'partial_so'       => 'warning',
                        'converted_to_so'  => 'primary',
                        'rejected'         => 'danger',
                        default            => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'            => 'DRAFT',
                        'sent_to_customer' => 'TERKIRIM KE CUSTOMER',
                        'deal'             => 'CUSTOMER DEAL',
                        'partial_so'       => 'SEBAGIAN SO TERBIT',
                        'converted_to_so'  => 'SEMUA SO TERBIT',
                        'rejected'         => 'DITOLAK / BATAL',
                        default            => strtoupper($state),
                    }),

                Tables\Columns\TextColumn::make('so_progress')
                    ->label('Progres SO')
                    ->badge()
                    ->getStateUsing(fn (OrderConfirmation $record) => $record->so_conversion_status['label'])
                    ->color(fn (OrderConfirmation $record) => $record->so_conversion_status['color']),
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
                        'partial_so'       => 'Sebagian SO Terbit',
                        'converted_to_so'  => 'Semua SO Terbit',
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'customer',
                'items',
                'salesOrders',
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
