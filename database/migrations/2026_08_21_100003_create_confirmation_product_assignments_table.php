<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('confirmation_product_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();
            $table->foreignUlid('membership_id')
                ->constrained('store_memberships')
                ->cascadeOnDelete();
            $table->foreignUlid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['store_id', 'membership_id', 'product_id'], 'cpa_store_member_prod');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('confirmation_product_assignments');
    }
};
