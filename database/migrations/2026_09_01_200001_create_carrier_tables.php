<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform-managed delivery carrier reference tables.
     *
     * carrier_platforms : master companies (Ecotrack, ZR Express, ...)
     * carriers          : sub-companies belonging to a platform (World Express, Anderson,
     *                     ZR Express v2) OR standalone companies (parent_id = null).
     *                     Each carries a JSON definition of credential fields shown to the
     *                     merchant when connecting that carrier.
     */
    public function up(): void
    {
        Schema::create('carrier_platforms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('carriers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('platform_id')
                ->nullable()
                ->constrained('carrier_platforms')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code')->nullable();
            $table->json('credential_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['platform_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
        Schema::dropIfExists('carrier_platforms');
    }
};
