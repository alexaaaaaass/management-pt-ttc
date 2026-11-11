<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportProduksiResource\Pages;
use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportProduksiResource extends Resource
{
    protected static ?string $model = MasterSpk::class;
    protected static ?string $navigationGroup = 'Report';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Report Produksi';
    protected static ?string $modelLabel = 'Report Produksi SPK';
    protected static ?string $pluralModelLabel = 'Report Analisa Produksi & Realisasi SPK';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'salesOrder.customer',
                'salesOrder.itemable',
                'printings.mesin',
                'packagings',
                'suratJalans',
            ])
            ->orderBy('tanggal_po', 'desc')
            ->orderBy('id', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_spk')
                    ->label('No. SPK')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('salesOrder.no_sales_order')
                    ->label('Order ID / SO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_po')
                    ->label('Tgl Order')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('salesOrder.customer.nama_customer')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('produk_summary')
                    ->label('Order Summary / Produk')
                    ->state(fn (MasterSpk $record): string => $record->salesOrder?->product_name
                        ?: ($record->salesOrder?->itemable?->nama_barang ?: '-'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('salesOrder', function (Builder $q) use ($search) {
                            $q->where('product_name', 'like', "%{$search}%")
                                ->orWhere('no_sales_order', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('mesin_cetak')
                    ->label('Mesin Cetak')
                    ->state(function (MasterSpk $record): string {
                        $names = $record->printings
                            ->map(fn ($p) => $p->mesin?->nama_mesin)
                            ->filter()
                            ->unique()
                            ->values();
                        return $names->isNotEmpty() ? $names->implode(', ') : 'Belum Cetak';
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('order_qty')
                    ->label('Qty Order')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('hasil_cetak_baik')
                    ->label('Hasil Cetak')
                    ->state(fn (MasterSpk $record): int => (int) $record->printings->sum('hasil_baik'))
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('waste_cetak')
                    ->label('Waste')
                    ->state(fn (MasterSpk $record): int => (int) $record->printings->sum(
                        fn ($p) => (int) ($p->hasil_rusak ?? 0) + (int) ($p->semi_waste ?? 0)
                    ))
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('total_good_packed')
                    ->label('Hasil Packing')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_shipped')
                    ->label('Terkirim (SJ)')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Realisasi')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state) => (float) $state >= 100 ? 'success' : ((float) $state > 0 ? 'warning' : 'gray')),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status SPK')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'FINISH'     => 'success',
                        'ON PROCESS' => 'warning',
                        default      => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status SPK')
                    ->options([
                        'FINISH'     => 'FINISH',
                        'ON PROCESS' => 'ON PROCESS',
                    ]),
                Tables\Filters\Filter::make('periode')
                    ->label('Periode Order')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal_po', '>=', $d))
                            ->when($data['sampai_tanggal'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal_po', '<=', $d));
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportProduksis::route('/'),
        ];
    }
}
