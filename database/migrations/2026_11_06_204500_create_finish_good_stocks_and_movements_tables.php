<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finish_good_stocks')) {
            Schema::create('finish_good_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finish_good_item_id')
                    ->unique()
                    ->constrained('finish_good_items')
                    ->cascadeOnDelete();
                $table->decimal('on_hand', 18, 2)->default(0);
                $table->decimal('blocked', 18, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('finish_good_movements')) {
            Schema::create('finish_good_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finish_good_item_id')
                    ->constrained('finish_good_items')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('spk_id')->nullable()->index();
                $table->string('movement_type', 20); // IN, OUT, ADJUSTMENT
                $table->string('source', 50)->nullable(); // PACKAGING, SUBCOUNT_IN, SURAT_JALAN, MANUAL_ADJUSTMENT
                $table->decimal('qty', 18, 2)->default(0);
                $table->decimal('before_stock', 18, 2)->default(0);
                $table->decimal('after_stock', 18, 2)->default(0);
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
            });
        }

        Schema::table('subcount_in_items', function (Blueprint $table) {
            $table->unsignedBigInteger('item_hasil_id')->nullable()->change();

            if (! Schema::hasColumn('subcount_in_items', 'tipe_hasil')) {
                $table->string('tipe_hasil', 30)->default('material')->after('subcount_out_id');
            }

            if (! Schema::hasColumn('subcount_in_items', 'spk_id')) {
                $table->unsignedBigInteger('spk_id')->nullable()->after('tipe_hasil');
            }

            if (! Schema::hasColumn('subcount_in_items', 'finish_good_item_id')) {
                $table->unsignedBigInteger('finish_good_item_id')->nullable()->after('item_hasil_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subcount_in_items', function (Blueprint $table) {
            $cols = array_filter(['tipe_hasil', 'spk_id', 'finish_good_item_id'], fn ($c) => Schema::hasColumn('subcount_in_items', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('finish_good_movements');
        Schema::dropIfExists('finish_good_stocks');
    }
};

