<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of agent wallets.
     */
    public function index(Request $request)
    {
        $query = AgentWallet::with('agent');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('agent', function ($agentQuery) use ($search) {
                $agentQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('agent_code', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by balance range
        if ($request->filled('min_balance')) {
            $query->where('balance', '>=', $request->min_balance);
        }

        if ($request->filled('max_balance')) {
            $query->where('balance', '<=', $request->max_balance);
        }

        $wallets = $query->orderBy('balance', 'desc')->paginate(15);

        // Summary statistics
        $summary = [
            'total_wallets' => AgentWallet::count(),
            'total_balance' => AgentWallet::sum('balance'),
            'total_earned' => AgentWallet::sum('total_earned'),
            'total_withdrawn' => AgentWallet::sum('total_withdrawn'),
            'pending_commission' => AgentWallet::sum('pending_commission'),
            'active_wallets' => AgentWallet::where('status', 'active')->count(),
            'suspended_wallets' => AgentWallet::where('status', 'suspended')->count(),
            'frozen_wallets' => AgentWallet::where('status', 'frozen')->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'summary'));
    }

    /**
     * Display the specified wallet.
     */
    public function show(AgentWallet $wallet)
    {
        $wallet->load('agent');
        $wallet->updatePendingCommission();

        $transactions = $wallet->transactions()
            ->with('commission.product', 'admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = $this->walletService->getWalletSummary($wallet->user_id);

        return view('admin.wallets.show', compact('wallet', 'transactions', 'summary'));
    }

    /**
     * Show the form for adjusting wallet balance.
     */
    public function edit(AgentWallet $wallet)
    {
        $wallet->load('agent');
        return view('admin.wallets.edit', compact('wallet'));
    }

    /**
     * Update wallet balance.
     */
    public function update(Request $request, AgentWallet $wallet)
    {
        $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $amount = $request->amount;
            $description = $request->description;
            $adminNotes = $request->admin_notes;

            switch ($request->adjustment_type) {
                case 'add':
                    $this->walletService->adjustWalletBalance(
                        $wallet->user_id,
                        $amount,
                        "Admin adjustment: {$description}",
                        auth()->id(),
                        $adminNotes
                    );
                    break;

                case 'subtract':
                    $this->walletService->adjustWalletBalance(
                        $wallet->user_id,
                        -$amount,
                        "Admin adjustment: {$description}",
                        auth()->id(),
                        $adminNotes
                    );
                    break;

                case 'set':
                    $currentBalance = $wallet->balance;
                    $difference = $amount - $currentBalance;
                    $this->walletService->adjustWalletBalance(
                        $wallet->user_id,
                        $difference,
                        "Admin adjustment: Set balance to RM " . number_format($amount, 2) . " - {$description}",
                        auth()->id(),
                        $adminNotes
                    );
                    break;
            }

            return redirect()->route('admin.wallets.show', $wallet)
                ->with('success', 'Wallet balance updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating wallet: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update wallet status.
     */
    public function updateStatus(Request $request, AgentWallet $wallet)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,frozen',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->walletService->updateWalletStatus(
                $wallet->user_id,
                $request->status,
                auth()->id(),
                $request->reason
            );

            return redirect()->route('admin.wallets.show', $wallet)
                ->with('success', 'Wallet status updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating wallet status: ' . $e->getMessage());
        }
    }

    /**
     * Process commission payments.
     */
    public function processCommissions(Request $request)
    {
        $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commissions,id',
        ]);

        try {
            $result = $this->walletService->processBulkCommissionPayments(
                $request->commission_ids,
                auth()->id()
            );

            $successCount = count($result['successful']);
            $errorCount = count($result['errors']);

            $message = "Successfully processed {$successCount} commission payments.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} payments failed.";
            }

            return redirect()->route('admin.commissions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error processing commissions: ' . $e->getMessage());
        }
    }

    /**
     * Sync all pending commissions.
     */
    public function syncPendingCommissions(Request $request)
    {
        try {
            $userId = $request->user_id;
            $processed = $this->walletService->syncPendingCommissions($userId);

            $message = "Successfully synced {$processed} pending commissions to wallets.";
            if ($userId) {
                return redirect()->route('admin.wallets.show', $userId)
                    ->with('success', $message);
            } else {
                return redirect()->route('admin.wallets.index')
                    ->with('success', $message);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error syncing commissions: ' . $e->getMessage());
        }
    }

    /**
     * Get wallet transactions for AJAX.
     */
    public function transactions(Request $request, AgentWallet $wallet)
    {
        $transactions = $wallet->transactions()
            ->with('commission.product', 'admin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Export wallet data.
     */
    public function export(Request $request)
    {
        // Implementation for exporting wallet data
        // This would typically generate a CSV or Excel file
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
