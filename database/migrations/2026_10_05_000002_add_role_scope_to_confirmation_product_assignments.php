<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| PHASE 35.3 — product ownership becomes role-aware
|--------------------------------------------------------------------------
|
| Confirmation product assignments used to be a single flat roster shared by
| both distribution roles. Strict ownership routing (see ProductOwnershipRouter)
| needs to tell a confirmation owner from a tracking owner, so each row now
| carries a role scope. Existing rows default to 'confirm' (the only role that
| historically consumed this table), which keeps every legacy assignment valid
| without a re-seed.
|
| The uniqueness key and lookup index are widened by role_scope: the same
| member may own the same product for both roles (two rows), but never twice
| within one role.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        // MySQL executes DDL outside a transaction, so a failed run can leave
        // the column behind while the migration is still marked Pending. The add
        // step is guarded so `php artisan migrate` can be safely re-run.
        if (! Schema::hasColumn('confirmation_product_assignments', 'role_scope')) {
            Schema::table('confirmation_product_assignments', function (Blueprint $table) {
                $table->string('role_scope')->default('confirm')->after('product_id');
            });
        }

        // The legacy uniqueness index is the index InnoDB picked to back the
        // store_id foreign key, and InnoDB refuses to drop an index a constraint
        // still depends on (SQLSTATE 1553, "Cannot drop index ... needed in a
        // foreign key constraint"). The constraints come off first, the index is
        // replaced, and the constraints are rebuilt on the widened keys.
        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['membership_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropUnique('cpa_store_member_prod');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->unique(
                ['store_id', 'membership_id', 'product_id', 'role_scope'],
                'cpa_store_member_prod_scope'
            );
            $table->index(['store_id', 'role_scope', 'product_id'], 'cpa_store_scope_product_idx');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('membership_id')->references('id')->on('store_memberships')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // The mirror image: the constraints that sit on the widened keys must
        // go before those keys, so the legacy uniqueness can take their place.
        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['membership_id']);
            $table->dropForeign(['product_id']);
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropIndex('cpa_store_scope_product_idx');
            $table->dropUnique('cpa_store_member_prod_scope');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->unique(['store_id', 'membership_id', 'product_id'], 'cpa_store_member_prod');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('membership_id')->references('id')->on('store_memberships')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropColumn('role_scope');
        });
    }
};
