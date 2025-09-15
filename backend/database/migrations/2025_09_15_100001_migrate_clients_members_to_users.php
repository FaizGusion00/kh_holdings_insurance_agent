<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate data from clients table to users table
        $this->migrateClientsToUsers();
        
        // Then, update users with member data where available
        $this->updateUsersWithMemberData();
        
        // Ensure all users have agent codes and are activated
        $this->ensureAllUsersAreAgents();
    }

    /**
     * Migrate clients data to users table.
     */
    private function migrateClientsToUsers(): void
    {
        $clients = DB::table('clients')->get();
        
        foreach ($clients as $client) {
            // Check if user already exists by NRIC
            $existingUser = DB::table('users')->where('nric', $client->nric)->first();
            
            if ($existingUser) {
                // Update existing user with client data
                DB::table('users')
                    ->where('id', $existingUser->id)
                    ->update([
                        'plan_name' => $client->plan_name,
                        'payment_mode' => $client->payment_mode,
                        'medical_card_type' => $client->medical_card_type,
                        'customer_type' => $client->customer_type,
                        'registration_id' => $client->registration_id,
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new user from client data
                $agentCode = \App\Models\User::generateAgentCode();
                $agentNumber = \App\Models\User::generateAgentNumber();
                
                DB::table('users')->insert([
                    'name' => $client->full_name,
                    'email' => $client->email ?: (strtolower(str_replace(' ', '', $client->full_name)) . '@wekongsi.local'),
                    'password' => bcrypt('Temp1234!'),
                    'agent_number' => $agentNumber,
                    'agent_code' => $agentCode,
                    'phone_number' => $client->phone_number,
                    'nric' => $client->nric,
                    'status' => $client->status,
                    'plan_name' => $client->plan_name,
                    'payment_mode' => $client->payment_mode,
                    'medical_card_type' => $client->medical_card_type,
                    'customer_type' => $client->customer_type,
                    'registration_id' => $client->registration_id,
                    'mlm_activation_date' => now(),
                    'registration_date' => $client->created_at ?? now(),
                    'balance' => 0.00,
                    'wallet_balance' => 0.00,
                    'created_at' => $client->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Update users with member data where available.
     */
    private function updateUsersWithMemberData(): void
    {
        $members = DB::table('members')->get();
        
        foreach ($members as $member) {
            $user = DB::table('users')->where('nric', $member->nric)->first();
            
            if ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'race' => $member->race,
                        'date_of_birth' => $member->date_of_birth,
                        'gender' => $member->gender,
                        'occupation' => $member->occupation,
                        'address' => $member->address,
                        'emergency_contact_name' => $member->emergency_contact_name,
                        'emergency_contact_phone' => $member->emergency_contact_phone,
                        'emergency_contact_relationship' => $member->emergency_contact_relationship,
                        'relationship_with_agent' => $member->relationship_with_agent,
                        'balance' => $member->balance,
                        'referrer_code' => $member->referrer_code,
                        'referrer_id' => $member->referrer_id,
                        'registration_date' => $member->registration_date,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Ensure all users have agent codes and are activated.
     */
    private function ensureAllUsersAreAgents(): void
    {
        $usersWithoutCodes = DB::table('users')
            ->where(function($query) {
                $query->whereNull('agent_code')
                      ->orWhere('agent_code', '');
            })
            ->get();

        foreach ($usersWithoutCodes as $user) {
            $agentCode = \App\Models\User::generateAgentCode();
            $agentNumber = \App\Models\User::generateAgentNumber();
            
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'agent_code' => $agentCode,
                    'agent_number' => $agentNumber,
                    'status' => 'active',
                    'mlm_activation_date' => $user->mlm_activation_date ?: now(),
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed as it consolidates data
        // The down method is intentionally left empty
        // To reverse, you would need to recreate separate tables and redistribute the data
    }
};
