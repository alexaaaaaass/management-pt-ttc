<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MasterDepartemen;
use Illuminate\Support\Facades\DB;

class CreateIMR
{
    public function handle(array $data): ImrPrinting
    {
        $items = $data['imr_items'] ?? [];

        unset($data['imr_items']);

        $departemen = MasterDepartemen::where('kode', 'PRNT')->firstOrFail();

        $data['departemen_id'] = $departemen->id;

        $data['status'] = 'Pending';

        return DB::transaction(function () use ($data, $items) {

            $imr = ImrPrinting::create($data);

            foreach ($items as $item) {

                if (($item['qty_request'] ?? 0) <= 0) {
                    continue;
                }

                $imr->items()->create([

                    'item_id'        => $item['item_id'],

                    'satuan'         => $item['satuan'],

                    'total_pesanan'  => $item['total_pesanan'],

                    'qty_request'    => $item['qty_request'],

                    'qty_approved'   => 0,

                    'qty_issued'     => 0,

                    'qty_returned'   => 0,

                    'catatan'        => $item['catatan'] ?? null,

                ]);

            }

            return $imr;
        });
    }
}