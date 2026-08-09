<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => config('app_name'),
                'type' => 'string',
                'description' => 'The name of the website',
            ],
            [
                'key' => 'site_logo',
                'value' => 'logo.png',
                'type' => 'string',
                'description' => 'The logo of the website',
            ],
            [
                'key' => 'default_currency',
                'value' => 'DZD',
                'type' => 'string',
                'description' => 'The default currency for transactions',
            ],
        ];
        foreach ($settings as $setting) {
             Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
