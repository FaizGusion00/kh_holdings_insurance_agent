<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'commission_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_number',
        'status',
        'admin_id',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the agent who owns this transaction.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the commission that generated this transaction.
     */
    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    /**
     * Get the admin who processed this transaction.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the agent's wallet.
     */
    public function wallet()
    {
        return $this->belongsTo(AgentWallet::class, 'user_id', 'user_id');
    }

    /**
     * Generate reference number.
     */
    public static function generateReferenceNumber()
    {
        do {
            $reference = 'WT' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Scope for credit transactions.
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debit transactions.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for transactions by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for transactions by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for transactions in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get transaction type label.
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'credit' => 'Credit',
            'debit' => 'Debit',
            'adjustment' => 'Admin Adjustment',
            'withdrawal' => 'Withdrawal',
            'refund' => 'Refund',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color class.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'gray',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get type color class.
     */
    public function getTypeColorAttribute()
    {
        return match($this->type) {
            'credit' => 'green',
            'debit' => 'red',
            'adjustment' => 'blue',
            'withdrawal' => 'orange',
            'refund' => 'purple',
            default => 'gray',
        };
    }
}
