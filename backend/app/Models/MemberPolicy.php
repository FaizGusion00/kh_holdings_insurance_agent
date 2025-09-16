<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Member Policy Model
 * 
 * Tracks insurance policies purchased by users
 */
class MemberPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'insurance_plan_id',
        'policy_number',
        'payment_mode',
        'premium_amount',
        'policy_start_date',
        'policy_end_date',
        'next_payment_due',
        'status',
        'payment_count',
        'total_paid',
        'auto_renewal',
        'policy_documents',
        'beneficiaries',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'premium_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'policy_start_date' => 'date',
            'policy_end_date' => 'date',
            'next_payment_due' => 'date',
            'payment_count' => 'integer',
            'auto_renewal' => 'boolean',
            'policy_documents' => 'json',
            'beneficiaries' => 'json',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function insurancePlan()
    {
        return $this->belongsTo(InsurancePlan::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('policy_end_date', '<=', Carbon::now()->addDays($days))
                    ->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('policy_end_date', '<', Carbon::now())
                    ->where('status', '!=', 'expired');
    }

    // Helper Methods
    public function isActive()
    {
        return $this->status === 'active' && $this->policy_end_date > Carbon::now();
    }

    public function isExpired()
    {
        return $this->policy_end_date < Carbon::now();
    }

    public function daysUntilExpiry()
    {
        return Carbon::now()->diffInDays($this->policy_end_date, false);
    }

    public static function generatePolicyNumber()
    {
        do {
            $number = 'POL' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('policy_number', $number)->exists());
        
        return $number;
    }
}
