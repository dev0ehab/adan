<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $region = Region::first();

        User::firstOrCreate(
            ['email' => 'admin@adan.com'],
            [
                'name' => 'Dr. Admin',
                'phone' => '+201000000000',
                'password' => 'password',
                'role' => 'doctor',
                'email_verified_at' => now(),
                'region_id' => $region?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'doctor@adan.com'],
            [
                'name' => 'Dr. Ahmed Hassan',
                'phone' => '+201001234567',
                'password' => 'password',
                'role' => 'doctor',
                'email_verified_at' => now(),
                'region_id' => $region?->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@adan.com'],
            [
                'name' => 'Mohammed Fathy',
                'phone' => '+201112345678',
                'password' => 'password',
                'role' => 'customer',
                'email_verified_at' => now(),
                'region_id' => $region?->id,
                'latitude' => 31.0409,
                'longitude' => 31.3785,
            ]
        );
    }
}
