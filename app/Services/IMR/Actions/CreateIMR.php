<?php

namespace App\Services\IMR\Actions;

use App\Models\ImrPrinting;
use App\Models\MasterDepartemen;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateIMR
{
    public function handle(array $data): ImrPrinting
    {
        $items = $data['imr_items'] ?? [];
        unset($data['imr_items']);

        $requestPlate = (bool) ($data['request_plate'] ?? false);
        $validItems = array_values(array_filter($items, fn ($item) => (float) ($item['qty_request'] ?? 0) > 0));

        if (empty($validItems) && ! $requestPlate) {
            throw ValidationException::withMessages([
                'imr_items' => 'Minimal isi Qty Request pada 1 material atau aktifkan Permintaan Plate Cetak dari Rak Pra Cetak.',
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

            if ($process && $process->status !== 'selesai') {
                $statusMsg = $process->status === 'handed_over'
                    ? 'Plate untuk SPK ini sudah diserahkan ke divisi Printing sebelumnya.'
                    : 'Proses Pra Cetak untuk SPK ini belum selesai disimpan di Rak (Tahap D). Selesaikan Pra Cetak terlebih dahulu sebelum mengajukan pengambilan plate via IMR.';

                throw ValidationException::withMessages([
                    'request_plate' => $statusMsg,
                ]);
            }

            if (! $process && $archive && $archive->status_kondisi !== 'tersimpan') {
                throw ValidationException::withMessages([
                    'request_plate' => "Arsip plate {$archive->kode_arsip} saat ini tidak dalam kondisi Tersimpan di Rak.",
                ]);
            }

            $data['pracetak_process_id'] = $process?->id;
            $data['plate_archive_id'] = $archive?->id;
            $data['plate_qty_requested'] = max(
                1,
                (int) ($data['plate_qty_requested'] ?? $process?->hasil_baik ?? $process?->jumlah_warna_plate ?? $archive?->jumlah_warna ?? 1)
            );
            $data['plate_approved'] = false;
        } else {
            $data['request_plate'] = false;
            $data['plate_qty_requested'] = 0;
            $data['plate_approved'] = false;
            $data['pracetak_process_id'] = null;
            $data['plate_archive_id'] = null;
            $data['plate_notes'] = null;
        }

        $departemen = MasterDepartemen::where('kode', 'PRNT')->first()
            ?? MasterDepartemen::where('nama_departemen', 'like', '%Print%')->first()
            ?? MasterDepartemen::firstOrFail();

        $data['departemen_id'] = $departemen->id;
        $data['status'] = 'Pending';

        return DB::transaction(function () use ($data, $validItems) {
            $imr = ImrPrinting::create($data);

            foreach ($validItems as $item) {
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