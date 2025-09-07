<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalInsurancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'plan_id',
        'agent_id',
        'policy_number',
        'customer_type',
        'customer_name',
        'customer_nric',
        'customer_phone',
        'customer_email',
        'payment_frequency',
        'premium_amount',
        'commitment_fee',
        'medical_card_type',
        'status',
        'start_date',
        'end_date',
        'next_payment_date',
        'activated_at',
    ];

    protected $casts = [
        'premium_amount' => 'decimal:2',
        'commitment_fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_payment_date' => 'date',
        'activated_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(MedicalInsuranceRegistration::class, 'registration_id');
    }

    public function plan()
    {
        return $this->belongsTo(MedicalInsurancePlan::class, 'plan_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function generatePolicyNumber()
    {
        $prefix = 'POL';
        $year = date('Y');
        $month = date('m');
        $lastPolicy = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastPolicy ? (intval(substr($lastPolicy->policy_number, -4)) + 1) : 1;
        
        return $prefix . $year . $month . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalAmount()
    {
        return $this->premium_amount + $this->commitment_fee;
    }
}
