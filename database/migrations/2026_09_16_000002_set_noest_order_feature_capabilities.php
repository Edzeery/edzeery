<?php

use App\Domains\Shipping\Models\Carrier;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Data correction: NOEST's structure flags aligned with its documented
     * API capabilities (docs/توثيق_واجهة_برمجة_تطبيقات_NOEST_v2.3.md).
     * - supports_refund_request → `remboursement` (0/1) is documented.
     * - supports_send_from_carrier_warehouse → `stock` + `quantite` are documented.
     * - supports_can_open stays false: the reference docs contain zero matches
     *   for an open-packaging key, so the lane is hidden and never sent.
     */
    public function up(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_refund_request' => true,
            'supports_send_from_carrier_warehouse' => true,
            'supports_can_open' => false,
        ]);
    }

    public function down(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_refund_request' => false,
            'supports_send_from_carrier_warehouse' => false,
            'supports_can_open' => false,
        ]);
    }
};