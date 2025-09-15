<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    /**
     * Get all members (users) for the authenticated agent.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,pending',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = User::where('referrer_id', $request->user()->id)
            ->with(['medicalInsurancePolicies', 'medicalInsurancePolicies.product']);

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nric', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $members = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Store a new member (user).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nric' => 'required|string|max:20|unique:users,nric',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'occupation' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            
            $member = User::create([
                'name' => $request->name,
                'nric' => $request->nric,
                'phone_number' => $request->phone_number,
                'email' => $request->email ?? (strtolower(str_replace(' ', '', $request->name)) . '@wekongsi.local'),
                'password' => bcrypt('Temp1234!'),
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'occupation' => $request->occupation,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'status' => 'active',
                'registration_date' => now(),
                'referrer_code' => $user->agent_code,
                'referrer_id' => $user->id,
                'agent_code' => User::generateAgentCode(),
                'agent_number' => User::generateAgentNumber(),
                'mlm_level' => $user->mlm_level + 1,
                'mlm_activation_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Member created successfully',
                'data' => $member->load(['medicalInsurancePolicies', 'medicalInsurancePolicies.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific member (user).
     */
    public function show(Request $request, User $member)
    {
        // Ensure the member belongs to the authenticated agent
        if ($member->referrer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $member->load(['medicalInsurancePolicies', 'medicalInsurancePolicies.product', 'paymentTransactions'])
        ]);
    }

    /**
     * Update a member (user).
     */
    public function update(Request $request, User $member)
    {
        // Ensure the member belongs to the authenticated agent
        if ($member->referrer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'nric' => 'sometimes|required|string|max:20|unique:users,nric,' . $member->id,
            'phone_number' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'occupation' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'status' => 'sometimes|required|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $member->update($request->only([
                'name', 'nric', 'phone_number', 'email', 'address',
                'date_of_birth', 'gender', 'occupation',
                'emergency_contact_name', 'emergency_contact_phone', 'status'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully',
                'data' => $member->load(['medicalInsurancePolicies', 'medicalInsurancePolicies.product'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a member (user).
     */
    public function destroy(Request $request, User $member)
    {
        // Ensure the member belongs to the authenticated agent
        if ($member->referrer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        // Check if member has active policies
        if ($member->medicalInsurancePolicies()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete member with active policies'
            ], 422);
        }

        try {
            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get policies for a specific member (user).
     */
    public function getPolicies(Request $request, User $member)
    {
        // Ensure the member belongs to the authenticated agent
        if ($member->referrer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $policies = $member->medicalInsurancePolicies()
            ->with(['product', 'paymentTransactions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $policies
        ]);
    }

    /**
     * Create a new policy for a member (user).
     */
    public function createPolicy(Request $request, User $member)
    {
        // Ensure the member belongs to the authenticated agent
        if ($member->referrer_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:insurance_products,id',
            'policy_number' => 'required|string|max:100|unique:medical_insurance_policies,policy_number',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'premium_amount' => 'required|numeric|min:0',
            'coverage_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,pending,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $policy = \App\Models\MedicalInsurancePolicy::create([
                'user_id' => $member->id,
                'product_id' => $request->product_id,
                'policy_number' => $request->policy_number,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'premium_amount' => $request->premium_amount,
                'coverage_amount' => $request->coverage_amount,
                'status' => $request->input('status', 'pending'),
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Policy created successfully',
                'data' => $policy->load(['product', 'user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
