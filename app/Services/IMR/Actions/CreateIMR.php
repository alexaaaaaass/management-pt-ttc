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

                $qtyReq = (float) ($item['qty_request'] ?? ($item['qty_input'] ?? 0));

                if ($qtyReq <= 0) {
                    continue;
                }

                $imr->items()->create([

                    'item_id'        => $item['item_id'],

                    'satuan'         => $item['satuan'] ?? '',

                    'total_pesanan'  => (float) ($item['total_pesanan'] ?? 0),

                    'qty_request'    => $qtyReq,

                    'qty_approved'   => 0,

                    'qty_issued'     => 0,

                    'qty_returned'   => 0,

                    'catatan'        => $item['catatan'] ?? null,

                ]);

            }

            if ($imr->items()->count() === 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'imr_items' => 'Minimal 1 material harus diisi Qty Request lebih besar dari 0.',
                ]);
            }

            return $imr;
        });
    }
}