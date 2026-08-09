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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->foreignUlid('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignUlid('created_by_membership_id')
                ->nullable()
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->foreignUlid('status_id')
                ->nullable()
                ->constrained('statuses')
                ->nullOnDelete();

            $table->string('number');

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->string('phone_secondary')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('deleted_at');
            // رقم الطلب فريد داخل نفس المتجر

            $table->unique(['store_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
