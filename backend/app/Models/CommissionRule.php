<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_name',
        'plan_type',
        'payment_frequency',
        'base_amount',
        'tier_level',
        'commission_percentage',
        'commission_amount',
        'commission_type',
        'is_active',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get commission amount for a given base amount
     */
    public function calculateCommission($baseAmount = null)
    {
        if ($this->commission_type === 'percentage') {
            $amount = $baseAmount ?? $this->base_amount;
            return ($amount * $this->commission_percentage) / 100;
        }
        
        return $this->commission_amount;
    }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for plan type
     */
    public function scopeForPlanType($query, $planType)
    {
        return $query->where('plan_type', $planType);
    }

    /**
     * Scope for payment frequency
     */
    public function scopeForFrequency($query, $frequency)
    {
        return $query->where('payment_frequency', $frequency);
    }

    /**
     * Scope for tier level
     */
    public function scopeForTier($query, $tier)
    {
        return $query->where('tier_level', $tier);
    }
}
