<?php

use App\Models\MasterMaterialPlate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_material_plates', function (Blueprint $table) {
            if (! Schema::hasColumn('master_material_plates', 'master_item_id')) {
                $table->foreignId('master_item_id')
                    ->nullable()
                    ->unique()
                    ->after('id')
                    ->constrained('master_items')
                    ->nullOnDelete();
            }
        });

        // Sinkronkan seluruh Master Material Plate eksisting ke master_items agar langsung bisa dipilih di PR -> PO -> LPB
        MasterMaterialPlate::query()->orderBy('id')->get()->each(function (MasterMaterialPlate $plate) {
            $plate->syncLinkedMasterItem();
        });
    }

    public function down(): void
    {
        Schema::table('master_material_plates', function (Blueprint $table) {
            if (Schema::hasColumn('master_material_plates', 'master_item_id')) {
                $table->dropConstrainedForeignId('master_item_id');
            }
        });
    }
};
