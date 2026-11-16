<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PiutangJatuhTempoWidget extends TableWidget
{
    protected static ?string $heading = 'Piutang & Tagihan Jatuh Tempo (AR Aging)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->with('customer')
                    ->where('status', '!=', 'PAID')
                    ->where(function ($q) {
                        $q->where('sisa_tagihan', '>', 0)
                            ->orWhereNull('sisa_tagihan');
                    })
                    ->orderByRaw('CASE WHEN tanggal_jatuh_tempo IS NULL THEN 1 ELSE 0 END, tanggal_jatuh_tempo ASC')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('kode_invoice')
                    ->label('No. Faktur')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('customer.nama_customer')
                    ->label('Pelanggan')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->customer?->nama_customer),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sisa_tagihan')
                    ->label('Sisa Piutang')
                    ->getStateUsing(fn ($record) => 'Rp ' . number_format($record->sisa_tagihan ?: $record->grand_total, 0, ',', '.'))
                    ->weight('bold')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('status_tempo')
                    ->label('Status')
                    ->badge()
                    ->color(function ($record) {
                        if (! $record->tanggal_jatuh_tempo) return 'gray';
                        $due = Carbon::parse($record->tanggal_jatuh_tempo)->startOfDay();
                        $today = Carbon::today();

                        if ($due->lt($today)) return 'danger';
                        if ($due->isToday()) return 'warning';
                        return 'info';
                    })
                    ->getStateUsing(function ($record) {
                        if (! $record->tanggal_jatuh_tempo) return 'Belum Diatur';
                        $due = Carbon::parse($record->tanggal_jatuh_tempo)->startOfDay();
                        $today = Carbon::today();

                        if ($due->lt($today)) {
                            $diff = $due->diffInDays($today);
                            return "Lewat {$diff} Hari";
                        }
                        if ($due->isToday()) {
                            return 'Hari Ini';
                        }
                        $diff = $today->diffInDays($due);
                        return "{$diff} Hari Lagi";
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Lihat')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Invoice $record): string => \App\Filament\Admin\Resources\InvoiceResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }
}
