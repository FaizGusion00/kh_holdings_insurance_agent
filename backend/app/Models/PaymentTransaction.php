<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'policy_id',
        'amount',
        'payment_type',
        'payment_method',
        'status',
        'transaction_date',
        'description',
        'reference_number',
        'gateway_reference',
        'gateway_response',
        'processed_at',
        'failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'gateway_response' => 'array',
    ];

    /**
     * Get the policy that owns the payment transaction.
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(MemberPolicy::class);
    }

    /**
     * Get the member that owns the payment transaction.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
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
        return $query->where('payment_type', $type);
    }

    /**
     * Scope for transactions by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get payment method icon.
     */
    public function getPaymentMethodIconAttribute(): string
    {
        return match($this->payment_method) {
            'mandate' => 'credit-card',
            'manual' => 'hand',
            'card' => 'credit-card',
            default => 'credit-card',
        };
    }
}