<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Scopes a confirmation shift to one distribution role: 'confirm' (the
     * existing confirmation-team behaviour — the column default keeps every
     * legacy row valid with no data loss or re-seeding) or 'track' (tracking
     * team capacity). Phase 34.1 schema groundwork only; the composite index
     * serves the per-role active-shift lookups of Phase 34.2/34.3.
     */
    public function up(): void
    {
        Schema::table('confirmation_shifts', function (Blueprint $table) {
            $table->string('role_scope')->default('confirm')->after('is_active');

            $table->index(['store_id', 'membership_id', 'role_scope', 'is_active'], 'confirmation_shifts_store_role_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('confirmation_shifts', function (Blueprint $table) {
            $table->dropIndex('confirmation_shifts_store_role_active_idx');
            $table->dropColumn('role_scope');
        });
    }
};