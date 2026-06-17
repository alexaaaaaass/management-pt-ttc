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
            ->state(fn ($record) =>
                $record->printings()->latest()->first()?->proses_printing
            ),

        TextEntry::make('tahap_printing')
            ->state(fn ($record) =>
                $record->printings()->latest()->first()?->tahap_printing
            ),

        TextEntry::make('hasil_baik')
            ->numeric()
            ->state(fn ($record) =>
                $record->printings()->latest()->first()?->hasil_baik
            ),

        TextEntry::make('hasil_rusak')
            ->numeric()
            ->state(fn ($record) =>
                $record->printings()->latest()->first()?->hasil_rusak
            ),

    ])
    ->columns(2),

   Section::make('Finishing')
    ->schema([

        TextEntry::make('proses_finishing')
            ->label('Proses')
            ->state(fn ($record) =>
                $record->finishings()->latest()->first()?->proses_finishing
            ),

        TextEntry::make('tahap_finishing')
            ->label('Tahap')
            ->state(fn ($record) =>
                $record->finishings()->latest()->first()?->tahap_finishing
            ),

        TextEntry::make('hasil_baik_finishing')
            ->label('Hasil Baik')
            ->numeric()
            ->state(fn ($record) =>
                $record->finishings()->latest()->first()?->hasil_baik
            ),

        TextEntry::make('hasil_rusak_finishing')
            ->label('Hasil Rusak')
            ->numeric()
            ->state(fn ($record) =>
                $record->finishings()->latest()->first()?->hasil_rusak
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