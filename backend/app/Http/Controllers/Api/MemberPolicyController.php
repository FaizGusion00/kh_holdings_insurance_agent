<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberPolicy;
use App\Models\InsurancePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberPolicyController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);

            $policies = MemberPolicy::where('user_id', $user->id)
                ->with('insurancePlan')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $policies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch policies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $policy = MemberPolicy::where('user_id', $user->id)
                ->with('insurancePlan')
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => ['policy' => $policy]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Policy not found'
            ], 404);
        }
    }

    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'insurance_plan_id' => 'required|exists:insurance_plans,id',
            'payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually'
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
            $plan = InsurancePlan::findOrFail($request->insurance_plan_id);
            
            // Check age eligibility
            $age = now()->diffInYears($user->date_of_birth);
            if (!$plan->isEligibleByAge($age)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not eligible for this plan based on age requirements'
                ], 400);
            }

            $premiumAmount = $plan->getPriceByMode($request->payment_mode);
            if (!$premiumAmount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid payment mode for this plan'
                ], 400);
            }

            // Create policy
            $policy = MemberPolicy::create([
                'user_id' => $user->id,
                'insurance_plan_id' => $plan->id,
                'policy_number' => MemberPolicy::generatePolicyNumber(),
                'payment_mode' => $request->payment_mode,
                'premium_amount' => $premiumAmount,
                'policy_start_date' => now(),
                'policy_end_date' => $this->calculateEndDate($request->payment_mode),
                'next_payment_due' => $this->calculateNextPayment($request->payment_mode),
                'status' => 'pending_payment'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Policy created successfully. Please proceed with payment.',
                'data' => ['policy' => $policy]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function renew($id)
    {
        try {
            $user = Auth::user();
            
            $policy = MemberPolicy::where('user_id', $user->id)->findOrFail($id);
            
            if ($policy->status !== 'expired') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only expired policies can be renewed'
                ], 400);
            }

            // Create renewal logic here
            return response()->json([
                'status' => 'success',
                'message' => 'Policy renewal initiated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to renew policy'
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $user = Auth::user();
            
            $policy = MemberPolicy::where('user_id', $user->id)->findOrFail($id);
            
            $policy->update(['status' => 'cancelled']);

            return response()->json([
                'status' => 'success',
                'message' => 'Policy cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel policy'
            ], 500);
        }
    }

    private function calculateEndDate($paymentMode)
    {
        return match($paymentMode) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi_annually' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addMonth()
        };
    }

    private function calculateNextPayment($paymentMode)
    {
        return match($paymentMode) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi_annually' => now()->addMonths(6),
            'annually' => now()->addYear(),
            default => now()->addMonth()
        };
    }
}
