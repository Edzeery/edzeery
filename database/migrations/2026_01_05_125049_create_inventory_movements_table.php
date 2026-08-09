<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // ALWAYS positive, direction determined by type
            $table->unsignedInteger('quantity');

            // Snapshot after movement
            $table->integer('balance_after');

            // InventoryMovementType enum value
            $table->string('type');

            // Order, Purchase, Adjustment, etc.
            $table->nullableUlidMorphs('source');

            $table->timestamps();
            $table->softDeletes();
            $table->index(
                ['store_id', 'product_variant_id', 'deleted_at', 'created_at', 'type'],
                'im_Sid_PVid_DelAt_CAt_type_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
