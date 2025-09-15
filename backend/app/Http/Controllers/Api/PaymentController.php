<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentTransaction;
use App\Models\PaymentMandate;
use App\Models\MemberPolicy;

class PaymentController extends Controller
{
    /**
     * Get payment overview for the authenticated agent.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get payment statistics
        $stats = $this->getPaymentStats($user);
        
        // Get recent payments
        $recentPayments = $this->getRecentPayments($user);
        
        // Get active mandates
        $activeMandates = $this->getActiveMandates($user);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_payments' => $recentPayments,
                'active_mandates' => $activeMandates
            ]
        ]);
    }

    /**
     * Get payment history for the authenticated agent.
     */
    public function getHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2030',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
            'type' => 'nullable|in:membership_fee,sharing_account,policy_premium',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = PaymentTransaction::whereHas('member', function($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with(['member', 'policy.member', 'policy.product']);

        // Apply filters
        if ($request->has('month')) {
            $query->whereMonth('transaction_date', $request->month);
        }

        if ($request->has('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('payment_type', $request->type);
        }

        $perPage = $request->input('per_page', 15);
        $payments = $query->orderBy('transaction_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get payment mandates for the authenticated agent.
     */
    public function getMandates(Request $request)
    {
        $user = $request->user();
        
        $mandates = PaymentMandate::where('user_id', $user->id)
            ->with(['member', 'policy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mandates
        ]);
    }

    /**
     * Process a payment.
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'policy_id' => 'nullable|exists:member_policies,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:membership_fee,sharing_account,policy_premium',
            'payment_method' => 'required|in:mandate,manual,card',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify the member belongs to the authenticated agent
        $member = User::where('id', $request->member_id)
            ->where('referrer_id', $request->user()->id)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Create payment transaction
            $payment = PaymentTransaction::create([
                'member_id' => $request->member_id,
                'policy_id' => $request->policy_id,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'transaction_date' => now(),
                'description' => $request->description,
                'reference_number' => 'PAY_' . time() . '_' . rand(1000, 9999),
            ]);

            // Update member balance if it's a sharing account payment
            if ($request->payment_type === 'sharing_account') {
                $member->increment('balance', $request->amount);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $payment->load(['policy.member', 'policy.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Setup a payment mandate.
     */
    public function setupMandate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'policy_id' => 'nullable|exists:member_policies,id',
            'mandate_type' => 'required|in:membership_fee,sharing_account,recurring',
            'frequency' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'bank_account' => 'required|string|max:50',
            'bank_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify the member belongs to the authenticated agent
        $member = User::where('id', $request->member_id)
            ->where('referrer_id', $request->user()->id)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Calculate next processing date based on frequency
            $nextProcessingDate = $this->calculateNextProcessingDate($request->start_date, $request->frequency);
            
            $mandate = PaymentMandate::create([
                'user_id' => $request->user()->id,
                'member_id' => $request->member_id,
                'policy_id' => $request->policy_id,
                'mandate_type' => $request->mandate_type,
                'frequency' => $request->frequency,
                'amount' => $request->amount,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'bank_account' => $request->bank_account,
                'bank_name' => $request->bank_name,
                'status' => 'active',
                'reference_number' => 'MANDATE_' . time() . '_' . rand(1000, 9999),
                'next_processing_date' => $nextProcessingDate,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment mandate setup successfully',
                'data' => $mandate->load(['member', 'policy'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to setup payment mandate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a payment mandate.
     */
    public function updateMandate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mandate_id' => 'required|exists:payment_mandates,id',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'frequency' => 'sometimes|required|in:monthly,quarterly,half_yearly,yearly',
            'status' => 'sometimes|required|in:active,inactive,cancelled',
            'end_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify the mandate belongs to the authenticated agent
        $mandate = PaymentMandate::where('id', $request->mandate_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$mandate) {
            return response()->json([
                'success' => false,
                'message' => 'Mandate not found'
            ], 404);
        }

        try {
            $updateData = $request->only(['amount', 'frequency', 'status', 'end_date']);
            $mandate->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Payment mandate updated successfully',
                'data' => $mandate->fresh()->load(['member', 'policy'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment mandate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics for the agent.
     */
    private function getPaymentStats($user)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Total payments this month
        $monthlyPayments = PaymentTransaction::whereHas('member', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereMonth('transaction_date', $currentMonth)
        ->whereYear('transaction_date', $currentYear)
        ->where('status', 'completed')
        ->sum('amount');

        // Total payments all time
        $totalPayments = PaymentTransaction::whereHas('member', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'completed')
        ->sum('amount');

        // Active mandates count
        $activeMandates = PaymentMandate::where('user_id', $user->id)
            ->active()
            ->count();

        // Pending payments
        $pendingPayments = PaymentTransaction::whereHas('member', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->sum('amount');

        return [
            'monthly_payments' => round($monthlyPayments, 2),
            'total_payments' => round($totalPayments, 2),
            'active_mandates' => $activeMandates,
            'pending_payments' => round($pendingPayments, 2),
            'current_month' => $currentMonth,
            'current_year' => $currentYear
        ];
    }

    /**
     * Get recent payments for the agent.
     */
    private function getRecentPayments($user)
    {
        return PaymentTransaction::whereHas('member', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['member', 'policy.member', 'policy.product'])
        ->orderBy('transaction_date', 'desc')
        ->limit(5)
        ->get();
    }

    /**
     * Get active mandates for the agent.
     */
    private function getActiveMandates($user)
    {
        return PaymentMandate::where('user_id', $user->id)
            ->active()
            ->with(['member', 'policy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Calculate next processing date based on frequency.
     */
    private function calculateNextProcessingDate($startDate, $frequency)
    {
        $date = \Carbon\Carbon::parse($startDate);
        
        return match($frequency) {
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            'half_yearly' => $date->addMonths(6),
            'yearly' => $date->addYear(),
            default => $date->addMonth(),
        };
    }
}
