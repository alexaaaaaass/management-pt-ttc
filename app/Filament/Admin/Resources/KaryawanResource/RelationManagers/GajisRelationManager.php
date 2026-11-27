<?php

namespace App\Filament\Admin\Resources\KaryawanResource\RelationManagers;

use App\Models\Gaji;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GajisRelationManager extends RelationManager
{
    protected static string $relationship = 'gajis';
    protected static ?string $title = 'Histori Slip Gaji';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('periode', 'desc')
            ->columns([
                TextColumn::make('kode_slip')->label('Kode Slip')->badge()->color('primary'),
                TextColumn::make('periode')->label('Periode')->badge(),
                TextColumn::make('tipe_payroll')->label('Skema'),
                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),
                TextColumn::make('total_potongan')
                    ->label('Potongan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->color('danger'),
                TextColumn::make('gaji_bersih')
                    ->label('Take Home Pay')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Paid'     => 'success',
                        'Approved' => 'info',
                        default    => 'warning',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('pdfSlip')
                    ->label('PDF Slip')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Gaji $record) => route('hrd.slip-gaji.preview', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
