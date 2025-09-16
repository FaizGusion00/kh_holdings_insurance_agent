<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Insurance Plan Model
 * 
 * Manages the different insurance plans available in the system
 * (MediPlan Coop, Senior Care Gold 270, Senior Care Diamond 370)
 */
class InsurancePlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'plan_name',
        'plan_code',
        'description',
        'monthly_price',
        'quarterly_price',
        'semi_annually_price',
        'annually_price',
        'commitment_fee',
        'room_board_limit',
        'annual_limit',
        'government_cash_allowance',
        'death_benefit',
        'min_age',
        'max_age',
        'renewal_age',
        'benefits',
        'terms_conditions',
        'waiting_period_general',
        'waiting_period_specific',
        'administrator',
        'panel_hospitals',
        'panel_clinics',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'quarterly_price' => 'decimal:2',
            'semi_annually_price' => 'decimal:2',
            'annually_price' => 'decimal:2',
            'commitment_fee' => 'decimal:2',
            'room_board_limit' => 'decimal:2',
            'annual_limit' => 'decimal:2',
            'government_cash_allowance' => 'decimal:2',
            'death_benefit' => 'decimal:2',
            'min_age' => 'integer',
            'max_age' => 'integer',
            'renewal_age' => 'integer',
            'waiting_period_general' => 'integer',
            'waiting_period_specific' => 'integer',
            'panel_hospitals' => 'integer',
            'panel_clinics' => 'integer',
            'is_active' => 'boolean',
            'benefits' => 'json',
            'terms_conditions' => 'json',
        ];
    }

    // Relationships

    /**
     * Get the commission rates for this plan
     */
    public function commissionRates()
    {
        return $this->hasMany(CommissionRate::class);
    }

    /**
     * Get the member policies for this plan
     */
    public function memberPolicies()
    {
        return $this->hasMany(MemberPolicy::class);
    }

    // Scopes

    /**
     * Scope for active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for plans by age eligibility
     */
    public function scopeByAge($query, $age)
    {
        return $query->where('min_age', '<=', $age)
                    ->where('max_age', '>=', $age);
    }

    // Helper Methods

    /**
     * Get price by payment mode
     */
    public function getPriceByMode($paymentMode)
    {
        switch ($paymentMode) {
            case 'monthly':
                return $this->monthly_price;
            case 'quarterly':
                return $this->quarterly_price;
            case 'semi_annually':
                return $this->semi_annually_price;
            case 'annually':
                return $this->annually_price;
            default:
                return null;
        }
    }

    /**
     * Check if user is eligible by age
     */
    public function isEligibleByAge($age)
    {
        return $age >= $this->min_age && $age <= $this->max_age;
    }

    /**
     * Get total price including commitment fee
     */
    public function getTotalPriceByMode($paymentMode)
    {
        $basePrice = $this->getPriceByMode($paymentMode);
        if ($basePrice === null) {
            return null;
        }
        
        // Add commitment fee for monthly payments of Senior Care plans
        if ($paymentMode === 'monthly' && $this->commitment_fee > 0) {
            return $basePrice + $this->commitment_fee;
        }
        
        return $basePrice;
    }

    /**
     * Get available payment modes
     */
    public function getAvailablePaymentModes()
    {
        $modes = [];
        
        if ($this->monthly_price > 0) $modes[] = 'monthly';
        if ($this->quarterly_price > 0) $modes[] = 'quarterly';
        if ($this->semi_annually_price > 0) $modes[] = 'semi_annually';
        if ($this->annually_price > 0) $modes[] = 'annually';
        
        return $modes;
    }
}
