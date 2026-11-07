<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinishGoodLedgerResource\Pages;
use App\Models\FinishGoodMovement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinishGoodLedgerResource extends Resource
{
    protected static ?string $model = FinishGoodMovement::class;

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $navigationLabel = 'Finish Good History';

    protected static ?string $modelLabel = 'Finish Good History';

    protected static ?string $pluralModelLabel = 'Finish Good History';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['finishGoodItem', 'spk', 'user'])
            ->latest();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('finishGoodItem.kode_material_produk')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('finishGoodItem.nama_barang')
                    ->label('Nama Barang Jadi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('movement_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'IN'         => 'success',
                        'OUT'        => 'danger',
                        'BLOCK'      => 'warning',
                        'ADJUSTMENT' => 'info',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber Transaksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'PACKAGING'         => 'Packaging Produksi',
                        'SUBCOUNT_IN'       => 'Subcount In',
                        'SURAT_JALAN'       => 'Surat Jalan',
                        'MANUAL_ADJUSTMENT' => 'Penyesuaian Manual',
                        default             => $state ?: '-',
                    })
                    ->color('gray'),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty Mutasi')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('before_stock')
                    ->label('Stok Sebelum')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.'),

                Tables\Columns\TextColumn::make('after_stock')
                    ->label('Saldo Akhir')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Tipe Mutasi')
                    ->options([
                        'IN'         => 'IN (Barang Masuk)',
                        'OUT'        => 'OUT (Barang Keluar)',
                        'ADJUSTMENT' => 'ADJUSTMENT (Penyesuaian)',
                        'BLOCK'      => 'BLOCK (Hold QC)',
                    ]),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Sumber Transaksi')
                    ->options([
                        'PACKAGING'         => 'Packaging Produksi',
                        'SUBCOUNT_IN'       => 'Subcount In',
                        'SURAT_JALAN'       => 'Surat Jalan',
                        'MANUAL_ADJUSTMENT' => 'Penyesuaian Manual',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinishGoodLedgers::route('/'),
        ];
    }
}

