<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCommissionRule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'payment_frequency',
        'tier_level',
        'commission_type',
        'commission_value',
        'minimum_requirement',
        'maximum_cap',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:4',
            'minimum_requirement' => 'decimal:2',
            'maximum_cap' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the insurance product.
     */
    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'product_id');
    }

    /**
     * Scope to filter active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by product type.
     */
    public function scopeByProductType($query, $productType)
    {
        return $query->whereHas('product', function($q) use ($productType) {
            $q->where('product_type', $productType);
        });
    }

    /**
     * Scope to filter by tier level.
     */
    public function scopeByTier($query, $tier)
    {
        return $query->where('tier_level', $tier);
    }

    /**
     * Scope to filter by payment frequency.
     */
    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('payment_frequency', $frequency);
    }

    /**
     * Calculate commission amount based on base amount.
     */
    public function calculateCommission($baseAmount)
    {
        if ($this->commission_type === 'percentage') {
            $commission = $baseAmount * ($this->commission_value / 100);
        } else {
            // Fixed amount
            $commission = $this->commission_value;
        }

        // Apply minimum requirement
        if ($baseAmount < $this->minimum_requirement) {
            return 0;
        }

        // Apply maximum cap
        if ($this->maximum_cap > 0 && $commission > $this->maximum_cap) {
            $commission = $this->maximum_cap;
        }

        return round($commission, 2);
    }

    /**
     * Check if base amount meets minimum requirement.
     */
    public function meetsMinimumRequirement($baseAmount)
    {
        return $baseAmount >= $this->minimum_requirement;
    }
}