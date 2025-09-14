<?php

namespace App\Services;

use App\Models\AgentWallet;
use App\Models\WalletTransaction;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Create or get agent wallet.
     */
    public function getOrCreateWallet($userId)
    {
        $wallet = AgentWallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            $wallet = AgentWallet::create([
                'user_id' => $userId,
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'pending_commission' => 0,
                'status' => 'active',
                'last_updated_at' => now(),
            ]);
        }
        
        return $wallet;
    }

    /**
     * Get pending commissions for an agent.
     */
    public function getPendingCommissions($userId)
    {
        return Commission::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('commission_amount');
    }

    /**
     * Get wallet transactions for an agent.
     */
    public function getWalletTransactions($userId, $limit = 10)
    {
        return WalletTransaction::where('user_id', $userId)
            ->with(['commission.product', 'admin'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->toISOString(),
                    'commission_id' => $transaction->commission_id,
                ];
            });
    }

    /**
     * Process commission payment to wallet.
     */
    public function processCommissionPayment($commissionId, $adminId = null)
    {
        $commission = Commission::with('agent')->findOrFail($commissionId);
        
        if ($commission->status === 'paid') {
            throw new \Exception('Commission already paid');
        }

        return DB::transaction(function () use ($commission, $adminId) {
            // Get or create wallet
            $wallet = $this->getOrCreateWallet($commission->user_id);
            
            // Add funds to wallet
            $description = "Commission payment";
            if ($commission->product) {
                $description .= " for {$commission->product->name}";
            }
            if ($commission->policy) {
                $description .= " - Policy #{$commission->policy->policy_number}";
            }
            $description .= " - Tier {$commission->tier_level}";
            
            $transaction = $wallet->addFunds(
                $commission->commission_amount,
                $description,
                $commission->id,
                $adminId
            );

            // Update pending commission
            $wallet->updatePendingCommission();

            Log::info("Commission payment processed", [
                'commission_id' => $commission->id,
                'agent_id' => $commission->user_id,
                'amount' => $commission->commission_amount,
                'wallet_balance' => $wallet->fresh()->balance,
            ]);

            return $transaction;
        });
    }

    /**
     * Process bulk commission payments.
     */
    public function processBulkCommissionPayments($commissionIds, $adminId = null)
    {
        $results = [];
        $errors = [];

        foreach ($commissionIds as $commissionId) {
            try {
                $transaction = $this->processCommissionPayment($commissionId, $adminId);
                $results[] = $transaction;
            } catch (\Exception $e) {
                $errors[] = [
                    'commission_id' => $commissionId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'successful' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * Adjust wallet balance (admin only).
     */
    public function adjustWalletBalance($userId, $amount, $description, $adminId, $adminNotes = null)
    {
        $wallet = $this->getOrCreateWallet($userId);
        
        return $wallet->adjustBalance($amount, $description, $adminId, $adminNotes);
    }

    /**
     * Process withdrawal request.
     */
    public function processWithdrawal($userId, $amount, $description, $adminId = null)
    {
        $wallet = $this->getOrCreateWallet($userId);
        
        if ($wallet->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        if ($wallet->status !== 'active') {
            throw new \Exception('Wallet is not active');
        }

        return $wallet->deductFunds($amount, $description, $adminId);
    }

    /**
     * Update wallet status.
     */
    public function updateWalletStatus($userId, $status, $adminId, $reason = null)
    {
        $wallet = $this->getOrCreateWallet($userId);
        
        $oldStatus = $wallet->status;
        $wallet->update(['status' => $status]);

        // Log status change
        WalletTransaction::create([
            'user_id' => $userId,
            'type' => 'adjustment',
            'amount' => 0,
            'balance_before' => $wallet->balance,
            'balance_after' => $wallet->balance,
            'description' => "Wallet status changed from {$oldStatus} to {$status}" . ($reason ? " - {$reason}" : ''),
            'status' => 'completed',
            'admin_id' => $adminId,
            'admin_notes' => $reason,
            'processed_at' => now(),
        ]);

        return $wallet;
    }

    /**
     * Get wallet summary for agent.
     */
    public function getWalletSummary($userId)
    {
        $wallet = $this->getOrCreateWallet($userId);
        $wallet->updatePendingCommission();

        return [
            'wallet' => $wallet->getSummary(),
            'recent_transactions' => $wallet->recentTransactions(10)->get(),
            'monthly_earnings' => $this->getMonthlyEarnings($userId),
            'commission_stats' => $this->getCommissionStats($userId),
        ];
    }

    /**
     * Get monthly earnings for agent.
     */
    public function getMonthlyEarnings($userId, $months = 12)
    {
        $earnings = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            
            $amount = WalletTransaction::where('user_id', $userId)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            
            $earnings[] = [
                'month' => $month,
                'amount' => $amount,
                'formatted_month' => $date->format('M Y'),
            ];
        }
        
        return $earnings;
    }

    /**
     * Get commission statistics for agent.
     */
    public function getCommissionStats($userId)
    {
        $stats = Commission::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_commissions,
                SUM(CASE WHEN status = "pending" THEN commission_amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = "paid" THEN commission_amount ELSE 0 END) as paid_amount,
                AVG(commission_amount) as average_commission
            ')
            ->first();

        return [
            'total_commissions' => $stats->total_commissions ?? 0,
            'pending_amount' => $stats->pending_amount ?? 0,
            'paid_amount' => $stats->paid_amount ?? 0,
            'average_commission' => $stats->average_commission ?? 0,
        ];
    }

    /**
     * Sync all pending commissions to wallet.
     */
    public function syncPendingCommissions($userId = null)
    {
        $query = Commission::where('status', 'pending');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $commissions = $query->get();
        $processed = 0;
        
        foreach ($commissions as $commission) {
            try {
                $this->processCommissionPayment($commission->id);
                $processed++;
            } catch (\Exception $e) {
                Log::error("Failed to sync commission to wallet", [
                    'commission_id' => $commission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $processed;
    }
}
