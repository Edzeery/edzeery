<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * store_scope_id كان unsignedBigInteger معمولاً عليه IFNULL(store_id, 0) بينما
     * store_id هو ULID (char(26)) — أي ULID يتقطّع عند التحويل إلى عدد صحيح
     * (SQLSTATE[01000] 1265 Data truncated) وتتصادم كل المتاجر في scope=1.
     * يصبح العمود نصياً char(26) بنفس المعنى: النظام = '0'، المتجر = ULID.
     */
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropUnique('statuses_scope_type_key_unique');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('store_scope_id');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->string('store_scope_id', 26)
                ->virtualAs(DB::raw("IFNULL(store_id, '0')"))
                ->after('store_id');

            $table->unique(['store_scope_id', 'type', 'key'], 'statuses_scope_type_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropUnique('statuses_scope_type_key_unique');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('store_scope_id');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('store_scope_id')
                ->virtualAs(DB::raw('IFNULL(store_id, 0)'))
                ->after('store_id');

            $table->unique(['store_scope_id', 'type', 'key'], 'statuses_scope_type_key_unique');
        });
    }
};