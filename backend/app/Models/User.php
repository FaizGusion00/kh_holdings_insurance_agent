<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agent_number',
        'agent_code',
        'referrer_code',
        'referrer_id',
        'phone_number',
        'nric',
        'address',
        'city',
        'state',
        'postal_code',
        'bank_name',
        'bank_account_number',
        'bank_account_owner',
        'mlm_level',
        'total_commission_earned',
        'monthly_commission_target',
        'status',
        'phone_verified_at',
        'mlm_activation_date',
        // Plan and Policy Information (consolidated from clients)
        'plan_name',
        'payment_mode',
        'medical_card_type',
        'customer_type',
        'registration_id',
        // Demographics (consolidated from members)
        'race',
        'date_of_birth',
        'gender',
        'occupation',
        'height_cm',
        'weight_kg',
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        // Medical History
        'medical_consultation_2_years',
        'serious_illness_history',
        'insurance_rejection_history',
        'serious_injury_history',
        // Registration and Financial Info
        'registration_date',
        'relationship_with_agent',
        'balance',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'mlm_activation_date' => 'datetime',
            'password' => 'hashed',
            'total_commission_earned' => 'decimal:2',
            'monthly_commission_target' => 'decimal:2',
            'date_of_birth' => 'date',
            'registration_date' => 'date',
            'medical_consultation_2_years' => 'boolean',
            'serious_illness_history' => 'boolean',
            'insurance_rejection_history' => 'boolean',
            'serious_injury_history' => 'boolean',
            'balance' => 'decimal:2',
            'wallet_balance' => 'decimal:2',
        ];
    }

    /**
     * Get the referral record for this agent.
     */
    public function referral()
    {
        return $this->hasOne(Referral::class);
    }

    /**
     * Get the commissions earned by this agent.
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Get the agent who referred this user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_code', 'agent_code');
    }

    /**
     * Get the agents referred by this user.
     */
    public function referredAgents()
    {
        return $this->hasMany(User::class, 'referrer_code', 'agent_code');
    }

    /**
     * Get commissions earned from referring this user.
     */
    public function commissionEarnings()
    {
        return $this->hasMany(Commission::class, 'referrer_id');
    }

    /**
     * Get the agent's wallet.
     */
    public function wallet()
    {
        return $this->hasOne(AgentWallet::class);
    }

    /**
     * Get all wallet transactions.
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get medical insurance registration associated with this user.
     */
    public function medicalInsuranceRegistration()
    {
        return $this->belongsTo(MedicalInsuranceRegistration::class, 'registration_id');
    }

    /**
     * Get payment transactions for this user.
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get users registered by this agent (for agent dashboard).
     */
    public function registeredUsers()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * Generate a unique agent code.
     */
    public static function generateAgentCode(): string
    {
        // Determine the next sequential code based on the current maximum
        $last = self::whereNotNull('agent_code')
            ->where('agent_code', 'like', 'AGT%')
            ->select('agent_code')
            ->orderByDesc('agent_code')
            ->first();

        $nextNumber = 1;
        if ($last && preg_match('/^AGT(\d{5})$/', $last->agent_code, $m)) {
            $nextNumber = intval($m[1]) + 1;
        }

        $code = 'AGT' . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);

        // Ensure uniqueness just in case of race
        while (self::where('agent_code', $code)->exists()) {
            $nextNumber++;
            $code = 'AGT' . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);
        }

        return $code;
    }

    /**
     * Generate a unique agent number.
     */
    public static function generateAgentNumber(): string
    {
        // Determine the next sequential agent number
        $last = self::whereNotNull('agent_number')
            ->where('agent_number', 'REGEXP', '^[0-9]{6}$')
            ->select('agent_number')
            ->orderByDesc('agent_number')
            ->first();

        $nextNumber = 1;
        if ($last && is_numeric($last->agent_number)) {
            $nextNumber = intval($last->agent_number) + 1;
        }

        $agentNumber = str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);

        // Ensure uniqueness
        while (self::where('agent_number', $agentNumber)->exists()) {
            $nextNumber++;
            $agentNumber = str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
        }

        return $agentNumber;
    }

    /**
     * Check if user is an active agent.
     */
    public function isActiveAgent(): bool
    {
        return $this->status === 'active' && !empty($this->agent_code) && !empty($this->mlm_activation_date);
    }

    /**
     * Get full name for display.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get display status.
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Scope to get only active agents.
     */
    public function scopeActiveAgents($query)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('agent_code')
                    ->whereNotNull('mlm_activation_date');
    }

    /**
     * Scope to get only users with plans (customers).
     */
    public function scopeWithPlans($query)
    {
        return $query->whereNotNull('plan_name');
    }
}
