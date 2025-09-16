<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed insurance plans and commission rates
        $this->call([
            InsurancePlansSeeder::class,
            CommissionRatesSeeder::class,
        ]);

        $this->command->info('Database seeding completed successfully!');
    }
}
