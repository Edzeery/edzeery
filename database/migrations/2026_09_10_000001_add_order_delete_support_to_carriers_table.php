<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * New carrier capability: cancel-send / order deletion pre-expedition.
     *
     * Only carriers whose integration exposes a delete endpoint (NOEST:
     * POST /api/public/delete/order) get supports_order_delete = true. The safe
     * default is false so a carrier without real delete support never gets its
     * shipments cancelled at the API level.
     */
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('supports_order_delete')->default(false)->after('supports_api_notes');
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn('supports_order_delete');
        });
    }
};