<?php

namespace Database\Seeders;

use Database\Seeders\Locations\AlgerianCitiesSeeder;
use Database\Seeders\Locations\AlgerianStatesSeeder;
use Database\Seeders\Locations\ArabCountriesSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Outhebox\Translations\Database\Seeders\ContributorSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            SettingSeeder::class,
            RolesAndPermissionsSeeder::class,
            SystemStatusesSeeder::class,
            ArabCountriesSeeder::class,
            AlgerianStatesSeeder::class,
            AlgerianCitiesSeeder::class,
            StoreRolesAndPermissionsSeeder::class,
            PlansSeeder::class,
            InvoiceSeeder::class,
            InvoiceTemplateSeeder::class,
            ContributorSeeder::class,
        ]);
    }
}
