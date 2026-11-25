<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\IssueMaterialResource\Tables\Actions\IssueAction;

class IssueMaterialTable
{
    public static function make(Table $table): Table
    {
        return $table

            ->striped()

            ->defaultSort('tanggal_request','desc')

            ->columns([

                Tables\Columns\TextColumn::make('no_imr')

                    ->label('No IMR')

                    ->weight('bold')

                    ->searchable()

                    ->sortable()

                    ->copyable(),

                Tables\Columns\TextColumn::make('tanggal_request')

                    ->label('Tgl Permintaan')

                    ->date('d M Y')

                    ->sortable(),

                Tables\Columns\TextColumn::make('spk.no_spk')

                    ->label('Nomor SPK')

                    ->searchable(),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')

                    ->label('Departemen')

                    ->badge()

                    ->color(fn ($record): string => $record->stasiun === \App\Models\IMRPrinting::STASIUN_DIEMAKING ? 'info' : 'primary'),

                /*
                |--------------------------------------------------------------------------
                | Total Disetujui
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('approved')

                    ->label('Disetujui')

                    ->badge()

                    ->color('success')

                    ->alignCenter()

                    ->state(fn($record)=>

                        number_format(

                            $record->items->sum('qty_approved'),

                            2

                        )

                    ),

                /*
                |--------------------------------------------------------------------------
                | Sudah Dikeluarkan
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('issued')

                    ->label('Dikeluarkan')

                    ->badge()

                    ->color('info')

                    ->alignCenter()

                    ->state(fn($record)=>

                        number_format(

                            $record->items->sum('qty_issued'),

                            2

                        )

                    ),

                /*
                |--------------------------------------------------------------------------
                | Sisa Pengeluaran
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('remaining')

                    ->label('Sisa')

                    ->badge()

                    ->alignCenter()

                    ->state(function($record){

                        return number_format(

                            $record->items->sum(

                                fn($item)=>

                                    $item->qty_approved

                                    -

                                    $item->qty_issued

                            ),

                            2

                        );

                    })

                    ->color(function($record){

                        $remaining=$record->items->sum(

                            fn($item)=>

                                $item->qty_approved

                                -

                                $item->qty_issued

                        );

                        return $remaining>0

                            ? 'danger'

                            : 'success';

                    }),

                /*
                |--------------------------------------------------------------------------
                | Progress
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('plate_issue_status')
                    ->label('Plate Cetak')
                    ->badge()
                    ->alignCenter()
                    ->state(function ($record): string {
                        if (! $record->request_plate) {
                            return '-';
                        }
                        $qty = (int) ($record->plate_qty_requested ?: 0);
                        if ($record->plate_issued_at) {
                            return "{$qty} Plate · Diserahkan";
                        }
                        if ($record->plate_approved) {
                            $rak = $record->pracetakProcess?->lokasi_simpan_plate ?: ($record->plateArchive?->lokasi_rak ?: 'Rak');

                            return "{$qty} Plate · {$rak}";
                        }

                        return 'Tidak Disetujui';
                    })
                    ->color(function ($record): string {
                        if (! $record->request_plate) {
                            return 'gray';
                        }
                        if ($record->plate_issued_at) {
                            return 'success';
                        }
                        if ($record->plate_approved) {
                            return 'warning';
                        }

                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function ($record) {
                        $approved = (float) $record->items->sum('qty_approved');
                        $issued = (float) $record->items->sum('qty_issued');

                        if ($approved <= 0) {
                            if ($record->request_plate && $record->plate_approved) {
                                return $record->plate_issued_at ? 'Plate Diserahkan (100%)' : 'Plate Siap Keluar (0%)';
                            }

                            return '-';
                        }

                        return number_format($issued, 2)
                            . ' / '
                            . number_format($approved, 2)
                            . ' ('
                            . round(($issued / $approved) * 100, 1)
                            . '%)';
                    }),

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('status')

                    ->label('Status')

                    ->badge()

                    ->formatStateUsing(fn($state)=>match($state){

                        'Approved'=>'Siap Dikeluarkan',

                        'Partial Issued'=>'Sebagian Sudah Dikeluarkan',

                        'Issued'=>'Sudah Dikeluarkan',

                        default=>$state,

                    })

                    ->color(fn($state)=>match($state){

                        'Approved'=>'success',

                        'Partial Issued'=>'warning',

                        'Issued'=>'primary',

                        default=>'gray',

                    }),

            ])

            ->filters([
                Tables\Filters\SelectFilter::make('stasiun')
                    ->label('Stasiun / Divisi')
                    ->options([
                        \App\Models\IMRPrinting::STASIUN_PRINTING  => 'Printing',
                        \App\Models\IMRPrinting::STASIUN_DIEMAKING => 'Die Making',
                    ]),
            ])

            ->actions([

                IssueAction::make(),

            ]);
    }
}