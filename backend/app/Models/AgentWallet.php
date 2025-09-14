<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AgentWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_withdrawn',
        'pending_commission',
        'status',
        'last_updated_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'pending_commission' => 'decimal:2',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Get the agent who owns this wallet.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all wallet transactions.
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Get recent transactions.
     */
    public function recentTransactions($limit = 10)
    {
        return $this->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Add funds to wallet.
     */
    public function addFunds($amount, $description, $commissionId = null, $adminId = null)
    {
        return DB::transaction(function () use ($amount, $description, $commissionId, $adminId) {
            $balanceBefore = $this->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update wallet
            $this->update([
                'balance' => $balanceAfter,
                'total_earned' => $this->total_earned + $amount,
                'last_updated_at' => now(),
            ]);

            // Create transaction record
            $transaction = WalletTransaction::create([
                'user_id' => $this->user_id,
                'commission_id' => $commissionId,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'status' => 'completed',
                'admin_id' => $adminId,
                'processed_at' => now(),
            ]);

            // Update commission with wallet transaction ID
            if ($commissionId) {
                Commission::where('id', $commissionId)->update([
                    'wallet_transaction_id' => $transaction->id,
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Deduct funds from wallet.
     */
    public function deductFunds($amount, $description, $adminId = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        return DB::transaction(function () use ($amount, $description, $adminId) {
            $balanceBefore = $this->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update wallet
            $this->update([
                'balance' => $balanceAfter,
                'total_withdrawn' => $this->total_withdrawn + $amount,
                'last_updated_at' => now(),
            ]);

            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'status' => 'completed',
                'admin_id' => $adminId,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Adjust wallet balance (admin only).
     */
    public function adjustBalance($amount, $description, $adminId, $adminNotes = null)
    {
        return DB::transaction(function () use ($amount, $description, $adminId, $adminNotes) {
            $balanceBefore = $this->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update wallet
            $this->update([
                'balance' => $balanceAfter,
                'last_updated_at' => now(),
            ]);

            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $this->user_id,
                'type' => 'adjustment',
                'amount' => abs($amount),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'status' => 'completed',
                'admin_id' => $adminId,
                'admin_notes' => $adminNotes,
                'processed_at' => now(),
            ]);
        });
    }

    /**
     * Update pending commission amount.
     */
    public function updatePendingCommission()
    {
        $pendingAmount = Commission::where('user_id', $this->user_id)
            ->where('status', 'pending')
            ->sum('commission_amount');

        $this->update([
            'pending_commission' => $pendingAmount,
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Get wallet summary.
     */
    public function getSummary()
    {
        return [
            'current_balance' => $this->balance,
            'total_earned' => $this->total_earned,
            'total_withdrawn' => $this->total_withdrawn,
            'pending_commission' => $this->pending_commission,
            'available_balance' => $this->balance,
            'status' => $this->status,
            'last_updated' => $this->last_updated_at,
        ];
    }

    /**
     * Scope for active wallets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for suspended wallets.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope for frozen wallets.
     */
    public function scopeFrozen($query)
    {
        return $query->where('status', 'frozen');
    }
}
