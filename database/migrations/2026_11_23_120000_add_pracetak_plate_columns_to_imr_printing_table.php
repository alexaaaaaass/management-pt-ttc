<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Perbaiki enum status imr_printing agar mendukung 'Partial Issued' dan 'Issued'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `imr_printing` MODIFY COLUMN `status` ENUM('Pending','Approved','Partial','Partial Issued','Issued','Rejected','Closed') NOT NULL DEFAULT 'Pending'");
        }

        Schema::table('imr_printing', function (Blueprint $table) {
            if (! Schema::hasColumn('imr_printing', 'pracetak_process_id')) {
                $table->foreignId('pracetak_process_id')
                    ->nullable()
                    ->after('spk_id')
                    ->constrained('pracetak_processes')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('imr_printing', 'plate_archive_id')) {
                $table->foreignId('plate_archive_id')
                    ->nullable()
                    ->after('pracetak_process_id')
                    ->constrained('pracetak_plate_archives')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('imr_printing', 'request_plate')) {
                $table->boolean('request_plate')->default(false)->after('status');
            }
            if (! Schema::hasColumn('imr_printing', 'plate_qty_requested')) {
                $table->unsignedInteger('plate_qty_requested')->default(0)->after('request_plate');
            }
            if (! Schema::hasColumn('imr_printing', 'plate_approved')) {
                $table->boolean('plate_approved')->default(false)->after('plate_qty_requested');
            }
            if (! Schema::hasColumn('imr_printing', 'plate_issued_at')) {
                $table->timestamp('plate_issued_at')->nullable()->after('plate_approved');
            }
            if (! Schema::hasColumn('imr_printing', 'plate_received_by')) {
                $table->string('plate_received_by', 150)->nullable()->after('plate_issued_at');
            }
            if (! Schema::hasColumn('imr_printing', 'plate_notes')) {
                $table->text('plate_notes')->nullable()->after('plate_received_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('imr_printing', function (Blueprint $table) {
            if (Schema::hasColumn('imr_printing', 'plate_archive_id')) {
                $table->dropConstrainedForeignId('plate_archive_id');
            }
            if (Schema::hasColumn('imr_printing', 'pracetak_process_id')) {
                $table->dropConstrainedForeignId('pracetak_process_id');
            }
            $dropCols = array_values(array_filter([
                'request_plate',
                'plate_qty_requested',
                'plate_approved',
                'plate_issued_at',
                'plate_received_by',
                'plate_notes',
            ], fn (string $col) => Schema::hasColumn('imr_printing', $col)));

            if (! empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
