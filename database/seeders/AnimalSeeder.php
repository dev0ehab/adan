<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalCategory;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Cattle' => ['Holstein Cow', 'Baladi Cow', 'Friesian Cattle', 'Buffalo'],
            'Sheep' => ['Ossimi Sheep', 'Rahmani Sheep', 'Barki Sheep', 'Awassi Sheep'],
            'Goats' => ['Zaraibi Goat', 'Shami Goat', 'Baladi Goat', 'Nubian Goat'],
            'Poultry' => ['Broiler Chicken', 'Layer Chicken', 'Baladi Chicken', 'Duck', 'Turkey'],
            'Horses' => ['Arabian Horse', 'Thoroughbred', 'Donkey'],
            'Camels' => ['Dromedary Camel', 'Racing Camel'],
            'Rabbits' => ['New Zealand White', 'Baladi Rabbit', 'Rex Rabbit'],
            'Fish' => ['Nile Tilapia', 'Catfish', 'Mullet'],
        ];

        foreach ($data as $categoryName => $animals) {
            $category = AnimalCategory::where('name', $categoryName)->first();
            if (! $category) {
                continue;
            }
            foreach ($animals as $animalName) {
                Animal::firstOrCreate(
                    ['name' => $animalName, 'category_id' => $category->id]
                );
            }
        }
    }
}
