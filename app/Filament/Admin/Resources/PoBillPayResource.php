<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PoBillPayResource\Pages;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Models\MetodeBayar;
use App\Models\PoBilling;
use App\Models\PoBillPay;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PoBillPayResource extends Resource
{
    protected static ?string $model = PoBillPay::class;
     protected static ?string $navigationGroup = 'Finance';
protected static ?int $navigationSort = 12;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
{
    return $form
        ->schema([

            Section::make(
                'Data Header Transaksi'
            )

            ->schema([

                Select::make('po_billing_id')
                    ->label('Billing Reference')
                    ->placeholder('Pilih tagihan PO yang belum lunas...')
                    ->options(function (?PoBillPay $record) {
                        return PoBilling::with(['supplier', 'purchaseOrder'])
                            ->withSum('payments', 'total_pembayaran')
                            ->orderByDesc('created_at')
                            ->orderByDesc('id')
                            ->get()
                            ->filter(function ($bill) use ($record) {
                                // Tetap tampilkan billing milik record yang sedang diedit
                                if ($record && (int) $bill->id === (int) $record->po_billing_id) {
                                    return true;
                                }

                                $grandTotal = (float) ($bill->grand_total ?? 0);
                                $paid = (float) ($bill->payments_sum_total_pembayaran ?? 0);
                                $sisa = round($grandTotal - $paid, 2);

                                // Sembunyikan tagihan yang sudah lunas (sisa <= 0)
                                return $sisa > 0;
                            })
                            ->mapWithKeys(function ($bill) {
                                $grandTotal = (float) ($bill->grand_total ?? 0);
                                $paid = (float) ($bill->payments_sum_total_pembayaran ?? 0);
                                $sisa = max(0.0, round($grandTotal - $paid, 2));
                                $poNo = $bill->purchaseOrder?->no_po;
                                $poPart = $poNo ? "PO: {$poNo} · " : '';
                                $statusPart = $sisa <= 0
                                    ? 'LUNAS'
                                    : 'Sisa: Rp ' . number_format($sisa, 0, ',', '.');
                                $label = $bill->kode_tagihan . ' • ' . ($bill->supplier?->nama_supplier ?? '-') . ' (' . $poPart . $statusPart . ')';

                                return [$bill->id => $label];
                            });
                    })
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated()
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        $bill = PoBilling::find($state);
                        if (! $bill) {
                            return;
                        }

                        $sisaFormatted = \App\Support\MoneyHelper::format($bill->sisa_tagihan, 2) ?? '0';

                        $set('gudang', 'UGRMS');
                        $set('periode', $bill->periode);
                        $set('total_tagihan', $sisaFormatted);

                        $items = $get('items') ?? [];
                        if (count($items) === 1) {
                            $firstKey = array_key_first($items);
                            if ($firstKey !== null && empty($items[$firstKey]['nominal_pembayaran'])) {
                                $items[$firstKey]['nominal_pembayaran'] = $sisaFormatted;
                                $set('items', $items);
                                $set('total_pembayaran', $sisaFormatted);
                            }
                        }
                    }),

                Grid::make(3)
                    ->schema([
                        DatePicker::make('tanggal_pembayaran')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->default(now()),

                        TextInput::make('gudang')
                            ->label('Gudang'),

                        TextInput::make('periode')
                            ->label('Periode'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('karyawan_id')
                            ->label('PIC / Karyawan')
                            ->options(fn () => Karyawan::orderBy('nama')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload(),

                        TextInput::make('total_tagihan')
                            ->label('Total Sisa Tagihan')
                            ->prefix('Rp')
                            ->currencyMask(2)
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]),

            Section::make(
                'Rincian Pembayaran'
            )

            ->schema([

                Repeater::make('items')

                    ->defaultItems(1)

                    ->schema([

                        Grid::make(2)
                            ->schema([

                                Select::make(
                                    'metode_bayar_id'
                                )

                                    ->label(
                                        'Metode Pembayaran'
                                    )

                                    ->options(
                                        MetodeBayar::pluck(
                                            'metode_pembayaran',
                                            'id'
                                        )
                                    )

                                    ->searchable(),

                                TextInput::make('nominal_pembayaran')
                                    ->label('Nominal Pembayaran')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->placeholder('0.00')
                                    ->required()
                                    ->extraInputAttributes(['onwheel' => 'this.blur()'])
                                    ->helperText('Otomatis diberi pemisah ribuan. Dibulatkan ke sisa tagihan jika selisih ≤ Rp 1.000')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $items = $get('../../items') ?? [];
                                        $total = collect($items)->sum(fn ($item) => (float) (\App\Support\MoneyHelper::clean($item['nominal_pembayaran'] ?? 0) ?? 0));
                                        $sisaTagihan = (float) (\App\Support\MoneyHelper::clean($get('../../total_tagihan') ?? 0) ?? 0);

                                        if (
                                            $sisaTagihan > 0 &&
                                            $total > 0 &&
                                            abs($sisaTagihan - $total) > 0 &&
                                            abs($sisaTagihan - $total) <= \App\Services\Finance\PoBillPayService::PAYMENT_TOLERANCE
                                        ) {
                                            $diff = round($sisaTagihan - $total, 2);
                                            $currVal = \App\Support\MoneyHelper::clean($state) ?? 0;
                                            $adjusted = round($currVal + $diff, 2);
                                            $set('nominal_pembayaran', \App\Support\MoneyHelper::format($adjusted, 2) ?? '0');
                                            $total = $sisaTagihan;

                                            \Filament\Notifications\Notification::make()
                                                ->title('Auto-Snap Nominal Pembayaran')
                                                ->body('Selisih Rp ' . number_format(abs($diff), 0, ',', '.') . ' berada dalam batas toleransi (≤ Rp 1.000). Nominal otomatis disesuaikan menjadi Rp ' . number_format($sisaTagihan, 0, ',', '.') . '.')
                                                ->info()
                                                ->send();
                                        }

                                        $set('../../total_pembayaran', \App\Support\MoneyHelper::format($total, 2) ?? '0');
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([

                                Select::make(
                                    'coa_debit_id'
                                )
                                    ->label(
                                        'Akun Debit'
                                    )
                                    ->options(
                                        MasterCOA::orderBy('kode_akun')->get()->mapWithKeys(
                                            fn ($coa) => [$coa->id => trim($coa->kode_akun) . ' - ' . $coa->nama_akun]
                                        )
                                    )
                                    ->searchable(),

                                Select::make(
                                    'coa_kredit_id'
                                )
                                    ->label(
                                        'Akun Kredit'
                                    )
                                    ->options(
                                        MasterCOA::orderBy('kode_akun')->get()->mapWithKeys(
                                            fn ($coa) => [$coa->id => trim($coa->kode_akun) . ' - ' . $coa->nama_akun]
                                        )
                                    )
                                    ->searchable(),
                            ]),

                        Grid::make(2)
                            ->schema([

                                TextInput::make(
                                    'mata_uang'
                                )
                                    ->default('IDR'),

                                TextInput::make(
                                    'nama_bank'
                                ),
                            ]),

                        Grid::make(2)
                            ->schema([

                                TextInput::make(
                                    'no_rekening'
                                ),

                                TextInput::make(
                                    'atas_nama'
                                ),
                            ]),

                        Textarea::make(
                            'memo'
                        ),

                    ])
                    ->afterStateUpdated(function ($state, Set $set) {
                        $total = collect($state)->sum(
                            fn ($item) => (float) (\App\Support\MoneyHelper::clean($item['nominal_pembayaran'] ?? 0) ?? 0)
                        );

                        $set('total_pembayaran', \App\Support\MoneyHelper::format($total, 2) ?? '0');
                    }),
            ]),

            Section::make(
                'Summary'
            )

            ->schema([

                TextInput::make(
                    'total_pembayaran'
                )
                    ->prefix('Rp')
                    ->currencyMask(2)
                    ->disabled()
                    ->dehydrated(),
            ]),
        ]);
}

    public static function getNavigationLabel(): string
{
    return 'Payment Order / Pembayaran Order';
}

    public static function getModelLabel(): string
    {
        return 'Payment Order / Pembayaran Order';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Payment Order / Pembayaran Order';
    }

   public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->searchPlaceholder('Cari No Billing, No PO, No Bukti, Supplier...')
        ->groups([
            Tables\Grouping\Group::make('poBilling.kode_tagihan')
                ->label('No Billing')
                ->getDescriptionFromRecordUsing(fn (PoBillPay $record): string => ($record->poBilling?->supplier?->nama_supplier ?? '-')
                    . ($record->poBilling?->purchaseOrder?->no_po ? ' · PO: ' . $record->poBilling->purchaseOrder->no_po : '')
                    . ' · Status: ' . ($record->poBilling?->status ?? '-'))
                ->collapsible(),
        ])
        ->columns([
            Tables\Columns\TextColumn::make('kode_pembayaran')
                ->label('No. Bukti')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn (PoBillPay $record): string => $record->tanggal_pembayaran
                    ? $record->tanggal_pembayaran->format('d M Y')
                    : '-'),

            Tables\Columns\TextColumn::make('poBilling.kode_tagihan')
                ->label('Billing Ref')
                ->description(fn (PoBillPay $record): ?string => $record->poBilling?->purchaseOrder?->no_po
                    ? 'PO: ' . $record->poBilling->purchaseOrder->no_po
                    : null)
                ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                    return $query->whereHas('poBilling', function ($q) use ($search) {
                        $q->where('kode_tagihan', 'like', "%{$search}%")
                            ->orWhereHas('purchaseOrder', fn ($poQ) => $poQ->where('no_po', 'like', "%{$search}%"));
                    });
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('poBilling.supplier.nama_supplier')
                ->label('Supplier')
                ->searchable(),

            Tables\Columns\TextColumn::make('gudang')
                ->label('Gudang')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('periode')
                ->label('Periode')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            // Total Tagihan sebelum pembayaran tahap ini
            Tables\Columns\TextColumn::make('total_tagihan')
                ->label('Tagihan Awal Tahap')
                ->money('IDR', locale: 'id')
                ->sortable(),

            // Total Pembayaran yang dibayarkan pada tahap ini
            Tables\Columns\TextColumn::make('total_pembayaran')
                ->label('Dibayar Tahap Ini')
                ->formatStateUsing(function ($state) {
                    $val = (float) $state;
                    return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                })
                ->sortable()
                ->badge()
                ->color('success')
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total Dibayar')
                        ->money('IDR', locale: 'id')
                ),

            // Sisa Tagihan setelah transaksi pembayaran ini
            Tables\Columns\TextColumn::make('sisa_setelah_bayar')
                ->label('Sisa Tagihan')
                ->getStateUsing(function (PoBillPay $record): float {
                    return max(0.0, round((float) $record->total_tagihan - (float) $record->total_pembayaran, 2));
                })
                ->formatStateUsing(function ($state): string {
                    $val = (float) $state;
                    if ($val <= 0) {
                        return 'Rp 0 (Lunas)';
                    }

                    return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                })
                ->badge()
                ->color(fn ($state): string => (float) $state <= 0 ? 'success' : 'warning')
                ->description(function (PoBillPay $record): ?string {
                    $sisaTahap = max(0.0, round((float) $record->total_tagihan - (float) $record->total_pembayaran, 2));
                    $sisaBillingKini = (float) ($record->poBilling?->sisa_tagihan ?? 0);

                    if ($sisaTahap > 0 && $sisaBillingKini <= 0) {
                        return 'Billing kini: Lunas';
                    }

                    return null;
                }),

            Tables\Columns\TextColumn::make('status_billing')
                ->label('Status Billing')
                ->getStateUsing(fn (PoBillPay $record): string => $record->poBilling?->status ?? '-')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Paid'    => 'success',
                    'Partial' => 'warning',
                    'Unpaid'  => 'danger',
                    default   => 'gray',
                }),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('po_billing_id')
                ->label('No Billing (Billing Ref)')
                ->options(function () {
                    return PoBilling::with(['supplier', 'purchaseOrder'])
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->get()
                        ->mapWithKeys(function ($bill) {
                            $poNo = $bill->purchaseOrder?->no_po;
                            $sup = $bill->supplier?->nama_supplier ?? '-';

                            return [
                                $bill->id => $bill->kode_tagihan . ' • ' . $sup . ($poNo ? " ({$poNo})" : ''),
                            ];
                        });
                })
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('supplier')
                ->label('Supplier')
                ->relationship('poBilling.supplier', 'nama_supplier')
                ->searchable()
                ->preload(),

            Tables\Filters\Filter::make('tanggal_pembayaran')
                ->form([
                    DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                    DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['dari_tanggal'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '>=', $date))
                        ->when($data['sampai_tanggal'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '<=', $date));
                }),
        ])
        ->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('lihat_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-information-circle')
                    ->color('info')
                    ->modalHeading(fn (PoBillPay $record) => 'Detail Pembayaran · ' . ($record->kode_pembayaran ?: 'PAY-' . $record->id))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (PoBillPay $record): \Illuminate\Support\HtmlString {
                        $record->loadMissing(['poBilling.supplier', 'poBilling.purchaseOrder', 'karyawan', 'items.metodeBayar', 'items.coaDebit', 'items.coaKredit']);
                        $fmt = fn (float $n) => 'Rp ' . number_format($n, 0, ',', '.');
                        $bill = $record->poBilling;
                        $sisaTahap = max(0.0, round((float) $record->total_tagihan - (float) $record->total_pembayaran, 2));
                        $sisaKini = (float) ($bill?->sisa_tagihan ?? 0);

                        $rowsHtml = '';
                        foreach ($record->items as $idx => $item) {
                            $metode = e($item->metodeBayar?->metode_pembayaran ?? '-');
                            $bank = e(collect([$item->nama_bank, $item->no_rekening, $item->atas_nama])->filter()->implode(' / ') ?: '-');
                            $coaD = e($item->coaDebit ? trim($item->coaDebit->kode_akun) . ' - ' . $item->coaDebit->nama_akun : '-');
                            $coaK = e($item->coaKredit ? trim($item->coaKredit->kode_akun) . ' - ' . $item->coaKredit->nama_akun : '-');
                            $memo = e($item->memo ?: '-');
                            $nom = $fmt((float) $item->nominal_pembayaran);

                            $rowsHtml .= "<tr style='border-bottom:1px solid rgba(148,163,184,0.2);'>"
                                . "<td style='padding:6px 8px;'>" . ($idx + 1) . "</td>"
                                . "<td style='padding:6px 8px;'><div><strong>{$metode}</strong></div><div style='font-size:11px;opacity:0.75;'>{$bank}</div></td>"
                                . "<td style='padding:6px 8px;font-size:11px;'><div>D: {$coaD}</div><div>K: {$coaK}</div></td>"
                                . "<td style='padding:6px 8px;font-size:12px;'>{$memo}</td>"
                                . "<td style='padding:6px 8px;text-align:right;font-weight:600;color:#10b981;'>{$nom}</td>"
                                . '</tr>';
                        }

                        $html = '<div style="font-size:13px;line-height:1.6;">'
                            . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:10px 12px;border-radius:8px;border:1px solid rgba(148,163,184,0.25);background:rgba(148,163,184,0.08);margin-bottom:12px;">'
                            . '<div><span style="opacity:0.7;">Billing Ref:</span> <strong>' . e($bill?->kode_tagihan ?? '-') . '</strong></div>'
                            . '<div><span style="opacity:0.7;">No PO:</span> <strong>' . e($bill?->purchaseOrder?->no_po ?? '-') . '</strong></div>'
                            . '<div><span style="opacity:0.7;">Supplier:</span> <strong>' . e($bill?->supplier?->nama_supplier ?? '-') . '</strong></div>'
                            . '<div><span style="opacity:0.7;">PIC:</span> <strong>' . e($record->karyawan?->nama ?? '-') . '</strong></div>'
                            . '<div><span style="opacity:0.7;">Tagihan Awal Tahap:</span> <strong>' . $fmt((float) $record->total_tagihan) . '</strong></div>'
                            . '<div><span style="opacity:0.7;">Dibayar Tahap Ini:</span> <strong style="color:#10b981;">' . $fmt((float) $record->total_pembayaran) . '</strong></div>'
                            . '<div><span style="opacity:0.7;">Sisa Setelah Tahap Ini:</span> <strong style="color:' . ($sisaTahap <= 0 ? '#10b981' : '#f59e0b') . ';">' . $fmt($sisaTahap) . '</strong></div>'
                            . '<div><span style="opacity:0.7;">Sisa Billing Saat Ini:</span> <strong style="color:' . ($sisaKini <= 0 ? '#10b981' : '#f59e0b') . ';">' . $fmt($sisaKini) . ' (' . e($bill?->status ?? '-') . ')</strong></div>'
                            . '</div>'
                            . '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
                            . '<thead><tr style="border-bottom:1px solid rgba(148,163,184,0.3);opacity:0.75;">'
                            . '<th style="padding:6px 8px;text-align:left;">#</th>'
                            . '<th style="padding:6px 8px;text-align:left;">Metode / Bank</th>'
                            . '<th style="padding:6px 8px;text-align:left;">Akun COA</th>'
                            . '<th style="padding:6px 8px;text-align:left;">Memo</th>'
                            . '<th style="padding:6px 8px;text-align:right;">Nominal</th>'
                            . '</tr></thead><tbody>' . $rowsHtml . '</tbody></table>'
                            . '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    }),

                Tables\Actions\Action::make('bayar_sisa')
                    ->label('Bayar Sisa Tagihan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (PoBillPay $record): bool => (float) ($record->poBilling?->sisa_tagihan ?? 0) > 0)
                    ->url(fn (PoBillPay $record): string => static::getUrl('create', ['po_billing_id' => $record->po_billing_id])),

                Tables\Actions\Action::make('preview_pdf')
                    ->label('Preview Bukti Bayar (PDF)')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PoBillPay $record): string => route('finance.voucher.po-bill-pay.preview', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_pdf')
                    ->label('Download Bukti Bayar (PDF)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (PoBillPay $record): string => route('finance.voucher.po-bill-pay.download', $record)),

                Tables\Actions\Action::make('lihat_billing')
                    ->label('Lihat Tagihan (Billing)')
                    ->icon('heroicon-o-document-text')
                    ->visible(fn (PoBillPay $record): bool => (bool) $record->po_billing_id)
                    ->url(fn (PoBillPay $record): string => PoBillingResource::getUrl('edit', ['record' => $record->po_billing_id])),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->using(fn ($record) => app(\App\Services\Finance\PoBillPayService::class)->deletePayment($record)),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'poBilling.supplier',
            'poBilling.purchaseOrder',
            'poBilling.payments',
            'karyawan',
            'items.metodeBayar',
            'items.coaDebit',
            'items.coaKredit',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoBillPays::route('/'),
            'create' => Pages\CreatePoBillPay::route('/create'),
            'edit' => Pages\EditPoBillPay::route('/{record}/edit'),
        ];
    }
}