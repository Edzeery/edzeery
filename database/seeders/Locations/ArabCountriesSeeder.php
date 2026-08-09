<?php

namespace Database\Seeders\Locations;

use App\Models\Locations\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArabCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $countries = [
            ['DZ', 'Algeria', 'الجزائر'],
            ['MA', 'Morocco', 'المغرب'],
            ['TN', 'Tunisia', 'تونس'],
            ['LY', 'Libya', 'ليبيا'],
            ['EG', 'Egypt', 'مصر'],
            ['SD', 'Sudan', 'السودان'],
            ['SA', 'Saudi Arabia', 'السعودية'],
            ['AE', 'United Arab Emirates', 'الإمارات'],
            ['QA', 'Qatar', 'قطر'],
            ['KW', 'Kuwait', 'الكويت'],
            ['BH', 'Bahrain', 'البحرين'],
            ['OM', 'Oman', 'عُمان'],
            ['YE', 'Yemen', 'اليمن'],
            ['JO', 'Jordan', 'الأردن'],
            ['LB', 'Lebanon', 'لبنان'],
            ['SY', 'Syria', 'سوريا'],
            ['IQ', 'Iraq', 'العراق'],
            ['PS', 'Palestine', 'فلسطين'],
            ['MR', 'Mauritania', 'موريتانيا'],
            ['SO', 'Somalia', 'الصومال'],
            ['DJ', 'Djibouti', 'جيبوتي'],
            ['KM', 'Comoros', 'جزر القمر'],
        ];

        foreach ($countries as $order => [$code, $en, $ar]) {
            Country::updateOrCreate(
                [
                    'code'     => $code,
                ],
                [
                    'name'          => $en,
                    'arabic_name'          => $ar,
                    'is_active'        => true,
                    'is_cod_available' => true,
                    'sort_order'       => $order + 1,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]
            );
        }
    }
}
