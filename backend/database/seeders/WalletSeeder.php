<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AgentWallet;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create wallets for all existing agents
        $agents = User::where('status', 'active')->get();
        
        foreach ($agents as $agent) {
            // Check if wallet already exists
            if (!$agent->wallet) {
                AgentWallet::create([
                    'user_id' => $agent->id,
                    'balance' => 0,
                    'total_earned' => $agent->total_commission_earned ?? 0,
                    'total_withdrawn' => 0,
                    'pending_commission' => 0,
                    'status' => 'active',
                    'last_updated_at' => now(),
                ]);
            }
        }
        
        $this->command->info('Created wallets for ' . $agents->count() . ' agents.');
    }
}
