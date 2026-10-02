<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Tables;

use Filament\Tables;

class Filters
{
    public static function make(): array
    {
        return [

            Tables\Filters\SelectFilter::make('status')
                ->options([

                     'Pending'        => 'Pending',

                    'Approved'       => 'Approved',

                    'Partial'        => 'Partial',

                    'Issued'         => 'Issued',

                    'Partial Issued' => 'Partial Issued',

                    'Rejected'       => 'Rejected',

                    'Closed'         => 'Closed',


                ]),

            Tables\Filters\SelectFilter::make('departemen_id')
                ->relationship(
                    'departemen',
                    'nama_departemen'
                ),

            Tables\Filters\Filter::make('today')
                ->label('Hari Ini')
                ->query(fn($query) =>

                    $query->whereDate(
                        'tanggal_request',
                        today()
                    )

                ),

        ];
    }
}