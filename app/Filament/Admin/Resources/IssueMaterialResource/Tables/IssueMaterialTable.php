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

                    ->color('primary'),

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

                Tables\Columns\TextColumn::make('progress')

                    ->label('Progress')

                    ->state(function($record){

                        $approved=$record->items->sum('qty_approved');

                        $issued=$record->items->sum('qty_issued');

                        if($approved==0){

                            return '-';

                        }

                        return

                            number_format($issued,2)

                            .' / '.

                            number_format($approved,2)

                            .' ('

                            .round(

                                ($issued/$approved)*100,

                                1

                            )

                            .'%)';

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

            ->actions([

                IssueAction::make(),

            ]);
    }
}