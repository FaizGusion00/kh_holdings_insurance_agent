<?php

namespace Database\Seeders;

use App\Models\InsurancePlan;
use Illuminate\Database\Seeder;

class InsurancePlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks and clear existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        InsurancePlan::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create MediPlan Coop
        InsurancePlan::create([
            'plan_name' => 'MediPlan Coop',
            'plan_code' => 'MEDIPLAN_COOP',
            'description' => 'Affordable medical card plan for healthy Malaysian citizens with comprehensive coverage.',
            'monthly_price' => 90.00,
            'quarterly_price' => null,
            'semi_annually_price' => null,
            'annually_price' => 1080.00,
            'commitment_fee' => 0.00,
            'room_board_limit' => 250.00,
            'annual_limit' => 1000000.00,
            'government_cash_allowance' => 100.00,
            'death_benefit' => 10000.00,
            'min_age' => 0, // 30 days in years (approximately)
            'max_age' => 45,
            'renewal_age' => 100,
            'benefits' => json_encode([
                'room_board' => 'RM250 per day',
                'ambulance_fees' => 'Included',
                'intensive_care_unit' => 'Included',
                'hospital_supplies_services' => 'Included',
                'surgical_fees' => 'Included',
                'operating_theater_fees' => 'Included',
                'anesthetist_fees' => 'Included',
                'in_hospital_doctor_visit' => 'Included',
                'day_care_surgery' => 'Included',
                'second_surgical_opinion' => 'Included',
                'emergency_accidental_dental' => 'Included',
                'covid_test_admission' => 'Included',
                'daily_cash_allowance_govt' => 'RM100 per day (max 120 days)',
                'pre_hospital_diagnostic' => 'RM5,000 per admission',
                'accidental_injury_surgery' => 'RM10,000',
                'bereavement' => 'RM10,000',
                'outpatient_cancer_treatment' => 'RM100,000',
                'conditional_outpatient_benefits' => 'As Charged'
            ]),
            'terms_conditions' => json_encode([
                'eligibility' => 'Healthy Malaysian citizens with no pre-existing medical conditions',
                'age_enrollment' => '30 days to 45 years old',
                'renewal_age' => 'Up to 100 years old',
                'contribution_rates' => 'Fixed regardless of gender, age, or occupation',
                'admission_procedure' => 'Must follow prescribed procedures'
            ]),
            'waiting_period_general' => 90,
            'waiting_period_specific' => 180,
            'administrator' => 'eMAS (Eximius Medical Administration Solutions)',
            'panel_hospitals' => 250,
            'panel_clinics' => 4000,
            'is_active' => true,
        ]);

        // Create Senior Care Plan Gold 270
        InsurancePlan::create([
            'plan_name' => 'Senior Care Plan Gold 270',
            'plan_code' => 'SENIOR_CARE_GOLD_270',
            'description' => 'Comprehensive medical coverage for seniors aged 46-65 with RM270 daily room and board.',
            'monthly_price' => 150.00,
            'quarterly_price' => 450.00,
            'semi_annually_price' => 900.00,
            'annually_price' => 1800.00,
            'commitment_fee' => 150.00, // For monthly payments
            'room_board_limit' => 270.00,
            'annual_limit' => 75000.00,
            'government_cash_allowance' => 100.00,
            'death_benefit' => 10000.00,
            'min_age' => 46,
            'max_age' => 65,
            'renewal_age' => 70,
            'benefits' => json_encode([
                'room_board' => 'RM270 per day (max 180 days)',
                'intensive_care_unit' => 'Full reimbursement (max 30 days)',
                'hospital_supplies_services' => 'Full reimbursement',
                'surgeon_fee' => 'Full reimbursement',
                'anaesthetist_fee' => 'Full reimbursement',
                'operating_theatre_charges' => 'Full reimbursement',
                'daily_in_hospital_physician_visit' => 'Full reimbursement (max 180 days)',
                'pre_hospital_diagnostic' => 'Within 60 days before confinement',
                'pre_hospitalization_specialist' => 'Within 60 days before confinement',
                'second_surgical_opinion' => 'Within 60 days before confinement',
                'post_hospitalization_treatment' => 'Within 60 days from discharge',
                'emergency_accidental_outpatient' => 'Within 24 hours + 60 days follow-up',
                'outpatient_cancer_treatment' => 'Included',
                'outpatient_kidney_dialysis' => 'Included',
                'daycare_procedure' => 'Included',
                'ambulance_charges' => 'By road',
                'government_service_tax' => 'Included',
                'govt_hospital_cash_allowance' => 'RM100 per day (max 180 days)',
                'medical_report_fee' => 'RM80',
                'funeral_expenses_accidental' => 'RM10,000'
            ]),
            'terms_conditions' => json_encode([
                'minimum_entry_age' => '46 years',
                'maximum_entry_age' => '65 years',
                'renewal_age' => 'Up to 70 years old',
                'eligibility' => 'Healthy Malaysian Citizens',
                'payment_modes' => 'Monthly, quarterly, semi-annual, annual',
                'payment_methods' => 'Debit/credit card, FPX Online, BNPL, e-Wallet'
            ]),
            'waiting_period_general' => 90,
            'waiting_period_specific' => 180,
            'administrator' => 'MiCare',
            'panel_hospitals' => 148,
            'panel_clinics' => 0,
            'is_active' => true,
        ]);

        // Create Senior Care Plan Diamond 370
        InsurancePlan::create([
            'plan_name' => 'Senior Care Plan Diamond 370',
            'plan_code' => 'SENIOR_CARE_DIAMOND_370',
            'description' => 'Premium medical coverage for seniors aged 46-65 with RM370 daily room and board.',
            'monthly_price' => 210.00,
            'quarterly_price' => 630.00,
            'semi_annually_price' => 1260.00,
            'annually_price' => 2520.00,
            'commitment_fee' => 210.00, // For monthly payments
            'room_board_limit' => 370.00,
            'annual_limit' => 100000.00,
            'government_cash_allowance' => 200.00,
            'death_benefit' => 10000.00,
            'min_age' => 46,
            'max_age' => 65,
            'renewal_age' => 70,
            'benefits' => json_encode([
                'room_board' => 'RM370 per day (max 180 days)',
                'intensive_care_unit' => 'Full reimbursement (max 30 days)',
                'hospital_supplies_services' => 'Full reimbursement',
                'surgeon_fee' => 'Full reimbursement',
                'anaesthetist_fee' => 'Full reimbursement',
                'operating_theatre_charges' => 'Full reimbursement',
                'daily_in_hospital_physician_visit' => 'Full reimbursement (max 180 days)',
                'pre_hospital_diagnostic' => 'Within 60 days before confinement',
                'pre_hospitalization_specialist' => 'Within 60 days before confinement',
                'second_surgical_opinion' => 'Within 60 days before confinement',
                'post_hospitalization_treatment' => 'Within 60 days from discharge',
                'emergency_accidental_outpatient' => 'Within 24 hours + 60 days follow-up',
                'outpatient_cancer_treatment' => 'Included',
                'outpatient_kidney_dialysis' => 'Included',
                'daycare_procedure' => 'Included',
                'ambulance_charges' => 'By road',
                'government_service_tax' => 'Included',
                'govt_hospital_cash_allowance' => 'RM200 per day (max 180 days)',
                'medical_report_fee' => 'RM80',
                'funeral_expenses_accidental' => 'RM10,000'
            ]),
            'terms_conditions' => json_encode([
                'minimum_entry_age' => '46 years',
                'maximum_entry_age' => '65 years',
                'renewal_age' => 'Up to 70 years old',
                'eligibility' => 'Healthy Malaysian Citizens',
                'payment_modes' => 'Monthly, quarterly, semi-annual, annual',
                'payment_methods' => 'Debit/credit card, FPX Online, BNPL, e-Wallet'
            ]),
            'waiting_period_general' => 90,
            'waiting_period_specific' => 180,
            'administrator' => 'MiCare',
            'panel_hospitals' => 148,
            'panel_clinics' => 0,
            'is_active' => true,
        ]);

        $this->command->info('Insurance plans seeded successfully!');
    }
}