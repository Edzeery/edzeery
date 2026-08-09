<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Countries
        |--------------------------------------------------------------------------
        */
        Schema::create('countries', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name');
            $table->string('arabic_name')->nullable();

            $table->string('code', 5)->unique();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | States (Wilayas)
        |--------------------------------------------------------------------------
        */
        Schema::create('states', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('country_id')
                ->constrained()
                ->cascadeOnDelete();

            // الرمز الإداري الرسمي 01 → 58
            $table->char('state_code', 2)->index();

            $table->string('name');
            $table->string('arabic_name')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();

            $table->timestamps();

            $table->unique(['country_id', 'state_code']);
        });

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */
        Schema::create('cities', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('state_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('arabic_name')->nullable();

            // اختياري لو احتجت كود بلدية لاحقًا
            $table->string('city_code', 10)->nullable()->index();
            $table->string('post_code');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_cod_available')->default(true);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->timestamps();

            $table->unique(['state_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
