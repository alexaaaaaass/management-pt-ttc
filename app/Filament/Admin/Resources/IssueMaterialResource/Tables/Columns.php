<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Tables;

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
                ->label('Tanggal')
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

            Tables\Columns\TextColumn::make('approved_qty')
                ->label('Approved')
                ->alignCenter()
                ->badge()
                ->color('success')
                ->state(function ($record) {

                    return $record->items->sum('qty_approved');

                }),

            Tables\Columns\TextColumn::make('issued_qty')
                ->label('Issued')
                ->alignCenter()
                ->badge()
                ->color('warning')
                ->state(function ($record) {

                    return $record->items->sum('qty_issued');

                }),

            Tables\Columns\TextColumn::make('remaining_qty')
                ->label('Remaining')
                ->alignCenter()
                ->badge()
                ->color('danger')
                ->state(function ($record) {

                    return $record->items->sum(function ($item) {

                        return
                            $item->qty_approved
                            -
                            $item->qty_issued;

                    });

                }),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->sortable()
                ->color(fn ($state) => match ($state) {

                    'Approved'       => 'success',

                    'Partial Issued' => 'warning',

                    'Issued'         => 'primary',

                    default          => 'gray',

                }),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created')
                ->since()
                ->toggleable(isToggledHiddenByDefault: true),

        ];
    }
}