<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks an assigned order (or tracking row) whose assignee left their
     * shift during a handover sweep and for whom no eligible replacement
     * existed. The assignment is kept — never silently unassigned — and the
     * flag records the wait so the queue can surface it (badge = follow-up).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('stranded_at')
                ->nullable()
                ->after('over_capacity');
            $table->index(['store_id', 'stranded_at']);
        });

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->timestamp('stranded_at')
                ->nullable()
                ->after('over_capacity');
            $table->index(['store_id', 'stranded_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'stranded_at']);
            $table->dropColumn('stranded_at');
        });

        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'stranded_at']);
            $table->dropColumn('stranded_at');
        });
    }
};
