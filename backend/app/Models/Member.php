<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'nric',
        'phone',
        'email',
        'address',
        'date_of_birth',
        'gender',
        'occupation',
        'race',
        'relationship_with_agent',
        'status',
        'registration_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'balance',
        'referrer_code',
        'referrer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
        ];
    }

    /**
     * Get the agent who registered this member.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the policies for this member.
     */
    public function policies()
    {
        return $this->hasMany(MemberPolicy::class);
    }

    /**
     * Get the payment transactions for this member.
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get active policies only.
     */
    public function activePolicies()
    {
        return $this->policies()->where('status', 'active');
    }

    /**
     * Calculate total premium paid by this member.
     */
    public function getTotalPremiumPaidAttribute()
    {
        return $this->policies()->sum('total_paid');
    }

    /**
     * Get next payment due date.
     */
    public function getNextPaymentDueDateAttribute()
    {
        return $this->activePolicies()
                    ->orderBy('next_payment_date')
                    ->value('next_payment_date');
    }
}
