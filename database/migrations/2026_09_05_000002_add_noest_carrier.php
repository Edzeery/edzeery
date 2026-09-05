<?php

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $platform = CarrierPlatform::updateOrCreate(
            ['slug' => 'noest'],
            ['name' => 'NOEST', 'is_active' => true],
        );

        Carrier::updateOrCreate(
            ['code' => 'noest'],
            [
                'platform_id'       => $platform->id,
                'name'              => 'NOEST',
                'is_active'         => true,
                'sort_order'        => 5,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                    ['key' => 'guid', 'label' => 'User GUID', 'type' => 'text', 'required' => true],
                    ['key' => 'api_base', 'label' => 'API Base URL', 'type' => 'text', 'required' => false],
                ],
            ],
        );
    }

    public function down(): void
    {
        Carrier::where('code', 'noest')->delete();
        CarrierPlatform::where('slug', 'noest')->delete();
    }
};