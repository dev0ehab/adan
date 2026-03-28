<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Vaccine;
use Illuminate\Database\Seeder;

class VaccineSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Holstein Cow' => [
                ['name' => 'FMD (Foot & Mouth)', 'doses_count' => 2, 'interval_days' => 180, 'is_lifetime' => false],
                ['name' => 'Brucellosis', 'doses_count' => 1, 'interval_days' => null, 'is_lifetime' => true],
                ['name' => 'Anthrax', 'doses_count' => 1, 'interval_days' => 365, 'is_lifetime' => false],
                ['name' => 'LSD (Lumpy Skin Disease)', 'doses_count' => 1, 'interval_days' => 365, 'is_lifetime' => false],
                ['name' => 'Blackleg', 'doses_count' => 2, 'interval_days' => 30, 'is_lifetime' => false],
            ],
            'Ossimi Sheep' => [
                ['name' => 'FMD (Foot & Mouth)', 'doses_count' => 2, 'interval_days' => 180, 'is_lifetime' => false],
                ['name' => 'Sheep Pox', 'doses_count' => 1, 'interval_days' => 365, 'is_lifetime' => false],
                ['name' => 'Enterotoxemia', 'doses_count' => 2, 'interval_days' => 365, 'is_lifetime' => false],
                ['name' => 'Brucellosis', 'doses_count' => 1, 'interval_days' => null, 'is_lifetime' => true],
            ],
            'Zaraibi Goat' => [
                ['name' => 'FMD (Foot & Mouth)', 'doses_count' => 2, 'interval_days' => 180, 'is_lifetime' => false],
                ['name' => 'Goat Pox', 'doses_count' => 1, 'interval_days' => 365, 'is_lifetime' => false],
                ['name' => 'Enterotoxemia', 'doses_count' => 2, 'interval_days' => 365, 'is_lifetime' => false],
            ],
            'Broiler Chicken' => [
                ['name' => 'Newcastle Disease (NDV)', 'doses_count' => 3, 'interval_days' => 30, 'is_lifetime' => false],
                ['name' => 'Avian Influenza (H5N1)', 'doses_count' => 2, 'interval_days' => 180, 'is_lifetime' => false],
                ['name' => 'Gumboro (IBD)', 'doses_count' => 2, 'interval_days' => 21, 'is_lifetime' => false],
                ['name' => "Marek's Disease", 'doses_count' => 1, 'interval_days' => null, 'is_lifetime' => true],
                ['name' => 'Infectious Bronchitis', 'doses_count' => 2, 'interval_days' => 90, 'is_lifetime' => false],
            ],
        ];

        foreach ($data as $animalName => $vaccines) {
            $animal = Animal::where('name', $animalName)->first();
            if (! $animal) {
                continue;
            }
            foreach ($vaccines as $v) {
                Vaccine::firstOrCreate(
                    ['name' => $v['name'], 'animal_id' => $animal->id],
                    $v
                );
            }
        }
    }
}
