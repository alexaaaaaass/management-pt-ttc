<?php

namespace App\Filament\Admin\Resources\MasterMaterialPlateResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Histori Mutasi Plate (Ledger IN / OUT)';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'IN',
                        'danger'  => 'OUT',
                        'warning' => 'ADJUSTMENT',
                    ]),

                Tables\Columns\TextColumn::make('qty_masuk')
                    ->label('Masuk (IN)')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? '+' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') : '-')
                    ->color('success')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('qty_keluar')
                    ->label('Keluar (OUT)')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? '-' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') : '-')
                    ->color('danger')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('saldo_setelah')
                    ->label('Saldo Akhir')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet')
                    ->weight('bold')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga / Sheet')
                    ->money('IDR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_nominal')
                    ->label('Total Nominal')
                    ->money('IDR')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Mutasi')
                    ->options([
                        'IN'         => 'IN (Masuk)',
                        'OUT'        => 'OUT (Keluar)',
                        'ADJUSTMENT' => 'Adjustment / Reversal',
                    ]),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
