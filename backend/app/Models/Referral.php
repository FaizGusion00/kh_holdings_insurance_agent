<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_code',
        'referrer_code',
        'user_id',
        'referral_level',
        'upline_chain',
        'downline_count',
        'total_downline_count',
        'status',
        'activation_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'upline_chain' => 'array',
            'activation_date' => 'datetime',
        ];
    }

    /**
     * Get the agent (user) for this referral.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the agent who referred this one.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_code', 'agent_code');
    }

    /**
     * Get direct downlines (referrals made by this agent).
     */
    public function directDownlines()
    {
        return $this->hasMany(Referral::class, 'referrer_code', 'agent_code');
    }

    /**
     * Scope to filter active referrals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by referral level.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('referral_level', $level);
    }

    /**
     * Get all upline agents from the chain.
     */
    public function getUplineAgents()
    {
        if (!$this->upline_chain) {
            return collect();
        }

        return User::whereIn('agent_code', $this->upline_chain)->get();
    }

    /**
     * Build upline chain for a new referral.
     */
    public static function buildUplineChain($referrerCode, $maxLevels = 5)
    {
        $chain = [];
        $currentReferrer = $referrerCode;
        $level = 0;

        while ($currentReferrer && $level < $maxLevels) {
            $chain[] = $currentReferrer;
            
            // Find the next upline
            $uplineReferral = self::where('agent_code', $currentReferrer)->first();
            $currentReferrer = $uplineReferral ? $uplineReferral->referrer_code : null;
            $level++;
        }

        return $chain;
    }

    /**
     * Update downline counts for upline chain.
     */
    public function updateUplineDownlineCounts()
    {
        if (!$this->upline_chain) {
            return;
        }

        foreach ($this->upline_chain as $index => $uplineCode) {
            $uplineReferral = self::where('agent_code', $uplineCode)->first();
            
            if ($uplineReferral) {
                if ($index === 0) {
                    // Direct downline
                    $uplineReferral->increment('downline_count');
                }
                $uplineReferral->increment('total_downline_count');
            }
        }
    }
}