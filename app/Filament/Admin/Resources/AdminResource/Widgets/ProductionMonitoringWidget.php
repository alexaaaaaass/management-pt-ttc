<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\MasterSpk;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProductionMonitoringWidget extends TableWidget
{
    protected static ?string $heading = 'Production Monitoring Board';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MasterSpk::query()
                    ->with([
                        'salesOrder.itemable',
                        'printings',
                        'finishings',
                        'packagings',
                        'suratJalans'
                    ])
            )
            ->columns([

                Tables\Columns\TextColumn::make('no_spk')
                    ->label('SPK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->getStateUsing(fn ($record) =>
                        $record->salesOrder?->itemable?->nama_barang ?? '-'
                    ),

                Tables\Columns\TextColumn::make('tanggal_estimasi_selesai')
                    ->label('Deadline')
                    ->date('d-m-Y'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->getStateUsing(fn ($record) =>
                        number_format($record->salesOrder?->qty ?? 0)
                    ),

                Tables\Columns\TextColumn::make('kurang_kirim')
                    ->label('Kurang Kirim')
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'success' : 'danger')
                    ->getStateUsing(function ($record) {

                        $order = $record->salesOrder?->qty ?? 0;

                        $terkirim = $record->suratJalans()
                            ->sum('qty_pengiriman');

                        return max(0, $order - $terkirim);
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($state) {

                        if ($state == 'Complete')
                            return 'success';

                        if (str_contains($state, 'Packaging'))
                            return 'primary';

                        if (str_contains($state, 'Finishing'))
                            return 'warning';

                        return 'danger';
                    })
                    ->getStateUsing(function ($record) {

                        $order = $record->salesOrder?->qty ?? 0;

                        $terkirim = $record->suratJalans()
                            ->sum('qty_pengiriman');

                        if ($terkirim >= $order) {
                            return 'Complete';
                        }

                        if ($record->packagings()->exists()) {
                            return 'Packaging';
                        }

                        $finishing = $record->finishings()
                            ->latest()
                            ->first();

                        if ($finishing) {
                            return 'Finishing - ' .
                                ucfirst($finishing->proses_finishing);
                        }

                        $printing = $record->printings()
                            ->latest()
                            ->first();

                        if ($printing) {
                            return 'Printing - ' .
                                ucfirst($printing->tahap_printing);
                        }

                        return 'Belum Produksi';
                    }),

            ]);
    }
}