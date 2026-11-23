<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MaterialStock;
use App\Services\IMR\Helpers\UpdateStatus;
use App\Services\IMR\Validators\ValidateApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\MaterialMovement\RecordMaterialMovement;

class ApproveIMR
{
    public function __construct(
        protected ValidateApproval $validator,
        protected UpdateStatus $statusUpdater,
        protected RecordMaterialMovement $movement,
    ) {}

    public function handle(
        ImrPrinting $imr,
        array $data
    ): void {

        DB::transaction(function () use ($imr, $data) {

            if ($imr->status !== 'Pending') {

                throw ValidationException::withMessages([
                    'status' => 'IMR sudah diproses.',
                ]);

            }

            $imr->loadMissing(['items.item', 'pracetakProcess', 'plateArchive']);

            $this->validator->handle(
                $imr,
                $data
            );

            foreach ($imr->items as $index => $item) {
                $qtyApproved = (float) ($data['items'][$index]['qty_approved'] ?? 0);

                $stock = MaterialStock::where('item_id', $item->item_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new \Exception("Stock {$item->item->nama_master_item} tidak ditemukan.");
                }

                if ($qtyApproved > $stock->available) {
                    throw new \Exception("Available Stock {$item->item->nama_master_item} hanya {$stock->available}");
                }

                $item->update([
                    'qty_approved' => $qtyApproved,
                ]);

                if ($qtyApproved > 0) {
                    $stock->increment('allocation', $qtyApproved);

                    $this->movement->handle([
                        'item_id'        => $item->item_id,
                        'reference_type' => 'IMR',
                        'reference_id'   => $imr->id,
                        'movement_type'  => 'APPROVE',
                        'qty'            => $qtyApproved,
                        'stock_before'   => $stock->on_hand,
                        'stock_after'    => $stock->on_hand,
                        'remarks'        => "Approve IMR {$imr->no_imr}",
                    ]);
                }
            }

            // Persetujuan pengambilan Plate Cetak Jadi dari Rak Pra Cetak (1 pintu via IMR)
            if ($imr->request_plate) {
                $approvePlate = (bool) ($data['plate_approved'] ?? true);

                if ($approvePlate) {
                    if ($imr->pracetakProcess && ! in_array($imr->pracetakProcess->status, ['selesai', 'handed_over'], true)) {
                        throw new \Exception('Plate pada Pra Cetak belum selesai disimpan di Rak (status saat ini: ' . $imr->pracetakProcess->status . ').');
                    }

                    $archive = $imr->plateArchive ?: ($imr->pracetakProcess?->plateArchive ?: $imr->pracetakProcess?->generatedArchive);
                    if ($archive) {
                        $availableInRack = $archive->qty_di_rak;
                        $qtyRequested = (int) ($imr->plate_qty_requested ?: 1);
                        if ($qtyRequested > $availableInRack) {
                            throw new \Exception("Jumlah plate diminta ({$qtyRequested} lembar) melebihi sisa plate di rak ({$availableInRack} lembar).");
                        }
                    }
                }

                $imr->update([
                    'plate_approved' => $approvePlate,
                ]);
            }

            $this->statusUpdater->handle($imr);

        });

    }
}