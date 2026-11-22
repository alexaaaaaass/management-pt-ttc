<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakPenerimaanResource\Pages;
use App\Models\MasterMaterialPlate;
use App\Models\MasterSupplier;
use App\Models\PracetakPenerimaan;
use App\Services\Pracetak\PracetakStockService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PracetakPenerimaanResource extends Resource
{
    protected static ?string $model = PracetakPenerimaan::class;

    protected static ?string $navigationGroup = 'Pra Cetak';

    protected static ?string $navigationLabel = 'Penerimaan Plate';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Header Penerimaan')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('nomor_penerimaan')
                        ->label('Nomor Penerimaan')
                        ->default(fn () => PracetakPenerimaan::generateNomorPenerimaan())
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_terima')
                        ->label('Tanggal Terima')
                        ->default(now())
                        ->required()
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted()),

                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(MasterSupplier::query()->orderBy('nama_supplier')->pluck('nama_supplier', 'id'))
                        ->searchable()
                        ->nullable()
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted()),

                    Forms\Components\TextInput::make('no_surat_jalan')
                        ->label('No. Surat Jalan')
                        ->maxLength(100)
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted()),

                    Forms\Components\TextInput::make('no_invoice_supplier')
                        ->label('No. Invoice Supplier')
                        ->maxLength(100)
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted()),

                    Forms\Components\Select::make('status')
                        ->label('Status Transaksi')
                        ->options([
                            'draft'     => 'Draft',
                            'posted'    => 'Posted',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('draft')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan Penerimaan')
                        ->columnSpanFull()
                        ->rows(2)
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted()),
                ]),

            Forms\Components\Section::make('Item Material Plate Masuk')
                ->description('Pilih master material plate dan masukkan kuantitas sheet utuh serta harga beli aktual.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->label('Daftar Plate')
                        ->columns(6)
                        ->disabled(fn (?PracetakPenerimaan $record) => $record && $record->isPosted())
                        ->schema([
                            Forms\Components\Select::make('master_material_plate_id')
                                ->label('Material Plate')
                                ->options(MasterMaterialPlate::query()
                                    ->where('status', 'aktif')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [$p->id => "{$p->kode_material} - {$p->nama_material} ({$p->brand})"]))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (!$state) {
                                        return;
                                    }
                                    $plate = MasterMaterialPlate::find($state);
                                    if ($plate) {
                                        $set('brand', $plate->brand);
                                        $set('tipe_plate', $plate->tipe_plate);
                                        $set('ketebalan_mm', $plate->ketebalan_mm);
                                        $set('panjang_sheet_mm', $plate->panjang_sheet_mm);
                                        $set('lebar_sheet_mm', $plate->lebar_sheet_mm);
                                        $set('harga_satuan', number_format((float) $plate->harga_beli, 2, '.', ''));
                                        $set('subtotal', number_format((float) $plate->harga_beli * 1, 2, '.', ''));
                                    }
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('brand')
                                ->label('Brand')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('ketebalan_mm')
                                ->label('Tebal (mm)')
                                ->disabled()
                                ->dehydrated()
                                ->suffix('mm')
                                ->columnSpan(1),

                            Forms\Components\Hidden::make('tipe_plate'),
                            Forms\Components\Hidden::make('panjang_sheet_mm'),
                            Forms\Components\Hidden::make('lebar_sheet_mm'),

                            Forms\Components\TextInput::make('qty_sheet')
                                ->label('Qty Sheet')
                                ->numeric()
                                ->default(1)
                                ->minValue(0.01)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) $get('qty_sheet');
                                    $rawHarga = MoneyHelper::clean((string) $get('harga_satuan'));
                                    $subtotal = round($qty * $rawHarga, 2);
                                    $set('subtotal', number_format($subtotal, 2, '.', ''));
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('harga_satuan')
                                ->label('Harga Beli / Sheet')
                                ->currencyMask(2)
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $qty = (float) $get('qty_sheet');
                                    $rawHarga = MoneyHelper::clean((string) $get('harga_satuan'));
                                    $subtotal = round($qty * $rawHarga, 2);
                                    $set('subtotal', number_format($subtotal, 2, '.', ''));
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal (Rp)')
                                ->currencyMask(2)
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('catatan')
                                ->label('Catatan Item')
                                ->placeholder('Opsional (misal: Batch / Lot No)')
                                ->columnSpan(4),
                        ])
                        ->minItems(1)
                        ->defaultItems(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_penerimaan')
                    ->label('No. Penerimaan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tanggal_terima')
                    ->label('Tgl Terima')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_surat_jalan')
                    ->label('No. SJ')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Total Sheet')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ',') . ' Sheet')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_nominal')
                    ->label('Total Nominal')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'posted',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('poster.name')
                    ->label('Diposting Oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('posted_at')
                    ->label('Diposting Pada')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'  => 'Draft',
                        'posted' => 'Posted',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(MasterSupplier::query()->orderBy('nama_supplier')->pluck('nama_supplier', 'id')),

                Tables\Filters\Filter::make('tanggal_terima')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $date) => $q->whereDate('tanggal_terima', '>=', $date))
                            ->when($data['sampai'], fn ($q, $date) => $q->whereDate('tanggal_terima', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (PracetakPenerimaan $record) => $record->isDraft()),

                Tables\Actions\Action::make('post')
                    ->label('Posting')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PracetakPenerimaan $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->modalHeading('Posting Penerimaan Material Plate')
                    ->modalDescription('Apakah Anda yakin ingin memposting penerimaan ini? Stok sheet utuh akan bertambah dan mutasi akan dicatat ke Ledger Histori Pra Cetak.')
                    ->action(function (PracetakPenerimaan $record) {
                        try {
                            app(PracetakStockService::class)->postPenerimaan($record, auth()->id());

                            Notification::make()
                                ->title('Penerimaan Berhasil Diposting')
                                ->body("Transaksi {$record->nomor_penerimaan} berhasil diposting. Stok plate telah diperbarui.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal Posting Penerimaan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (PracetakPenerimaan $record) => $record->isDraft()),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Informasi Penerimaan')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('nomor_penerimaan')->label('No. Penerimaan')->weight('bold'),
                    Infolists\Components\TextEntry::make('tanggal_terima')->label('Tanggal Terima')->date('d M Y'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'draft'  => 'warning',
                            'posted' => 'success',
                            default  => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('supplier.nama_supplier')->label('Supplier')->placeholder('-'),
                    Infolists\Components\TextEntry::make('no_surat_jalan')->label('No. Surat Jalan')->placeholder('-'),
                    Infolists\Components\TextEntry::make('no_invoice_supplier')->label('No. Invoice')->placeholder('-'),

                    Infolists\Components\TextEntry::make('total_qty')
                        ->label('Total Qty')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 0, '.', ',') . ' Sheet'),
                    Infolists\Components\TextEntry::make('total_nominal')->label('Total Nominal')->money('IDR'),
                    Infolists\Components\TextEntry::make('poster.name')->label('Diposting Oleh')->placeholder('-'),

                    Infolists\Components\TextEntry::make('catatan')->label('Catatan')->columnSpanFull()->placeholder('-'),
                ]),

            Infolists\Components\Section::make('Rincian Plate Masuk')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->columns(6)
                        ->schema([
                            Infolists\Components\TextEntry::make('masterPlate.kode_material')->label('Kode Plate'),
                            Infolists\Components\TextEntry::make('masterPlate.nama_material')->label('Nama Material'),
                            Infolists\Components\TextEntry::make('brand')->label('Brand'),
                            Infolists\Components\TextEntry::make('ketebalan_mm')->label('Tebal')->suffix(' mm'),
                            Infolists\Components\TextEntry::make('qty_sheet')->label('Qty')->suffix(' Sheet'),
                            Infolists\Components\TextEntry::make('harga_satuan')->label('Harga Satuan')->money('IDR'),
                            Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            Infolists\Components\TextEntry::make('catatan')->label('Catatan Item')->columnSpanFull()->placeholder('-'),
                        ]),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPracetakPenerimaans::route('/'),
            'create' => Pages\CreatePracetakPenerimaan::route('/create'),
            'view'   => Pages\ViewPracetakPenerimaan::route('/{record}'),
            'edit'   => Pages\EditPracetakPenerimaan::route('/{record}/edit'),
        ];
    }
}
