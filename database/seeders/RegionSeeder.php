<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Mansoura' => [
                'Al Mansurah Center', 'Gesr El Suez', 'Bahr El Saghir',
                'Al Manyal', 'Al Corniche', 'El Gomhoria',
            ],
            'Mit Ghamr' => ['Mit Ghamr Center', 'Al Barha', 'Samalut'],
            'Nasr City' => ['Nasr City 1', 'Nasr City 2', 'Al Hay Al Ashir'],
        ];

        foreach ($data as $cityName => $regions) {
            $city = City::where('name', $cityName)->first();
            if (! $city) {
                continue;
            }
            foreach ($regions as $regionName) {
                Region::firstOrCreate(['name' => $regionName, 'city_id' => $city->id]);
            }
        }
    }
}
