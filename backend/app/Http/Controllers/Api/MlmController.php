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
            $totalAmount = 0;
            $clientsData = [];
            $amountBreakdown = [];

            foreach ($request->clients as $clientData) {
                // Check age eligibility for the plan
                $plan = InsurancePlan::findOrFail($clientData['insurance_plan_id']);
                $age = Carbon::parse($clientData['date_of_birth'])->age;
                
                if (!$plan->isEligibleByAge($age)) {
                    throw new \Exception("Client {$clientData['name']} (age {$age}) is not eligible for plan '{$plan->plan_name}' (age range: {$plan->min_age}-{$plan->max_age})");
                }

                // Calculate premium amount without creating user yet
                $premiumAmount = $plan->getPriceByMode($clientData['payment_mode']);
                if (!$premiumAmount) {
                    throw new \Exception("Invalid payment mode for plan {$plan->plan_name}");
                }

                // Compute medical card add-on fee (NFC physical card)
                $cardFee = 0.00;
                if (!empty($clientData['medical_card_type'])) {
                    $cardType = strtolower($clientData['medical_card_type']);
                    if (str_contains($cardType, 'nfc') || str_contains($cardType, 'physical') || str_contains($cardType, 'fizikal')) {
                        $cardFee = 34.90;
                    }
                }

                $clientTotal = (float) $premiumAmount + (float) $cardFee;

                // Store client data with plan info and computed amounts
                $clientData['premium_amount'] = $premiumAmount;
                $clientData['card_fee'] = $cardFee;
                $clientData['total_amount'] = $clientTotal;
                $clientData['plan_name'] = $plan->plan_name;
                $clientsData[] = $clientData;

                // Breakdown row for UI
                $amountBreakdown[] = [
                    'customer_name' => $clientData['name'] ?? 'Customer',
                    'plan' => $plan->plan_name,
                    'payment_mode' => $clientData['payment_mode'],
                    'premium' => (float) $premiumAmount,
                    'card_fee' => (float) $cardFee,
                    'total' => (float) $clientTotal,
                ];

                $totalAmount += $clientTotal;
            }

            // Generate unique batch ID
            $batchId = \App\Models\PendingRegistration::generateBatchId();

            // Create pending registration record
            $pendingRegistration = \App\Models\PendingRegistration::create([
                'agent_id' => $agent->id,
                'registration_batch_id' => $batchId,
                'clients_data' => $clientsData,
                'total_amount' => $totalAmount,
                'status' => 'pending_payment',
                'expires_at' => now()->addHours(24) // Registration expires in 24 hours
            ]);

            // Create payment transaction linked to pending registration
            $payment = PaymentTransaction::create([
                'user_id' => $agent->id,
                'transaction_id' => PaymentTransaction::generateTransactionId(),
                'amount' => $totalAmount,
                'currency' => 'MYR',
                'payment_method' => 'curlec',
                'payment_type' => 'premium',
                'status' => 'pending',
                'notes' => 'Bulk client registration payment - ' . count($clientsData) . ' clients',
                'gateway_response' => json_encode([
                    'pending_registration_id' => $pendingRegistration->id,
                    'batch_id' => $batchId,
                    'agent_id' => $agent->id,
                    'type' => 'bulk_registration',
                    'client_count' => count($clientsData)
                ])
            ]);

            // Link payment transaction to pending registration
            $pendingRegistration->update(['payment_transaction_id' => $payment->id]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Registration prepared successfully. Please proceed to payment.',
                'data' => [
                    'registration_id' => $payment->id,
                    'batch_id' => $batchId,
                    'client_count' => count($clientsData),
                    'total_amount' => $totalAmount,
                    'breakdown' => $amountBreakdown,
                    'payment_transaction' => $payment,
                    'expires_at' => $pendingRegistration->expires_at
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to prepare registration: ' . $e->getMessage(),
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
