<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMandate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'policy_id',
        'mandate_type',
        'frequency',
        'amount',
        'start_date',
        'end_date',
        'bank_account',
        'bank_name',
        'status',
        'reference_number',
        'gateway_reference',
        'gateway_response',
        'last_processed_at',
        'next_processing_date',
        'total_processed',
        'total_amount_processed',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_processed_at' => 'datetime',
        'next_processing_date' => 'date',
        'total_amount_processed' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    /**
     * Get the user that owns the mandate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the policy that owns the mandate.
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(MemberPolicy::class);
    }

    /**
     * Scope for active mandates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive mandates.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for cancelled mandates.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for mandates by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('mandate_type', $type);
    }

    /**
     * Scope for mandates by frequency.
     */
    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope for mandates due for processing.
     */
    public function scopeDueForProcessing($query)
    {
        return $query->where('status', 'active')
            ->where('next_processing_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>', now());
            });
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
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'suspended' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get frequency display name.
     */
    public function getFrequencyDisplayAttribute(): string
    {
        return match($this->frequency) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half_yearly' => 'Half Yearly',
            'yearly' => 'Yearly',
            default => $this->frequency,
        };
    }

    /**
     * Get mandate type display name.
     */
    public function getMandateTypeDisplayAttribute(): string
    {
        return match($this->mandate_type) {
            'membership_fee' => 'Membership Fee',
            'sharing_account' => 'Sharing Account',
            default => $this->mandate_type,
        };
    }

    /**
     * Check if mandate is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               ($this->end_date === null || $this->end_date > now());
    }

    /**
     * Check if mandate is due for processing.
     */
    public function isDueForProcessing(): bool
    {
        return $this->isActive() && $this->next_processing_date <= now();
    }

    /**
     * Calculate next processing date based on frequency.
     */
    public function calculateNextProcessingDate(): \Carbon\Carbon
    {
        $baseDate = $this->last_processed_at ?? $this->start_date;
        
        return match($this->frequency) {
            'monthly' => $baseDate->addMonth(),
            'quarterly' => $baseDate->addMonths(3),
            'half_yearly' => $baseDate->addMonths(6),
            'yearly' => $baseDate->addYear(),
            default => $baseDate->addMonth(),
        };
    }

    /**
     * Process the mandate payment.
     */
    public function processPayment(): bool
    {
        if (!$this->isDueForProcessing()) {
            return false;
        }

        try {
            // Create payment transaction
            PaymentTransaction::create([
                'policy_id' => $this->policy_id,
                'amount' => $this->amount,
                'payment_type' => $this->mandate_type,
                'payment_method' => 'mandate',
                'status' => 'pending',
                'transaction_date' => now(),
                'description' => "Automatic payment via mandate - {$this->mandate_type_display}",
                'reference_number' => 'MANDATE_' . time() . '_' . rand(1000, 9999),
                'gateway_reference' => $this->gateway_reference,
            ]);

            // Update mandate processing info
            $this->update([
                'last_processed_at' => now(),
                'next_processing_date' => $this->calculateNextProcessingDate(),
                'total_processed' => $this->total_processed + 1,
                'total_amount_processed' => $this->total_amount_processed + $this->amount,
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to process mandate payment: " . $e->getMessage());
            return false;
        }
    }
}
