<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Member;
use App\Models\MemberPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payment transactions.
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['member', 'policy.product'])
            ->withSum('policy', 'total_paid');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($memberQuery) use ($search) {
                      $memberQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('nric', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        
        // Filter by amount range
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        $payments = $query->orderBy('transaction_date', 'desc')->paginate(15);
        
        // Get agents for filtering
        $agents = \App\Models\User::where('status', 'active')->get();
        
        // Summary statistics
        $summary = [
            'total_amount' => $payments->sum('amount'),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
            'completed_amount' => $payments->where('status', 'completed')->sum('amount'),
            'failed_amount' => $payments->where('status', 'failed')->sum('amount'),
            'total_transactions' => $payments->count(),
        ];
        
        return view('admin.payments.index', compact('payments', 'summary', 'agents'));
    }

    /**
     * Show the form for creating a new payment transaction.
     */
    public function create()
    {
        $members = Member::where('status', 'active')->get();
        $policies = MemberPolicy::where('status', 'active')->get();
        
        return view('admin.payments.create', compact('members', 'policies'));
    }

    /**
     * Store a newly created payment transaction in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'policy_id' => 'required|exists:member_policies,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:membership_fee,sharing_account,policy_premium',
            'payment_method' => 'required|in:mandate,manual,card',
            'status' => 'required|in:pending,processing,completed,failed,cancelled',
            'transaction_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100|unique:payment_transactions,reference_number',
            'gateway_reference' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Generate reference number if not provided
        $referenceNumber = $request->reference_number ?: 'REF' . date('Ymd') . str_pad(PaymentTransaction::count() + 1, 4, '0', STR_PAD_LEFT);

        $payment = PaymentTransaction::create([
            'member_id' => $request->member_id,
            'policy_id' => $request->policy_id,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
            'reference_number' => $referenceNumber,
            'gateway_reference' => $request->gateway_reference,
        ]);

        // Update member balance if payment is completed
        if ($request->status === 'completed') {
            $member = Member::find($request->member_id);
            $member->increment('balance', $request->amount);
            $payment->update(['processed_at' => now()]);
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment transaction created successfully!');
    }

    /**
     * Display the specified payment transaction.
     */
    public function show(PaymentTransaction $payment)
    {
        $payment->load(['member', 'policy.product']);
        
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment transaction.
     */
    public function edit(PaymentTransaction $payment)
    {
        $members = Member::where('status', 'active')->get();
        $policies = MemberPolicy::where('status', 'active')->get();
        
        return view('admin.payments.edit', compact('payment', 'members', 'policies'));
    }

    /**
     * Update the specified payment transaction in storage.
     */
    public function update(Request $request, PaymentTransaction $payment)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'policy_id' => 'required|exists:member_policies,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:membership_fee,sharing_account,policy_premium',
            'payment_method' => 'required|in:mandate,manual,card',
            'status' => 'required|in:pending,processing,completed,failed,cancelled',
            'transaction_date' => 'required|date|before_or_equal:today',
            'description' => 'nullable|string|max:500',
            'reference_number' => 'nullable|string|max:100|unique:payment_transactions,reference_number,' . $payment->id,
            'gateway_reference' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldStatus = $payment->status;
        $oldAmount = $payment->amount;
        $newAmount = $request->amount;

        DB::beginTransaction();
        try {
            // Update payment transaction
            $payment->update([
                'member_id' => $request->member_id,
                'policy_id' => $request->policy_id,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'status' => $request->status,
                'transaction_date' => $request->transaction_date,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'gateway_reference' => $request->gateway_reference,
            ]);

            // Handle member balance changes
            $member = Member::find($request->member_id);
            
            if ($oldStatus === 'completed' && $request->status !== 'completed') {
                // Payment was completed but now is not, reduce balance
                $member->decrement('balance', $oldAmount);
                $payment->update(['processed_at' => null]);
            } elseif ($oldStatus !== 'completed' && $request->status === 'completed') {
                // Payment was not completed but now is, increase balance
                $member->increment('balance', $newAmount);
                $payment->update(['processed_at' => now()]);
            } elseif ($oldStatus === 'completed' && $request->status === 'completed' && $oldAmount !== $newAmount) {
                // Payment amount changed, adjust balance
                $difference = $newAmount - $oldAmount;
                if ($difference > 0) {
                    $member->increment('balance', $difference);
                } else {
                    $member->decrement('balance', abs($difference));
                }
            }

            // Update failed_at if status is failed
            if ($request->status === 'failed') {
                $payment->update(['failed_at' => now()]);
            }

            DB::commit();

            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment transaction updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating payment transaction: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment transaction from storage.
     */
    public function destroy(PaymentTransaction $payment)
    {
        if ($payment->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot delete completed payment transaction.');
        }

        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment transaction deleted successfully!');
    }

    /**
     * Show pending payments.
     */
    public function pending()
    {
        $pendingPayments = PaymentTransaction::with(['member', 'policy.product'])
            ->where('status', 'pending')
            ->orderBy('transaction_date', 'asc')
            ->paginate(15);
        
        return view('admin.payments.pending', compact('pendingPayments'));
    }

    /**
     * Approve a payment transaction.
     */
    public function approve(Request $request, PaymentTransaction $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending payments can be approved.');
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // Update member balance
            $member = $payment->member;
            $member->increment('balance', $payment->amount);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Payment approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error approving payment: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve payments.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payment_transactions,id',
        ]);

        $payments = PaymentTransaction::whereIn('id', $request->payment_ids)
            ->where('status', 'pending')
            ->get();

        $approvedCount = 0;
        $totalAmount = 0;

        DB::beginTransaction();
        try {
            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                // Update member balance
                $member = $payment->member;
                $member->increment('balance', $payment->amount);

                $approvedCount++;
                $totalAmount += $payment->amount;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "Successfully approved {$approvedCount} payments totaling RM " . number_format($totalAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error bulk approving payments: ' . $e->getMessage());
        }
    }
}
