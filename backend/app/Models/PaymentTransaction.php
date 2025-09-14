<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Get the member who made this payment.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the policy this payment is for.
     */
    public function policy()
    {
        return $this->belongsTo(MemberPolicy::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}