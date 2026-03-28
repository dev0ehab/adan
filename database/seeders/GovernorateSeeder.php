<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $egypt = Country::where('code', 'EG')->first();
        if (! $egypt) {
            return;
        }

        $governorates = [
            'Cairo', 'Giza', 'Alexandria', 'Dakahlia', 'Red Sea',
            'Beheira', 'Fayoum', 'Gharbeya', 'Ismailia', 'Menofia',
            'Minya', 'Qaliubeya', 'New Valley', 'Suez', 'Asyut',
            'South Sinai', 'Kafr el-Sheikh', 'Matruh', 'Luxor',
            'Qena', 'North Sinai', 'Sohag', 'Beni Suef',
            'Port Said', 'Damietta', 'Aswan', 'Sharqia',
        ];

        foreach ($governorates as $name) {
            Governorate::firstOrCreate(
                ['name' => $name, 'country_id' => $egypt->id]
            );
        }
    }
}
