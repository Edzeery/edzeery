<?php

use App\Domains\Shipping\Models\Carrier;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Data correction: NOEST supports order deletion via POST /delete/order
     * (docs/api_documentation_en_v2_3.md §8 — only unvalidated orders can be
     * deleted). Kept separate from the schema migration so future carriers
     * without real delete support keep the safe default of false.
     */
    public function up(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_order_delete' => true,
        ]);
    }

    public function down(): void
    {
        Carrier::where('code', 'noest')->update([
            'supports_order_delete' => false,
        ]);
    }
};