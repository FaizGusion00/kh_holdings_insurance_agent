<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AgentWalletController extends Controller
{
    protected $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    /**
     * Get agent's wallet information
     */
    public function getWallet(Request $request)
    {
        try {
            $agentId = auth()->id();
            $wallet = $this->walletService->getOrCreateWallet($agentId);
            
            // Get pending commissions
            $pendingCommissions = $this->walletService->getPendingCommissions($agentId);
            
            // Get recent transactions
            $recentTransactions = $this->walletService->getWalletTransactions($agentId, 10);
            
            // Get withdrawal requests
            $withdrawalRequests = WithdrawalRequest::where('agent_id', $agentId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'amount' => $request->amount,
                        'status' => $request->status,
                        'created_at' => $request->created_at->toISOString(),
                        'processed_at' => $request->processed_at ? $request->processed_at->toISOString() : null,
                        'admin_notes' => $request->admin_notes,
                        'proof_url' => $request->proof_url,
                    ];
                });
            
            // Calculate total earned
            $totalEarned = WalletTransaction::where('user_id', $agentId)
                ->where('type', 'credit')
                ->sum('amount');

            $data = [
                'balance' => $wallet->balance,
                'pending_commissions' => $pendingCommissions,
                'total_earned' => $totalEarned,
                'recent_transactions' => $recentTransactions,
                'withdrawal_requests' => $withdrawalRequests,
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load wallet data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $agentId = auth()->id();
            $wallet = $this->walletService->getOrCreateWallet($agentId);

            if ($request->amount > $wallet->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance for withdrawal'
                ], 400);
            }

            // Create withdrawal request
            $withdrawalRequest = WithdrawalRequest::create([
                'agent_id' => $agentId,
                'amount' => $request->amount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'data' => $withdrawalRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal history
     */
    public function getWithdrawalHistory(Request $request)
    {
        try {
            $agentId = auth()->id();
            $perPage = $request->get('per_page', 15);
            
            $withdrawals = WithdrawalRequest::where('agent_id', $agentId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $withdrawals->items(),
                'current_page' => $withdrawals->currentPage(),
                'total' => $withdrawals->total(),
                'per_page' => $withdrawals->perPage(),
                'last_page' => $withdrawals->lastPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load withdrawal history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function getWalletTransactions(Request $request)
    {
        try {
            $agentId = auth()->id();
            $perPage = $request->get('per_page', 15);
            
            $transactions = WalletTransaction::whereHas('wallet', function($query) use ($agentId) {
                    $query->where('user_id', $agentId);
                })
                ->with(['commission.product', 'admin'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load wallet transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
