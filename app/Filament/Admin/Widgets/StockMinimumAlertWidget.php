<?php

namespace App\Filament\Admin\Widgets;

use App\Models\MasterItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class StockMinimumAlertWidget extends TableWidget
{
    protected static ?string $heading = 'Peringatan Stok Minimum (Reorder Alert)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MasterItem::query()
                    ->with('satuan')
                    ->whereNotNull('min_stock')
                    ->where('min_stock', '>', 0)
                    ->whereColumn('qty', '<=', 'min_stock')
                    ->orderBy('qty', 'asc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('kode_master_item')
                    ->label('Kode')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_master_item')
                    ->label('Nama Barang')
                    ->searchable()
                    ->limit(22)
                    ->tooltip(fn ($record) => $record->nama_master_item),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Sisa Stok')
                    ->formatStateUsing(fn ($record) => number_format($record->qty, 0, ',', '.') . ' ' . ($record->satuan?->nama_satuan ?? ''))
                    ->weight('bold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min. Stok')
                    ->formatStateUsing(fn ($record) => number_format($record->min_stock, 0, ',', '.') . ' ' . ($record->satuan?->nama_satuan ?? ''))
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn ($record) => $record->qty <= 0 ? 'danger' : 'warning')
                    ->getStateUsing(fn ($record) => $record->qty <= 0 ? 'Stok Habis' : 'Perlu Reorder'),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Lihat')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (MasterItem $record): string => \App\Filament\Admin\Resources\MasterItemResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
