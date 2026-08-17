<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->string('type', 50);
            $table->boolean('count_at_incurrence')->default(false);
            $table->string('counterparty_name')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->date('reminder_date')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('status');
            $table->index('type');
            $table->index('due_date');
            $table->index('store_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
