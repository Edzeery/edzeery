<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "collection" lane was renamed to a refund request (طلب تعويض أموال /
     * Refund request / demande de remboursement) and the open-packaging
     * authorization key is temporarily "can_open" until each carrier's exact
     * API key is known.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'is_collection')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('is_collection', 'refund_request');
            $table->renameColumn('authorized_to_open', 'can_open');
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->renameColumn('collection_enabled', 'refund_request_enabled');
            $table->renameColumn('open_authorization_enabled', 'can_open_enabled');
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->renameColumn('supports_collection', 'supports_refund_request');
            $table->renameColumn('supports_open_authorization', 'supports_can_open');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('refund_request', 'is_collection');
            $table->renameColumn('can_open', 'authorized_to_open');
        });

        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->renameColumn('refund_request_enabled', 'collection_enabled');
            $table->renameColumn('can_open_enabled', 'open_authorization_enabled');
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->renameColumn('supports_refund_request', 'supports_collection');
            $table->renameColumn('supports_can_open', 'supports_open_authorization');
        });
    }
};