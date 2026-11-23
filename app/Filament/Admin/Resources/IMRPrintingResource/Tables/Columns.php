<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Tables;

use Filament\Tables;

class Columns
{
    public static function make(): array
    {
        return [

            Tables\Columns\TextColumn::make('no_imr')
                ->label('No IMR')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal_request')
                ->label('Tanggal Request')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('departemen.nama_departemen')
                ->label('Departemen')
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('items_count')
                ->label('Jumlah Item')
                ->counts('items')
                ->badge(),

            Tables\Columns\TextColumn::make('total_request')
                ->label('Qty Request')
                ->getStateUsing(
                    fn($record) => $record->items->sum('qty_request')
                ),

            Tables\Columns\TextColumn::make('plate_status_badge')
                ->label('Plate Cetak')
                ->badge()
                ->getStateUsing(function ($record): string {
                    if (! $record->request_plate) {
                        return '-';
                    }
                    $qty = (int) ($record->plate_qty_requested ?: 0);
                    if ($record->plate_issued_at) {
                        return "{$qty} Plate · Diserahkan";
                    }
                    if ($record->plate_approved) {
                        return "{$qty} Plate · Siap Keluar";
                    }

                    return "{$qty} Plate · Diminta";
                })
                ->color(function ($record): string {
                    if (! $record->request_plate) {
                        return 'gray';
                    }
                    if ($record->plate_issued_at) {
                        return 'success';
                    }
                    if ($record->plate_approved) {
                        return 'info';
                    }

                    return 'warning';
                }),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->sortable()
                ->color(fn(string $state) => match ($state) {

                    'Pending'        => 'warning',

                    'Approved'       => 'success',

                    'Partial'        => 'info',

                    'Issued'         => 'primary',

                    'Partial Issued' => 'indigo',

                    'Rejected'       => 'danger',

                    'Closed'         => 'gray',

                    default          => 'gray',

                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->since()
                ->toggleable(isToggledHiddenByDefault: true),

        ];
    }
}