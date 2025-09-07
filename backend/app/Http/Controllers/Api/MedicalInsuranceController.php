<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalInsuranceRegistration;
use App\Models\MedicalInsurancePlan;
use App\Models\MedicalInsurancePolicy;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MedicalInsuranceController extends Controller
{
    /**
     * Get all available medical insurance plans
     */
    public function getPlans()
    {
        try {
            $plans = MedicalInsurancePlan::where('is_active', true)->get();
            
            return response()->json([
                'success' => true,
                'data' => $plans,
                'message' => 'Medical insurance plans retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve medical insurance plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific medical insurance plan
     */
    public function getPlan($id)
    {
        try {
            $plan = MedicalInsurancePlan::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Medical insurance plan retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Medical insurance plan not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Register for medical insurance
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'plan_type' => 'required|string|in:MediPlan Coop,Senior Care Plan Gold 270,Senior Care Plan Diamond 370',
                'full_name' => 'required|string|max:255',
                'nric' => 'required|string|size:14|regex:/^\d{6}-\d{2}-\d{4}$/',
                'race' => 'required|string|in:Malay,Chinese,Indian,Others',
                'height_cm' => 'required|integer|min:100|max:250',
                'weight_kg' => 'required|integer|min:30|max:300',
                'phone_number' => 'required|string|regex:/^\+?6?0?1[0-9]{8,9}$/',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6',
                'medical_consultation_2_years' => 'required|boolean',
                'serious_illness_history' => 'required|boolean',
                'insurance_rejection_history' => 'required|boolean',
                'serious_injury_history' => 'required|boolean',
                'emergency_contact_name' => 'required|string|max:255',
                'emergency_contact_phone' => 'required|string|regex:/^\+?6?0?1[0-9]{8,9}$/',
                'emergency_contact_relationship' => 'required|string|max:255',
                'payment_mode' => 'required|string|in:monthly,quarterly,half_yearly,yearly',
                'medical_card_type' => 'required|string|in:e-Medical Card,e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)',
                'add_second_customer' => 'boolean',
                'add_third_customer' => 'boolean',
                'add_fourth_customer' => 'boolean',
                'add_fifth_customer' => 'boolean',
                'add_sixth_customer' => 'boolean',
                'add_seventh_customer' => 'boolean',
                'add_eighth_customer' => 'boolean',
                'add_ninth_customer' => 'boolean',
                'add_tenth_customer' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the plan details
            $plan = MedicalInsurancePlan::where('name', $request->plan_type)->first();
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected plan not found'
                ], 404);
            }

            // Calculate contribution amount
            $contributionAmount = $this->calculateContributionAmount($plan, $request->payment_mode);

            DB::beginTransaction();

            try {
                // Create registration
                $registration = new MedicalInsuranceRegistration();
                $registration->agent_id = auth()->id();
                $registration->registration_number = $registration->generateRegistrationNumber();
                $registration->agent_code = auth()->user()->agent_code;
                $registration->plan_type = $request->plan_type;
                $registration->full_name = $request->full_name;
                $registration->nric = $request->nric;
                $registration->race = $request->race;
                $registration->height_cm = $request->height_cm;
                $registration->weight_kg = $request->weight_kg;
                $registration->phone_number = $request->phone_number;
                $registration->email = $request->email;
                $registration->password = Hash::make($request->password);
                $registration->medical_consultation_2_years = $request->medical_consultation_2_years;
                $registration->serious_illness_history = $request->serious_illness_history;
                $registration->insurance_rejection_history = $request->insurance_rejection_history;
                $registration->serious_injury_history = $request->serious_injury_history;
                $registration->emergency_contact_name = $request->emergency_contact_name;
                $registration->emergency_contact_phone = $request->emergency_contact_phone;
                $registration->emergency_contact_relationship = $request->emergency_contact_relationship;
                $registration->payment_mode = $request->payment_mode;
                $registration->contribution_amount = $contributionAmount;
                $registration->medical_card_type = $request->medical_card_type;
                $registration->add_second_customer = $request->add_second_customer ?? false;
                $registration->add_third_customer = $request->add_third_customer ?? false;
                $registration->add_fourth_customer = $request->add_fourth_customer ?? false;
                $registration->add_fifth_customer = $request->add_fifth_customer ?? false;
                $registration->add_sixth_customer = $request->add_sixth_customer ?? false;
                $registration->add_seventh_customer = $request->add_seventh_customer ?? false;
                $registration->add_eighth_customer = $request->add_eighth_customer ?? false;
                $registration->add_ninth_customer = $request->add_ninth_customer ?? false;
                $registration->add_tenth_customer = $request->add_tenth_customer ?? false;

                // Save additional customer data
                $this->saveAdditionalCustomerData($registration, $request);

                $registration->save();

                // Save emergency contacts after registration is saved
                $this->saveEmergencyContacts($registration, $request);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $registration,
                    'message' => 'Medical insurance registration submitted successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register for medical insurance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registration status
     */
    public function getRegistrationStatus($id)
    {
        try {
            $registration = MedicalInsuranceRegistration::with(['policies', 'agent'])
                ->where('id', $id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $registration,
                'message' => 'Registration status retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all registrations for the authenticated agent
     */
    public function getRegistrations()
    {
        try {
            $registrations = MedicalInsuranceRegistration::with(['policies'])
                ->where('agent_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $registrations,
                'message' => 'Registrations retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve registrations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's medical insurance policies
     */
    public function getUserPolicies()
    {
        try {
            $policies = MedicalInsurancePolicy::where('agent_id', auth()->id())
                ->with(['plan', 'registration'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $policies,
                'message' => 'Medical insurance policies retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve medical insurance policies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate contribution amount based on plan and payment frequency
     */
    private function calculateContributionAmount($plan, $paymentMode)
    {
        $baseAmount = $plan->getPriceByFrequency($paymentMode);
        $commitmentFee = $plan->commitment_fee;
        
        return $baseAmount + $commitmentFee;
    }

    /**
     * Save additional customer data to registration
     */
    private function saveAdditionalCustomerData($registration, $request)
    {
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $planField = "{$customerNumber}_customer_plan_type";
            $nameField = "{$customerNumber}_customer_full_name";
            
            // Only save if the customer has a plan type and full name
            if ($request->has($planField) && $request->has($nameField) && 
                !empty($request->$planField) && !empty($request->$nameField)) {
                
                // Get the plan for this customer
                $customerPlan = MedicalInsurancePlan::where('name', $request->$planField)->first();
                if ($customerPlan) {
                    // Calculate contribution amount for this customer
                    $customerPaymentMode = $request->{"{$customerNumber}_customer_payment_mode"} ?? 'monthly';
                    $customerContributionAmount = $this->calculateContributionAmount($customerPlan, $customerPaymentMode);
                    
                    // Save all customer data
                    $registration->{"{$customerNumber}_customer_plan_type"} = $request->$planField;
                    $registration->{"{$customerNumber}_customer_full_name"} = $request->$nameField;
                    $registration->{"{$customerNumber}_customer_nric"} = $request->{"{$customerNumber}_customer_nric"} ?? null;
                    $registration->{"{$customerNumber}_customer_race"} = $request->{"{$customerNumber}_customer_race"} ?? null;
                    $registration->{"{$customerNumber}_customer_height_cm"} = $request->{"{$customerNumber}_customer_height_cm"} ?? null;
                    $registration->{"{$customerNumber}_customer_weight_kg"} = $request->{"{$customerNumber}_customer_weight_kg"} ?? null;
                    $registration->{"{$customerNumber}_customer_phone_number"} = $request->{"{$customerNumber}_customer_phone_number"} ?? null;
                    $registration->{"{$customerNumber}_customer_medical_consultation_2_years"} = $request->{"{$customerNumber}_customer_medical_consultation_2_years"} ?? false;
                    $registration->{"{$customerNumber}_customer_serious_illness_history"} = $request->{"{$customerNumber}_customer_serious_illness_history"} ?? false;
                    $registration->{"{$customerNumber}_customer_insurance_rejection_history"} = $request->{"{$customerNumber}_customer_insurance_rejection_history"} ?? false;
                    $registration->{"{$customerNumber}_customer_serious_injury_history"} = $request->{"{$customerNumber}_customer_serious_injury_history"} ?? false;
                    $registration->{"{$customerNumber}_customer_payment_mode"} = $customerPaymentMode;
                    $registration->{"{$customerNumber}_customer_contribution_amount"} = $customerContributionAmount;
                    $registration->{"{$customerNumber}_customer_medical_card_type"} = $request->{"{$customerNumber}_customer_medical_card_type"} ?? 'e-Medical Card';
                    
                    // Set the add flag to true
                    $registration->{"add_{$customerNumber}_customer"} = true;
                }
            }
        }
    }

    /**
     * Save emergency contacts for all customers
     */
    private function saveEmergencyContacts($registration, $request)
    {
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $emergencyNameField = "{$customerNumber}_customer_emergency_contact_name";
            $emergencyPhoneField = "{$customerNumber}_customer_emergency_contact_phone";
            $emergencyRelationshipField = "{$customerNumber}_customer_emergency_contact_relationship";
            
            // Check if emergency contact data is provided
            if ($request->has($emergencyNameField) && $request->has($emergencyPhoneField) && $request->has($emergencyRelationshipField) &&
                !empty($request->$emergencyNameField) && !empty($request->$emergencyPhoneField) && !empty($request->$emergencyRelationshipField)) {
                
                EmergencyContact::create([
                    'registration_id' => $registration->id,
                    'customer_type' => $customerNumber,
                    'contact_name' => $request->$emergencyNameField,
                    'contact_phone' => $request->$emergencyPhoneField,
                    'contact_relationship' => $request->$emergencyRelationshipField,
                ]);
            }
        }
    }
}
