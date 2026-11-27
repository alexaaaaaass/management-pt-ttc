<?php

namespace App\Filament\Admin\Resources\KaryawanResource\RelationManagers;

use App\Models\PengPinjaman;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PinjamansRelationManager extends RelationManager
{
    protected static string $relationship = 'pinjamans';
    protected static ?string $title = 'Histori Pinjaman / Kasbon';
    protected static ?string $icon = 'heroicon-o-credit-card';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('nomor_bukti')->label('No. Bukti')->badge()->color('primary'),
                TextColumn::make('tanggal_pengajuan')->label('Tanggal')->date('d/m/Y'),
                TextColumn::make('nilai_pinjaman')
                    ->label('Nilai Pinjaman')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),
                TextColumn::make('cicilan_per_bulan')
                    ->label('Cicilan / Bln')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),
                TextColumn::make('sisa_pinjaman')
                    ->label('Sisa Pinjaman')
                    ->getStateUsing(fn (PengPinjaman $r) => $r->sisa_pinjaman)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn ($state) => (float) $state <= 0 ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default    => 'warning',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('bukaPinjaman')
                    ->label('Detail / Cicilan')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PengPinjaman $record) => url('/admin/peng-pinjamen/' . $record->id . '/edit')),
                Tables\Actions\Action::make('pdfPinjaman')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (PengPinjaman $record) => route('hrd.pinjaman.preview', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
