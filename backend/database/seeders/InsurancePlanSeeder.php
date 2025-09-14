<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceProduct;

class InsurancePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing products
        InsuranceProduct::truncate();
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $plans = [
            [
                'product_type' => 'medical_card',
                'name' => 'MediPlan Coop',
                'description' => 'Medical cooperative plan with comprehensive coverage',
                'base_price' => 90.00, // Monthly
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'room_board' => 250,
                    'ambulance_fees' => 'Included',
                    'intensive_care_unit' => 'Included',
                    'hospital_supplies_services' => 'Included',
                    'surgical_fees' => 'Included',
                    'operating_theater_fees' => 'Included',
                    'anesthetist_fees' => 'Included',
                    'in_hospital_doctor_visit' => 'Included',
                    'day_care_surgery' => 'Included',
                    'second_surgical_opinion' => 'Included',
                    'emergency_dental' => 'Included',
                    'covid_test' => 'Included',
                    'government_hospital_allowance' => 100,
                    'pre_hospital_diagnostic' => 5000,
                    'accidental_injury_surgery' => 10000,
                    'bereavement' => 10000,
                    'outpatient_cancer_treatment' => 100000,
                    'conditional_outpatient' => 'As Charged',
                    'annual_limit' => 1000000,
                    'panel_hospitals' => 250,
                    'panel_clinics' => 4000,
                    'tpa' => 'eMAS (Eximius Medical Administration Solutions)',
                    'age_eligibility' => '30 days to 45 years (renewal up to 100)',
                    'waiting_period' => '90 days general, 180 days specific',
                    'pricing' => [
                        'monthly' => 90,
                        'yearly' => 1080
                    ]
                ],
                'waiting_period_days' => 90,
                'max_coverage_amount' => 1000000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'medical_card',
                'name' => 'Senior Care Plan Gold 270',
                'description' => 'Senior care plan with gold level benefits',
                'base_price' => 150.00, // Monthly + 150 commitment
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'room_board' => 270,
                    'intensive_care_unit' => 'Full Reimbursement',
                    'hospital_supplies_services' => 'Included',
                    'surgeon_fee' => 'Included',
                    'anaesthetist_fee' => 'Included',
                    'operating_theatre_charges' => 'Included',
                    'daily_physician_visit' => 'Included',
                    'pre_hospital_diagnostic' => 'Included',
                    'pre_hospitalization_consultation' => 'Included',
                    'second_surgical_opinion' => 'Included',
                    'post_hospitalization_treatment' => 'Included',
                    'emergency_accidental_outpatient' => 'Included',
                    'outpatient_cancer_treatment' => 'Included',
                    'outpatient_kidney_dialysis' => 'Included',
                    'daycare_procedure' => 'Included',
                    'ambulance_charges' => 'Included',
                    'government_hospital_allowance' => 100,
                    'medical_report_fee' => 80,
                    'funeral_expenses' => 10000,
                    'annual_limit' => 75000,
                    'panel_hospitals' => 148,
                    'tpa' => 'MiCare',
                    'age_eligibility' => '46-65 years (renewal up to 70)',
                    'pricing' => [
                        'monthly' => 150,
                        'quarterly' => 450,
                        'semi_annually' => 900,
                        'yearly' => 1800
                    ]
                ],
                'waiting_period_days' => 30,
                'max_coverage_amount' => 75000.00,
                'is_active' => true,
            ],
            [
                'product_type' => 'medical_card',
                'name' => 'Senior Care Plan Diamond 370',
                'description' => 'Senior care plan with diamond level benefits',
                'base_price' => 210.00, // Monthly + 210 commitment
                'payment_frequency' => 'monthly',
                'price_multiplier' => 1.00,
                'coverage_details' => [
                    'room_board' => 370,
                    'intensive_care_unit' => 'Full Reimbursement',
                    'hospital_supplies_services' => 'Included',
                    'surgeon_fee' => 'Included',
                    'anaesthetist_fee' => 'Included',
                    'operating_theatre_charges' => 'Included',
                    'daily_physician_visit' => 'Included',
                    'pre_hospital_diagnostic' => 'Included',
                    'pre_hospitalization_consultation' => 'Included',
                    'second_surgical_opinion' => 'Included',
                    'post_hospitalization_treatment' => 'Included',
                    'emergency_accidental_outpatient' => 'Included',
                    'outpatient_cancer_treatment' => 'Included',
                    'outpatient_kidney_dialysis' => 'Included',
                    'daycare_procedure' => 'Included',
                    'ambulance_charges' => 'Included',
                    'government_hospital_allowance' => 200,
                    'medical_report_fee' => 80,
                    'funeral_expenses' => 10000,
                    'annual_limit' => 100000,
                    'panel_hospitals' => 148,
                    'tpa' => 'MiCare',
                    'age_eligibility' => '46-65 years (renewal up to 70)',
                    'pricing' => [
                        'monthly' => 210,
                        'quarterly' => 630,
                        'semi_annually' => 1260,
                        'yearly' => 2520
                    ]
                ],
                'waiting_period_days' => 30,
                'max_coverage_amount' => 100000.00,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            InsuranceProduct::create($plan);
        }
    }
}
