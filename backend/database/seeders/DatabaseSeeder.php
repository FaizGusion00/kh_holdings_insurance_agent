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
            AdminSeeder::class,
            InsuranceProductSeeder::class,
            ProductCommissionRuleSeeder::class,
            UserSeeder::class,
            MedicalCaseSeeder::class,
            HospitalSeeder::class,
            ClinicSeeder::class,
            WalletSeeder::class,
            CommissionRuleSeeder::class, // Added commission rules
        ]);
    }
}
