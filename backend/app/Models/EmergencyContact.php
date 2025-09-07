<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'customer_type',
        'contact_name',
        'contact_phone',
        'contact_relationship',
    ];

    public function registration()
    {
        return $this->belongsTo(MedicalInsuranceRegistration::class);
    }
}