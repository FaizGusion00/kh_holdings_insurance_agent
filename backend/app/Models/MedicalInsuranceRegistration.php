<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalInsuranceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'registration_number',
        'agent_code',
        'plan_type',
        'full_name',
        'nric',
        'race',
        'height_cm',
        'weight_kg',
        'phone_number',
        'email',
        'password',
        'medical_consultation_2_years',
        'serious_illness_history',
        'insurance_rejection_history',
        'serious_injury_history',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'payment_mode',
        'contribution_amount',
        'medical_card_type',
        'add_second_customer',
        'second_customer_plan_type',
        'second_customer_full_name',
        'second_customer_nric',
        'second_customer_race',
        'second_customer_height_cm',
        'second_customer_weight_kg',
        'second_customer_phone_number',
        'second_customer_medical_consultation_2_years',
        'second_customer_serious_illness_history',
        'second_customer_insurance_rejection_history',
        'second_customer_serious_injury_history',
        'second_customer_payment_mode',
        'second_customer_contribution_amount',
        'second_customer_medical_card_type',
        'add_third_customer',
        'third_customer_plan_type',
        'third_customer_full_name',
        'third_customer_nric',
        'third_customer_race',
        'third_customer_height_cm',
        'third_customer_weight_kg',
        'third_customer_phone_number',
        'third_customer_medical_consultation_2_years',
        'third_customer_serious_illness_history',
        'third_customer_insurance_rejection_history',
        'third_customer_serious_injury_history',
        'third_customer_payment_mode',
        'third_customer_contribution_amount',
        'third_customer_medical_card_type',
        'add_fourth_customer',
        'fourth_customer_plan_type',
        'fourth_customer_full_name',
        'fourth_customer_nric',
        'fourth_customer_race',
        'fourth_customer_height_cm',
        'fourth_customer_weight_kg',
        'fourth_customer_phone_number',
        'fourth_customer_medical_consultation_2_years',
        'fourth_customer_serious_illness_history',
        'fourth_customer_insurance_rejection_history',
        'fourth_customer_serious_injury_history',
        'fourth_customer_payment_mode',
        'fourth_customer_contribution_amount',
        'fourth_customer_medical_card_type',
        'add_fifth_customer',
        'fifth_customer_plan_type',
        'fifth_customer_full_name',
        'fifth_customer_nric',
        'fifth_customer_race',
        'fifth_customer_height_cm',
        'fifth_customer_weight_kg',
        'fifth_customer_phone_number',
        'fifth_customer_medical_consultation_2_years',
        'fifth_customer_serious_illness_history',
        'fifth_customer_insurance_rejection_history',
        'fifth_customer_serious_injury_history',
        'fifth_customer_payment_mode',
        'fifth_customer_contribution_amount',
        'fifth_customer_medical_card_type',
        'add_sixth_customer',
        'sixth_customer_plan_type',
        'sixth_customer_full_name',
        'sixth_customer_nric',
        'sixth_customer_race',
        'sixth_customer_height_cm',
        'sixth_customer_weight_kg',
        'sixth_customer_phone_number',
        'sixth_customer_medical_consultation_2_years',
        'sixth_customer_serious_illness_history',
        'sixth_customer_insurance_rejection_history',
        'sixth_customer_serious_injury_history',
        'sixth_customer_payment_mode',
        'sixth_customer_contribution_amount',
        'sixth_customer_medical_card_type',
        'add_seventh_customer',
        'seventh_customer_plan_type',
        'seventh_customer_full_name',
        'seventh_customer_nric',
        'seventh_customer_race',
        'seventh_customer_height_cm',
        'seventh_customer_weight_kg',
        'seventh_customer_phone_number',
        'seventh_customer_medical_consultation_2_years',
        'seventh_customer_serious_illness_history',
        'seventh_customer_insurance_rejection_history',
        'seventh_customer_serious_injury_history',
        'seventh_customer_payment_mode',
        'seventh_customer_contribution_amount',
        'seventh_customer_medical_card_type',
        'add_eighth_customer',
        'eighth_customer_plan_type',
        'eighth_customer_full_name',
        'eighth_customer_nric',
        'eighth_customer_race',
        'eighth_customer_height_cm',
        'eighth_customer_weight_kg',
        'eighth_customer_phone_number',
        'eighth_customer_medical_consultation_2_years',
        'eighth_customer_serious_illness_history',
        'eighth_customer_insurance_rejection_history',
        'eighth_customer_serious_injury_history',
        'eighth_customer_payment_mode',
        'eighth_customer_contribution_amount',
        'eighth_customer_medical_card_type',
        'add_ninth_customer',
        'ninth_customer_plan_type',
        'ninth_customer_full_name',
        'ninth_customer_nric',
        'ninth_customer_race',
        'ninth_customer_height_cm',
        'ninth_customer_weight_kg',
        'ninth_customer_phone_number',
        'ninth_customer_medical_consultation_2_years',
        'ninth_customer_serious_illness_history',
        'ninth_customer_insurance_rejection_history',
        'ninth_customer_serious_injury_history',
        'ninth_customer_payment_mode',
        'ninth_customer_contribution_amount',
        'ninth_customer_medical_card_type',
        'add_tenth_customer',
        'tenth_customer_plan_type',
        'tenth_customer_full_name',
        'tenth_customer_nric',
        'tenth_customer_race',
        'tenth_customer_height_cm',
        'tenth_customer_weight_kg',
        'tenth_customer_phone_number',
        'tenth_customer_medical_consultation_2_years',
        'tenth_customer_serious_illness_history',
        'tenth_customer_insurance_rejection_history',
        'tenth_customer_serious_injury_history',
        'tenth_customer_payment_mode',
        'tenth_customer_contribution_amount',
        'tenth_customer_medical_card_type',
        'status',
        'rejection_reason',
        'approved_at',
        'payment_completed_at',
    ];

    protected $casts = [
        'medical_consultation_2_years' => 'boolean',
        'serious_illness_history' => 'boolean',
        'insurance_rejection_history' => 'boolean',
        'serious_injury_history' => 'boolean',
        'add_second_customer' => 'boolean',
        'second_customer_medical_consultation_2_years' => 'boolean',
        'second_customer_serious_illness_history' => 'boolean',
        'second_customer_insurance_rejection_history' => 'boolean',
        'second_customer_serious_injury_history' => 'boolean',
        'add_third_customer' => 'boolean',
        'third_customer_medical_consultation_2_years' => 'boolean',
        'third_customer_serious_illness_history' => 'boolean',
        'third_customer_insurance_rejection_history' => 'boolean',
        'third_customer_serious_injury_history' => 'boolean',
        'add_fourth_customer' => 'boolean',
        'fourth_customer_medical_consultation_2_years' => 'boolean',
        'fourth_customer_serious_illness_history' => 'boolean',
        'fourth_customer_insurance_rejection_history' => 'boolean',
        'fourth_customer_serious_injury_history' => 'boolean',
        'add_fifth_customer' => 'boolean',
        'fifth_customer_medical_consultation_2_years' => 'boolean',
        'fifth_customer_serious_illness_history' => 'boolean',
        'fifth_customer_insurance_rejection_history' => 'boolean',
        'fifth_customer_serious_injury_history' => 'boolean',
        'add_sixth_customer' => 'boolean',
        'sixth_customer_medical_consultation_2_years' => 'boolean',
        'sixth_customer_serious_illness_history' => 'boolean',
        'sixth_customer_insurance_rejection_history' => 'boolean',
        'sixth_customer_serious_injury_history' => 'boolean',
        'add_seventh_customer' => 'boolean',
        'seventh_customer_medical_consultation_2_years' => 'boolean',
        'seventh_customer_serious_illness_history' => 'boolean',
        'seventh_customer_insurance_rejection_history' => 'boolean',
        'seventh_customer_serious_injury_history' => 'boolean',
        'add_eighth_customer' => 'boolean',
        'eighth_customer_medical_consultation_2_years' => 'boolean',
        'eighth_customer_serious_illness_history' => 'boolean',
        'eighth_customer_insurance_rejection_history' => 'boolean',
        'eighth_customer_serious_injury_history' => 'boolean',
        'add_ninth_customer' => 'boolean',
        'ninth_customer_medical_consultation_2_years' => 'boolean',
        'ninth_customer_serious_illness_history' => 'boolean',
        'ninth_customer_insurance_rejection_history' => 'boolean',
        'ninth_customer_serious_injury_history' => 'boolean',
        'add_tenth_customer' => 'boolean',
        'tenth_customer_medical_consultation_2_years' => 'boolean',
        'tenth_customer_serious_illness_history' => 'boolean',
        'tenth_customer_insurance_rejection_history' => 'boolean',
        'tenth_customer_serious_injury_history' => 'boolean',
        'contribution_amount' => 'decimal:2',
        'second_customer_contribution_amount' => 'decimal:2',
        'third_customer_contribution_amount' => 'decimal:2',
        'fourth_customer_contribution_amount' => 'decimal:2',
        'fifth_customer_contribution_amount' => 'decimal:2',
        'sixth_customer_contribution_amount' => 'decimal:2',
        'seventh_customer_contribution_amount' => 'decimal:2',
        'eighth_customer_contribution_amount' => 'decimal:2',
        'ninth_customer_contribution_amount' => 'decimal:2',
        'tenth_customer_contribution_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'payment_completed_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function policies()
    {
        return $this->hasMany(MedicalInsurancePolicy::class, 'registration_id');
    }

    public function generateRegistrationNumber()
    {
        $prefix = 'MED';
        $year = date('Y');
        $month = date('m');
        $lastRegistration = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastRegistration ? (intval(substr($lastRegistration->registration_number, -4)) + 1) : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all customers data as array
     */
    public function getAllCustomers()
    {
        $customers = [];
        
        // Primary customer - only include if has plan_type and full_name
        if ($this->plan_type && $this->full_name) {
            $customers[] = [
                'type' => 'primary',
                'plan_type' => $this->plan_type,
                'full_name' => $this->full_name,
                'nric' => $this->nric,
                'race' => $this->race,
                'height_cm' => $this->height_cm,
                'weight_kg' => $this->weight_kg,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'medical_consultation_2_years' => $this->medical_consultation_2_years,
                'serious_illness_history' => $this->serious_illness_history,
                'insurance_rejection_history' => $this->insurance_rejection_history,
                'serious_injury_history' => $this->serious_injury_history,
                'payment_mode' => $this->payment_mode,
                'contribution_amount' => $this->contribution_amount,
                'medical_card_type' => $this->medical_card_type,
            ];
        }

        // Additional customers - check if they have plan_type and full_name
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $planField = "{$customerNumber}_customer_plan_type";
            $nameField = "{$customerNumber}_customer_full_name";
            
            if ($this->$planField && $this->$nameField) {
                $customers[] = [
                    'type' => $customerNumber,
                    'plan_type' => $this->{"{$customerNumber}_customer_plan_type"},
                    'full_name' => $this->{"{$customerNumber}_customer_full_name"},
                    'nric' => $this->{"{$customerNumber}_customer_nric"},
                    'race' => $this->{"{$customerNumber}_customer_race"},
                    'height_cm' => $this->{"{$customerNumber}_customer_height_cm"},
                    'weight_kg' => $this->{"{$customerNumber}_customer_weight_kg"},
                    'phone_number' => $this->{"{$customerNumber}_customer_phone_number"},
                    'email' => $this->email, // Use primary email
                    'medical_consultation_2_years' => $this->{"{$customerNumber}_customer_medical_consultation_2_years"},
                    'serious_illness_history' => $this->{"{$customerNumber}_customer_serious_illness_history"},
                    'insurance_rejection_history' => $this->{"{$customerNumber}_customer_insurance_rejection_history"},
                    'serious_injury_history' => $this->{"{$customerNumber}_customer_serious_injury_history"},
                    'payment_mode' => $this->{"{$customerNumber}_customer_payment_mode"},
                    'contribution_amount' => $this->{"{$customerNumber}_customer_contribution_amount"},
                    'medical_card_type' => $this->{"{$customerNumber}_customer_medical_card_type"},
                ];
            }
        }

        return $customers;
    }

    /**
     * Get total number of customers
     */
    public function getTotalCustomersCount()
    {
        $count = 0;
        
        // Primary customer - always present if has plan_type and full_name
        if ($this->plan_type && $this->full_name) {
            $count++;
        }
        
        // Additional customers - check if they have plan_type and full_name
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $planField = "{$customerNumber}_customer_plan_type";
            $nameField = "{$customerNumber}_customer_full_name";
            
            if ($this->$planField && $this->$nameField) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Calculate total amount for all customers
     */
    public function getTotalAmount()
    {
        $total = $this->contribution_amount;
        
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $addCustomerField = "add_{$customerNumber}_customer";
            if ($this->$addCustomerField) {
                $contributionField = "{$customerNumber}_customer_contribution_amount";
                $total += $this->$contributionField;
            }
        }

        return $total;
    }

    /**
     * Get customer count by plan type
     */
    public function getCustomerCountByPlan()
    {
        $planCounts = [];
        $customers = $this->getAllCustomers();
        
        foreach ($customers as $customer) {
            $planType = $customer['plan_type'];
            if (!isset($planCounts[$planType])) {
                $planCounts[$planType] = 0;
            }
            $planCounts[$planType]++;
        }
        
        return $planCounts;
    }
}
