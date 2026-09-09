<?php

use App\Domains\Shipping\Models\Carrier;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Data correction: NOEST's real API capability flags.
     *
     * - supports_express_economic is false on purpose — the reference docs
     *   (docs/توثيق_واجهة_برمجة_تطبيقات_NOEST_v2.3.md) contain zero matches
     *   for express/economic delivery tiers.
     * - supports_api_notes relies on POST /add/maj (§ add/maj).
     * - supports_price_sync relies on GET /fees (§13).
     *
     * Kept separate from the schema migration so future carriers without real
     * capability data keep the safe default of false.
     */
    public function up(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_delivery' => true,
            'supports_exchange' => true,
            'supports_pickup' => true,
            'supports_free_shipping_mode' => true,
            'supports_express_economic' => false,
            'supports_api_notes' => true,
            'supports_price_sync' => true,
        ]);
    }

    public function down(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_delivery' => false,
            'supports_exchange' => false,
            'supports_pickup' => false,
            'supports_free_shipping_mode' => false,
            'supports_express_economic' => false,
            'supports_api_notes' => false,
            'supports_price_sync' => false,
        ]);
    }
};
