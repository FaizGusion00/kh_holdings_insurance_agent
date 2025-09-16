<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\MemberPolicy;
use App\Models\User;
use App\Services\CurlecPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $curlecService;

    public function __construct(CurlecPaymentService $curlecService)
    {
        $this->curlecService = $curlecService;
    }
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);

            $payments = PaymentTransaction::where('user_id', $user->id)
                ->with('memberPolicy.insurancePlan')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $payment = PaymentTransaction::where('user_id', $user->id)
                ->with('memberPolicy.insurancePlan')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => ['payment' => $payment]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found'
            ], 404);
        }
    }

    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_policy_id' => 'required|exists:member_policies,id',
            'payment_method' => 'required|in:curlec,fpx,credit_card,debit_card,ewallet',
            'return_url' => 'nullable|url',
            'cancel_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $policy = MemberPolicy::where('user_id', $user->id)
                ->with('insurancePlan')
                ->findOrFail($request->member_policy_id);

            DB::beginTransaction();

            // Create payment transaction
            $payment = PaymentTransaction::create([
                'user_id' => $user->id,
                'member_policy_id' => $policy->id,
                'transaction_id' => PaymentTransaction::generateTransactionId(),
                'amount' => $policy->premium_amount,
                'currency' => 'MYR',
                'payment_method' => $request->payment_method,
                'payment_type' => 'premium',
                'status' => 'pending'
            ]);

            // Create Curlec order
            $curlecOrder = $this->curlecService->createOrder(
                $policy->premium_amount,
                'MYR',
                $payment->transaction_id,
                [
                    'policy_id' => $policy->id,
                    'user_id' => $user->id,
                    'plan_name' => $policy->insurancePlan->plan_name
                ]
            );

            if (!$curlecOrder['success']) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment gateway error: ' . $curlecOrder['error']
                ], 500);
            }

            // Update payment with Curlec order ID
            $payment->update([
                'gateway_order_id' => $curlecOrder['data']['id'],
                'gateway_response' => $curlecOrder['data']
            ]);

            // Create checkout session data for frontend
            $checkoutData = $this->curlecService->createCheckoutSession(
                $curlecOrder['data'],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number
                ],
                [
                    'description' => "Premium payment for {$policy->insurancePlan->plan_name}",
                    'callback_url' => route('api.payments.callback'),
                    'redirect' => $request->return_url ?: url('/dashboard')
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'checkout_data' => $checkoutData,
                    'curlec_options' => $this->curlecService->getCheckoutOptions()
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createBulkPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:payment_transactions,id',
            'payment_method' => 'required|in:curlec,fpx,credit_card,debit_card,ewallet',
            'return_url' => 'nullable|url',
            'cancel_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $payment = PaymentTransaction::where('user_id', $user->id)
                ->where('id', $request->registration_id)
                ->where('status', 'pending')
                ->firstOrFail();

            // Create Curlec order
            $curlecOrder = $this->curlecService->createOrder(
                $payment->amount,
                'MYR',
                $payment->transaction_id,
                [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'type' => 'bulk_registration'
                ]
            );

            if (!$curlecOrder['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment gateway error: ' . $curlecOrder['error']
                ], 500);
            }

            // Update payment with Curlec order ID
            $payment->update([
                'gateway_order_id' => $curlecOrder['data']['id'],
                'gateway_response' => $curlecOrder['data'],
                'payment_method' => $request->payment_method
            ]);

            // Create checkout session data for frontend
            $checkoutData = $this->curlecService->createCheckoutSession(
                $curlecOrder['data'],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_number
                ],
                [
                    'description' => $payment->description,
                    'callback_url' => route('api.payments.callback'),
                    'redirect' => $request->return_url ?: url('/dashboard')
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk payment initiated successfully',
                'data' => [
                    'payment' => $payment,
                    'checkout_data' => $checkoutData,
                    'curlec_options' => $this->curlecService->getCheckoutOptions()
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create bulk payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            // Verify webhook signature for security
            $signature = $request->header('X-Razorpay-Signature') ?: $request->header('X-Curlec-Signature');
            $payload = $request->getContent();
            
            // Note: Enable this in production
            // if (!$this->curlecService->verifySignature($payload, $signature)) {
            //     return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            // }

            $data = $request->all();
            
            // Handle payment success/failure
            if (isset($data['event']) && $data['event'] === 'payment.captured') {
                $paymentData = $data['payload']['payment']['entity'];
                $this->handlePaymentSuccess($paymentData);
            } elseif (isset($data['event']) && $data['event'] === 'payment.failed') {
                $paymentData = $data['payload']['payment']['entity'];
                $this->handlePaymentFailure($paymentData);
            }

            // Handle direct payment response (from frontend)
            if (isset($data['razorpay_payment_id'])) {
                $this->handleDirectPaymentResponse($data);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            \Log::error('Payment callback failed: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Callback processing failed'
            ], 500);
        }
    }

    private function handlePaymentSuccess($paymentData)
    {
        $orderId = $paymentData['order_id'];
        $paymentId = $paymentData['id'];
        
        $payment = PaymentTransaction::where('gateway_order_id', $orderId)->first();
        
        if ($payment) {
            DB::beginTransaction();
            try {
                $payment->update([
                    'status' => 'completed',
                    'gateway_payment_id' => $paymentId,
                    'gateway_response' => $paymentData,
                    'paid_at' => now()
                ]);

                $this->processSuccessfulPayment($payment);
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Payment success processing failed: ' . $e->getMessage());
            }
        }
    }

    private function handlePaymentFailure($paymentData)
    {
        $orderId = $paymentData['order_id'];
        
        $payment = PaymentTransaction::where('gateway_order_id', $orderId)->first();
        
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => $paymentData,
                'failure_reason' => $paymentData['error_description'] ?? 'Payment failed'
            ]);
        }
    }

    private function handleDirectPaymentResponse($data)
    {
        // Verify payment with Curlec
        $paymentId = $data['razorpay_payment_id'];
        $paymentDetails = $this->curlecService->getPayment($paymentId);
        
        if ($paymentDetails['success'] && $paymentDetails['data']['status'] === 'captured') {
            $this->handlePaymentSuccess($paymentDetails['data']);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            
            $payment = PaymentTransaction::where('transaction_id', $transactionId)->first();

            if (!$payment) {
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => ['payment' => $payment]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify payment'
            ], 500);
        }
    }

    public function getReceipt($id)
    {
        try {
            $user = Auth::user();
            
            $payment = PaymentTransaction::where('user_id', $user->id)
                ->where('status', 'completed')
                ->with('memberPolicy.insurancePlan')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => ['receipt' => $payment]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Receipt not found'
            ], 404);
        }
    }

    private function generatePaymentUrl($payment)
    {
        // Generate Curlec payment URL
        return "https://payment.curlec.com/checkout/{$payment->transaction_id}";
    }

    private function processSuccessfulPayment($payment)
    {
        // Check if this is a bulk payment by looking at gateway_response
        $gatewayResponse = json_decode($payment->gateway_response, true);
        $isBulkPayment = isset($gatewayResponse['type']) && $gatewayResponse['type'] === 'bulk_registration';
        
        if ($isBulkPayment) {
            // Handle bulk payment processing
            $this->processBulkPaymentSuccess($payment);
        } else {
            // Handle individual policy payment
            if ($payment->memberPolicy) {
                $policy = $payment->memberPolicy;
                $policy->update(['status' => 'active']);
                
                // Process MLM commissions for individual policy
                $this->processCommissions($payment);
            }
        }
    }

    private function processBulkPaymentSuccess($payment)
    {
        $gatewayResponse = json_decode($payment->gateway_response, true);
        
        // Check if this is a new pending registration system
        if (isset($gatewayResponse['pending_registration_id'])) {
            $this->processNewBulkRegistration($payment, $gatewayResponse);
            return;
        }
        
        // Legacy support for old system
        if (!$gatewayResponse || !isset($gatewayResponse['client_ids']) || !isset($gatewayResponse['policy_ids'])) {
            \Log::error('Invalid bulk payment gateway_response: ' . $payment->id);
            return;
        }

        // Activate all clients and policies (legacy)
        $clientIds = $gatewayResponse['client_ids'];
        $policyIds = $gatewayResponse['policy_ids'];
        
        // Activate all client accounts
        User::whereIn('id', $clientIds)->update([
            'status' => 'active'
        ]);
        
        // Activate all policies
        MemberPolicy::whereIn('id', $policyIds)->update([
            'status' => 'active'
        ]);
        
        // Process commissions for each policy
        $policies = MemberPolicy::whereIn('id', $policyIds)->with(['user', 'insurancePlan'])->get();
        
        foreach ($policies as $policy) {
            $this->processBulkCommissions($policy, $payment);
        }
    }

    private function processNewBulkRegistration($payment, $gatewayResponse)
    {
        try {
            DB::beginTransaction();

            // Get pending registration
            $pendingRegistration = \App\Models\PendingRegistration::findOrFail($gatewayResponse['pending_registration_id']);
            
            if ($pendingRegistration->status !== 'pending_payment') {
                \Log::error('Pending registration already processed: ' . $pendingRegistration->id);
                return;
            }

            $agent = $pendingRegistration->agent;
            $clientsData = $pendingRegistration->clients_data;
            
            $createdClients = [];
            $createdPolicies = [];

            // Now create the actual users and policies
            foreach ($clientsData as $clientData) {
                // Get the insurance plan
                $plan = InsurancePlan::findOrFail($clientData['insurance_plan_id']);

                // Calculate the agent's MLM level for this client
                $clientLevel = $this->calculateClientLevel($agent);

                // Create the client user
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
                    'status' => 'active', // Immediately active after payment
                    'mlm_level' => 0, // Clients are level 0
                    'registration_date' => now(),
                    // Insurance tracking fields
                    'current_insurance_plan_id' => $plan->id,
                    'policy_start_date' => now(),
                    'policy_end_date' => $this->calculateEndDate($clientData['payment_mode']),
                    'next_payment_due' => $this->calculateNextPayment($clientData['payment_mode']),
                    'policy_status' => 'active',
                    'premium_amount' => $clientData['premium_amount'],
                    'current_payment_mode' => $clientData['payment_mode'],
                ]);

                // Create member policy
                $policy = MemberPolicy::create([
                    'user_id' => $client->id,
                    'insurance_plan_id' => $plan->id,
                    'policy_number' => MemberPolicy::generatePolicyNumber(),
                    'payment_mode' => $clientData['payment_mode'],
                    'premium_amount' => $clientData['premium_amount'],
                    'medical_card_type' => $clientData['medical_card_type'],
                    'policy_start_date' => now(),
                    'policy_end_date' => $this->calculateEndDate($clientData['payment_mode']),
                    'next_payment_due' => $this->calculateNextPayment($clientData['payment_mode']),
                    'status' => 'active'
                ]);

                $createdClients[] = $client;
                $createdPolicies[] = $policy;

                // Process MLM commissions for this client
                $this->processNewMLMCommissions($client, $policy, $payment);
            }

            // Mark pending registration as completed
            $pendingRegistration->update(['status' => 'payment_completed']);

            DB::commit();

            \Log::info('Successfully processed bulk registration', [
                'registration_id' => $pendingRegistration->id,
                'clients_created' => count($createdClients),
                'policies_created' => count($createdPolicies)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to process bulk registration: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'pending_registration_id' => $gatewayResponse['pending_registration_id'] ?? null
            ]);
        }
    }

    private function calculateClientLevel($agent)
    {
        // Clients are always level 0, but for future use
        return 0;
    }

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

    private function processNewMLMCommissions($client, $policy, $payment)
    {
        $currentAgent = User::where('agent_code', $client->referrer_code)->first();
        $level = 1;
        $maxLevels = 5;

        while ($currentAgent && $level <= $maxLevels) {
            $commissionAmount = $this->calculateCommissionForLevel($policy, $level);
            
            if ($commissionAmount > 0) {
                // Add commission to agent's wallet
                \App\Http\Controllers\Api\WalletController::addCommission(
                    $currentAgent->id,
                    $commissionAmount,
                    "L{$level} commission from {$client->name} - {$policy->insurancePlan->plan_name}",
                    $client->id
                );

                \Log::info("Commission distributed", [
                    'agent_id' => $currentAgent->id,
                    'agent_code' => $currentAgent->agent_code,
                    'level' => $level,
                    'amount' => $commissionAmount,
                    'client' => $client->name,
                    'plan' => $policy->insurancePlan->plan_name
                ]);
            }

            // Move to next level in hierarchy
            if ($currentAgent->referrer_code) {
                $currentAgent = User::where('agent_code', $currentAgent->referrer_code)->first();
                $level++;
            } else {
                break; // Reached top level
            }
        }
    }

    private function calculateCommissionForLevel($policy, $level)
    {
        $plan = $policy->insurancePlan;
        $paymentMode = $policy->payment_mode;
        
        $commissionRate = \App\Models\CommissionRate::where('insurance_plan_id', $plan->id)
            ->where('payment_mode', $paymentMode)
            ->where('tier_level', $level)
            ->first();
        
        return $commissionRate ? $commissionRate->commission_amount : 0;
    }

    private function processBulkCommissions($policy, $bulkPayment)
    {
        $client = $policy->user;
        
        // Find the agent (referrer) and process commissions up the chain
        if ($client->referrer_code) {
            $agent = User::where('agent_code', $client->referrer_code)->first();
            if ($agent) {
                // Process T1 commission for direct agent
                $commission = $this->calculatePolicyCommission($policy, 1);
                if ($commission > 0) {
                    WalletController::addCommission(
                        $agent->id,
                        $commission,
                        "T1 Commission from {$client->name} - {$policy->insurancePlan->plan_name}",
                        $client->id
                    );
                }
                
                // Continue up the chain for T2-T5
                $this->processUplineBulkCommissions($agent, $policy, $client, 2);
            }
        }
    }

    private function processUplineBulkCommissions($currentUser, $policy, $originalClient, $level)
    {
        if ($level > 5 || !$currentUser->referrer_code) {
            return;
        }

        $uplineUser = User::where('agent_code', $currentUser->referrer_code)->first();
        if ($uplineUser) {
            $commission = $this->calculatePolicyCommission($policy, $level);
            if ($commission > 0) {
                WalletController::addCommission(
                    $uplineUser->id,
                    $commission,
                    "T{$level} Commission from {$originalClient->name} - {$policy->insurancePlan->plan_name}",
                    $originalClient->id
                );
            }
            
            // Continue to next level
            $this->processUplineBulkCommissions($uplineUser, $policy, $originalClient, $level + 1);
        }
    }

    private function calculatePolicyCommission($policy, $tierLevel)
    {
        $plan = $policy->insurancePlan;
        $paymentMode = $policy->payment_mode;
        
        $commissionRate = \App\Models\CommissionRate::where('insurance_plan_id', $plan->id)
            ->where('payment_mode', $paymentMode)
            ->where('tier_level', $tierLevel)
            ->first();
        
        return $commissionRate ? $commissionRate->commission_amount : 0;
    }

    /**
     * Test method to simulate payment completion (for testing only)
     */
    public function testCompletePayment($id)
    {
        try {
            $payment = PaymentTransaction::findOrFail($id);
            
            if ($payment->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment is not in pending status'
                ], 400);
            }

            DB::beginTransaction();
            
            // Mark payment as completed
            $payment->update([
                'status' => 'completed',
                'gateway_payment_id' => 'test_payment_' . time(),
                'paid_at' => now()
            ]);

            // Process the successful payment (activate clients and distribute commissions)
            $this->processSuccessfulPayment($payment);
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment completed and commissions distributed successfully',
                'data' => ['payment' => $payment]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete payment: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processCommissions($payment)
    {
        // Get the user who made the payment
        $user = $payment->user;
        
        // Find their referrer and process commissions up the chain
        if ($user->referrer_code) {
            $referrer = \App\Models\User::where('agent_code', $user->referrer_code)->first();
            if ($referrer) {
                // Process T1 commission for direct referrer
                $commission = $this->calculateCommission($payment, 1);
                if ($commission > 0) {
                    \App\Http\Controllers\Api\WalletController::addCommission(
                        $referrer->id,
                        $commission,
                        "T1 Commission from {$user->name} - {$payment->memberPolicy->insurancePlan->plan_name}",
                        $user->id
                    );
                }
                
                // Continue up the chain for T2-T5
                $this->processUplineCommissions($referrer, $payment, $user, 2);
            }
        }
    }

    private function processUplineCommissions($currentUser, $payment, $originalBuyer, $level)
    {
        if ($level > 5 || !$currentUser->referrer_code) {
            return;
        }

        $uplineUser = \App\Models\User::where('agent_code', $currentUser->referrer_code)->first();
        if ($uplineUser) {
            $commission = $this->calculateCommission($payment, $level);
            if ($commission > 0) {
                \App\Http\Controllers\Api\WalletController::addCommission(
                    $uplineUser->id,
                    $commission,
                    "T{$level} Commission from {$originalBuyer->name} - {$payment->memberPolicy->insurancePlan->plan_name}",
                    $originalBuyer->id
                );
            }
            
            // Continue to next level
            $this->processUplineCommissions($uplineUser, $payment, $originalBuyer, $level + 1);
        }
    }

    private function calculateCommission($payment, $tierLevel)
    {
        $plan = $payment->memberPolicy->insurancePlan;
        $paymentMode = $payment->memberPolicy->payment_mode;
        
        $commissionRate = \App\Models\CommissionRate::where('insurance_plan_id', $plan->id)
            ->where('payment_mode', $paymentMode)
            ->where('tier_level', $tierLevel)
            ->first();
        
        return $commissionRate ? $commissionRate->commission_amount : 0;
    }
}
