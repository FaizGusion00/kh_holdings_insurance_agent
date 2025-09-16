<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * User Model for Insurance MLM System
 * 
 * This model handles both regular clients and agents in the MLM system.
 * Agents have agent_codes and can refer new users to earn commissions.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'agent_code',
        'referrer_code',
        'name',
        'email',
        'phone_number',
        'nric',
        'race',
        'date_of_birth',
        'gender',
        'occupation',
        'height_cm',
        'weight_kg',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_consultation_2_years',
        'serious_illness_history',
        'insurance_rejection_history',
        'serious_injury_history',
        'relationship_with_agent',
        'address',
        'city',
        'state',
        'postal_code',
        'bank_name',
        'bank_account_number',
        'bank_account_owner',
        'mlm_level',
        'monthly_commission_target',
        'status',
        'plan_name',
        'payment_mode',
        'medical_card_type',
        'customer_type',
        'current_insurance_plan_id',
        'policy_start_date',
        'policy_end_date',
        'next_payment_due',
        'policy_status',
        'premium_amount',
        'current_payment_mode',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'registration_date' => 'datetime',
            'mlm_activation_date' => 'datetime',
            'password' => 'hashed',
            'medical_consultation_2_years' => 'boolean',
            'insurance_rejection_history' => 'boolean',
            'balance' => 'decimal:2',
            'wallet_balance' => 'decimal:2',
            'total_commission_earned' => 'decimal:2',
            'monthly_commission_target' => 'decimal:2',
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'mlm_level' => 'integer',
        ];
    }

    // JWT Auth Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'customer_type' => $this->customer_type,
            'agent_code' => $this->agent_code,
            'mlm_level' => $this->mlm_level,
        ];
    }

    // Relationships
    
    /**
     * Get the user's insurance policies
     */
    public function memberPolicies()
    {
        return $this->hasMany(MemberPolicy::class);
    }

    /**
     * Get the user's payment transactions
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get the user's wallet transactions
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get the user's withdrawal requests
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get the user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the agent who referred this user
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_code', 'agent_code');
    }

    /**
     * Get users referred by this user (downline)
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_code', 'agent_code');
    }

    // Scopes

    /**
     * Scope for active users only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for agents only
     */
    public function scopeAgents($query)
    {
        return $query->where('customer_type', 'agent');
    }

    /**
     * Scope for clients only
     */
    public function scopeClients($query)
    {
        return $query->where('customer_type', 'client');
    }

    /**
     * Scope for users by MLM level
     */
    public function scopeByMlmLevel($query, $level)
    {
        return $query->where('mlm_level', $level);
    }

    // Helper Methods

    /**
     * Generate a unique agent code in format AGT + 5 digits
     */
    public static function generateAgentCode()
    {
        do {
            // Generate random 5 digits
            $digits = str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $agentCode = 'AGT' . $digits;
            
            // Check if this agent code already exists
            $exists = self::where('agent_code', $agentCode)->exists();
        } while ($exists);
        
        return $agentCode;
    }

    /**
     * Auto-assign agent code when creating new agents
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            // Auto-generate agent code for new agents
            if ($user->customer_type === 'agent' && empty($user->agent_code)) {
                $user->agent_code = self::generateAgentCode();
            }
        });
    }

    /**
     * Check if user is an agent
     */
    public function isAgent()
    {
        return $this->customer_type === 'agent';
    }

    /**
     * Check if user is a client
     */
    public function isClient()
    {
        return $this->customer_type === 'client';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }


    /**
     * Get user's total downline count
     */
    public function getDownlineCount()
    {
        return $this->referrals()->count();
    }

    /**
     * Get user's active policies count
     */
    public function getActivePoliciesCount()
    {
        return $this->memberPolicies()->where('status', 'active')->count();
    }
}
