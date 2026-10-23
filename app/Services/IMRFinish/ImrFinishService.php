<?php

namespace App\Services\IMRFinish;

use App\Models\ImrFinish;
use App\Models\MasterItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImrFinishService
{
    public function create(array $data): ImrFinish
    {
        return DB::transaction(function () use ($data) {
            $items = $data['imr_items'] ?? [];

            unset($data['imr_items'], $data['no_imr']);

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'imr_items' => 'Daftar material wajib diisi. Gunakan tombol Load Material IMR.',
                ]);
            }

            $imr = ImrFinish::create($data);

            foreach ($items as $item) {
                $itemId = (int) ($item['item_id'] ?? 0);
                $qtyRequest = (float) (
                    $item['qty_input'] ?? $item['qty_request'] ?? 0
                );

                if (
                    $itemId <= 0 ||
                    ! MasterItem::whereKey($itemId)->exists()
                ) {
                    throw ValidationException::withMessages([
                        'imr_items' => 'Ada material yang tidak valid. Muat ulang daftar material.',
                    ]);
                }

                if ($qtyRequest < 0) {
                    throw ValidationException::withMessages([
                        'imr_items' => 'Qty Request tidak boleh negatif.',
                    ]);
                }

                if ($qtyRequest <= 0) {
                    continue;
                }

                $imr->items()->create([
                    'item_id' => $itemId,
                    'total_pesanan' => (float) ($item['total_pesanan'] ?? 0),
                    'qty_request' => $qtyRequest,
                    'qty_approved' => 0,
                    'qty_issued' => 0,
                    'qty_returned' => 0,
                    'satuan' => $item['satuan'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            if (! $imr->items()->exists()) {
                throw ValidationException::withMessages([
                    'imr_items' => 'Masukkan Qty Request lebih dari 0 untuk minimal satu material.',
                ]);
            }

            return $imr;
        });
    }
}