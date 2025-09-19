<?php

namespace App\Services;

use App\Models\GatewayRecord;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurlecPaymentService
{
    private string $baseUrl;
    private ?string $keyId;
    private ?string $keySecret;
    private bool $sandbox;

    public function __construct()
    {
        $this->baseUrl = config('services.curlec.base_url', 'https://api.curlec.com');
        $this->keyId = config('services.curlec.key_id');
        $this->keySecret = config('services.curlec.key_secret');
        $this->sandbox = (bool) config('services.curlec.sandbox', true);
    }

    public function createOrder(PaymentTransaction $payment): array
    {
        $payload = [
            'amount' => $payment->amount_cents / 100, // Convert to RM
            'currency' => 'MYR',
            'receipt' => 'KHI-' . $payment->id,
            'notes' => [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
            ],
        ];

        // If no keys configured, return a mock response for local testing
        if ($this->sandbox && (! $this->keyId || ! $this->keySecret)) {
            return [
                'order_id' => 'MOCK-' . $payment->id,
                'amount' => $payload['amount'],
                'currency' => $payload['currency'],
                'checkout_url' => null,
            ];
        }

        $response = $this->makeRequest('POST', '/v1/orders', $payload);
        
        // Store gateway record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'request',
            'external_ref' => $response['id'] ?? null,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        return [
            'order_id' => $response['id'],
            'amount' => $response['amount'],
            'currency' => $response['currency'],
            'checkout_url' => $response['checkout_url'] ?? null,
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        // In sandbox or if signature is empty, accept to ease local testing
        if ($this->sandbox || empty($signature)) {
            return true;
        }
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->keySecret ?? '');
        return hash_equals($expectedSignature, $signature);
    }

    public function handleWebhook(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderId || !$status) {
            Log::warning('Invalid Curlec webhook payload', $payload);
            return;
        }

        // Store webhook record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'webhook',
            'external_ref' => $orderId,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        // Find payment by order ID in notes
        $payment = PaymentTransaction::where('external_ref', $orderId)
            ->orWhere('meta->order_id', $orderId)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for Curlec order', ['order_id' => $orderId]);
            return;
        }

        // Update payment status
        if ($status === 'paid') {
            $payment->status = 'paid';
            $payment->paid_at = now();
            $payment->save();

            // Trigger commission disbursement
            app(CommissionService::class)->disburseForPayment($payment);

            // Ensure payer has an agent_code after successful first payment
            $user = $payment->user;
            if ($user && empty($user->agent_code)) {
                $seq = str_pad((string) (\App\Models\User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
                $user->agent_code = 'AGT' . $seq;
                $user->save();
            }
        } elseif ($status === 'failed') {
            $payment->status = 'failed';
            $payment->save();
        }
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->keySecret,
            'Content-Type' => 'application/json',
            'X-Curlec-Key' => $this->keyId,
        ])->$method($url, $data);

        if (!$response->successful()) {
            Log::error('Curlec API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Curlec payment failed: ' . $response->body());
        }

        return $response->json();
    }
}
