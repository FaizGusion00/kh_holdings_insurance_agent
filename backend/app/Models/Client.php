<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'registration_id',
        'customer_type',
        'full_name',
        'nric',
        'phone_number',
        'email',
        'plan_name',
        'payment_mode',
        'medical_card_type',
        'status',
        'policy_id',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}


