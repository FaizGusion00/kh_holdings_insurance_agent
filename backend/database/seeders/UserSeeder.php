<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Referral;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create master agent (top-level)
        $masterAgent = User::create([
            'name' => 'Master Agent',
            'email' => 'master@khhinsurance.com',
            'password' => Hash::make('password123'),
            'agent_number' => '100001',
            'agent_code' => 'AGT00001',
            'referrer_code' => null, // Top-level agent
            'phone_number' => '+60123456789',
            'nric' => '123456789012',
            'address' => '123 Master Street',
            'city' => 'Kuala Lumpur',
            'state' => 'Kuala Lumpur',
            'postal_code' => '50000',
            'bank_name' => 'Maybank',
            'bank_account_number' => '1234567890',
            'bank_account_owner' => 'Master Agent',
            'status' => 'active',
            'mlm_level' => 1,
            'total_commission_earned' => 5000.00,
            'monthly_commission_target' => 2000.00,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'mlm_activation_date' => now(),
        ]);

        // Create referral record for master agent
        Referral::create([
            'agent_code' => 'AGT00001',
            'referrer_code' => null,
            'user_id' => $masterAgent->id,
            'referral_level' => 1,
            'upline_chain' => null,
            'downline_count' => 0,
            'total_downline_count' => 0,
            'status' => 'active',
            'activation_date' => now(),
        ]);

        // Create test agent (level 1 under master)
        $testAgent = User::create([
            'name' => 'Test Agent',
            'email' => 'test@khhinsurance.com',
            'password' => Hash::make('password123'),
            'agent_number' => '100002',
            'agent_code' => 'AGT00002',
            'referrer_code' => 'AGT00001',
            'phone_number' => '+60123456788',
            'nric' => '123456789013',
            'address' => '456 Test Avenue',
            'city' => 'Petaling Jaya',
            'state' => 'Selangor',
            'postal_code' => '47300',
            'bank_name' => 'CIMB Bank',
            'bank_account_number' => '9876543210',
            'bank_account_owner' => 'Test Agent',
            'status' => 'active',
            'mlm_level' => 1,
            'total_commission_earned' => 1500.00,
            'monthly_commission_target' => 1000.00,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'mlm_activation_date' => now(),
        ]);

        // Create referral record for test agent
        $uplineChain = ['AGT00001']; // Master agent as upline
        Referral::create([
            'agent_code' => 'AGT00002',
            'referrer_code' => 'AGT00001',
            'user_id' => $testAgent->id,
            'referral_level' => 1,
            'upline_chain' => $uplineChain,
            'downline_count' => 0,
            'total_downline_count' => 0,
            'status' => 'active',
            'activation_date' => now(),
        ]);

        // Update master agent's downline count
        $masterReferral = Referral::where('agent_code', 'AGT00001')->first();
        $masterReferral->increment('downline_count');
        $masterReferral->increment('total_downline_count');

        // Create junior agent (level 2 under test agent)
        $juniorAgent = User::create([
            'name' => 'Junior Agent',
            'email' => 'junior@khhinsurance.com',
            'password' => Hash::make('password123'),
            'agent_number' => '100003',
            'agent_code' => 'AGT00003',
            'referrer_code' => 'AGT00002',
            'phone_number' => '+60123456787',
            'nric' => '123456789014',
            'address' => '789 Junior Road',
            'city' => 'Shah Alam',
            'state' => 'Selangor',
            'postal_code' => '40000',
            'bank_name' => 'Public Bank',
            'bank_account_number' => '5555666677',
            'bank_account_owner' => 'Junior Agent',
            'status' => 'active',
            'mlm_level' => 1,
            'total_commission_earned' => 500.00,
            'monthly_commission_target' => 800.00,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'mlm_activation_date' => now(),
        ]);

        // Create referral record for junior agent
        $juniorUplineChain = ['AGT00002', 'AGT00001']; // Test agent and master agent as upline
        Referral::create([
            'agent_code' => 'AGT00003',
            'referrer_code' => 'AGT00002',
            'user_id' => $juniorAgent->id,
            'referral_level' => 1,
            'upline_chain' => $juniorUplineChain,
            'downline_count' => 0,
            'total_downline_count' => 0,
            'status' => 'active',
            'activation_date' => now(),
        ]);

        // Update test agent's downline count
        $testReferral = Referral::where('agent_code', 'AGT00002')->first();
        $testReferral->increment('downline_count');
        $testReferral->increment('total_downline_count');

        // Update master agent's total downline count (indirect referral)
        $masterReferral->increment('total_downline_count');

        // Create demo agent for frontend testing
        $demoAgent = User::create([
            'name' => 'Demo Agent',
            'email' => 'demo@khhinsurance.com',
            'password' => Hash::make('demo123'),
            'agent_number' => '100000', // Easy to remember
            'agent_code' => 'AGT00000',
            'referrer_code' => null,
            'phone_number' => '+60123456700',
            'nric' => '123456789000',
            'address' => '1 Demo Lane',
            'city' => 'Cyberjaya',
            'state' => 'Selangor',
            'postal_code' => '63000',
            'bank_name' => 'RHB Bank',
            'bank_account_number' => '1111222233',
            'bank_account_owner' => 'Demo Agent',
            'status' => 'active',
            'mlm_level' => 1,
            'total_commission_earned' => 0.00,
            'monthly_commission_target' => 500.00,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'mlm_activation_date' => now(),
        ]);

        // Create referral record for demo agent
        Referral::create([
            'agent_code' => 'AGT00000',
            'referrer_code' => null,
            'user_id' => $demoAgent->id,
            'referral_level' => 1,
            'upline_chain' => null,
            'downline_count' => 0,
            'total_downline_count' => 0,
            'status' => 'active',
            'activation_date' => now(),
        ]);
    }
}