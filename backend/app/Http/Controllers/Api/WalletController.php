<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Wallet Controller for API
 * 
 * Handles agent wallet management, transactions, and withdrawal requests
 */
class WalletController extends Controller
{
    /**
     * Get wallet overview data
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get wallet data
            $walletData = $this->getWalletData($user);

            return response()->json([
                'status' => 'success',
                'data' => $walletData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch wallet data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet balance
     */
    public function getBalance()
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'balance' => floatval($user->wallet_balance ?? 0),
                    'total_earned' => floatval($user->total_commission_earned ?? 0)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet transactions with pagination
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 15);
            $type = $request->input('type');

            $query = WalletTransaction::where('user_id', $user->id)->with('relatedUser');

            if ($type) {
                $query->where('type', $type);
            }

            $transactions = $query->latest()->paginate($perPage);

            // Transform the data
            $transactions->getCollection()->transform(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'balance_before' => $transaction->balance_before,
                    'balance_after' => $transaction->balance_after,
                    'related_user' => $transaction->relatedUser ? [
                        'name' => $transaction->relatedUser->name,
                        'email' => $transaction->relatedUser->email
                    ] : null,
                    'created_at' => $transaction->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $transactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create withdrawal request
     */
    public function createWithdrawalRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:50|max:50000',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_owner' => 'required|string|max:255'
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
            $requestedAmount = $request->amount;

            // Check balance
            if ($user->wallet_balance < $requestedAmount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient wallet balance'
                ], 400);
            }

            // Check for pending requests
            $pendingRequest = WithdrawalRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($pendingRequest) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have a pending withdrawal request'
                ], 400);
            }

            DB::beginTransaction();

            $withdrawalRequest = WithdrawalRequest::create([
                'user_id' => $user->id,
                'request_id' => WithdrawalRequest::generateRequestId(),
                'amount' => $requestedAmount,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_owner' => $request->bank_account_owner,
                'status' => 'pending'
            ]);

            // Deduct from wallet
            $user->update(['wallet_balance' => $user->wallet_balance - $requestedAmount]);

            // Create transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'reference_id' => $withdrawalRequest->request_id,
                'type' => 'withdrawal',
                'amount' => -$requestedAmount,
                'balance_before' => $user->wallet_balance + $requestedAmount,
                'balance_after' => $user->wallet_balance,
                'description' => "Withdrawal request #{$withdrawalRequest->request_id}"
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Withdrawal request submitted successfully',
                'data' => [
                    'withdrawal_request' => $withdrawalRequest,
                    'new_balance' => floatval($user->wallet_balance)
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal requests
     */
    public function getWithdrawalRequests(Request $request)
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 10);

            $requests = WithdrawalRequest::where('user_id', $user->id)
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $requests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch withdrawal requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific withdrawal request
     */
    public function getWithdrawalRequest($id)
    {
        try {
            $user = Auth::user();
            
            $request = WithdrawalRequest::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$request) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Withdrawal request not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => ['withdrawal_request' => $request]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet data helper
     */
    private function getWalletData($user)
    {
        $walletBalance = $user->wallet_balance ?? 0;
        $totalEarned = $user->total_commission_earned ?? 0;
        
        // Recent transactions
        $recentTransactions = WalletTransaction::where('user_id', $user->id)
            ->with('relatedUser')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at
                ];
            });
        
        // Withdrawal requests
        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return [
            'balance' => floatval($walletBalance),
            'total_earned' => floatval($totalEarned),
            'recent_transactions' => $recentTransactions,
            'withdrawal_requests' => $withdrawalRequests
        ];
    }

    /**
     * Add commission to user's wallet (static method for use by PaymentController)
     */
    public static function addCommission($userId, $amount, $description, $sourceUserId = null)
    {
        try {
            DB::beginTransaction();

            // Get the user
            $user = User::findOrFail($userId);

            // Update wallet balance
            $user->increment('wallet_balance', $amount);
            $user->increment('total_commission_earned', $amount);

            // Create wallet transaction record
            WalletTransaction::create([
                'user_id' => $userId,
                'transaction_id' => WalletTransaction::generateTransactionId(),
                'type' => 'commission_earned',
                'amount' => $amount,
                'balance_after' => $user->fresh()->wallet_balance,
                'description' => $description,
                'reference_id' => $sourceUserId,
                'status' => 'completed'
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to add commission: ' . $e->getMessage());
            return false;
        }
    }
}