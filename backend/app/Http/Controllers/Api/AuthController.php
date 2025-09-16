<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Authentication Controller for API
 * 
 * Handles user registration, login, logout, and profile management
 */
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword']]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'nric' => 'required|string|max:20|unique:users',
            'race' => 'required|in:Malay,Chinese,Indian,Other',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:Male,Female',
            'occupation' => 'required|string|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relationship' => 'required|string|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'password' => 'required|string|min:8|confirmed',
            'referrer_code' => 'nullable|string|exists:users,agent_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'nric' => $request->nric,
            'race' => $request->race,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'occupation' => $request->occupation,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'emergency_contact_relationship' => $request->emergency_contact_relationship,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'password' => Hash::make($request->password),
            'referrer_code' => $request->referrer_code,
            'customer_type' => 'client',
            'status' => 'pending_verification',
            'registration_date' => now(),
        ]);

        // Create JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token'
            ], 500);
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]
        ]);
    }

    /**
     * Get the authenticated User
     */
    public function me()
    {
        $user = Auth::user();
        $user->load(['memberPolicies.insurancePlan', 'walletTransactions' => function($query) {
            $query->latest()->limit(5);
        }]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'active_policies_count' => $user->getActivePoliciesCount(),
                'downline_count' => $user->getDownlineCount(),
            ]
        ]);
    }

    /**
     * Log the user out (Invalidate the token)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to logout, please try again'
            ], 500);
        }
    }

    /**
     * Refresh a token
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'status' => 'success',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token cannot be refreshed'
            ], 401);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|string|max:20',
            'occupation' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'bank_name' => 'sometimes|string|max:255',
            'bank_account_number' => 'sometimes|string|max:50',
            'bank_account_owner' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'name', 'email', 'phone_number', 'occupation', 'address', 'city', 'state', 
            'postal_code', 'bank_name', 'bank_account_number', 'bank_account_owner'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => ['user' => $user]
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Forgot password (placeholder)
     */
    public function forgotPassword(Request $request)
    {
        // TODO: Implement forgot password functionality
        return response()->json([
            'status' => 'info',
            'message' => 'Forgot password functionality will be implemented soon'
        ]);
    }

    /**
     * Reset password (placeholder)
     */
    public function resetPassword(Request $request)
    {
        // TODO: Implement reset password functionality
        return response()->json([
            'status' => 'info',
            'message' => 'Reset password functionality will be implemented soon'
        ]);
    }

    /**
     * Verify email (placeholder)
     */
    public function verifyEmail(Request $request)
    {
        // TODO: Implement email verification
        return response()->json([
            'status' => 'info',
            'message' => 'Email verification will be implemented soon'
        ]);
    }

    /**
     * Resend verification email (placeholder)
     */
    public function resendVerification(Request $request)
    {
        // TODO: Implement resend verification
        return response()->json([
            'status' => 'info',
            'message' => 'Resend verification will be implemented soon'
        ]);
    }
}