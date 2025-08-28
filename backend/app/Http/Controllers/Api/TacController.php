<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TacController extends Controller
{
    /**
     * Send TAC code to phone number.
     */
    public function sendTac(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
            'purpose' => 'required|in:phone_verification,phone_change,password_reset,login',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $phoneNumber = $request->phone_number;
            $purpose = $request->purpose;
            
            // Generate 6-digit TAC code
            $tacCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store TAC in cache for 5 minutes
            $cacheKey = "tac_{$purpose}_{$phoneNumber}";
            Cache::put($cacheKey, $tacCode, now()->addMinutes(5));
            
            // TODO: Integrate with your SMS service provider
            // For now, we'll just log the TAC code
            Log::info("TAC Code sent to {$phoneNumber}: {$tacCode} (Purpose: {$purpose})");
            
            // In production, you would send this via SMS:
            // $this->sendSms($phoneNumber, "Your TAC code is: {$tacCode}. Valid for 5 minutes.");
            
            return response()->json([
                'success' => true,
                'message' => 'TAC code sent successfully',
                'data' => [
                    'phone_number' => $phoneNumber,
                    'purpose' => $purpose,
                    'expires_in' => 300, // 5 minutes in seconds
                    'note' => 'TAC code sent to your phone number'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send TAC: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send TAC code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify TAC code.
     */
    public function verifyTac(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
            'tac_code' => 'required|string|size:6',
            'purpose' => 'required|in:phone_verification,phone_change,password_reset,login',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $phoneNumber = $request->phone_number;
            $tacCode = $request->tac_code;
            $purpose = $request->purpose;
            
            // Check if TAC exists in cache
            $cacheKey = "tac_{$purpose}_{$phoneNumber}";
            $storedTac = Cache::get($cacheKey);
            
            if (!$storedTac) {
                return response()->json([
                    'success' => false,
                    'message' => 'TAC code expired or not found'
                ], 422);
            }
            
            // Verify TAC code
            if ($storedTac !== $tacCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid TAC code'
                ], 422);
            }
            
            // Remove TAC from cache after successful verification
            Cache::forget($cacheKey);
            
            // Store verification result in cache for 10 minutes (for subsequent operations)
            $verificationKey = "verified_{$purpose}_{$phoneNumber}";
            Cache::put($verificationKey, true, now()->addMinutes(10));
            
            Log::info("TAC verification successful for {$phoneNumber} (Purpose: {$purpose})");
            
            return response()->json([
                'success' => true,
                'message' => 'TAC code verified successfully',
                'data' => [
                    'phone_number' => $phoneNumber,
                    'purpose' => $purpose,
                    'verified_at' => now()->toISOString(),
                    'verification_valid_until' => now()->addMinutes(10)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to verify TAC: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify TAC code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if phone number is verified for a specific purpose.
     */
    public static function isPhoneVerified($phoneNumber, $purpose)
    {
        $verificationKey = "verified_{$purpose}_{$phoneNumber}";
        return Cache::has($verificationKey);
    }

    /**
     * Send SMS via service provider (placeholder method).
     */
    private function sendSms($phoneNumber, $message)
    {
        // TODO: Integrate with your SMS service provider
        // Examples:
        // - Twilio
        // - Nexmo/Vonage
        // - AWS SNS
        // - Local Malaysian SMS providers
        
        // For now, just log the message
        Log::info("SMS would be sent to {$phoneNumber}: {$message}");
        
        // Example Twilio integration:
        /*
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $twilio->messages->create(
            $phoneNumber,
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message
            ]
        );
        */
    }

    /**
     * Resend TAC code (rate limiting).
     */
    public function resendTac(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
            'purpose' => 'required|in:phone_verification,phone_change,password_reset,login',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->phone_number;
        $purpose = $request->purpose;
        
        // Check rate limiting (max 3 attempts per phone number per hour)
        $rateLimitKey = "rate_limit_{$purpose}_{$phoneNumber}";
        $attempts = Cache::get($rateLimitKey, 0);
        
        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Too many TAC requests. Please try again later.',
                'data' => [
                    'retry_after' => Cache::get("rate_limit_expiry_{$purpose}_{$phoneNumber}"),
                ]
            ], 429);
        }
        
        // Increment attempts
        Cache::put($rateLimitKey, $attempts + 1, now()->addHour());
        Cache::put("rate_limit_expiry_{$purpose}_{$phoneNumber}", now()->addHour()->toISOString(), now()->addHour());
        
        // Send new TAC
        return $this->sendTac($request);
    }
}
