<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawal requests.
     */
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['agent', 'processedByAdmin']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by agent name or agent code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('agent', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('agent_code', 'like', "%{$search}%");
            });
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Show the form for reviewing a withdrawal request.
     */
    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load(['agent', 'processedByAdmin']);
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approve a withdrawal request.
     */
    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($withdrawal->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This withdrawal request has already been processed.');
        }

        try {
            DB::beginTransaction();

            // Update withdrawal request
            $withdrawal->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'processed_by_admin_id' => auth()->id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Withdrawal request approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve withdrawal request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdrawal request.
     */
    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($withdrawal->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This withdrawal request has already been processed.');
        }

        try {
            DB::beginTransaction();

            // Update withdrawal request
            $withdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'processed_by_admin_id' => auth()->id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Withdrawal request rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reject withdrawal request: ' . $e->getMessage());
        }
    }

    /**
     * Mark withdrawal as completed and upload proof.
     */
    public function complete(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($withdrawal->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved withdrawal requests can be marked as completed.');
        }

        try {
            DB::beginTransaction();

            // Upload proof file
            $proofPath = $request->file('proof_file')->store('withdrawal-proofs', 'public');

            // Update withdrawal request
            $withdrawal->update([
                'status' => 'completed',
                'proof_url' => $proofPath,
                'admin_notes' => $request->admin_notes ?: $withdrawal->admin_notes,
                'processed_by_admin_id' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Deduct amount from agent's wallet
            $wallet = AgentWallet::where('user_id', $withdrawal->agent_id)->first();
            if ($wallet) {
                $wallet->decrement('balance', $withdrawal->amount);

                // Create wallet transaction record
                WalletTransaction::create([
                    'agent_wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $withdrawal->amount,
                    'description' => 'Withdrawal - ' . $withdrawal->amount,
                    'status' => 'completed',
                    'admin_id' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.withdrawals.index')
                ->with('success', 'Withdrawal completed successfully and proof uploaded!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to complete withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Get withdrawal statistics for dashboard.
     */
    public function getStats()
    {
        $stats = [
            'pending_count' => WithdrawalRequest::where('status', 'pending')->count(),
            'approved_count' => WithdrawalRequest::where('status', 'approved')->count(),
            'completed_count' => WithdrawalRequest::where('status', 'completed')->count(),
            'rejected_count' => WithdrawalRequest::where('status', 'rejected')->count(),
            'total_pending_amount' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'total_approved_amount' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
        ];

        return response()->json($stats);
    }
}
