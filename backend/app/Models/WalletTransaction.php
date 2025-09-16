<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'related_user_id', 'reference_id', 'type', 'amount',
        'balance_before', 'balance_after', 'description', 'metadata'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'metadata' => 'json',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function scopeCommissions($query)
    {
        return $query->where('type', 'commission_earned');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }
}