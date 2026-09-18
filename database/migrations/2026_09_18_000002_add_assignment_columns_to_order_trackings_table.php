<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors the confirmation-side assignment columns on order_trackings so
     * the tracking team can later be assigned per-order with the exact same
     * shape as the confirmation team (Phase 34.2/34.3). Schema only — no
     * assignment logic is introduced by this phase.
     */
    public function up(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->foreignUlid('assigned_to_membership_id')
                ->nullable()
                ->after('order_id')
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->timestamp('assigned_at')
                ->nullable()
                ->after('assigned_to_membership_id');

            $table->string('assignment_method')
                ->nullable()
                ->after('assigned_at');

            $table->foreignUlid('assigned_by_membership_id')
                ->nullable()
                ->after('assignment_method')
                ->constrained('store_memberships')
                ->nullOnDelete();

            $table->index('assigned_to_membership_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropIndex(['assigned_to_membership_id']);
            $table->dropColumn([
                'assigned_to_membership_id',
                'assigned_at',
                'assignment_method',
                'assigned_by_membership_id',
            ]);
        });
    }
};