<?php

namespace App\Services\IMR\Actions;

use App\Models\IMRPrinting;
use App\Models\MasterDepartemen;
use App\Models\MasterItem;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateIMR
{
    public function handle(array $data): IMRPrinting
    {
        $stasiun = $data['stasiun'] ?? IMRPrinting::STASIUN_PRINTING;
        $data['stasiun'] = $stasiun;
        $isDieMaking = $stasiun === IMRPrinting::STASIUN_DIEMAKING;

        $items = $data['imr_items'] ?? [];
        unset($data['imr_items'], $data['no_imr']);

        $requestPlate = ! $isDieMaking && (bool) ($data['request_plate'] ?? false);
        $validItems = [];
        foreach ($items as $item) {
            $itemId = (int) ($item['item_id'] ?? 0);
            $qtyReq = max(0, (float) ($item['qty_input'] ?? $item['qty_request'] ?? 0));
            if ($itemId > 0 && $qtyReq > 0) {
                $item['item_id'] = $itemId;
                $item['qty_request'] = $qtyReq;
                $validItems[] = $item;
            }
        }

        if (empty($validItems) && ! $requestPlate) {
            throw ValidationException::withMessages([
                'imr_items' => $isDieMaking
                    ? 'Minimal isi Qty Request lebih dari 0 pada 1 material (muat dari BOM SPK atau tambah material manual).'
                    : 'Minimal isi Qty Request pada 1 material atau aktifkan Permintaan Plate Cetak dari Rak Pra Cetak.',
            ]);
        }

        if ($requestPlate) {
            $process = ! empty($data['pracetak_process_id'])
                ? PracetakProcess::find($data['pracetak_process_id'])
                : (! empty($data['spk_id']) ? PracetakProcess::where('spk_id', $data['spk_id'])->latest('id')->first() : null);

            $archive = ! empty($data['plate_archive_id'])
                ? PracetakPlateArchive::find($data['plate_archive_id'])
                : ($process?->plateArchive ?: $process?->generatedArchive);

            if (! $process && ! $archive) {
                throw ValidationException::withMessages([
                    'request_plate' => 'SPK ini belum memiliki data Plate Jadi dari Pra Cetak / Arsip Rak.',
                ]);
            }

            $availableInRack = $archive ? $archive->qty_di_rak : (int) ($process?->hasil_baik ?: ($process?->jumlah_warna_plate ?: 0));

            if ($process && ! in_array($process->status, ['selesai', 'handed_over'], true)) {
                throw ValidationException::withMessages([
                    'request_plate' => 'Proses Pra Cetak untuk SPK ini belum selesai disimpan di Rak (Tahap D). Selesaikan Pra Cetak terlebih dahulu sebelum mengajukan pengambilan plate via IMR.',
                ]);
            }

            if ($availableInRack <= 0) {
                throw ValidationException::withMessages([
                    'request_plate' => 'Seluruh plate untuk SPK ini sedang dipakai di divisi Printing (sisa di rak: 0 lembar).',
                ]);
            }

            $qtyReqPlate = max(1, (int) ($data['plate_qty_requested'] ?? $availableInRack));
            if ($qtyReqPlate > $availableInRack) {
                throw ValidationException::withMessages([
                    'plate_qty_requested' => "Jumlah plate yang diminta ({$qtyReqPlate} lembar) melebihi sisa plate di rak ({$availableInRack} lembar).",
                ]);
            }

            $data['pracetak_process_id'] = $process?->id;
            $data['plate_archive_id'] = $archive?->id;
            $data['plate_qty_requested'] = $qtyReqPlate;
            $data['plate_approved'] = false;
        } else {
            $data['request_plate'] = false;
            $data['plate_qty_requested'] = 0;
            $data['plate_approved'] = false;
            $data['pracetak_process_id'] = null;
            $data['plate_archive_id'] = null;
            $data['plate_notes'] = null;
        }

        if (empty($data['departemen_id'])) {
            if ($isDieMaking) {
                $departemen = MasterDepartemen::where('kode', 'DIEM')->first()
                    ?? MasterDepartemen::where('nama_departemen', 'like', '%Die%')->first()
                    ?? MasterDepartemen::firstOrFail();
            } else {
                $departemen = MasterDepartemen::where('kode', 'PRNT')->first()
                    ?? MasterDepartemen::where('kode', 'PRT')->first()
                    ?? MasterDepartemen::where('nama_departemen', 'like', '%Print%')->first()
                    ?? MasterDepartemen::firstOrFail();
            }
            $data['departemen_id'] = $departemen->id;
        }

        $data['status'] = 'Pending';

        return DB::transaction(function () use ($data, $validItems) {
            $imr = IMRPrinting::create($data);

            $itemIds = array_unique(array_column($validItems, 'item_id'));
            $masterItems = MasterItem::with('satuan')->whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($validItems as $item) {
                $masterItem = $masterItems->get($item['item_id']);
                $satuan = trim((string) ($item['satuan'] ?? ''))
                    ?: ($masterItem?->satuan?->nama_satuan ?? '-');

                $imr->items()->create([
                    'item_id'        => $item['item_id'],
                    'satuan'         => $satuan,
                    'total_pesanan'  => (float) ($item['total_pesanan'] ?? 0),
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