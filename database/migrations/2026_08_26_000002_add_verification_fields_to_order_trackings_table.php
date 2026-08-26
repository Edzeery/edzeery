<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            // Step 1: barcode scan verification (physical package arrival)
            $table->string('verification_barcode')->nullable()->unique()->after('webhook_token');
            $table->timestamp('verified_at')->nullable()->after('verification_barcode');
            $table->foreignUlid('verified_by_membership_id')
                ->nullable()
                ->after('verified_at')
                ->constrained('store_memberships')
                ->nullOnDelete();

            // Step 2: inspection decision (processing)
            $table->string('inspection_result')->nullable()->after('verified_by_membership_id');
            $table->text('inspection_notes')->nullable()->after('inspection_result');
            $table->timestamp('processed_at')->nullable()->after('inspection_notes');
            $table->foreignUlid('processed_by_membership_id')
                ->nullable()
                ->after('processed_at')
                ->constrained('store_memberships')
                ->nullOnDelete();

            // Step 3: requeue decision (business action, separate from inspection)
            $table->timestamp('requeued_at')->nullable()->after('processed_by_membership_id');
            $table->foreignUlid('requeued_by_membership_id')
                ->nullable()
                ->after('requeued_at')
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->index('verified_at');
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropColumn([
                'verification_barcode', 'verified_at', 'verified_by_membership_id',
                'inspection_result', 'inspection_notes', 'processed_at', 'processed_by_membership_id',
                'requeued_at', 'requeued_by_membership_id',
            ]);
        });
    }
};
