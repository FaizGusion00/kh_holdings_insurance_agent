<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurlecPaymentService
{
    private $keyId;
    private $keySecret;
    private $webhookSecret;
    private $environment;
    private $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.curlec.key_id');
        $this->keySecret = config('services.curlec.key_secret');
        $this->webhookSecret = config('services.curlec.webhook_secret');
        $this->environment = config('services.curlec.environment', 'test');
        $this->baseUrl = $this->environment === 'live' 
            ? 'https://api.razorpay.com/v1' 
            : 'https://api.razorpay.com/v1';
    }

    /**
     * Create a payment order
     */
    public function createOrder($amount, $currency = 'MYR', $receipt = null, $notes = [])
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/orders', [
                    'amount' => $amount * 100, // Convert to cents
                    'currency' => $currency,
                    'receipt' => $receipt ?? 'order_' . time(),
                    'notes' => $notes
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Failed to create order'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Payment Order Creation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable'
            ];
        }
    }

    /**
     * Create a subscription plan
     */
    public function createPlan($planData)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/plans', $planData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Failed to create plan'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Plan Creation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Plan creation service unavailable'
            ];
        }
    }

    /**
     * Create a subscription
     */
    public function createSubscription($subscriptionData)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/subscriptions', $subscriptionData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Failed to create subscription'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Subscription Creation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Subscription creation service unavailable'
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        $generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, $this->keySecret);
        
        return hash_equals($generatedSignature, $razorpaySignature);
    }

    /**
     * Verify subscription signature
     */
    public function verifySubscriptionSignature($razorpayPaymentId, $razorpaySubscriptionId, $razorpaySignature)
    {
        $generatedSignature = hash_hmac('sha256', $razorpayPaymentId . "|" . $razorpaySubscriptionId, $this->keySecret);
        
        return hash_equals($generatedSignature, $razorpaySignature);
    }

    /**
     * Get payment details
     */
    public function getPayment($paymentId)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get($this->baseUrl . '/payments/' . $paymentId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Payment not found'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Payment Retrieval Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service unavailable'
            ];
        }
    }

    /**
     * Get subscription details
     */
    public function getSubscription($subscriptionId)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get($this->baseUrl . '/subscriptions/' . $subscriptionId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Subscription not found'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Subscription Retrieval Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Subscription service unavailable'
            ];
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($subscriptionId, $cancelAtCycleEnd = false)
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/subscriptions/' . $subscriptionId . '/cancel', [
                    'cancel_at_cycle_end' => $cancelAtCycleEnd
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['description'] ?? 'Failed to cancel subscription'
            ];

        } catch (\Exception $e) {
            Log::error('Curlec Subscription Cancellation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Subscription cancellation service unavailable'
            ];
        }
    }

    /**
     * Get client-side configuration for Razorpay checkout
     */
    public function getCheckoutConfig($orderId, $amount, $currency = 'MYR', $name = 'Medical Insurance', $description = 'Medical Insurance Payment')
    {
        return [
            'key' => $this->keyId,
            'amount' => $amount * 100, // Convert to cents
            'currency' => $currency,
            'name' => $name,
            'description' => $description,
            'order_id' => $orderId,
            'prefill' => [
                'name' => '',
                'email' => '',
                'contact' => ''
            ],
            'theme' => [
                'color' => '#F37254'
            ],
            'handler' => 'function (response) { console.log(response); }',
            'modal' => [
                'ondismiss' => 'function() { console.log("Payment cancelled"); }'
            ]
        ];
    }
}
