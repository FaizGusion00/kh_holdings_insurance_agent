<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalInsurancePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'quarterly_price',
        'half_yearly_price',
        'yearly_price',
        'commitment_fee',
        'is_active',
        'coverage_details',
        'max_age',
        'min_age',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'quarterly_price' => 'decimal:2',
        'half_yearly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'commitment_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'coverage_details' => 'array',
    ];

    public function policies()
    {
        return $this->hasMany(MedicalInsurancePolicy::class, 'plan_id');
    }

    public function getPriceByFrequency($frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return $this->monthly_price;
            case 'quarterly':
                return $this->quarterly_price;
            case 'half_yearly':
                return $this->half_yearly_price;
            case 'yearly':
                return $this->yearly_price;
            default:
                return $this->monthly_price;
        }
    }

    public function getTotalPriceByFrequency($frequency)
    {
        $basePrice = $this->getPriceByFrequency($frequency);
        return $basePrice + $this->commitment_fee;
    }
}
