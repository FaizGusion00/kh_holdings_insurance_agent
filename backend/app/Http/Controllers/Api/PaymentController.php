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
        
        if (!$gatewayResponse || !isset($gatewayResponse['client_ids']) || !isset($gatewayResponse['policy_ids'])) {
            \Log::error('Invalid bulk payment gateway_response: ' . $payment->id);
            return;
        }

        // Activate all clients and policies
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
