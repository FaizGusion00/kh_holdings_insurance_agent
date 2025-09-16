<?php

namespace Database\Seeders;

use App\Models\CommissionRate;
use App\Models\InsurancePlan;
use Illuminate\Database\Seeder;

class CommissionRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks and clear existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CommissionRate::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get the insurance plans
        $mediplancoop = InsurancePlan::where('plan_code', 'MEDIPLAN_COOP')->first();
        $seniorGold = InsurancePlan::where('plan_code', 'SENIOR_CARE_GOLD_270')->first();
        $seniorDiamond = InsurancePlan::where('plan_code', 'SENIOR_CARE_DIAMOND_370')->first();

        if (!$mediplancoop || !$seniorGold || !$seniorDiamond) {
            $this->command->error('Insurance plans not found. Please run InsurancePlansSeeder first.');
            return;
        }

        // Commission rates for Senior Care Plan Gold 270
        $this->createCommissionRates($seniorGold, [
            // Monthly (RM150)
            'monthly' => [
                1 => ['percentage' => 11.11, 'amount' => 16.67],
                2 => ['percentage' => 2.22, 'amount' => 3.33],
                3 => ['percentage' => 2.22, 'amount' => 3.33],
                4 => ['percentage' => 1.11, 'amount' => 1.67],
                5 => ['percentage' => 0.83, 'amount' => 1.25],
            ],
            // Quarterly (RM450)
            'quarterly' => [
                1 => ['percentage' => 11.11, 'amount' => 50.00],
                2 => ['percentage' => 2.22, 'amount' => 9.99],
                3 => ['percentage' => 2.22, 'amount' => 9.99],
                4 => ['percentage' => 1.11, 'amount' => 5.00],
                5 => ['percentage' => 0.83, 'amount' => 3.74],
            ],
            // Semi Annually (RM900)
            'semi_annually' => [
                1 => ['percentage' => 11.11, 'amount' => 99.99],
                2 => ['percentage' => 2.22, 'amount' => 19.98],
                3 => ['percentage' => 2.22, 'amount' => 19.98],
                4 => ['percentage' => 1.11, 'amount' => 9.99],
                5 => ['percentage' => 0.83, 'amount' => 7.47],
            ],
            // Annually (RM1800)
            'annually' => [
                1 => ['percentage' => 11.11, 'amount' => 199.98],
                2 => ['percentage' => 2.22, 'amount' => 39.96],
                3 => ['percentage' => 2.22, 'amount' => 39.96],
                4 => ['percentage' => 1.11, 'amount' => 19.98],
                5 => ['percentage' => 0.83, 'amount' => 14.94],
            ],
        ]);

        // Commission rates for Senior Care Plan Diamond 370
        $this->createCommissionRates($seniorDiamond, [
            // Monthly (RM210)
            'monthly' => [
                1 => ['percentage' => 11.11, 'amount' => 23.33],
                2 => ['percentage' => 2.22, 'amount' => 4.66],
                3 => ['percentage' => 2.22, 'amount' => 4.66],
                4 => ['percentage' => 1.11, 'amount' => 2.33],
                5 => ['percentage' => 0.83, 'amount' => 1.74],
            ],
            // Quarterly (RM630)
            'quarterly' => [
                1 => ['percentage' => 11.11, 'amount' => 69.99],
                2 => ['percentage' => 2.22, 'amount' => 13.99],
                3 => ['percentage' => 2.22, 'amount' => 13.99],
                4 => ['percentage' => 1.11, 'amount' => 6.99],
                5 => ['percentage' => 0.83, 'amount' => 5.23],
            ],
            // Semi Annually (RM1260)
            'semi_annually' => [
                1 => ['percentage' => 11.11, 'amount' => 139.99],
                2 => ['percentage' => 2.22, 'amount' => 27.97],
                3 => ['percentage' => 2.22, 'amount' => 27.97],
                4 => ['percentage' => 1.11, 'amount' => 13.99],
                5 => ['percentage' => 0.83, 'amount' => 10.46],
            ],
            // Annually (RM2520)
            'annually' => [
                1 => ['percentage' => 11.11, 'amount' => 279.97],
                2 => ['percentage' => 2.22, 'amount' => 55.94],
                3 => ['percentage' => 2.22, 'amount' => 55.94],
                4 => ['percentage' => 1.11, 'amount' => 27.97],
                5 => ['percentage' => 0.83, 'amount' => 20.92],
            ],
        ]);

        // Commission rates for MediPlan Coop (Fixed amounts per sale)
        $this->createCommissionRates($mediplancoop, [
            // Monthly (RM90) - Fixed commission amounts
            'monthly' => [
                1 => ['percentage' => 11.11, 'amount' => 10.00], // RM10 for every customer
                2 => ['percentage' => 2.22, 'amount' => 2.00],   // RM2 for every agent sale
                3 => ['percentage' => 2.22, 'amount' => 2.00],   // RM2 for every agent sale
                4 => ['percentage' => 1.11, 'amount' => 1.00],   // RM1 for every agent sale
                5 => ['percentage' => 0.83, 'amount' => 0.75],   // RM0.75 for every agent sale
            ],
            // Annually (RM1080) - Fixed commission amounts
            'annually' => [
                1 => ['percentage' => 11.11, 'amount' => 10.00], // RM10 for every customer
                2 => ['percentage' => 2.22, 'amount' => 2.00],   // RM2 for every agent sale
                3 => ['percentage' => 2.22, 'amount' => 2.00],   // RM2 for every agent sale
                4 => ['percentage' => 1.11, 'amount' => 1.00],   // RM1 for every agent sale
                5 => ['percentage' => 0.83, 'amount' => 0.75],   // RM0.75 for every agent sale
            ],
        ]);

        $this->command->info('Commission rates seeded successfully!');
    }

    /**
     * Create commission rates for a specific plan
     */
    private function createCommissionRates(InsurancePlan $plan, array $rates)
    {
        foreach ($rates as $paymentMode => $tiers) {
            foreach ($tiers as $tierLevel => $commission) {
                CommissionRate::create([
                    'insurance_plan_id' => $plan->id,
                    'payment_mode' => $paymentMode,
                    'tier_level' => $tierLevel,
                    'commission_percentage' => $commission['percentage'],
                    'commission_amount' => $commission['amount'],
                ]);
            }
        }
    }
}