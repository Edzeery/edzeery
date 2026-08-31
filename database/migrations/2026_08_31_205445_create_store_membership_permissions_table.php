<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-membership scoped permissions (decision #6 — hybrid approach).
     * A user belonging to multiple stores keeps independent custom permissions
     * per membership, so editing one store's team never overrides another.
     */
    public function up(): void
    {
        Schema::create('store_membership_permissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('membership_id')
                ->constrained('store_memberships')
                ->cascadeOnDelete();
            $table->string('permission');
            $table->timestamps();

            $table->unique(['membership_id', 'permission']);
            $table->index('permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_membership_permissions');
    }
};
