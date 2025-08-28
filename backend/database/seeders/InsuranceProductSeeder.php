<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceProduct;

class InsuranceProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_type' => 'medical_card',
                'name' => 'Medical Card Basic',
                'description' => 'Basic medical coverage for individuals and families',
                'base_price' => 150.00,
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'annual_limit' => 100000,
                    'room_benefit' => 'Private room up to RM300/day',
                    'surgical_benefit' => 'Up to RM50,000',
                    'outpatient_benefit' => 'Up to RM2,000',
                ],
                'waiting_period_days' => 30,
                'max_coverage_amount' => 100000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'medical_card',
                'name' => 'Medical Card Premium',
                'description' => 'Premium medical coverage with enhanced benefits',
                'base_price' => 300.00,
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'annual_limit' => 300000,
                    'room_benefit' => 'Private room up to RM500/day',
                    'surgical_benefit' => 'Up to RM150,000',
                    'outpatient_benefit' => 'Up to RM5,000',
                ],
                'waiting_period_days' => 30,
                'max_coverage_amount' => 300000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'roadtax',
                'name' => 'Motor Insurance Basic',
                'description' => 'Basic motor vehicle insurance coverage',
                'base_price' => 100.00,
                'payment_frequency' => 'annually',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'coverage_type' => 'comprehensive',
                    'third_party_coverage' => 'Unlimited',
                    'own_damage' => 'Market value',
                    'theft_coverage' => true,
                ],
                'waiting_period_days' => 0,
                'max_coverage_amount' => 500000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'hibah',
                'name' => 'Hibah Gold 270 Plan',
                'description' => 'Hibah senior care plan with comprehensive coverage',
                'base_price' => 150.00,
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'plan_type' => 'Gold 270',
                    'coverage_period' => '5 years',
                    'death_benefit' => 50000,
                    'maturity_benefit' => 40000,
                ],
                'waiting_period_days' => 90,
                'max_coverage_amount' => 50000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'hibah',
                'name' => 'Hibah Platinum 270 Plan',
                'description' => 'Hibah senior care premium plan',
                'base_price' => 250.00,
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'plan_type' => 'Platinum 270',
                    'coverage_period' => '5 years',
                    'death_benefit' => 100000,
                    'maturity_benefit' => 80000,
                ],
                'waiting_period_days' => 90,
                'max_coverage_amount' => 100000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'travel_pa',
                'name' => 'Travel Personal Accident',
                'description' => 'Personal accident coverage for travelers',
                'base_price' => 50.00,
                'payment_frequency' => 'annually',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'coverage_area' => 'Worldwide',
                    'accidental_death' => 100000,
                    'permanent_disability' => 100000,
                    'medical_expenses' => 50000,
                ],
                'waiting_period_days' => 0,
                'max_coverage_amount' => 100000.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            InsuranceProduct::create($product);
        }
    }
}