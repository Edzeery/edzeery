<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dedicated table for the manager product VISIBILITY SCOPE.
 *
 * Until now this concern shared `confirmation_product_assignments` with the
 * order-confirmation SPECIALIST assignment, so two UIs with conflicting intent
 * wrote to one table and no column told them apart. This table ends that
 * collision; the specialist table keeps its own rows untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_product_scopes', function (Blueprint $table) {
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
            // Null-on-delete (not cascade): the scope outlives the member who
            // created it. Cascading here would silently drop a live visibility
            // restriction the moment that person left the store.
            $table->foreignUlid('created_by_membership_id')
                ->nullable()
                ->constrained('store_memberships')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['store_id', 'membership_id', 'product_id'], 'mps_store_member_prod');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_product_scopes');
    }
};
