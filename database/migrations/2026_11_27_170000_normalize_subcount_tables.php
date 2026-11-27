<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcount_outs', function (Blueprint $table) {
            if (! Schema::hasColumn('subcount_outs', 'jenis_proses')) {
                $table->string('jenis_proses', 100)->nullable()->after('supplier_id');
            }
            if (! Schema::hasColumn('subcount_outs', 'tanggal_target_selesai')) {
                $table->date('tanggal_target_selesai')->nullable()->after('tanggal_subcount');
            }
        });

        Schema::table('subcount_ins', function (Blueprint $table) {
            if (! Schema::hasColumn('subcount_ins', 'subcount_out_id')) {
                $table->foreignId('subcount_out_id')
                    ->nullable()
                    ->after('no_subcount')
                    ->constrained('subcount_outs')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('subcount_ins', 'supplier_id')) {
                $table->foreignId('supplier_id')
                    ->nullable()
                    ->after('subcount_out_id')
                    ->constrained('master_suppliers')
                    ->nullOnDelete();
            }
        });

        // Backfill subcount_ins.subcount_out_id & supplier_id from existing subcount_in_items
        $subcountIns = DB::table('subcount_ins')
            ->whereNull('subcount_out_id')
            ->orWhereNull('supplier_id')
            ->get(['id', 'subcount_out_id', 'supplier_id']);

        foreach ($subcountIns as $in) {
            $outId = $in->subcount_out_id;
            if (! $outId) {
                $outId = DB::table('subcount_in_items')
                    ->where('subcount_in_id', $in->id)
                    ->whereNotNull('subcount_out_id')
                    ->value('subcount_out_id');
            }

            if ($outId) {
                $supplierId = $in->supplier_id ?: DB::table('subcount_outs')
                    ->where('id', $outId)
                    ->value('supplier_id');

                DB::table('subcount_ins')
                    ->where('id', $in->id)
                    ->update([
                        'subcount_out_id' => $outId,
                        'supplier_id' => $supplierId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('subcount_ins', function (Blueprint $table) {
            if (Schema::hasColumn('subcount_ins', 'supplier_id')) {
                $table->dropConstrainedForeignId('supplier_id');
            }
            if (Schema::hasColumn('subcount_ins', 'subcount_out_id')) {
                $table->dropConstrainedForeignId('subcount_out_id');
            }
        });

        Schema::table('subcount_outs', function (Blueprint $table) {
            if (Schema::hasColumn('subcount_outs', 'tanggal_target_selesai')) {
                $table->dropColumn('tanggal_target_selesai');
            }
            if (Schema::hasColumn('subcount_outs', 'jenis_proses')) {
                $table->dropColumn('jenis_proses');
            }
        });
    }
};
