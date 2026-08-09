<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // null = system status
            $table->foreignUlid('store_id')
                ->nullable()
                ->constrained()->nullOnDelete();

            /**
             * نطاق موحد:
             * system = 0
             * store  = store_id
             */
            $table->unsignedBigInteger('store_scope_id')
                ->virtualAs(DB::raw('IFNULL(store_id, 0)'));

            // order | payment | shipment
            $table->string('type');

            // pending | paid | shipped | custom_hold
            $table->string('key');

            // الاسم المعروض
            $table->string('label');

            $table->string('color')->default('gray');

            // حالة نظامية غير قابلة للحذف
            $table->boolean('is_system')->default(false);

            // هل تؤثر على المخزون؟
            $table->boolean('affects_inventory')->default(false);

            // SALE | RETURN | RESERVE | RELEASE | ADJUSTMENT
            $table->string('movement_type')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            /**
             * يضمن:
             * - system status فريد
             * - store status فريد داخل نفس المتجر
             */
            $table->unique(
                ['store_scope_id', 'type', 'key'],
                'statuses_scope_type_key_unique'
            );
            $table->unique(['store_id', 'type', 'key']);

            $table->index(['type', 'is_system']);
            $table->index(['store_id', 'type']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};
