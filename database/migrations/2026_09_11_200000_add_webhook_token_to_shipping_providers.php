<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-store secret that delivery webhooks POST against
     * (/webhooks/delivery/{token}) plus a last-seen stamp so the merchant can
     * verify the endpoint is alive. Token is nullable: only carrier-backed,
     * active providers get one when the merchant enables the endpoint, so the
     * route can never resolve a disabled provider.
     */
    public function up(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->uuid('webhook_token')
                ->unique()
                ->nullable()
                ->after('is_default');

            $table->timestamp('webhook_last_seen_at')
                ->nullable()
                ->after('webhook_token');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_providers', function (Blueprint $table) {
            $table->dropColumn(['webhook_last_seen_at', 'webhook_token']);
        });
    }
};