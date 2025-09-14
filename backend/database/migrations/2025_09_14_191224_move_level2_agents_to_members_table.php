<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Member;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move Level 2+ agents to members table
        $level2PlusAgents = User::where('mlm_level', '>', 1)
            ->whereNotNull('agent_code')
            ->get();

        foreach ($level2PlusAgents as $agent) {
            // Check if member already exists
            $existingMember = Member::where('user_id', $agent->id)->first();
            
            if (!$existingMember) {
                // Get referrer agent info
                $referrerAgent = $agent->referrer_id ? User::find($agent->referrer_id) : null;
                
                Member::create([
                    'user_id' => $agent->id,
                    'name' => $agent->name,
                    'nric' => $agent->nric ?? 'AGENT-' . $agent->agent_code,
                    'phone' => $agent->phone ?? 'N/A',
                    'email' => $agent->email,
                    'address' => $agent->address ?? 'N/A',
                    'date_of_birth' => $agent->date_of_birth ?? now()->subYears(25),
                    'gender' => $agent->gender ?? 'male',
                    'occupation' => 'Insurance Agent',
                    'race' => 'N/A',
                    'relationship_with_agent' => 'Self',
                    'status' => 'active',
                    'registration_date' => $agent->created_at,
                    'emergency_contact_name' => 'N/A',
                    'emergency_contact_phone' => 'N/A',
                    'emergency_contact_relationship' => 'N/A',
                    'referrer_code' => $agent->referrer_code,
                    'agent_code' => $agent->agent_code,
                    'mlm_level' => $agent->mlm_level,
                    'referrer_agent_id' => $agent->referrer_id,
                    'referrer_agent_code' => $referrerAgent ? $referrerAgent->agent_code : null,
                    'is_agent' => true,
                    'wallet_balance' => 0,
                    'balance' => 0,
                ]);
            }
        }

        // Foreign key constraint already exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the agent members
        Member::where('is_agent', true)->delete();
    }
};