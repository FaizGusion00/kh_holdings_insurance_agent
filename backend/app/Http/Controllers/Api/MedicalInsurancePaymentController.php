<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurlecPaymentService;
use App\Services\CommissionAutomationService;
use App\Models\MedicalInsuranceRegistration;
use App\Models\MedicalInsurancePlan;
use App\Models\MedicalInsurancePolicy;
use App\Models\PaymentTransaction;
use App\Models\GatewayPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MedicalInsurancePaymentController extends Controller
{
    protected $curlecService;
    protected $commissionAutomationService;

    public function __construct(CurlecPaymentService $curlecService, CommissionAutomationService $commissionAutomationService)
    {
        $this->curlecService = $curlecService;
        $this->commissionAutomationService = $commissionAutomationService;
    }

    /**
     * Create payment order for medical insurance
     */
    public function createPaymentOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'registration_id' => 'required|exists:medical_insurance_registrations,id',
                'plan_id' => 'required|exists:medical_insurance_plans,id',
                'payment_frequency' => 'required|in:monthly,quarterly,half_yearly,yearly',
                'customer_type' => 'required|in:primary,second,third,fourth,fifth,sixth,seventh,eighth,ninth,tenth',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $registration = MedicalInsuranceRegistration::findOrFail($request->registration_id);
            $plan = MedicalInsurancePlan::findOrFail($request->plan_id);

            // Verify the registration belongs to the authenticated agent
            if ($registration->agent_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to registration'
                ], 403);
            }

            // Calculate amount based on customer type
            $amount = $this->calculateAmount($registration, $plan, $request->customer_type, $request->payment_frequency);

            // Create payment order
            $orderResult = $this->curlecService->createOrder(
                $amount,
                'MYR',
                'med_ins_' . $registration->registration_number . '_' . $request->customer_type,
                [
                    'registration_id' => $registration->id,
                    'plan_id' => $plan->id,
                    'customer_type' => $request->customer_type,
                    'agent_id' => auth()->id()
                ]
            );

            if (!$orderResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment order',
                    'error' => $orderResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $orderResult['data'],
                    'checkout_config' => $this->curlecService->getCheckoutConfig(
                        $orderResult['data']['id'],
                        $amount,
                        'MYR',
                        'Medical Insurance Payment',
                        'Medical Insurance Payment for ' . $plan->name
                    )
                ],
                'message' => 'Payment order created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment and update registration status
     */
    public function verifyPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'registration_id' => 'required|exists:medical_insurance_registrations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $registration = MedicalInsuranceRegistration::findOrFail($request->registration_id);

            // Verify the registration belongs to the authenticated agent
            if ($registration->agent_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to registration'
                ], 403);
            }

            // Verify payment signature
            $isValid = $this->curlecService->verifyPaymentSignature(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature
            );

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment signature'
                ], 400);
            }

            // Get payment details from Curlec
            $paymentResult = $this->curlecService->getPayment($request->razorpay_payment_id);

            if (!$paymentResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify payment with payment gateway',
                    'gateway_error' => $paymentResult['error'] ?? null
                ], 400);
            }

            $payment = $paymentResult['data'];

            // Update registration status
            DB::beginTransaction();

            try {
                $registration->status = 'payment_pending';
                $registration->payment_completed_at = now();
                $registration->save();

                // Create policy if payment is successful
                if (in_array($payment['status'], ['captured', 'authorized'])) {
                    Log::info('MI payment verified, creating policies and commissions', [
                        'registration_id' => $registration->id,
                        'payment_id' => $payment['id'] ?? null,
                        'amount' => $payment['amount'] ?? null,
                        'status' => $payment['status'] ?? null,
                    ]);
                    $this->createPolicyFromRegistration($registration);
                    // Create/update users per registered customers and record payments per user
                    $users = $this->syncClientsFromRegistration($registration);
                    $this->recordGatewayPayment($registration, $payment, $users);
                    $registration->status = 'active';
                    $registration->save();

                    // Process commission automation
                    $this->processCommissionAutomation($registration);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'registration' => $registration->fresh(),
                        'payment' => $payment
                    ],
                    'message' => 'Payment verified successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create payment order for all customers in registration
     */
    public function createPaymentOrderForAllCustomers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'registration_id' => 'required|exists:medical_insurance_registrations,id',
                'calculate_only' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $registration = MedicalInsuranceRegistration::findOrFail($request->registration_id);

            // Verify the registration belongs to the authenticated agent
            if ($registration->agent_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to registration'
                ], 403);
            }

            // Calculate total amount and per-customer breakdown
            [$totalAmount, $breakdown] = $this->calculateTotalAmountWithBreakdown($registration);
            Log::info('MI total pre-checkout', [
                'registration_id' => $registration->id,
                'total_amount' => $totalAmount,
            ]);

            // If calculate_only is true, just return the total amount
            if ($request->calculate_only) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_amount' => $totalAmount,
                        'currency' => 'MYR',
                        'customer_count' => $registration->getTotalCustomersCount(),
                        'breakdown' => $breakdown
                    ],
                    'message' => 'Total amount calculated successfully'
                ]);
            }

            if ($totalAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid customers found for payment'
                ], 400);
            }

            // Create payment order
            $orderResult = $this->curlecService->createOrder(
                $totalAmount,
                'MYR',
                'med_ins_all_' . $registration->registration_number,
                [
                    'registration_id' => $registration->id,
                    'agent_id' => auth()->id(),
                    'customer_count' => $registration->getTotalCustomersCount()
                ]
            );

            if (!$orderResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment order',
                    'error' => $orderResult['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderResult['data']['id'],
                    'amount' => $totalAmount,
                    'currency' => 'MYR',
                    'customer_count' => $registration->getTotalCustomersCount(),
                    'breakdown' => $breakdown,
                    'checkout_config' => $this->curlecService->getCheckoutConfig(
                        $orderResult['data']['id'],
                        $totalAmount,
                        'MYR',
                        'Medical Insurance Registration',
                        'Payment for ' . $registration->getTotalCustomersCount() . ' customers'
                    )
                ],
                'message' => 'Payment order created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment configuration for frontend
     */
    public function getPaymentConfig()
    {
        try {
            $config = [
                'key_id' => config('services.curlec.key_id'),
                'environment' => config('services.curlec.environment'),
                'currency' => 'MYR',
                'theme' => [
                    'color' => '#F37254'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Payment configuration retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List gateway payments for authenticated agent
     */
    public function getGatewayPayments(Request $request)
    {
        try {
            $perPage = (int) ($request->query('per_page', 15));
            $payments = GatewayPayment::where('agent_id', auth()->id())
                ->orderByDesc('created_at')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Gateway payments retrieved successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve gateway payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate payment amount based on customer type and plan
     */
    private function calculateAmount($registration, $plan, $customerType, $paymentFrequency)
    {
        switch ($customerType) {
            case 'primary':
                return $plan->getTotalPriceByFrequency($paymentFrequency);
            case 'second':
                $secondPlan = MedicalInsurancePlan::where('name', $registration->second_customer_plan_type)->first();
                return $secondPlan ? $secondPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'third':
                $thirdPlan = MedicalInsurancePlan::where('name', $registration->third_customer_plan_type)->first();
                return $thirdPlan ? $thirdPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'fourth':
                $fourthPlan = MedicalInsurancePlan::where('name', $registration->fourth_customer_plan_type)->first();
                return $fourthPlan ? $fourthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'fifth':
                $fifthPlan = MedicalInsurancePlan::where('name', $registration->fifth_customer_plan_type)->first();
                return $fifthPlan ? $fifthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'sixth':
                $sixthPlan = MedicalInsurancePlan::where('name', $registration->sixth_customer_plan_type)->first();
                return $sixthPlan ? $sixthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'seventh':
                $seventhPlan = MedicalInsurancePlan::where('name', $registration->seventh_customer_plan_type)->first();
                return $seventhPlan ? $seventhPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'eighth':
                $eighthPlan = MedicalInsurancePlan::where('name', $registration->eighth_customer_plan_type)->first();
                return $eighthPlan ? $eighthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'ninth':
                $ninthPlan = MedicalInsurancePlan::where('name', $registration->ninth_customer_plan_type)->first();
                return $ninthPlan ? $ninthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            case 'tenth':
                $tenthPlan = MedicalInsurancePlan::where('name', $registration->tenth_customer_plan_type)->first();
                return $tenthPlan ? $tenthPlan->getTotalPriceByFrequency($paymentFrequency) : 0;
            default:
                return 0;
        }
    }

    /**
     * Calculate total amount for all customers in registration
     */
    private function calculateTotalAmountForAllCustomers($registration)
    {
        $totalAmount = 0;
        
        // Calculate based on actual customer data present, not flags
        $customers = $this->getAllCustomersFromRegistration($registration);
        
        foreach ($customers as $customer) {
            $plan = MedicalInsurancePlan::where('name', $customer['plan_type'])->first();
            if ($plan) {
                // Get base price based on payment mode
                $basePrice = $this->getPlanPriceByFrequency($plan, $customer['payment_mode']);
                $cardFee = $this->getCardFeeByType($customer['medical_card_type'] ?? '');
                $totalAmount += $basePrice + ($plan->commitment_fee ?? 0) + $cardFee;
            }
        }
        
        return $totalAmount;
    }

    /**
     * Calculate total amount and provide a per-customer breakdown
     */
    private function calculateTotalAmountWithBreakdown($registration): array
    {
        $totalAmount = 0;
        $breakdown = [];

        $customers = $this->getAllCustomersFromRegistration($registration);

        foreach ($customers as $customer) {
            $plan = MedicalInsurancePlan::where('name', $customer['plan_type'])->first();
            if (!$plan) {
                continue;
            }
            $basePrice = $this->getPlanPriceByFrequency($plan, $customer['payment_mode']);
            $commitment = $plan->commitment_fee ?? 0;
            $cardFee = $this->getCardFeeByType($customer['medical_card_type'] ?? '');
            $lineTotal = $basePrice + $commitment + $cardFee;
            $totalAmount += $lineTotal;

            $breakdown[] = [
                'customer_type' => $customer['type'] ?? 'primary',
                'plan_type' => $customer['plan_type'],
                'payment_mode' => $customer['payment_mode'],
                'base_price' => $basePrice,
                'commitment_fee' => $commitment,
                'card_fee' => $cardFee,
                'line_total' => $lineTotal,
            ];
        }

        return [$totalAmount, $breakdown];
    }

    /**
     * Additional fee for physical/NFC medical card types
     */
    private function getCardFeeByType(?string $cardType): float
    {
        if (!$cardType) return 0.0;
        $normalized = trim(strtolower($cardType));
        if (str_contains($normalized, 'nfc') || str_contains($normalized, 'fizikal')) {
            return 34.90;
        }
        return 0.0;
    }
    
    /**
     * Get all customers from registration based on actual data present
     */
    private function getAllCustomersFromRegistration($registration)
    {
        $customers = [];
        
        // Primary customer - always present
        if ($registration->plan_type && $registration->full_name) {
            $customers[] = [
                'plan_type' => $registration->plan_type,
                'payment_mode' => $registration->payment_mode,
                'medical_card_type' => $registration->medical_card_type,
            ];
        }
        
        // Additional customers - check if they have plan_type and full_name
        $customerNumbers = ['second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth'];
        
        foreach ($customerNumbers as $customerNumber) {
            $planField = "{$customerNumber}_customer_plan_type";
            $nameField = "{$customerNumber}_customer_full_name";
            $paymentField = "{$customerNumber}_customer_payment_mode";
            $cardField = "{$customerNumber}_customer_medical_card_type";
            
            if ($registration->$planField && $registration->$nameField) {
                $customers[] = [
                    'plan_type' => $registration->$planField,
                    'payment_mode' => $registration->$paymentField,
                    'medical_card_type' => $registration->$cardField,
                ];
            }
        }
        
        return $customers;
    }
    
    /**
     * Get plan price by frequency
     */
    private function getPlanPriceByFrequency($plan, $frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return $plan->monthly_price;
            case 'quarterly':
                return $plan->quarterly_price ?? 0;
            case 'half_yearly':
                return $plan->half_yearly_price ?? 0;
            case 'yearly':
                return $plan->yearly_price;
            default:
                return $plan->monthly_price;
        }
    }

    /**
     * Create policy from registration
     */
    private function createPolicyFromRegistration($registration)
    {
        $customers = $registration->getAllCustomers();
        
        foreach ($customers as $customer) {
            $plan = MedicalInsurancePlan::where('name', $customer['plan_type'])->first();
            
            if ($plan) {
                $policy = new MedicalInsurancePolicy();
                $policy->registration_id = $registration->id;
                $policy->plan_id = $plan->id;
                $policy->agent_id = $registration->agent_id;
                $policy->policy_number = $policy->generatePolicyNumber();
                $policy->customer_type = $customer['type'];
                $policy->customer_name = $customer['full_name'];
                $policy->customer_nric = $customer['nric'];
                $policy->customer_phone = $customer['phone_number'];
                $policy->customer_email = $customer['email'];
                $policy->payment_frequency = $customer['payment_mode'];
                $policy->premium_amount = $plan->getPriceByFrequency($customer['payment_mode']);
                $policy->commitment_fee = $plan->commitment_fee;
                $policy->medical_card_type = $customer['medical_card_type'];
                $policy->status = 'active';
                $policy->start_date = now()->toDateString();
                $policy->end_date = now()->addYear()->toDateString();
                $policy->next_payment_date = $this->calculateNextPaymentDate($customer['payment_mode']);
                $policy->activated_at = now();
                $policy->save();
            }
        }
    }

    /**
     * Record payment in payment history
     */
    private function recordPaymentHistory($registration, $payment)
    {
        $totalAmount = $registration->getTotalAmount();
        $customerCount = $registration->getTotalCustomersCount();
        
        // Create payment transaction record aligned with schema
        $paymentTransaction = new PaymentTransaction();
        // Associate to agent via a synthetic member if needed; otherwise, leave null
        // Since this is a registration payment across multiple potential customers, store minimal gateway refs
        $paymentTransaction->member_id = null;
        $paymentTransaction->policy_id = null;
        $paymentTransaction->transaction_id = $payment['id'] ?? ('TXN' . now()->format('YmdHis'));
        $paymentTransaction->amount = $totalAmount;
        // Map to existing enums
        $paymentTransaction->payment_method = 'card';
        $paymentTransaction->status = 'completed';
        $paymentTransaction->payment_date = now();
        $paymentTransaction->reference_number = $registration->registration_number;
        $paymentTransaction->notes = "Medical Insurance Registration - {$customerCount} customers";
        $paymentTransaction->save();
    }

    /**
     * Store raw gateway payment tied to registration/agent
     */
    private function recordGatewayPayment($registration, array $payment, array $users = []): void
    {
        try {
            // Store one aggregate payment row
            GatewayPayment::create([
                'registration_id' => $registration->id,
                'agent_id' => $registration->agent_id,
                'client_id' => null,
                'gateway' => 'curlec',
                'payment_id' => $payment['id'] ?? null,
                'order_id' => $payment['order_id'] ?? null,
                'amount' => $registration->getTotalAmount(),
                'currency' => 'MYR',
                'status' => $payment['status'] ?? 'completed',
                'description' => "Medical Insurance Registration - {$registration->getTotalCustomersCount()} customers",
                'metadata' => [
                    'registration_number' => $registration->registration_number,
                    'plans' => $registration->getCustomerCountByPlan(),
                ],
                'gateway_response' => $payment,
                'completed_at' => now(),
            ]);

            // Store per-user payment rows
            foreach ($users as $user) {
                GatewayPayment::create([
                    'registration_id' => $registration->id,
                    'agent_id' => $registration->agent_id,
                    'client_id' => $user->id, // Now references user id
                    'gateway' => 'curlec',
                    'payment_id' => $payment['id'] ?? null,
                    'order_id' => $payment['order_id'] ?? null,
                    'amount' => $user->plan_name ? ((\App\Models\MedicalInsurancePlan::where('name', $user->plan_name)->first()?->getTotalPriceByFrequency($user->payment_mode) ?? 0) + $this->getCardFeeByType($user->medical_card_type)) : 0,
                    'currency' => 'MYR',
                    'status' => $payment['status'] ?? 'completed',
                    'description' => "Payment for {$user->name} ({$user->customer_type})",
                    'metadata' => [
                        'plan_name' => $user->plan_name,
                        'payment_mode' => $user->payment_mode,
                        'medical_card_type' => $user->medical_card_type,
                    ],
                    'gateway_response' => $payment,
                    'completed_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to persist gateway payment: ' . $e->getMessage(), [
                'registration_id' => $registration->id,
            ]);
        }
    }

    /**
     * Ensure User records exist for each customer in the registration (consolidated approach)
     * @return User[]
     */
    private function syncClientsFromRegistration($registration): array
    {
        $users = [];
        foreach ($registration->getAllCustomers() as $customer) {
            try {
                // Get the referrer agent who registered this client
                $referrerAgent = $registration->agent;
                
                // Calculate MLM level for the new user
                $mlmLevel = 1;
                if ($referrerAgent && $referrerAgent->referral) {
                    $mlmLevel = ((int) $referrerAgent->referral->referral_level) + 1;
                } elseif ($referrerAgent) {
                    $mlmLevel = 2;
                }

                // Create or update the User record with all consolidated data
                $user = \App\Models\User::firstOrCreate(
                    [
                        'nric' => $customer['nric'],
                    ],
                    [
                        // Basic user info
                        'name' => $customer['full_name'],
                        'email' => $customer['email'] ?? (strtolower(str_replace(' ', '', $customer['full_name'])) . '@wekongsi.local'),
                        'password' => bcrypt('Temp1234!'),
                        'phone_number' => $customer['phone_number'] ?? '',
                        'status' => 'active',
                        
                        // Agent info (auto-generated)
                        'agent_code' => \App\Models\User::generateAgentCode(),
                        'agent_number' => \App\Models\User::generateAgentNumber(),
                        'mlm_activation_date' => now(),
                        
                        // Network/referral info
                        'referrer_code' => $referrerAgent?->agent_code,
                        'referrer_id' => $referrerAgent?->id,
                        'mlm_level' => $mlmLevel,
                        
                        // Plan/policy info (consolidated from client data)
                        'plan_name' => $customer['plan_type'],
                        'payment_mode' => $customer['payment_mode'],
                        'medical_card_type' => $customer['medical_card_type'],
                        'customer_type' => $customer['type'],
                        'registration_id' => $registration->id,
                        
                        // Customer demographics
                        'race' => $customer['race'] ?? '',
                        'height_cm' => $customer['height_cm'] ?? null,
                        'weight_kg' => $customer['weight_kg'] ?? null,
                        
                        // Emergency contact
                        'emergency_contact_name' => $registration->emergency_contact_name,
                        'emergency_contact_phone' => $registration->emergency_contact_phone,
                        'emergency_contact_relationship' => $registration->emergency_contact_relationship,
                        
                        // Medical history
                        'medical_consultation_2_years' => $registration->medical_consultation_2_years ?? false,
                        'serious_illness_history' => $registration->serious_illness_history ?? false,
                        'insurance_rejection_history' => $registration->insurance_rejection_history ?? false,
                        'serious_injury_history' => $registration->serious_injury_history ?? false,
                        
                        // Timestamps and balances
                        'registration_date' => now(),
                        'balance' => 0,
                        'wallet_balance' => 0,
                    ]
                );

                // Update existing user if needed to ensure they have agent status and plan info
                $updateData = [];
                if (!$user->agent_code) {
                    $updateData['agent_code'] = \App\Models\User::generateAgentCode();
                }
                if (!$user->agent_number) {
                    $updateData['agent_number'] = \App\Models\User::generateAgentNumber();
                }
                if (empty($user->status) || $user->status !== 'active') {
                    $updateData['status'] = 'active';
                }
                if (!$user->mlm_activation_date) {
                    $updateData['mlm_activation_date'] = now();
                }
                if (!$user->plan_name && $customer['plan_type']) {
                    $updateData['plan_name'] = $customer['plan_type'];
                    $updateData['payment_mode'] = $customer['payment_mode'];
                    $updateData['medical_card_type'] = $customer['medical_card_type'];
                    $updateData['customer_type'] = $customer['type'];
                    $updateData['registration_id'] = $registration->id;
                }
                if ($updateData) {
                    $user->update($updateData);
                    $user->refresh();
                }

                // Create Referral record for network tracking
                if ($user->agent_code) {
                    \App\Models\Referral::firstOrCreate(
                        [ 'user_id' => $user->id ],
                        [
                            'agent_code' => $user->agent_code,
                            'referrer_code' => $referrerAgent?->agent_code,
                            'referral_level' => $mlmLevel,
                            'upline_chain' => ($referrerAgent && $referrerAgent->referral) 
                                ? array_merge([$referrerAgent->agent_code], (array) $referrerAgent->referral->upline_chain) 
                                : [],
                            'status' => 'active',
                            'activation_date' => now(),
                        ]
                    );
                }

                $users[] = $user;

            } catch (\Throwable $e) {
                \Log::warning('Failed to create/update user for client: ' . $e->getMessage(), [
                    'registration_id' => $registration->id,
                    'nric' => $customer['nric'] ?? null,
                    'customer_type' => $customer['type'] ?? null,
                ]);
            }
        }

        return $users;
    }

    /**
     * Calculate next payment date based on frequency
     */
    private function calculateNextPaymentDate($frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return now()->addMonth()->toDateString();
            case 'quarterly':
                return now()->addMonths(3)->toDateString();
            case 'half_yearly':
                return now()->addMonths(6)->toDateString();
            case 'yearly':
                return now()->addYear()->toDateString();
            default:
                return now()->addMonth()->toDateString();
        }
    }

    /**
     * Process commission automation for successful payment
     */
    private function processCommissionAutomation($registration)
    {
        try {
            Log::info("Starting commission automation for registration {$registration->id}");
            
            $result = $this->commissionAutomationService->processMedicalInsuranceCommission($registration->id);
            
            if ($result['success']) {
                Log::info("Commission automation completed successfully for registration {$registration->id}. Processed: {$result['processed_count']}, Total: RM " . number_format($result['total_amount'], 2));
            } else {
                Log::error("Commission automation failed for registration {$registration->id}: " . $result['error']);
            }
            
        } catch (\Exception $e) {
            Log::error("Exception in commission automation for registration {$registration->id}: " . $e->getMessage());
        }
    }
}
