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
                    ->schema([
                        Forms\Components\Hidden::make('item_id'),

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
                            ->label('Qty Diminta')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
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