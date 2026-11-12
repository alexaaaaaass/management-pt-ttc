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

                Grid::make(4)
                    ->schema([

                        DatePicker::make(
                            'tanggal_pembayaran'
                        )
                            ->required()
                            ->default(now()),

                        Select::make('po_billing_id')
                            ->label('Billing Reference')
                            ->options(function () {
                                return PoBilling::with(['supplier'])->get()->mapWithKeys(function ($bill) {
                                    $label = $bill->kode_tagihan . ' • ' . ($bill->supplier?->nama_supplier ?? '-') . ' (Sisa: Rp ' . number_format($bill->sisa_tagihan, 0, ',', '.') . ')';
                                    return [$bill->id => $label];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $bill = PoBilling::find($state);
                                if (!$bill) {
                                    return;
                                }

                                $set('gudang', 'UGRMS');
                                $set('periode', $bill->periode);
                                $set('total_tagihan', $bill->sisa_tagihan);

                                $items = $get('items') ?? [];
                                if (count($items) === 1) {
                                    $firstKey = array_key_first($items);
                                    if ($firstKey !== null && empty($items[$firstKey]['nominal_pembayaran'])) {
                                        $items[$firstKey]['nominal_pembayaran'] = $bill->sisa_tagihan;
                                        $set('items', $items);
                                        $set('total_pembayaran', $bill->sisa_tagihan);
                                    }
                                }
                            }),

                        TextInput::make(
                            'gudang'
                        ),

                        TextInput::make(
                            'periode'
                        ),
                    ]),

                Select::make(
                    'karyawan_id'
                )

                    ->label('PIC / Karyawan')

                    ->options(
                        Karyawan::pluck(
                            'nama',
                            'id'
                        )
                    )

                    ->searchable(),

                TextInput::make(
                    'total_tagihan'
                )
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(),
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
                                        $total = collect($items)->sum(fn ($item) => \App\Support\MoneyHelper::clean($item['nominal_pembayaran'] ?? 0));
                                        $sisaTagihan = (float) ($get('../../total_tagihan') ?? 0);

                                        if (
                                            $sisaTagihan > 0 &&
                                            $total > 0 &&
                                            abs($sisaTagihan - $total) > 0 &&
                                            abs($sisaTagihan - $total) <= \App\Services\Finance\PoBillPayService::PAYMENT_TOLERANCE
                                        ) {
                                            $diff = round($sisaTagihan - $total, 2);
                                            $currVal = \App\Support\MoneyHelper::clean($state) ?? 0;
                                            $adjusted = round($currVal + $diff, 2);
                                            $set('nominal_pembayaran', $adjusted);
                                            $total = $sisaTagihan;

                                            \Filament\Notifications\Notification::make()
                                                ->title('Auto-Snap Nominal Pembayaran')
                                                ->body('Selisih Rp ' . number_format(abs($diff), 0, ',', '.') . ' berada dalam batas toleransi (≤ Rp 1.000). Nominal otomatis disesuaikan menjadi Rp ' . number_format($sisaTagihan, 0, ',', '.') . '.')
                                                ->info()
                                                ->send();
                                        }

                                        $set('../../total_pembayaran', $total);
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
                                        MasterCOA::pluck(
                                            'nama_akun',
                                            'id'
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
                                        MasterCOA::pluck(
                                            'nama_akun',
                                            'id'
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

                    ->live()

                    ->afterStateUpdated(
                        function (
                            $state,
                            Set $set
                        ) {

                            $total = collect(
                                $state
                            )->sum(
                                'nominal_pembayaran'
                            );

                            $set(
                                'total_pembayaran',
                                $total
                            );
                        }
                    ),
            ]),

            Section::make(
                'Summary'
            )

            ->schema([

                TextInput::make(
                    'total_pembayaran'
                )
                    ->prefix('Rp')
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
        ->columns([
            Tables\Columns\TextColumn::make('tanggal_pembayaran')
                ->label('Tgl Pembayaran')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('poBilling.kode_tagihan')
                ->label('Billing Ref')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('gudang')
                ->label('Gudang')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('periode')
                ->label('Periode')
                ->sortable(),

            // Tables\Columns\TextColumn::make('karyawan.nama')
            //     ->label('PIC / Karyawan')
            //     ->searchable(),

            // Total Tagihan dari Billing Reference
            Tables\Columns\TextColumn::make('total_tagihan')
                ->label('Total Tagihan')
                ->money('IDR', locale: 'id')
                ->sortable(),

            // Total Pembayaran yang diinput / dijumlahkan dari repeater
            Tables\Columns\TextColumn::make('total_pembayaran')
                ->label('Total Bayar')
                ->formatStateUsing(function ($state) {
                    $val = (float) $state;
                    return 'Rp ' . (fmod(round($val, 2), 1.0) != 0.0 ? number_format($val, 2, ',', '.') : number_format($val, 0, ',', '.'));
                })
                ->sortable()
                ->badge()
                ->color(fn (string $state): string => 'success'),
        ])
        ->filters([
            Tables\Filters\Filter::make('tanggal_pembayaran')
                ->form([
                    DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                    DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['dari_tanggal'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '>=', $date))
                        ->when($data['sampai_tanggal'], fn ($q, $date) => $q->whereDate('tanggal_pembayaran', '<=', $date));
                })
        ])
        ->actions([
            Tables\Actions\ActionGroup::make([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoBillPays::route('/'),
            'create' => Pages\CreatePoBillPay::route('/create'),
            'edit' => Pages\EditPoBillPay::route('/{record}/edit'),
        ];
    }
}