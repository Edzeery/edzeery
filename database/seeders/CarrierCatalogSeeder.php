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
            ['name' => 'ZR Express', 'slug' => 'zr-express', 'is_active' => true],
        ];

        foreach ($platforms as $platform) {
            CarrierPlatform::updateOrCreate(
                ['slug' => $platform['slug']],
                $platform,
            );
        }

        $ecotrack = CarrierPlatform::where('slug', 'ecotrack')->first();
        $zr = CarrierPlatform::where('slug', 'zr-express')->first();

        $carriers = [
            [
                'platform_id'       => $ecotrack?->id,
                'name'              => 'Ecotrack',
                'code'              => 'ecotrack',
                'is_active'         => true,
                'sort_order'        => 1,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id'       => $ecotrack?->id,
                'name'              => 'World Express',
                'code'              => 'world_express',
                'is_active'         => true,
                'sort_order'        => 2,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                    ['key' => 'account_id', 'label' => 'Account ID', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'platform_id'       => $ecotrack?->id,
                'name'              => 'Anderson',
                'code'              => 'anderson',
                'is_active'         => true,
                'sort_order'        => 3,
                'credential_fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
                ],
            ],
            [
                'platform_id'       => $zr?->id,
                'name'              => 'ZR Express v2',
                'code'              => 'zrexpress_v2',
                'is_active'         => true,
                'sort_order'        => 1,
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