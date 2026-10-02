<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use Filament\Forms;

class ItemsRepeater
{
    public static function make(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Material Request')

            ->description('Daftar material yang akan diminta ke gudang.')

            ->schema([

                Forms\Components\Repeater::make('imr_items')

                    ->label('')

                    ->default([])


                    ->collapsible()

                    ->collapsed()

                    ->cloneable(false)

                    ->addable(false)

                    ->deletable(false)

                    ->reorderable(false)

                    ->itemLabel(function (array $state): ?string {

                        $nama = $state['nama_item'] ?? '-';

                        $qty = $state['total_pesanan'] ?? 0;

                        $satuan = $state['satuan'] ?? '';

                        return "{$nama} ({$qty} {$satuan})";

                    })

                    ->schema([

                        /*
                        |--------------------------------------------------------------------------
                        | Hidden
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Hidden::make('item_id'),

                        /*
                        |--------------------------------------------------------------------------
                        | Nama Item
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('nama_item')

                            ->label('Material')

                            ->disabled()

                            ->dehydrated(false),

                        /*
                        |--------------------------------------------------------------------------
                        | Departemen
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('department')

                            ->label('Departemen')

                            ->disabled()

                            ->dehydrated(false),

                        /*
                        |--------------------------------------------------------------------------
                        | Satuan
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('satuan')

                            ->disabled()

                            ->dehydrated(),

                        /*
                        |--------------------------------------------------------------------------
                        | Total BOM
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('total_pesanan')

                            ->label('Qty BOM')

                            ->numeric()

                            ->disabled()

                            ->dehydrated(),

                        /*
                        |--------------------------------------------------------------------------
                        | Qty Approved
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('qty_approved')

                            ->numeric()

                            ->disabled()

                            ->default(0)

                            ->dehydrated(),

                        /*
                        |--------------------------------------------------------------------------
                        | Qty Request
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Hidden::make('qty_request')

                            ->default(0),

                        /*
                        |--------------------------------------------------------------------------
                        | Qty Input User
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\TextInput::make('qty_input')

                            ->label('Qty Request')

                            ->numeric()

                            ->default(0)

                            ->minValue(0)

                            ->live(debounce: 500)

                            ->afterStateUpdated(function (

                                $state,

                                callable $set,

                                callable $get

                            ) {

                                $bom = (float) ($get('total_pesanan') ?? 0);

                                $qty = (float) $state;

                                /*
                                |--------------------------------------------------------------------------
                                | Tidak boleh minus
                                |--------------------------------------------------------------------------
                                */

                                if ($qty < 0) {

                                    $qty = 0;

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | Tidak boleh melebihi BOM
                                |--------------------------------------------------------------------------
                                */

                                $set('qty_input', $qty);

                                $set('qty_request', $qty);

                            })

                            ->helperText(

                                'Masukkan QTY Request.'

                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | Catatan
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Textarea::make('catatan')

                            ->rows(2)

                            ->columnSpanFull(),

                    ])

                    ->columns(4)

            ])

            ->columnSpanFull();
    }
}