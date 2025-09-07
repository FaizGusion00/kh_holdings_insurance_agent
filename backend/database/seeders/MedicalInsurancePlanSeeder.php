<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicalInsurancePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'MediPlan Coop',
                'description' => 'Basic medical insurance plan with essential coverage',
                'monthly_price' => 90.00,
                'quarterly_price' => null,
                'half_yearly_price' => null,
                'yearly_price' => 1080.00,
                'commitment_fee' => 0.00,
                'is_active' => true,
                'coverage_details' => [
                    'hospital_room' => 'Standard ward',
                    'surgical_benefit' => 'Up to RM 50,000',
                    'annual_limit' => 'RM 100,000',
                    'pre_existing_conditions' => 'Covered after 12 months'
                ],
                'max_age' => 65,
                'min_age' => 18,
            ],
            [
                'name' => 'Senior Care Plan Gold 270',
                'description' => 'Premium medical insurance plan for seniors with enhanced coverage',
                'monthly_price' => 150.00,
                'quarterly_price' => 450.00,
                'half_yearly_price' => 900.00,
                'yearly_price' => 1800.00,
                'commitment_fee' => 150.00,
                'is_active' => true,
                'coverage_details' => [
                    'hospital_room' => 'Private ward',
                    'surgical_benefit' => 'Up to RM 100,000',
                    'annual_limit' => 'RM 200,000',
                    'pre_existing_conditions' => 'Covered after 6 months',
                    'specialist_consultation' => 'Unlimited',
                    'dental_benefit' => 'Up to RM 5,000 per year'
                ],
                'max_age' => 80,
                'min_age' => 50,
            ],
            [
                'name' => 'Senior Care Plan Diamond 370',
                'description' => 'Premium medical insurance plan with comprehensive coverage for seniors',
                'monthly_price' => 210.00,
                'quarterly_price' => 630.00,
                'half_yearly_price' => 1260.00,
                'yearly_price' => 2520.00,
                'commitment_fee' => 210.00,
                'is_active' => true,
                'coverage_details' => [
                    'hospital_room' => 'VIP ward',
                    'surgical_benefit' => 'Up to RM 200,000',
                    'annual_limit' => 'RM 500,000',
                    'pre_existing_conditions' => 'Covered after 3 months',
                    'specialist_consultation' => 'Unlimited',
                    'dental_benefit' => 'Up to RM 10,000 per year',
                    'optical_benefit' => 'Up to RM 2,000 per year',
                    'wellness_checkup' => 'Annual comprehensive health screening'
                ],
                'max_age' => 85,
                'min_age' => 50,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\MedicalInsurancePlan::create($plan);
        }
    }
}
