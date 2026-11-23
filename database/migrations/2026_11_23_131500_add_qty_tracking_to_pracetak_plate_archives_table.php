<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pracetak_plate_archives', function (Blueprint $table) {
            if (! Schema::hasColumn('pracetak_plate_archives', 'qty_total')) {
                $table->unsignedInteger('qty_total')->default(0)->after('jumlah_warna');
            }
            if (! Schema::hasColumn('pracetak_plate_archives', 'qty_dipakai')) {
                $table->unsignedInteger('qty_dipakai')->default(0)->after('qty_total');
            }
        });

        // Backfill qty_total dari hasil_baik proses pra cetak atau jumlah_warna, dan qty_dipakai dari IMR yang sedang aktif
        $archives = DB::table('pracetak_plate_archives')->get();
        foreach ($archives as $arch) {
            $process = $arch->pracetak_process_id
                ? DB::table('pracetak_processes')->where('id', $arch->pracetak_process_id)->first()
                : null;

            $qtyTotal = (int) ($process?->hasil_baik ?: ($arch->jumlah_warna ?: 0));
            $qtyDipakai = 0;

            if ($arch->status_kondisi === 'dipakai_printing') {
                $latestIssuedImr = DB::table('imr_printing')
                    ->where('plate_archive_id', $arch->id)
                    ->where('request_plate', true)
                    ->whereNotNull('plate_issued_at')
                    ->orderByDesc('plate_issued_at')
                    ->first();

                $qtyDipakai = $latestIssuedImr && (int) $latestIssuedImr->plate_qty_requested > 0
                    ? min($qtyTotal, (int) $latestIssuedImr->plate_qty_requested)
                    : $qtyTotal;
            }

            DB::table('pracetak_plate_archives')
                ->where('id', $arch->id)
                ->update([
                    'qty_total'   => $qtyTotal,
                    'qty_dipakai' => $qtyDipakai,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('pracetak_plate_archives', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['qty_total', 'qty_dipakai'],
                fn (string $c) => Schema::hasColumn('pracetak_plate_archives', $c)
            ));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
