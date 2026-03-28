<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Egypt', 'code' => 'EG'],
            ['name' => 'Saudi Arabia', 'code' => 'SA'],
            ['name' => 'Jordan', 'code' => 'JO'],
            ['name' => 'Sudan', 'code' => 'SD'],
        ];

        foreach ($countries as $c) {
            Country::firstOrCreate(['code' => $c['code']], $c);
        }
    }
}
