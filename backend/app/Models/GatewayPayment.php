<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'agent_id',
        'gateway',
        'payment_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'description',
        'metadata',
        'gateway_response',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'gateway_response' => 'array',
        'completed_at' => 'datetime',
    ];
}


