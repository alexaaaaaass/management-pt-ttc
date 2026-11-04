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

                        $bom = (float) ($state['total_pesanan'] ?? 0);

                        $qty = (float) ($state['qty_input'] ?? ($state['qty_request'] ?? 0));

                        $satuan = $state['satuan'] ?? '';

                        if ($bom > 0) {
                            return "{$nama} (BOM: {$bom} {$satuan} | Req: {$qty} {$satuan})";
                        }

                        return "{$nama} [Bahan Penolong] (Req: {$qty} {$satuan})";

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

                            ->formatStateUsing(function ($state) {
                                $val = (float) ($state ?? 0);
                                return $val > 0 ? (string) $val : 'Bahan Penolong (Bebas Request)';
                            })

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

                            ->default(0)

                            ->dehydrated(),

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

                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                $req = (float) ($get('qty_request') ?? 0);
                                if ($req > 0 && ! $state) {
                                    $set('qty_input', $req);
                                }
                            })

                            ->afterStateUpdated(function (

                                $state,

                                callable $set,

                                callable $get

                            ) {

                                $bom = (float) ($get('total_pesanan') ?? 0);

                                $qty = max(0, (float) $state);

                                /*
                                |--------------------------------------------------------------------------
                                | Jika BOM > 0, batasi tidak boleh melebihi BOM
                                | Jika BOM = 0 (Bahan Penolong), bebas request sesuai kebutuhan mesin
                                |--------------------------------------------------------------------------
                                */

                                if ($bom > 0 && $qty > $bom) {
                                    $qty = $bom;
                                    \Filament\Notifications\Notification::make()
                                        ->title('Melebihi Kebutuhan BOM')
                                        ->body("Bahan baku terhitung tidak boleh melebihi total pesanan BOM ({$bom}).")
                                        ->warning()
                                        ->send();
                                }

                                $set('qty_input', $qty);

                                $set('qty_request', $qty);

                            })

                            ->helperText(function (callable $get) {
                                $bom = (float) ($get('total_pesanan') ?? 0);
                                $satuan = $get('satuan') ?? '';
                                if ($bom > 0) {
                                    return "Bahan Baku: Maksimal request sesuai BOM ({$bom} {$satuan}).";
                                }
                                return "Bahan Penolong: Bebas input request sesuai kebutuhan aktual di mesin.";
                            }),

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