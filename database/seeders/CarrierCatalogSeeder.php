<?php

namespace Database\Seeders;

use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\CarrierPlatform;
use Illuminate\Database\Seeder;

class CarrierCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['name' => 'Ecotrack', 'slug' => 'ecotrack', 'is_active' => true],
            ['name' => 'EcomV2', 'slug' => 'ecomv2', 'is_active' => true],
            ['name' => 'ZR Express', 'slug' => 'zr-express', 'is_active' => true],
            ['name' => 'Yalidine', 'slug' => 'yalidine', 'is_active' => true],
            ['name' => 'Noest', 'slug' => 'noest', 'is_active' => true],
            ['name' => 'New ZR Express', 'slug' => 'newzrexpress', 'is_active' => true],

        ];

        foreach ($platforms as $platform) {
            CarrierPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform,
            );
        }

        $ecotrack = CarrierPlatform::where('slug', 'ecotrack')->first();
        $ecomv2 = CarrierPlatform::where('slug', 'ecomv2')->first();
        $zr = CarrierPlatform::where('slug', 'zr-express')->first();
        $yalidine = CarrierPlatform::where('slug', 'yalidine')->first();
        $noest = CarrierPlatform::where('slug', 'noest')->first();
        $newzrexpress = CarrierPlatform::where('slug', 'newzrexpress')->first();

        $carriers = [
            [
                'platform_id' => $ecotrack?->id,
                'name' => 'Ovred Ecotrack',
                'code' => 'ovred.ecotrack',
                'is_active' => true,
                'sort_order' => 1,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $ecotrack?->id,
                'name' => 'DHD Ecotrack',
                'code' => 'dhd.ecotrack',
                'is_active' => true,
                'sort_order' => 2,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $ecotrack?->id,
                'name' => 'World Express',
                'code' => 'world_express',
                'is_active' => true,
                'sort_order' => 3,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                    ['key' => 'account_id', 'label' => 'Account ID', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'platform_id' => $ecotrack?->id,
                'name' => 'Anderson',
                'code' => 'anderson',
                'is_active' => true,
                'sort_order' => 4,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $ecomv2?->id,
                'name' => 'EcomV2',
                'code' => 'ecomv2',
                'is_active' => true,
                'sort_order' => 5,
                'credential_fields' => [
                    ['key' => 'id_store', 'label' => 'Id store', 'type' => 'text', 'required' => true],
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $zr?->id,
                'name' => 'ZR Express',
                'code' => 'zrexpress',
                'is_active' => true,
                'sort_order' => 6,
                'credential_fields' => [
                    ['key' => 'clé', 'label' => 'Clé', 'type' => 'text', 'required' => true],
                    ['key' => 'token', 'label' => 'Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $yalidine?->id,
                'name' => 'Yalidine',
                'code' => 'yalidine',
                'is_active' => true,
                'sort_order' => 7,
                'credential_fields' => [
                    ['key' => 'X_API_ID', 'label' => 'X API ID', 'type' => 'text', 'required' => true],
                    ['key' => 'X_API_TOKEN', 'label' => 'X API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $noest?->id,
                'name' => 'Noest',
                'code' => 'noest',
                'is_active' => true,
                'sort_order' => 8,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id' => $newzrexpress?->id,
                'name' => 'New ZR Express',
                'code' => 'newzrexpress',
                'is_active' => true,
                'sort_order' => 9,
                'credential_fields' => [
                    ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                    ['key' => 'tenant_id', 'label' => 'Tenant ID', 'type' => 'text', 'required' => true],
                ],
            ],
        ];

        foreach ($carriers as $carrier) {
            $data = array_diff_key($carrier, ['name' => '', 'code' => '']);
            Carrier::updateOrCreate(
                ['code' => $carrier['code']],
                array_merge($data, ['name' => $carrier['name']]),
            );
        }
    }
}
