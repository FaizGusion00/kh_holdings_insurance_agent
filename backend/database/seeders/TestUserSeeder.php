<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test agent
        $agent = User::firstOrCreate(
            ['email' => 'agt12345@khholdings.com'],
            [
                'name' => 'Test Agent',
                'agent_code' => 'AGT12345',
                'email' => 'agt12345@khholdings.com',
                'password' => Hash::make('password123'),
                'phone_number' => '+60123456789',
                'nric' => '901234567890',
                'customer_type' => 'agent',
                'status' => 'active',
                'mlm_level' => 1,
                'wallet_balance' => 1500.00,
                'total_commission_earned' => 2500.00,
                'race' => 'Malay',
                'date_of_birth' => '1990-01-15',
                'gender' => 'male',
                'occupation' => 'Insurance Agent',
                'address' => '123 Jalan Test',
                'city' => 'Kuala Lumpur',
                'state' => 'Selangor',
                'postal_code' => '50000',
                'emergency_contact_name' => 'Ahmad Rahman',
                'emergency_contact_phone' => '+60123456799',
                'emergency_contact_relationship' => 'Brother',
                'bank_name' => 'Maybank',
                'bank_account_number' => '1234567890',
                'bank_account_owner' => 'Test Agent',
                'referrer_code' => null, // Top level agent
                'email_verified_at' => now(),
            ]
        );

        // Create test client under the agent
        $client = User::firstOrCreate(
            ['email' => 'client1@example.com'],
            [
                'name' => 'Test Client',
                'agent_code' => null, // Clients don't have agent codes
                'email' => 'client1@example.com',
                'password' => Hash::make('password123'),
                'phone_number' => '+60123456788',
                'nric' => '901234567891',
                'customer_type' => 'client',
                'status' => 'active',
                'mlm_level' => 0,
                'wallet_balance' => 0.00,
                'total_commission_earned' => 0.00,
                'race' => 'Chinese',
                'date_of_birth' => '1985-05-20',
                'gender' => 'female',
                'occupation' => 'Teacher',
                'address' => '456 Jalan Client',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'postal_code' => '47300',
                'emergency_contact_name' => 'Lee Wei Ming',
                'emergency_contact_phone' => '+60123456777',
                'emergency_contact_relationship' => 'Husband',
                'bank_name' => 'CIMB Bank',
                'bank_account_number' => '9876543210',
                'bank_account_owner' => 'Test Client',
                'referrer_code' => 'AGT12345', // Referred by test agent
                'email_verified_at' => now(),
            ]
        );

        // Create second level agent
        $agent2 = User::firstOrCreate(
            ['email' => 'agt67890@khholdings.com'],
            [
                'name' => 'Second Level Agent',
                'agent_code' => 'AGT67890',
                'email' => 'agt67890@khholdings.com',
                'password' => Hash::make('password123'),
                'phone_number' => '+60123456787',
                'nric' => '901234567892',
                'customer_type' => 'agent',
                'status' => 'active',
                'mlm_level' => 2,
                'wallet_balance' => 800.00,
                'total_commission_earned' => 1200.00,
                'race' => 'Indian',
                'date_of_birth' => '1992-03-10',
                'gender' => 'male',
                'occupation' => 'Insurance Agent',
                'address' => '789 Jalan Agent',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postal_code' => '40000',
                'emergency_contact_name' => 'Raj Kumar',
                'emergency_contact_phone' => '+60123456766',
                'emergency_contact_relationship' => 'Father',
                'bank_name' => 'Public Bank',
                'bank_account_number' => '5678901234',
                'bank_account_owner' => 'Second Level Agent',
                'referrer_code' => 'AGT12345', // Referred by first agent
                'email_verified_at' => now(),
            ]
        );

        echo "Test users created successfully!\n";
        echo "=================================\n";
        echo "Agent Login Credentials:\n";
        echo "Agent Code: AGT12345\n";
        echo "Email: agt12345@khholdings.com\n";
        echo "Password: password123\n";
        echo "\n";
        echo "Second Agent:\n";
        echo "Agent Code: AGT67890\n";
        echo "Email: agt67890@khholdings.com\n";
        echo "Password: password123\n";
        echo "\n";
        echo "Client Login:\n";
        echo "Email: client1@example.com\n";
        echo "Password: password123\n";
        echo "=================================\n";
    }
}