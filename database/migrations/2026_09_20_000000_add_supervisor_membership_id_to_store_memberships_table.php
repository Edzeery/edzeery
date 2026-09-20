<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('store_memberships', 'supervisor_membership_id')) {
            Schema::table('store_memberships', function (Blueprint $table) {
                $table->foreignUlid('supervisor_membership_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('store_memberships')
                    ->nullOnDelete();
                $table->index('supervisor_membership_id');
            });
        }

        $this->backfillSupervisors();
    }

    /**
     * Staff memberships created before the hierarchy column existed were
     * anchored via `invited_by`. Reparent those to the inviter's ACTIVE
     * MANAGER membership in the same store when one exists — a staff member
     * reports to their manager, never to the owner/admin who merely invited
     * the team. Rows without a matching active manager stay unrouted.
     */
    private function backfillSupervisors(): void
    {
        StoreMembership::query()
            ->where('role', StoreRoleEnum::STAFF->value)
            ->whereNull('supervisor_membership_id')
            ->chunkById(200, function ($staffMemberships) {
                foreach ($staffMemberships as $membership) {
                    $supervisor = StoreMembership::query()
                        ->where('store_id', $membership->store_id)
                        ->where('user_id', $membership->invited_by)
                        ->where('is_active', true)
                        ->where('role', StoreRoleEnum::MANAGER->value)
                        ->first();

                    if ($supervisor) {
                        $membership->update(['supervisor_membership_id' => $supervisor->id]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('store_memberships', function (Blueprint $table) {
            $table->dropIndex(['supervisor_membership_id']);
            $table->dropForeign(['supervisor_membership_id']);
            $table->dropColumn('supervisor_membership_id');
        });
    }
};