<?php

namespace App\Filament\Admin\Resources\MaterialLedgerResource\Tables;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaterialLedgerTable
{
    public static function make(Table $table): Table
    {
        return $table

            ->defaultSort('created_at', 'desc')

            ->columns([

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.nama_master_item')
                    ->label('Material')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('movement_type')
                    ->label('Aktivitas')
                    ->colors([
                        'success' => 'LPB',
                        'warning' => 'APPROVE',
                        'danger'  => 'ISSUE',
                        'info'    => 'RETURN',
                    ]),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qty')
                    ->alignRight()
                    ->formatStateUsing(function ($record) {

                        if ($record->qty > 0) {
                            return '+' . number_format($record->qty, 2);
                        }

                        return number_format($record->qty, 2);
                    }),

                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Stock Sebelum')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Stock Sesudah')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Referensi')
                    ->searchable(),

                // Tables\Columns\TextColumn::make('user.name')
                //     ->label('User'),

            ])

            /*
            |--------------------------------------------------------------------------
            | Filter
            |--------------------------------------------------------------------------
            */

            ->filters([

                /*
                | Filter Material
                */

                Tables\Filters\SelectFilter::make('item_id')

                    ->label('Material')

                    ->relationship(
                        'item',
                        'nama_master_item'
                    )

                    ->searchable()

                    ->preload(),

                /*
                | Filter Periode
                */

                Tables\Filters\Filter::make('tanggal')

                    ->label('Periode')

                    ->form([

                        Forms\Components\DatePicker::make('dari')
                            ->label('Tanggal Mulai'),

                        Forms\Components\DatePicker::make('sampai')
                            ->label('Tanggal Akhir'),

                    ])

                    ->query(function (
                        Builder $query,
                        array $data
                    ) {

                        return $query

                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date) =>
                                    $query->whereDate(
                                        'created_at',
                                        '>=',
                                        $date
                                    )
                            )

                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date) =>
                                    $query->whereDate(
                                        'created_at',
                                        '<=',
                                        $date
                                    )
                            );
                    }),

            ]);
    }
}