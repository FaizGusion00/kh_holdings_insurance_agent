<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Administrator',
            'email' => 'admin@khholdings.com',
            'username' => 'superadmin',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'System Administrator',
            'email' => 'system@khholdings.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        Admin::create([
            'name' => 'Manager User',
            'email' => 'manager@khholdings.com',
            'username' => 'manager',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->command->info('Admin users seeded successfully!');
        $this->command->info('Default credentials:');
        $this->command->info('Super Admin: superadmin / admin123');
        $this->command->info('Admin: admin / admin123');
        $this->command->info('Manager: manager / manager123');
    }
}
