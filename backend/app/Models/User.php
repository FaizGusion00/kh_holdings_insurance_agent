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
        ];
    }

    /**
     * Get the members registered by this agent.
     */
    public function members()
    {
        return $this->hasMany(Member::class);
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
        do {
            $number = str_pad(mt_rand(10000, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('agent_number', $number)->exists());

        return $number;
    }
}
