<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            GovernorateSeeder::class,
            CitySeeder::class,
            RegionSeeder::class,
            AnimalCategorySeeder::class,
            AnimalSeeder::class,
            VaccineSeeder::class,
            UserSeeder::class,
        ]);
    }
}
