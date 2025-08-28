<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCommissionRule;
use App\Models\InsuranceProduct;

class ProductCommissionRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get insurance products
        $medicalCard = InsuranceProduct::where('product_type', 'medical_card')->first();
        $roadtax = InsuranceProduct::where('product_type', 'roadtax')->first();
        $hibahGold = InsuranceProduct::where('name', 'Hibah Gold 270 Plan')->first();
        $hibahPlatinum = InsuranceProduct::where('name', 'Hibah Platinum 270 Plan')->first();

        // Medical Card Commission Rules (Fixed amounts per tier)
        if ($medicalCard) {
            $medicalCardRules = [
                ['tier_level' => 1, 'commission_type' => 'fixed_amount', 'commission_value' => 10.00],
                ['tier_level' => 2, 'commission_type' => 'fixed_amount', 'commission_value' => 2.00],
                ['tier_level' => 3, 'commission_type' => 'fixed_amount', 'commission_value' => 2.00],
                ['tier_level' => 4, 'commission_type' => 'fixed_amount', 'commission_value' => 1.00],
                ['tier_level' => 5, 'commission_type' => 'fixed_amount', 'commission_value' => 0.75],
            ];

            foreach ($medicalCardRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $medicalCard->id,
                    'payment_frequency' => 'monthly',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00, // No cap
                    'is_active' => true,
                ]);
            }
        }

        // Roadtax Commission Rules (Percentage-based)
        if ($roadtax) {
            $roadtaxRules = [
                ['tier_level' => 1, 'commission_type' => 'percentage', 'commission_value' => 50.00], // 50%
                ['tier_level' => 2, 'commission_type' => 'percentage', 'commission_value' => 10.00], // 10%
                ['tier_level' => 3, 'commission_type' => 'percentage', 'commission_value' => 10.00], // 10%
            ];

            foreach ($roadtaxRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $roadtax->id,
                    'payment_frequency' => 'annually',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00, // No cap
                    'is_active' => true,
                ]);
            }
        }

        // Hibah Gold Plan Commission Rules
        if ($hibahGold) {
            // Monthly payment commissions
            $hibahGoldMonthlyRules = [
                ['tier_level' => 1, 'commission_type' => 'percentage', 'commission_value' => 11.11], // RM16.67
                ['tier_level' => 2, 'commission_type' => 'percentage', 'commission_value' => 2.22],  // RM3.33
                ['tier_level' => 3, 'commission_type' => 'percentage', 'commission_value' => 2.22],  // RM3.33
                ['tier_level' => 4, 'commission_type' => 'percentage', 'commission_value' => 1.33],  // RM2.00
                ['tier_level' => 5, 'commission_type' => 'percentage', 'commission_value' => 0.89],  // RM1.33
            ];

            foreach ($hibahGoldMonthlyRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $hibahGold->id,
                    'payment_frequency' => 'monthly',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00,
                    'is_active' => true,
                ]);
            }

            // Quarterly payment commissions (higher rates)
            $hibahGoldQuarterlyRules = [
                ['tier_level' => 1, 'commission_type' => 'percentage', 'commission_value' => 13.33], // RM60.00
                ['tier_level' => 2, 'commission_type' => 'percentage', 'commission_value' => 2.67],  // RM12.00
                ['tier_level' => 3, 'commission_type' => 'percentage', 'commission_value' => 2.67],  // RM12.00
                ['tier_level' => 4, 'commission_type' => 'percentage', 'commission_value' => 1.78],  // RM8.00
                ['tier_level' => 5, 'commission_type' => 'percentage', 'commission_value' => 1.07],  // RM4.80
            ];

            foreach ($hibahGoldQuarterlyRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $hibahGold->id,
                    'payment_frequency' => 'quarterly',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00,
                    'is_active' => true,
                ]);
            }
        }

        // Hibah Platinum Plan Commission Rules
        if ($hibahPlatinum) {
            // Monthly payment commissions
            $hibahPlatinumMonthlyRules = [
                ['tier_level' => 1, 'commission_type' => 'percentage', 'commission_value' => 13.33], // RM33.33
                ['tier_level' => 2, 'commission_type' => 'percentage', 'commission_value' => 2.67],  // RM6.67
                ['tier_level' => 3, 'commission_type' => 'percentage', 'commission_value' => 2.67],  // RM6.67
                ['tier_level' => 4, 'commission_type' => 'percentage', 'commission_value' => 1.60],  // RM4.00
                ['tier_level' => 5, 'commission_type' => 'percentage', 'commission_value' => 1.07],  // RM2.67
            ];

            foreach ($hibahPlatinumMonthlyRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $hibahPlatinum->id,
                    'payment_frequency' => 'monthly',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00,
                    'is_active' => true,
                ]);
            }

            // Quarterly payment commissions
            $hibahPlatinumQuarterlyRules = [
                ['tier_level' => 1, 'commission_type' => 'percentage', 'commission_value' => 16.00], // RM120.00
                ['tier_level' => 2, 'commission_type' => 'percentage', 'commission_value' => 3.20],  // RM24.00
                ['tier_level' => 3, 'commission_type' => 'percentage', 'commission_value' => 3.20],  // RM24.00
                ['tier_level' => 4, 'commission_type' => 'percentage', 'commission_value' => 2.13],  // RM16.00
                ['tier_level' => 5, 'commission_type' => 'percentage', 'commission_value' => 1.28],  // RM9.60
            ];

            foreach ($hibahPlatinumQuarterlyRules as $rule) {
                ProductCommissionRule::create([
                    'product_id' => $hibahPlatinum->id,
                    'payment_frequency' => 'quarterly',
                    'tier_level' => $rule['tier_level'],
                    'commission_type' => $rule['commission_type'],
                    'commission_value' => $rule['commission_value'],
                    'minimum_requirement' => 0.00,
                    'maximum_cap' => 0.00,
                    'is_active' => true,
                ]);
            }
        }
    }
}