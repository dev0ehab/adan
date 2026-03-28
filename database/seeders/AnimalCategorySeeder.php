<?php

namespace Database\Seeders;

use App\Models\AnimalCategory;
use Illuminate\Database\Seeder;

class AnimalCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cattle', 'description' => 'Cows, bulls, and buffalo used for dairy, meat, and labor'],
            ['name' => 'Sheep', 'description' => 'Domestic sheep raised for meat, wool, and dairy'],
            ['name' => 'Goats', 'description' => 'Domestic goats raised for meat, milk, and fiber'],
            ['name' => 'Poultry', 'description' => 'Chickens, ducks, turkeys, and other domesticated birds'],
            ['name' => 'Horses', 'description' => 'Horses and donkeys used for work and sport'],
            ['name' => 'Camels', 'description' => 'Dromedary and Bactrian camels'],
            ['name' => 'Rabbits', 'description' => 'Domestic rabbits raised for meat and fur'],
            ['name' => 'Fish', 'description' => 'Farmed fish including tilapia and catfish'],
        ];

        foreach ($categories as $cat) {
            AnimalCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
