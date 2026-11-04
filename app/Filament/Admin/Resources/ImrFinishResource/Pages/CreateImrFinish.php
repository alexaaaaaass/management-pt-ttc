<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Pages;

use App\Filament\Admin\Resources\ImrFinishResource;
use App\Models\ImrFinish;
use App\Models\MasterDepartemen;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateImrFinish extends CreateRecord
{
    protected static string $resource = ImrFinishResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['imr_items'] ?? [];
        unset($data['imr_items']);

        $dept = MasterDepartemen::where('kode', 'FNSH')->first();
        if ($dept) {
            $data['departemen_id'] = $dept->id;
        }

        $data['status'] = 'Pending';

        return DB::transaction(function () use ($data, $items) {
            $imr = ImrFinish::create($data);

            $validCount = 0;
            foreach ($items as $item) {
                $qtyReq = (float) ($item['qty_request'] ?? ($item['qty_input'] ?? 0));
                if ($qtyReq <= 0 || empty($item['item_id'])) {
                    continue;
                }

                $imr->items()->create([
                    'item_id'       => $item['item_id'],
                    'satuan'        => $item['satuan'] ?? '',
                    'total_pesanan' => (float) ($item['total_pesanan'] ?? 0),
                    'qty_request'   => $qtyReq,
                    'qty_approved'  => 0,
                    'qty_issued'    => 0,
                    'qty_returned'  => 0,
                    'catatan'       => $item['catatan'] ?? null,
                ]);
                $validCount++;
            }

            if ($validCount === 0) {
                throw ValidationException::withMessages([
                    'imr_items' => 'Minimal 1 material harus dipilih dan diisi Qty Request lebih besar dari 0.',
                ]);
            }

            return $imr;
        });
    }
}
