<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurlecPaymentService
{
    private $keyId;
    private $keySecret;
    private $isSandbox;
    private $baseUrl;

    public function __construct()
    {
        $this->keyId = config('services.curlec.key_id');
        $this->keySecret = config('services.curlec.key_secret');
        $this->isSandbox = config('services.curlec.sandbox', true);
        $this->baseUrl = $this->isSandbox ? 'https://api.curlec.com/v1' : 'https://api.curlec.com/v1';
    }

    /**
     * Create a payment order
     */
    public function createOrder($amount, $currency = 'MYR', $receiptId = null, $notes = [])
    {
        try {
            $data = [
                'amount' => $amount * 100, // Amount in sens (smallest currency unit)
                'currency' => $currency,
                'receipt' => $receiptId ?: 'receipt_' . time(),
                'notes' => $notes
            ];

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/orders', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Failed to create order',
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Curlec order creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Fetch payment details
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
                'error' => 'Payment not found',
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Curlec payment fetch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment service temporarily unavailable'
            ];
        }
    }

    /**
     * Verify payment signature for webhooks
     */
    public function verifySignature($payload, $signature, $secret = null)
    {
        $webhookSecret = $secret ?: config('services.curlec.webhook_secret');
        
        if (!$webhookSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Create a checkout session for frontend
     */
    public function createCheckoutSession($order, $customer = [], $options = [])
    {
        $checkoutData = [
            'key' => $this->keyId,
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'order_id' => $order['id'],
            'name' => config('app.name', 'KH Holdings Insurance'),
            'description' => $options['description'] ?? 'Insurance Premium Payment',
            'image' => $options['logo'] ?? '',
            'prefill' => [
                'name' => $customer['name'] ?? '',
                'email' => $customer['email'] ?? '',
                'contact' => $customer['phone'] ?? ''
            ],
            'theme' => [
                'color' => $options['theme_color'] ?? '#264EE4'
            ],
            'modal' => [
                'ondismiss' => 'function(){console.log("Payment cancelled")}'
            ]
        ];

        // Add callback URLs if provided
        if (isset($options['callback_url'])) {
            $checkoutData['callback_url'] = $options['callback_url'];
        }

        if (isset($options['redirect'])) {
            $checkoutData['redirect'] = $options['redirect'];
        }

        return $checkoutData;
    }

    /**
     * Refund a payment
     */
    public function refundPayment($paymentId, $amount = null, $notes = [])
    {
        try {
            $data = [
                'notes' => $notes
            ];

            if ($amount) {
                $data['amount'] = $amount * 100; // Amount in sens
            }

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/payments/' . $paymentId . '/refund', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Refund failed',
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Curlec refund failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Refund service temporarily unavailable'
            ];
        }
    }

    /**
     * Create subscription for recurring payments
     */
    public function createSubscription($planId, $customerId, $quantity = 1, $notes = [])
    {
        try {
            $data = [
                'plan_id' => $planId,
                'customer_id' => $customerId,
                'quantity' => $quantity,
                'notes' => $notes
            ];

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post($this->baseUrl . '/subscriptions', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error'] ?? 'Subscription creation failed',
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            Log::error('Curlec subscription creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Subscription service temporarily unavailable'
            ];
        }
    }

    /**
     * Get checkout options for frontend integration
     */
    public function getCheckoutOptions()
    {
        return [
            'key_id' => $this->keyId,
            'sandbox' => $this->isSandbox,
            'currency' => 'MYR',
            'company_name' => config('app.name', 'KH Holdings Insurance'),
            'company_logo' => url('/assets/logo.png'), // Add your logo URL
            'theme_color' => '#264EE4'
        ];
    }
}
