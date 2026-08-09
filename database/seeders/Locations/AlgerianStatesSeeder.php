<?php

namespace Database\Seeders\Locations;

use App\Models\Locations\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlgerianStatesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $wilayas = json_decode(file_get_contents(database_path('seeders/json/Wilaya_Of_Algeria.json')), true);

        // جلب الجزائر لكل متجر
        $algeriaCountries = DB::table('countries')
            ->where('code', 'DZ')
            ->get();

        foreach ($algeriaCountries as $country) {
            foreach ($wilayas as $order => $wilaya) {
              State::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'state_code' => str_pad($wilaya['code'], 2, '0', STR_PAD_LEFT),
                    ],
                    [
                        'name'       => $wilaya['name'],
                        'arabic_name' => $wilaya['ar_name'],
                        'is_active'  => true,
                        'is_cod_available' => true,
                        'sort_order' => $order + 1,
                        'longitude'  => $wilaya['longitude'],
                        'latitude'   => $wilaya['latitude'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
