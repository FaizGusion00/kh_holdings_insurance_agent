<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Get user profile information.
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['downlines', 'referral']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile_complete' => $this->calculateProfileCompleteness($user)
            ]
        ]);
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $request->user()->id,
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:100',
            'state' => 'sometimes|nullable|string|max:100',
            'postal_code' => 'sometimes|nullable|string|max:20',
            'bank_name' => 'sometimes|nullable|string|max:100',
            'bank_account_number' => 'sometimes|nullable|string|max:50',
            'bank_account_owner' => 'sometimes|nullable|string|max:255',
            'current_password' => 'required_with:name,email|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $updateData = $request->only([
                'name', 'email', 'address', 'city', 'state', 'postal_code',
                'bank_name', 'bank_account_number', 'bank_account_owner'
            ]);

            $user->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Check if new password is same as current
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'New password must be different from current password'
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user phone number.
     */
    public function changePhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_phone' => 'required|string',
            'new_phone' => 'required|string|unique:users,phone_number',
            'tac_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current phone number
        if ($user->phone_number !== $request->current_phone) {
            return response()->json([
                'success' => false,
                'message' => 'Current phone number is incorrect'
            ], 422);
        }

        // Verify TAC code (this would integrate with your TAC service)
        if (!$this->verifyTacCode($request->new_phone, $request->tac_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid TAC code'
            ], 422);
        }

        try {
            $user->update([
                'phone_number' => $request->new_phone,
                'phone_verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change phone number',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update bank information.
     */
    public function updateBankInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_owner' => 'required|string|max:255',
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $user->update([
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_owner' => $request->bank_account_owner,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bank information updated successfully',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate profile completeness percentage.
     */
    private function calculateProfileCompleteness($user)
    {
        $fields = [
            'name', 'email', 'phone_number', 'address', 'city', 'state', 'postal_code',
            'bank_name', 'bank_account_number', 'bank_account_owner'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100, 2);
    }

    /**
     * Verify TAC code (placeholder - integrate with your TAC service).
     */
    private function verifyTacCode($phone, $tac)
    {
        // TODO: Integrate with your TAC verification service
        // For now, we'll use a simple validation
        return strlen($tac) === 6 && is_numeric($tac);
    }
}
