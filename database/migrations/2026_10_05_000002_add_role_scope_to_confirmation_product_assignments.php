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
        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->string('role_scope')->default('confirm')->after('product_id');
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
    }

    public function down(): void
    {
        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropIndex('cpa_store_scope_product_idx');
            $table->dropUnique('cpa_store_member_prod_scope');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->unique(['store_id', 'membership_id', 'product_id'], 'cpa_store_member_prod');
        });

        Schema::table('confirmation_product_assignments', function (Blueprint $table) {
            $table->dropColumn('role_scope');
        });
    }
};
