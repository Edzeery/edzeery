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
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // nullable → system category
            $table->foreignUlid('store_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignUlid('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            // // scope: system = 0, store = store_id
            // $table->unsignedBigInteger('store_scope_id')->virtualAs(DB::raw('IFNULL(store_id, 0)'));

            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            // فريد داخل الـ scope
            $table->unique(['store_id', 'deleted_at', 'slug'], 'categories_scope_slug_unique');

            $table->index(['store_id', 'parent_id']);
            $table->index(['store_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
