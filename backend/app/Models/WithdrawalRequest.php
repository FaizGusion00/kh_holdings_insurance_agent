<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'request_id', 'amount', 'bank_name', 'bank_account_number',
        'bank_account_owner', 'status', 'admin_notes', 'processed_at',
        'processed_by', 'transaction_reference'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function generateRequestId()
    {
        return 'WDR' . date('YmdHis') . mt_rand(100, 999);
    }
}