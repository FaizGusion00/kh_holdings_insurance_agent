<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalCase;
use App\Models\User;
use Carbon\Carbon;

class MedicalCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('agent_number', '100000')->first();
        if (!$user) {
            return;
        }

        $members = User::where('referrer_id', $user->id)->whereNotNull('plan_name')->pluck('id')->all();
        if (empty($members)) {
            return;
        }

        // Seed some approved cases across recent months
        $now = Carbon::now();
        $rows = [];
        for ($i = 0; $i < 6; $i++) {
            $monthDate = $now->copy()->subMonths($i)->startOfMonth()->addDays(7); // around the 8th of month
            $approvedAt = $monthDate->copy()->addDays(rand(0, 10));

            $rows[] = [
                'user_id' => $members[array_rand($members)],
                'case_type' => 'hospital',
                'status' => 'approved',
                'approved_at' => $approvedAt,
                'description' => 'Seeded hospital case',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $rows[] = [
                'user_id' => $members[array_rand($members)],
                'case_type' => 'clinic',
                'status' => 'approved',
                'approved_at' => $approvedAt->copy()->addDays(2),
                'description' => 'Seeded clinic case',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        if (!empty($rows)) {
            MedicalCase::insert($rows);
        }
    }
}


