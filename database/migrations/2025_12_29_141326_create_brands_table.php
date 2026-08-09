<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
           $table->ulid('id')->primary();

            // NULL = system template brand
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();



            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
            $table->unique(['store_id', 'deleted_at', 'slug'], 'brands_scope_slug_unique');
             $table->index(['store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
