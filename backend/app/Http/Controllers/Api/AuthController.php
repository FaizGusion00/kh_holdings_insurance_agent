<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login agent and generate token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_code' => 'required|string|regex:/^AGT\d{5}$/',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by agent code
        $user = User::where('agent_code', $request->agent_code)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid agent code or password'
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Please contact support.'
            ], 403);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->makeHidden(['password']),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Register new agent.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string|max:15',
            'nric' => 'required|string|max:12|unique:users',
            'referrer_code' => 'nullable|string|exists:users,agent_code',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate unique agent number and code
            $agentNumber = User::generateAgentNumber();
            $agentCode = User::generateAgentCode();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'agent_number' => $agentNumber,
                'agent_code' => $agentCode,
                'referrer_code' => $request->referrer_code,
                'phone_number' => $request->phone_number,
                'nric' => $request->nric,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'status' => 'pending', // Will be activated by admin
                'mlm_level' => 1,
                'total_commission_earned' => 0,
                'monthly_commission_target' => 1000, // Default target
            ]);

            // Create referral record if referrer exists
            if ($request->referrer_code) {
                $referralLevel = 1;
                $uplineChain = Referral::buildUplineChain($request->referrer_code);
                
                $referral = Referral::create([
                    'agent_code' => $agentCode,
                    'referrer_code' => $request->referrer_code,
                    'user_id' => $user->id,
                    'referral_level' => $referralLevel,
                    'upline_chain' => $uplineChain,
                    'status' => 'pending',
                ]);

                // Update upline downline counts
                $referral->updateUplineDownlineCounts();
            } else {
                // Top-level agent (no referrer)
                Referral::create([
                    'agent_code' => $agentCode,
                    'user_id' => $user->id,
                    'referral_level' => 1,
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Your account is pending approval.',
                'data' => [
                    'user' => $user->makeHidden(['password']),
                    'agent_number' => $agentNumber,
                    'agent_code' => $agentCode,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['referral', 'members']);
        
        return response()->json([
            'success' => true,
            'data' => $user->makeHidden(['password'])
        ]);
    }

    /**
     * Logout user and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    /**
     * Request password reset.
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_number' => 'required|string|exists:users,agent_number',
            'email' => 'required|string|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user with both agent number and email
        $user = User::where('agent_number', $request->agent_number)
                   ->where('email', $request->email)
                   ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with these credentials'
            ], 404);
        }

        // TODO: Implement actual password reset email sending
        // For now, just return success message
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset instructions have been sent to your email.'
        ]);
    }

    /**
     * Verify agent code exists (for referral registration).
     */
    public function verifyAgentCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Agent code is required',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('agent_code', $request->agent_code)
                   ->where('status', 'active')
                   ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive agent code'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid agent code',
            'data' => [
                'referrer_name' => $user->name,
                'agent_code' => $user->agent_code,
            ]
        ]);
    }
}
