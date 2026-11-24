<?php

namespace App\Filament\Admin\Resources\AdminResource\Widgets;

use App\Models\MasterSpk;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;


class ProductionMonitoringWidget extends TableWidget
{
    protected static ?string $heading = 'Production Monitoring Board';

    protected static ?int $sort = 9;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MasterSpk::query()
                    ->with([
                        'salesOrder.itemable',
                        'printings',
                        'diemakings',
                        'finishings',
                        'packagings',
                        'suratJalans'
                    ])
            )
            ->defaultPaginationPageOption(5)
            ->paginationPageOptions([5, 10, 25])
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

                        $terkirim = $record->suratJalans
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

                        if (str_contains($state, 'Die Making'))
                            return 'info';

                        return 'danger';
                    })
                    ->getStateUsing(function ($record) {

                        $order = $record->salesOrder?->qty ?? 0;

                        $terkirim = $record->suratJalans
                            ->sum('qty_pengiriman');

                        if ($order > 0 && $terkirim >= $order) {
                            return 'Complete';
                        }

                        if ($record->packagings->isNotEmpty()) {
                            return 'Packaging';
                        }

                        $finishing = $record->finishings
                            ->sortByDesc('id')
                            ->first();

                        if ($finishing) {
                            return 'Finishing - ' . $finishing->proses_summary;
                        }

                        $diemaking = $record->diemakings
                            ->sortByDesc('id')
                            ->first();

                        if ($diemaking) {
                            return 'Die Making - ' . $diemaking->proses_summary;
                        }

                        $printing = $record->printings
                            ->sortByDesc('id')
                            ->first();

                        if ($printing) {
                            return 'Printing - ' . $printing->proses_summary;
                        }

                        return 'Belum Produksi';
                    }),

            ])
         ->actions([

    Action::make('view')
        ->label('')
        ->icon('heroicon-o-eye')
        ->color('primary')
        ->modalHeading('Detail Progress Tahapan Produksi Terakhir')
        ->modalWidth('7xl')

       ->infolist([

    Section::make('Informasi SPK')
        ->schema([

            TextEntry::make('no_spk')
                ->label('No SPK'),

            TextEntry::make('salesOrder.itemable.nama_barang')
                ->label('Produk'),

            TextEntry::make('salesOrder.qty')
                ->label('Qty Order')
                ->numeric(),

            TextEntry::make('tanggal_estimasi_selesai')
                ->label('Deadline')
                ->date(),

        ])
        ->columns(2),

    Section::make('Printing')
    ->schema([

        TextEntry::make('proses_printing')
            ->label('Proses Dikerjakan')
            ->state(fn ($record) =>
                $record->printings()->latest()->first()?->proses_summary ?? '-'
            ),

        TextEntry::make('status_printing')
            ->label('Status Laporan')
            ->state(fn ($record) =>
                ucfirst((string) ($record->printings()->latest()->first()?->status_laporan ?? '-'))
            ),

        TextEntry::make('hasil_baik')
            ->numeric()
            ->state(fn ($record) =>
                $record->printings()->sum('hasil_baik')
            ),

        TextEntry::make('hasil_rusak')
            ->numeric()
            ->state(fn ($record) =>
                $record->printings()->sum('hasil_rusak')
            ),

    ])
    ->columns(2),

    Section::make('Die Making')
    ->schema([

        TextEntry::make('proses_diemaking')
            ->label('Proses Dikerjakan')
            ->state(fn ($record) =>
                $record->diemakings()->latest()->first()?->proses_summary ?? '-'
            ),

        TextEntry::make('status_diemaking')
            ->label('Status Laporan')
            ->state(fn ($record) =>
                ucfirst((string) ($record->diemakings()->latest()->first()?->status_laporan ?? '-'))
            ),

        TextEntry::make('hasil_baik_diemaking')
            ->label('Hasil Baik')
            ->numeric()
            ->state(fn ($record) =>
                $record->diemakings()->sum('hasil_baik')
            ),

        TextEntry::make('hasil_rusak_diemaking')
            ->label('Hasil Rusak')
            ->numeric()
            ->state(fn ($record) =>
                $record->diemakings()->sum('hasil_rusak')
            ),

    ])
    ->columns(2)
    ->collapsible(),

   Section::make('Finishing')
    ->schema([

        TextEntry::make('proses_finishing')
            ->label('Proses Dikerjakan')
            ->state(fn ($record) =>
                $record->finishings()->latest()->first()?->proses_summary ?? '-'
            ),

        TextEntry::make('status_finishing')
            ->label('Status Laporan')
            ->state(fn ($record) =>
                ucfirst((string) ($record->finishings()->latest()->first()?->status_laporan ?? '-'))
            ),

        TextEntry::make('hasil_baik_finishing')
            ->label('Hasil Baik')
            ->numeric()
            ->state(fn ($record) =>
                $record->finishings()->sum('hasil_baik')
            ),

        TextEntry::make('hasil_rusak_finishing')
            ->label('Hasil Rusak')
            ->numeric()
            ->state(fn ($record) =>
                $record->finishings()->sum('hasil_rusak')
            ),

    ])
    ->columns(2)
    ->collapsible(),

Section::make('Packaging')
    ->schema([

        TextEntry::make('kode_packaging')
            ->label('Kode Packaging')
            ->state(fn ($record) =>
                $record->packagings()->latest()->first()?->kode_packaging
            ),

        TextEntry::make('qty_packaging')
            ->label('Qty Packaging')
            ->numeric()
            ->state(fn ($record) =>
                $record->packagings()->sum('grand_total')
            ),

    ])
    ->columns(2)
    ->collapsible(),

Section::make('Pengiriman')
    ->schema([

        TextEntry::make('qty_kirim')
            ->label('Qty Kirim')
            ->numeric()
            ->state(fn ($record) =>
                $record->suratJalans()->sum('qty_pengiriman')
            ),

        TextEntry::make('tanggal_kirim')
            ->label('Tanggal Kirim Terakhir')
            ->date()
            ->state(fn ($record) =>
                $record->suratJalans()->latest()->first()?->tanggal_surat_jalan
            ),

        TextEntry::make('kode_surat_jalan')
            ->label('Surat Jalan Terakhir')
            ->state(fn ($record) =>
                $record->suratJalans()->latest()->first()?->kode_surat_jalan
            ),

    ])
    ->columns(2)
    ->collapsible(),

])

]);
    }
}