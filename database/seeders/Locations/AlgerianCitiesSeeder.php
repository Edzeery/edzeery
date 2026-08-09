<?php

namespace Database\Seeders\Locations;

use App\Models\Locations\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlgerianCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $communes = json_decode(file_get_contents(database_path('seeders/json/Commune_Of_Algeria.json')), true);

        // جلب الجزائر لكل متجر
        $algeriaCountries = DB::table('countries')
            ->where('code', 'DZ')
            ->get();

        foreach ($algeriaCountries as $country) {
            foreach ($communes as $order => $commune) {
                // جلب ولاية للربط
                $state = DB::table('states')
                    ->where('country_id', $country->id)
                    ->where('state_code', str_pad($commune['wilaya_id'], 2, '0', STR_PAD_LEFT))
                    ->first();

                if (!$state) continue;
 
                    City::updateOrCreate(
                    [
                        'state_id' => $state->id,
                        'city_code' => $commune['id'],
                    ],
                    [
                        'name'        => $commune['name'],
                        'arabic_name' => $commune['ar_name'],
                        'post_code'   => $commune['post_code'] ?? null,
                        'is_active'   => true,
                        'is_cod_available' => true,
                        'sort_order'  => $order + 1,
                        'longitude'   => $commune['longitude'],
                        'latitude'    => $commune['latitude'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]
                );
            }
        }
    }
}
