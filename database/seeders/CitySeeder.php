<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Dakahlia' => [
                'Mansoura', 'Mit Ghamr', 'Talkha', 'Aga',
                'Belqas', 'Dikirnis', 'Sinbillawin', 'Sherbin',
            ],
            'Cairo' => [
                'Nasr City', 'Heliopolis', 'Maadi', 'Zamalek',
                'Shubra', 'New Cairo',
            ],
            'Giza' => [
                'Giza City', '6th of October', 'Sheikh Zayed',
                'Haram', 'Dokki',
            ],
        ];

        foreach ($data as $governorateName => $cities) {
            $gov = Governorate::where('name', $governorateName)->first();
            if (! $gov) {
                continue;
            }
            foreach ($cities as $cityName) {
                City::firstOrCreate(['name' => $cityName, 'governorate_id' => $gov->id]);
            }
        }
    }
}
