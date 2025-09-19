<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\CurlecPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MedicalRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1|max:10',
            'clients.*.plan_type' => 'required|string',
            'clients.*.full_name' => 'required|string|max:255',
            'clients.*.nric' => 'required|string|max:20|unique:users,nric',
            'clients.*.race' => 'required|string',
            'clients.*.height_cm' => 'required|numeric|min:50|max:300',
            'clients.*.weight_kg' => 'required|numeric|min:10|max:500',
            'clients.*.phone_number' => 'required|string|max:20',
            'clients.*.email' => 'required|email|unique:users,email',
            'clients.*.password' => 'required|string|min:6',
            'clients.*.medical_consultation_2_years' => 'required|boolean',
            'clients.*.serious_illness_history' => 'required|boolean',
            'clients.*.insurance_rejection_history' => 'required|boolean',
            'clients.*.serious_injury_history' => 'required|boolean',
            'clients.*.emergency_contact_name' => 'required|string|max:255',
            'clients.*.emergency_contact_phone' => 'required|string|max:20',
            'clients.*.emergency_contact_relationship' => 'required|string|max:100',
            'clients.*.payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'clients.*.medical_card_type' => 'required|string',
            'plan_id' => 'required|integer|exists:insurance_plans,id',
            'payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'agent_code' => 'nullable|string|exists:users,agent_code', // For external registration
        ]);

        $agent = auth('api')->user();
        
        // For external registration, find agent by agent_code
        if (!$agent && isset($validated['agent_code']) && $validated['agent_code']) {
            $agent = User::where('agent_code', $validated['agent_code'])->first();
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid agent code provided'
                ], 422);
            }
        }
        
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required or valid agent code must be provided'
            ], 401);
        }
        $registrationId = time() . $agent->id;
        $clients = [];
        $policies = [];
        $totalAmountCents = 0;
        $breakdown = [];

        try {
            DB::transaction(function () use ($validated, $agent, &$clients, &$policies, &$totalAmountCents, &$breakdown) {
                foreach ($validated['clients'] as $clientData) {
                    // Find the insurance plan using the plan_id from the main payload
                    $plan = InsurancePlan::find($validated['plan_id']);
                    if (!$plan) {
                        throw new \Exception("Invalid plan ID: {$validated['plan_id']}");
                    }

                    // Generate unique email if not provided
                    $email = $clientData['email'] ?? $this->generateUniqueEmail($clientData['full_name']);

                    // Create the client user
                    $clientDataToCreate = [
                        'name' => $clientData['full_name'],
                        'email' => $email,
                        'phone_number' => $clientData['phone_number'],
                        'nric' => $clientData['nric'],
                        'race' => $clientData['race'],
                        'height_cm' => $clientData['height_cm'],
                        'weight_kg' => $clientData['weight_kg'],
                        'emergency_contact_name' => $clientData['emergency_contact_name'],
                        'emergency_contact_phone' => $clientData['emergency_contact_phone'],
                        'emergency_contact_relationship' => $clientData['emergency_contact_relationship'],
                        'password' => Hash::make($clientData['password']), // Use password from request
                        'referrer_code' => $agent->agent_code,
                        'customer_type' => 'client',
                    ];
                    if (\Schema::hasColumn('users', 'status')) {
                        $clientDataToCreate['status'] = 'pending_payment';
                    }
                    // Include optional medical flags only if columns exist
                    if (\Schema::hasColumn('users', 'medical_consultation_2_years')) {
                        $clientDataToCreate['medical_consultation_2_years'] = (bool) ($clientData['medical_consultation_2_years'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'serious_illness_history')) {
                        $clientDataToCreate['serious_illness_history'] = (bool) ($clientData['serious_illness_history'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'insurance_rejection_history')) {
                        $clientDataToCreate['insurance_rejection_history'] = (bool) ($clientData['insurance_rejection_history'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'serious_injury_history')) {
                        $clientDataToCreate['serious_injury_history'] = (bool) ($clientData['serious_injury_history'] ?? false);
                    }
                    // Filter keys to existing columns to avoid unknown column errors
                    $existing = collect(\Schema::getColumnListing('users'))->flip();
                    $safePayload = collect($clientDataToCreate)->filter(function ($v, $k) use ($existing) {
                        return $existing->has($k);
                    })->all();

                    $client = User::create($safePayload);

                    // Create policy for the client
                    $policy = MemberPolicy::create([
                        'user_id' => $client->id,
                        'plan_id' => $plan->id,
                        'policy_number' => 'POL' . time() . $client->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => $this->calculateEndDate($clientData['payment_mode']),
                        'status' => 'pending',
                        'auto_renew' => true,
                    ]);

                    $clients[] = $client;
                    $policies[] = $policy;

                    // Calculate amount based on payment mode
                    $planAmountCents = $this->calculatePlanAmount($plan, $clientData['payment_mode']);
                    $totalAmountCents += $planAmountCents;

                    $breakdown[] = [
                        'client_name' => $client->name,
                        'plan_name' => $plan->name,
                        'payment_mode' => $clientData['payment_mode'],
                        'amount_cents' => $planAmountCents,
                        'line_total' => $planAmountCents / 100,
                    ];
                }
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'registration_id' => $registrationId,
                    'clients' => $clients,
                    'policies' => $policies,
                    'total_amount' => $totalAmountCents / 100,
                    'amount_breakdown' => $breakdown,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Medical registration failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function createPayment(Request $request, CurlecPaymentService $curlecService)
    {
        $validated = $request->validate([
            'registration_id' => 'required|string',
            'policy_ids' => 'required|array|min:1',
            'payment_method' => 'required|string',
            'return_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
        ]);

        try {
            $policies = MemberPolicy::whereIn('id', $validated['policy_ids'])->get();
            $totalAmountCents = 0;
            $planIds = [];

            foreach ($policies as $policy) {
                $plan = $policy->plan;
                $user = $policy->user;
                $planAmount = $this->calculatePlanAmount($plan, $user->current_payment_mode ?? 'monthly');
                $totalAmountCents += $planAmount;
                $planIds[] = $plan->id;
            }

            // For external registrations, use the first policy's user as the payment user
            $paymentUserId = auth('api')->id();
            if (!$paymentUserId && $policies->count() > 0) {
                $paymentUserId = $policies->first()->user_id;
            }

            // Create a consolidated payment transaction
            $payment = PaymentTransaction::create([
                'user_id' => $paymentUserId,
                'plan_id' => $planIds[0], // Use first plan as reference
                'gateway' => 'curlec',
                'amount_cents' => $totalAmountCents,
                'currency' => 'MYR',
                'status' => 'pending',
                'meta' => [
                    'registration_id' => $validated['registration_id'],
                    'policy_ids' => $validated['policy_ids'],
                    'plan_ids' => $planIds,
                ],
            ]);

            // Create Curlec order
            try {
                $curlecOrder = $curlecService->createOrder($payment);
                $payment->update([
                    'external_ref' => $curlecOrder['order_id'],
                    'meta' => array_merge($payment->meta, ['curlec_order' => $curlecOrder])
                ]);

                $checkoutData = [
                    'payment_id' => $payment->id,
                    'amount' => $totalAmountCents / 100,
                    'currency' => $curlecOrder['currency'],
                    'order_id' => $curlecOrder['order_id'],
                    'checkout_url' => $curlecOrder['checkout_url'],
                ];
            } catch (\Exception $e) {
                Log::warning('Curlec order creation failed, using mock: ' . $e->getMessage());
                
                $checkoutData = [
                    'payment_id' => $payment->id,
                    'amount' => $totalAmountCents / 100,
                    'currency' => 'MYR',
                    'order_id' => 'MOCK-' . $payment->id,
                    'checkout_url' => null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payment' => $payment,
                    'checkout_data' => $checkoutData,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment creation failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function verifyPayment(Request $request, CommissionService $commissionService)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payment_transactions,id',
            'status' => 'required|in:success,failed',
            'external_ref' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $commissionService) {
                $payment = PaymentTransaction::findOrFail($validated['payment_id']);
                
                if ($validated['status'] === 'success') {
                    // Update payment status
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'external_ref' => $validated['external_ref'] ?? $payment->external_ref,
                    ]);

                    // Update all related policies to active
                    if (isset($payment->meta['policy_ids'])) {
                        MemberPolicy::whereIn('id', $payment->meta['policy_ids'])
                            ->update(['status' => 'active']);

                        // Update users to have agent codes and active status
                        $policyIds = $payment->meta['policy_ids'];
                        $policies = MemberPolicy::whereIn('id', $policyIds)->with('user')->get();
                        
                        foreach ($policies as $policy) {
                            $user = $policy->user;
                            if (!$user->agent_code) {
                                $updates = ['agent_code' => $this->generateAgentCode()];
                                if (\Schema::hasColumn('users', 'status')) {
                                    $updates['status'] = 'active';
                                }
                                if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                                    $updates['mlm_activation_date'] = now();
                                }
                                $user->update($updates);
                            }
                        }
                    }

                    // Update the agent who made the payment to have an agent code if they don't have one
                    $agent = $payment->user;
                    if ($agent && empty($agent->agent_code)) {
                        $updates = ['agent_code' => $this->generateAgentCode()];
                        if (\Schema::hasColumn('users', 'status')) {
                            $updates['status'] = 'active';
                        }
                        if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                            $updates['mlm_activation_date'] = now();
                        }
                        $agent->update($updates);
                    }

                    // Calculate and disburse commissions
                    $commissionService->disburseForPayment($payment);

                    // Log the payment verification
                    Log::info('Payment verification completed', [
                        'payment_id' => $payment->id,
                        'agent_id' => $agent->id,
                        'agent_code' => $agent->agent_code,
                        'referrer_code' => $agent->referrer_code,
                    ]);

                } else {
                    // Update payment as failed
                    $payment->update(['status' => 'failed']);
                    
                    // Update related policies to failed
                    if (isset($payment->meta['policy_ids'])) {
                        MemberPolicy::whereIn('id', $payment->meta['policy_ids'])
                            ->update(['status' => 'failed']);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verification completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getReceipt(int $paymentId)
    {
        try {
            $payment = PaymentTransaction::with(['user', 'plan'])->findOrFail($paymentId);
            
            $receiptData = [
                'payment_id' => $payment->id,
                'payment_date' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'amount' => $payment->amount_cents / 100,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'agent_name' => $payment->user->name,
                'agent_email' => $payment->user->email,
                'breakdown' => [],
            ];

            // Get client details from policy IDs in meta
            if (isset($payment->meta['policy_ids'])) {
                $policies = MemberPolicy::whereIn('id', $payment->meta['policy_ids'])
                    ->with(['user', 'plan'])
                    ->get();

                foreach ($policies as $policy) {
                    $receiptData['breakdown'][] = [
                        'client_name' => $policy->user->name,
                        'client_email' => $policy->user->email,
                        'plan_name' => $policy->plan->name,
                        'policy_number' => $policy->policy_number,
                        'start_date' => $policy->start_date,
                        'end_date' => $policy->end_date,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => ['receipt' => $receiptData]
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Receipt not found'
            ], 404);
        }
    }

    private function generateUniqueEmail(string $name): string
    {
        $baseEmail = strtolower(str_replace(' ', '.', $name)) . '@generated.local';
        $counter = 1;
        $email = $baseEmail;
        
        while (User::where('email', $email)->exists()) {
            $email = strtolower(str_replace(' ', '.', $name)) . $counter . '@generated.local';
            $counter++;
        }
        
        return $email;
    }

    private function calculateEndDate(string $paymentMode): string
    {
        $startDate = now();
        
        switch ($paymentMode) {
            case 'monthly':
                return $startDate->addMonth()->toDateString();
            case 'quarterly':
                return $startDate->addMonths(3)->toDateString();
            case 'semi_annually':
                return $startDate->addMonths(6)->toDateString();
            case 'annually':
                return $startDate->addYear()->toDateString();
            default:
                return $startDate->addYear()->toDateString();
        }
    }

    private function calculatePlanAmount(InsurancePlan $plan, string $paymentMode): int
    {
        // Base annual amount
        $annualAmountCents = $plan->price_cents ?? 0;
        
        switch ($paymentMode) {
            case 'monthly':
                return intval($annualAmountCents / 12);
            case 'quarterly':
                return intval($annualAmountCents / 4);
            case 'semi_annually':
                return intval($annualAmountCents / 2);
            case 'annually':
                return $annualAmountCents;
            default:
                return $annualAmountCents;
        }
    }

    private function generateAgentCode(): string
    {
        $sequence = User::whereNotNull('agent_code')->count() + 1;
        // Enforce 5-digit suffix (AGT00001)
        return 'AGT' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
