<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * حالة تأكيد مخصصة مرتبطة بحالة أصلية: `linked_to` يحمل مفتاح الحالة
     * النظامية التي تتصرف الشحنة وفقها (انتقالات/سمات مخزون/عرض).
     * يُملأ فقط على صفوف متجر (is_system = false) من نوع 'order'.
     */
    public function up(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('linked_to')->nullable()->after('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('linked_to');
        });
    }
};