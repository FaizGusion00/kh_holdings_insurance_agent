<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'referrer_id',
        'product_id',
        'policy_id',
        'tier_level',
        'commission_type',
        'base_amount',
        'commission_percentage',
        'commission_amount',
        'payment_frequency',
        'month',
        'year',
        'status',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    /**
     * Get the agent who earned this commission.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the agent who referred (source of commission).
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the insurance product.
     */
    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'product_id');
    }

    /**
     * Get the member policy.
     */
    public function policy()
    {
        return $this->belongsTo(MemberPolicy::class, 'policy_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by year and month.
     */
    public function scopeByPeriod($query, $year, $month = null)
    {
        $query->where('year', $year);
        
        if ($month) {
            $query->where('month', $month);
        }
        
        return $query;
    }

    /**
     * Scope to filter pending commissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter paid commissions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}