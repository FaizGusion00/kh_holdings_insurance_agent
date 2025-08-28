<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InsuranceProductSeeder::class,
            ProductCommissionRuleSeeder::class,
            UserSeeder::class,
            HealthcareFacilitySeeder::class,
            MedicalCaseSeeder::class,
        ]);
    }
}
