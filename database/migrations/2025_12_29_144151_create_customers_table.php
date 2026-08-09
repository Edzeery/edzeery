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
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();
            $table->boolean('status')->default(true);

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            $table->foreignUlid('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignUlid('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignUlid('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->unique(['store_id', 'phone']);
            // اختياري لكن مُستحسن للأداء إذا عندك فلترة كثيرة حسب الموقع:
            $table->index(['country_id', 'state_id', 'city_id'], 'customers_location_idx');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
