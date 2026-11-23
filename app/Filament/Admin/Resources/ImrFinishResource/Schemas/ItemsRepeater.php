<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;

class ItemsRepeater
{
    public static function make(): Section
    {
        return Section::make('Daftar Material IMR Finish')
            ->description('Muat material dari SPK, lalu isi jumlah material yang diminta.')
            ->schema([
                Forms\Components\Actions::make([
                    LoadBomAction::make(),
                ]),

                Repeater::make('imr_items')
                    ->label('Material dari BOM')
                    ->defaultItems(0)
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(function (array $state): ?string {
                        $nama = $state['nama_item'] ?? '-';
                        $qty = max(0, (float) ($state['qty_input'] ?? $state['qty_request'] ?? 0));
                        $qtyFormatted = \App\Support\MoneyHelper::format($qty, 2) ?? '0';
                        $satuan = trim((string) ($state['satuan'] ?? ''));

                        return $satuan !== '' ? "{$nama} ({$qtyFormatted} {$satuan})" : "{$nama} ({$qtyFormatted})";
                    })
                    ->schema([
                        Forms\Components\Hidden::make('item_id'),
                        Forms\Components\Hidden::make('qty_request')->default(0),

                        Forms\Components\TextInput::make('nama_item')
                            ->label('Nama Material')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('department')
                            ->label('Departemen BOM')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('satuan')
                            ->label('Satuan')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('total_pesanan')
                            ->label('Total Kebutuhan SPK')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('qty_input')
                            ->label('Qty Request')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $qty = max(0, (float) $state);
                                $set('qty_input', $qty);
                                $set('qty_request', $qty);
                            })
                            ->helperText('Isi jumlah material yang benar-benar diminta.'),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpanFull(),
            ]);
    }
}