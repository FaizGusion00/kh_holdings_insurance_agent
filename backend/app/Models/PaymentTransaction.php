<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'member_policy_id', 'transaction_id', 'gateway_transaction_id',
        'amount', 'currency', 'payment_method', 'payment_type', 'status',
        'gateway_response', 'receipt_number', 'paid_at', 'failure_reason', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'gateway_response' => 'json',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function memberPolicy()
    {
        return $this->belongsTo(MemberPolicy::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function generateTransactionId()
    {
        return 'TXN' . date('YmdHis') . mt_rand(1000, 9999);
    }
}