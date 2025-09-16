<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\InsurancePlan;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MLM Controller for API
 * 
 * Handles MLM network management, referrals, and commission tracking
 */
class MlmController extends Controller
{
    /**
     * Get user's MLM network structure
     */
    public function getNetwork(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            // Get direct referrals
            $networkMembers = User::where('referrer_code', $user->agent_code)
                ->with(['memberPolicies' => function($q) {
                    $q->where('status', 'active');
                }])
                ->paginate($perPage);

            // Transform data
            $networkMembers->getCollection()->transform(function($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'nric' => $member->nric,
                    'agent_code' => $member->agent_code,
                    'mlm_level' => $member->mlm_level,
                    'registration_date' => $member->registration_date,
                    'status' => $member->status,
                    'active_policies_count' => $member->memberPolicies ? $member->memberPolicies->count() : 0,
                    'total_commission_earned' => $member->total_commission_earned ?? 0,
                    'downline_count' => User::where('referrer_code', $member->agent_code)->count()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => ['network_members' => $networkMembers]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch network data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commission history
     */
    public function getCommissionHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            $commissions = WalletTransaction::where('user_id', $user->id)
                ->where('type', 'commission_earned')
                ->with('relatedUser')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => ['commissions' => $commissions]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch commission history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get level summary
     */
    public function getLevelSummary()
    {
        try {
            $user = Auth::user();
            
            $summary = [
                'user_level' => $user->mlm_level,
                'total_downlines' => User::where('referrer_code', $user->agent_code)->count(),
                'active_downlines' => User::where('referrer_code', $user->agent_code)->where('status', 'active')->count(),
                'total_commission' => $user->total_commission_earned ?? 0,
                'monthly_commission' => WalletTransaction::where('user_id', $user->id)
                    ->where('type', 'commission_earned')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('amount')
            ];

            return response()->json([
                'status' => 'success',
                'data' => ['level_summary' => $summary]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch level summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register new client
     */
    public function registerClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone_number' => 'required|string|max:20',
            'nric' => 'required|string|unique:users',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $agent = Auth::user();

            $client = User::create([
                'referrer_code' => $agent->agent_code,
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'nric' => $request->nric,
                'race' => $request->race ?? 'Other',
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'occupation' => $request->occupation ?? 'Not specified',
                'address' => $request->address ?? '',
                'city' => $request->city ?? '',
                'state' => $request->state ?? '',
                'postal_code' => $request->postal_code ?? '',
                'emergency_contact_name' => $request->emergency_contact_name ?? '',
                'emergency_contact_phone' => $request->emergency_contact_phone ?? '',
                'emergency_contact_relationship' => $request->emergency_contact_relationship ?? '',
                'password' => bcrypt($request->password),
                'customer_type' => 'client',
                'status' => 'active',
                'registration_date' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Client registered successfully',
                'data' => ['client' => $client]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register multiple clients with insurance plans and handle payment
     */
    public function registerBulkClients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clients' => 'required|array|min:1|max:10',
            'clients.*.name' => 'required|string|max:255',
            'clients.*.email' => 'required|string|email|unique:users,email',
            'clients.*.phone_number' => 'required|string|max:20',
            'clients.*.nric' => 'required|string|unique:users,nric',
            'clients.*.date_of_birth' => 'required|date',
            'clients.*.gender' => 'required|in:Male,Female',
            'clients.*.password' => 'required|string|min:8',
            'clients.*.insurance_plan_id' => 'required|exists:insurance_plans,id',
            'clients.*.payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'clients.*.medical_card_type' => 'required|string',
            'clients.*.race' => 'nullable|string',
            'clients.*.occupation' => 'nullable|string',
            'clients.*.address' => 'nullable|string',
            'clients.*.city' => 'nullable|string',
            'clients.*.state' => 'nullable|string',
            'clients.*.postal_code' => 'nullable|string',
            'clients.*.emergency_contact_name' => 'nullable|string',
            'clients.*.emergency_contact_phone' => 'nullable|string',
            'clients.*.emergency_contact_relationship' => 'nullable|string',
            'clients.*.medical_consultation_2_years' => 'nullable|boolean',
            'clients.*.serious_illness_history' => 'nullable|boolean',
            'clients.*.insurance_rejection_history' => 'nullable|boolean',
            'clients.*.serious_injury_history' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $agent = Auth::user();
            $registeredClients = [];
            $totalAmount = 0;
            $clientPolicies = [];

            foreach ($request->clients as $clientData) {
                // Check age eligibility for the plan
                $plan = InsurancePlan::findOrFail($clientData['insurance_plan_id']);
                $age = Carbon::parse($clientData['date_of_birth'])->age;
                
                if (!$plan->isEligibleByAge($age)) {
                    throw new \Exception("Client {$clientData['name']} (age {$age}) is not eligible for plan '{$plan->plan_name}' (age range: {$plan->min_age}-{$plan->max_age})");
                }

                // Create client user
                $client = User::create([
                    'referrer_code' => $agent->agent_code,
                    'name' => $clientData['name'],
                    'email' => $clientData['email'],
                    'phone_number' => $clientData['phone_number'],
                    'nric' => $clientData['nric'],
                    'race' => $clientData['race'] ?? 'Other',
                    'date_of_birth' => $clientData['date_of_birth'],
                    'gender' => $clientData['gender'],
                    'occupation' => $clientData['occupation'] ?? 'Not specified',
                    'address' => $clientData['address'] ?? '',
                    'city' => $clientData['city'] ?? '',
                    'state' => $clientData['state'] ?? '',
                    'postal_code' => $clientData['postal_code'] ?? '',
                    'emergency_contact_name' => $clientData['emergency_contact_name'] ?? '',
                    'emergency_contact_phone' => $clientData['emergency_contact_phone'] ?? '',
                    'emergency_contact_relationship' => $clientData['emergency_contact_relationship'] ?? '',
                    'medical_consultation_2_years' => $clientData['medical_consultation_2_years'] ?? false,
                    'serious_illness_history' => ($clientData['serious_illness_history'] ?? false) ? json_encode($clientData['serious_illness_history']) : null,
                    'insurance_rejection_history' => $clientData['insurance_rejection_history'] ?? false,
                    'serious_injury_history' => ($clientData['serious_injury_history'] ?? false) ? json_encode($clientData['serious_injury_history']) : null,
                    'password' => bcrypt($clientData['password']),
                    'customer_type' => 'client',
                    'status' => 'pending_verification', // Will be activated after payment
                    'registration_date' => now(),
                ]);

                // Calculate premium amount
                $premiumAmount = $plan->getPriceByMode($clientData['payment_mode']);
                if (!$premiumAmount) {
                    throw new \Exception("Invalid payment mode for plan {$plan->plan_name}");
                }

                // Create member policy
                $policy = MemberPolicy::create([
                    'user_id' => $client->id,
                    'insurance_plan_id' => $plan->id,
                    'policy_number' => MemberPolicy::generatePolicyNumber(),
                    'payment_mode' => $clientData['payment_mode'],
                    'premium_amount' => $premiumAmount,
                    'medical_card_type' => $clientData['medical_card_type'],
                    'policy_start_date' => now(),
                    'policy_end_date' => $this->calculateEndDate($clientData['payment_mode']),
                    'next_payment_due' => $this->calculateNextPayment($clientData['payment_mode']),
                    'status' => 'pending_payment'
                ]);

                $registeredClients[] = $client;
                $clientPolicies[] = $policy;
                $totalAmount += $premiumAmount;
            }

            // Create a single bulk payment transaction
            $payment = PaymentTransaction::create([
                'user_id' => $agent->id, // Agent initiates the payment
                'transaction_id' => PaymentTransaction::generateTransactionId(),
                'amount' => $totalAmount,
                'currency' => 'MYR',
                'payment_method' => 'curlec',
                'payment_type' => 'premium',
                'status' => 'pending',
                'notes' => 'Bulk client registration payment - ' . count($registeredClients) . ' clients',
                'gateway_response' => json_encode([
                    'client_ids' => collect($registeredClients)->pluck('id')->toArray(),
                    'policy_ids' => collect($clientPolicies)->pluck('id')->toArray(),
                    'agent_id' => $agent->id,
                    'type' => 'bulk_registration'
                ])
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Clients registered successfully. Please proceed to payment.',
                'data' => [
                    'registration_id' => $payment->id,
                    'clients' => $registeredClients,
                    'policies' => $clientPolicies,
                    'total_amount' => $totalAmount,
                    'payment_transaction' => $payment
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register clients: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate policy end date based on payment mode
     */
    private function calculateEndDate($paymentMode)
    {
        switch ($paymentMode) {
            case 'monthly':
                return now()->addMonth();
            case 'quarterly':
                return now()->addMonths(3);
            case 'semi_annually':
                return now()->addMonths(6);
            case 'annually':
                return now()->addYear();
            default:
                return now()->addMonth();
        }
    }

    /**
     * Calculate next payment due date based on payment mode
     */
    private function calculateNextPayment($paymentMode)
    {
        switch ($paymentMode) {
            case 'monthly':
                return now()->addMonth();
            case 'quarterly':
                return now()->addMonths(3);
            case 'semi_annually':
                return now()->addMonths(6);
            case 'annually':
                return now()->addYear();
            default:
                return now()->addMonth();
        }
    }

    /**
     * Get team performance
     */
    public function getTeamPerformance()
    {
        try {
            $user = Auth::user();
            
            $teamMembers = User::where('referrer_code', $user->agent_code)->get();
            $performance = [];

            foreach ($teamMembers as $member) {
                $performance[] = [
                    'member' => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email
                    ],
                    'commission_earned' => WalletTransaction::where('user_id', $member->id)
                        ->where('type', 'commission_earned')
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->sum('amount'),
                    'referrals_count' => User::where('referrer_code', $member->agent_code)->count()
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => ['team_performance' => $performance]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch team performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
