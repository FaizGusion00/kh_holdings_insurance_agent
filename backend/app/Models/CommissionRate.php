<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Commission Rate Model
 * 
 * Manages commission rates for different insurance plans, payment modes, and tier levels
 */
class CommissionRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_plan_id',
        'payment_mode',
        'tier_level',
        'commission_percentage',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'tier_level' => 'integer',
            'commission_percentage' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function insurancePlan()
    {
        return $this->belongsTo(InsurancePlan::class);
    }

    // Scopes
    public function scopeByPlan($query, $planId)
    {
        return $query->where('insurance_plan_id', $planId);
    }

    public function scopeByPaymentMode($query, $mode)
    {
        return $query->where('payment_mode', $mode);
    }

    public function scopeByTierLevel($query, $level)
    {
        return $query->where('tier_level', $level);
    }

    // Helper Methods
    public static function getCommissionForTier($planId, $paymentMode, $tierLevel)
    {
        return self::where('insurance_plan_id', $planId)
                  ->where('payment_mode', $paymentMode)
                  ->where('tier_level', $tierLevel)
                  ->first();
    }
}
