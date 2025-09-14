<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CommissionRule;

class CommissionRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Senior Care Plan Gold 270 Rules
        $this->createSeniorCareGoldRules();
        
        // Senior Care Plan Diamond 370 Rules
        $this->createSeniorCareDiamondRules();
        
        // Medical Card Rules
        $this->createMedicalCardRules();
        
        // MediPlan Coop Rules
        $this->createMediPlanCoopRules();
    }

    private function createSeniorCareGoldRules()
    {
        $baseAmounts = [
            'monthly' => 150.00,
            'quarterly' => 450.00,
            'semi_annually' => 900.00,
            'annually' => 1800.00,
        ];

        $tierPercentages = [
            1 => 11.11,
            2 => 2.22,
            3 => 2.22,
            4 => 1.11,
            5 => 0.83,
        ];

        foreach ($baseAmounts as $frequency => $baseAmount) {
            foreach ($tierPercentages as $tier => $percentage) {
                CommissionRule::create([
                    'plan_name' => 'Senior Care Plan Gold 270',
                    'plan_type' => 'senior_care',
                    'payment_frequency' => $frequency,
                    'base_amount' => $baseAmount,
                    'tier_level' => $tier,
                    'commission_percentage' => $percentage,
                    'commission_amount' => null,
                    'commission_type' => 'percentage',
                    'is_active' => true,
                ]);
            }
        }
    }

    private function createSeniorCareDiamondRules()
    {
        $baseAmounts = [
            'monthly' => 210.00,
            'quarterly' => 630.00,
            'semi_annually' => 1260.00,
            'annually' => 2520.00,
        ];

        $tierPercentages = [
            1 => 11.11,
            2 => 2.22,
            3 => 2.22,
            4 => 1.11,
            5 => 0.83,
        ];

        foreach ($baseAmounts as $frequency => $baseAmount) {
            foreach ($tierPercentages as $tier => $percentage) {
                CommissionRule::create([
                    'plan_name' => 'Senior Care Plan Diamond 370',
                    'plan_type' => 'senior_care',
                    'payment_frequency' => $frequency,
                    'base_amount' => $baseAmount,
                    'tier_level' => $tier,
                    'commission_percentage' => $percentage,
                    'commission_amount' => null,
                    'commission_type' => 'percentage',
                    'is_active' => true,
                ]);
            }
        }
    }

    private function createMedicalCardRules()
    {
        $tierAmounts = [
            1 => 10.00,  // T1 - RM10 per customer
            2 => 2.00,   // T2 - RM2 per agent sale
            3 => 2.00,   // T3 - RM2 per agent sale (GUIDER)
            4 => 1.00,   // T4 - RM1 per agent sale (GUIDER)
            5 => 0.75,   // T5 - RM0.75 per agent sale (GUIDER)
        ];

        foreach ($tierAmounts as $tier => $amount) {
            CommissionRule::create([
                'plan_name' => 'Medical Card',
                'plan_type' => 'medical_card',
                'payment_frequency' => null, // Fixed amount regardless of frequency
                'base_amount' => 0.00,
                'tier_level' => $tier,
                'commission_percentage' => null,
                'commission_amount' => $amount,
                'commission_type' => 'fixed_amount',
                'is_active' => true,
            ]);
        }
    }

    private function createMediPlanCoopRules()
    {
        $baseAmounts = [
            'monthly' => 90.00,
            'quarterly' => 270.00,
            'half_yearly' => 540.00,
            'yearly' => 1080.00,
        ];

        $tierPercentages = [
            1 => 11.11,  // RM10.00
            2 => 2.22,   // RM2.00
            3 => 2.22,   // RM2.00
            4 => 1.11,   // RM1.00
            5 => 0.83,   // RM0.75
        ];

        foreach ($baseAmounts as $frequency => $baseAmount) {
            foreach ($tierPercentages as $tier => $percentage) {
                CommissionRule::create([
                    'plan_name' => 'MediPlan Coop',
                    'plan_type' => 'medical_card',
                    'payment_frequency' => $frequency,
                    'base_amount' => $baseAmount,
                    'tier_level' => $tier,
                    'commission_percentage' => $percentage,
                    'commission_amount' => null,
                    'commission_type' => 'percentage',
                    'is_active' => true,
                ]);
            }
        }
    }
}
