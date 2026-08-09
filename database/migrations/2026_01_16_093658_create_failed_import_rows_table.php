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
        Schema::create('failed_import_rows', function (Blueprint $table): void {
             $table->ulid('id')->primary();
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained('stores')
                ->nullOnDelete();
            $table->json('data');
            $table->foreignUlid('import_id')->constrained('imports')->cascadeOnDelete();
            $table->text('validation_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_import_rows');
    }
};
