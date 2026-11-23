<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MaterialStock;
use App\Services\IMR\Actions\UpdateIssueStatus;
use App\Services\MaterialMovement\RecordMaterialMovement;
use App\Services\Pracetak\PracetakCostingService;
use Illuminate\Support\Facades\DB;

class IssueMaterial
{
    public function __construct(
        protected UpdateIssueStatus $statusUpdater,
        protected RecordMaterialMovement $movement,
    ) {}

    public function handle(
        ImrPrinting $imr,
        array $data
    ): void {
        DB::transaction(function () use ($imr, $data) {
            $imr->loadMissing([
                'items.item.stock',
                'pracetakProcess',
                'plateArchive',
            ]);

            $anyProcessed = false;

            foreach ($imr->items as $index => $item) {
                $qtyIssue = (float) ($data['items'][$index]['qty_issue'] ?? 0);

                if ($qtyIssue <= 0) {
                    continue;
                }

                $remaining = (float) $item->qty_approved - (float) $item->qty_issued;

                if ($qtyIssue > $remaining) {
                    throw new \Exception("Qty Issue {$item->item->nama_master_item} melebihi sisa Qty Approved ({$remaining}).");
                }

                $stock = MaterialStock::where('item_id', $item->item_id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new \Exception("Stock material {$item->item->nama_master_item} tidak ditemukan.");
                }

                // Cek fisik on_hand (karena alokasi untuk IMR ini sudah dipesan saat Approve)
                if ($qtyIssue > (float) $stock->on_hand) {
                    throw new \Exception("Stok fisik {$item->item->nama_master_item} di gudang hanya {$stock->on_hand} {$item->satuan}.");
                }

                // Fix bug: increment qty_issued tepat 1 kali saja
                $item->increment('qty_issued', $qtyIssue);

                $stockBefore = (float) $stock->on_hand;
                $newOnHand = max(0, round($stockBefore - $qtyIssue, 4));
                $newAllocation = max(0, round((float) $stock->allocation - $qtyIssue, 4));

                $stock->update([
                    'on_hand'    => $newOnHand,
                    'allocation' => $newAllocation,
                ]);

                $this->movement->handle([
                    'item_id'        => $item->item_id,
                    'reference_type' => 'IMR',
                    'reference_id'   => $imr->id,
                    'movement_type'  => 'ISSUE',
                    'qty'            => -$qtyIssue,
                    'stock_before'   => $stockBefore,
                    'stock_after'    => $newOnHand,
                    'remarks'        => "Issue Material {$imr->no_imr}",
                ]);

                $anyProcessed = true;
            }

            // Serah terima Plate Cetak Jadi dari Rak Pra Cetak (1 pintu via Warehouse Pengeluaran Material)
            if ($imr->hasPendingPlateIssue() && ! empty($data['issue_plate'])) {
                $receiver = trim((string) ($data['plate_received_by'] ?? ''));
                if ($receiver === '') {
                    throw new \Exception('Nama Penerima (Operator Printing) wajib diisi untuk serah terima plate.');
                }

                $handoverNotes = trim((string) ($data['plate_handover_notes'] ?? ''));
                $imrNote = "Via {$imr->no_imr}" . ($handoverNotes !== '' ? " — {$handoverNotes}" : '');

                $qtyPlateOut = max(1, (int) ($imr->plate_qty_requested ?: 1));

                if ($imr->pracetakProcess) {
                    app(PracetakCostingService::class)->handOverToPrinting(
                        $imr->pracetakProcess,
                        $receiver,
                        $imrNote,
                        $qtyPlateOut
                    );
                } elseif ($imr->plateArchive) {
                    $archive = $imr->plateArchive;
                    $totalPlate = $archive->effective_qty_total;
                    $availableInRack = $archive->qty_di_rak;
                    if ($availableInRack > 0 && $qtyPlateOut > $availableInRack) {
                        throw new \Exception("Jumlah plate diminta ({$qtyPlateOut} lembar) melebihi sisa di rak ({$availableInRack} lembar).");
                    }
                    $newOut = min($totalPlate, (int) $archive->qty_dipakai + $qtyPlateOut);
                    $archive->increment('jumlah_dipakai');
                    $archive->update([
                        'qty_total'      => $totalPlate,
                        'qty_dipakai'    => $newOut,
                        'status_kondisi' => 'dipakai_printing',
                        'last_used_at'   => now(),
                        'catatan'        => trim(($archive->catatan ? $archive->catatan . "\n" : '') . '[' . now()->format('d/m/Y H:i') . "] Diserahkan {$qtyPlateOut} lembar ke {$receiver} (Sisa di rak: " . max(0, $totalPlate - $newOut) . " lbr) ({$imrNote})"),
                    ]);
                }

                $imr->update([
                    'plate_issued_at'   => now(),
                    'plate_received_by' => $receiver,
                    'plate_notes'       => $handoverNotes !== '' ? $handoverNotes : $imr->plate_notes,
                ]);

                $anyProcessed = true;
            }

            if (! $anyProcessed) {
                throw new \Exception('Tidak ada material atau plate yang diproses. Isi jumlah keluar atau centang serah terima plate.');
            }

            $this->statusUpdater->handle($imr);
        });
    }
}