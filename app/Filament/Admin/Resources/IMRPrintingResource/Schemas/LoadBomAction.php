<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use App\Models\MasterSpk;
use Filament\Forms;

class LoadBomAction
{
    public static function make(): Forms\Components\Actions
    {
        return Forms\Components\Actions::make([

            Forms\Components\Actions\Action::make('loadBom')

                ->label('Load Material IMR')

                ->icon('heroicon-o-arrow-down-tray')

                ->color('success')

                ->action(function ($get, $set) {

                    $spkId = $get('spk_id');

                    if (!$spkId) {
                        return;
                    }

                    $set('imr_items', []);

                    $spk = MasterSpk::with([

                        'salesOrder.itemable.materials.material.satuan',

                        'salesOrder.itemable.materials.departemen',

                    ])->find($spkId);

                    if (
                        !$spk ||
                        !$spk->salesOrder ||
                        !$spk->salesOrder->itemable
                    ) {
                        return;
                    }

                    $items = [];

                    foreach (
                        $spk
                            ->salesOrder
                            ->itemable
                            ->materials as $bom
                    ) {

                        $items[] = [

                            'item_id' => $bom->master_item_id,

                            'nama_item' =>
                                $bom->material->nama_master_item,

                            'department' =>
                                optional(
                                    $bom->departemen
                                )->nama_departemen,

                            'satuan' =>
                                optional(
                                    $bom
                                        ->material
                                        ->satuan
                                )->nama_satuan,

                            'total_pesanan' =>
                                (float) $bom->qty
                                *
                                (float) $spk
                                    ->salesOrder
                                    ->qty,

                            'qty_request' => 0,

                            'qty_approved' => 0,

                            'qty_input' => 0,

                            'catatan' => null,

                        ];
                    }

                    $set(
                        'imr_items',
                        $items
                    );

                })

        ]);
    }
}