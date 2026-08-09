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
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();

            // Company information
            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_logo')->nullable();

            // Client information
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();

            // Invoice details
            $table->date('invoice_date');
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Calculations
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Template and status
            $table->foreignUlid('template_id')->nullable()->constrained('templates')->nullOnDelete();
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');

            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
