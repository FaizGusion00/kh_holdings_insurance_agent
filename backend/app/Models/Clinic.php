<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'website',
        'contact_person',
        'contact_phone',
        'specialties',
        'operating_hours',
        'is_active',
    ];

    protected $casts = [
        'specialties' => 'array',
        'operating_hours' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the medical cases for this clinic.
     */
    public function medicalCases()
    {
        return $this->hasMany(MedicalCase::class);
    }

    /**
     * Scope to filter active clinics.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by state.
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state} {$this->postal_code}";
    }

    /**
     * Check if clinic is open on a specific day and time.
     */
    public function isOpen($day = null, $time = null)
    {
        if (!$this->operating_hours) {
            return false;
        }

        $day = $day ?? strtolower(date('l'));
        $time = $time ?? date('H:i');

        foreach ($this->operating_hours as $hours) {
            if (strtolower($hours['day']) === $day) {
                return $time >= $hours['open_time'] && $time <= $hours['close_time'];
            }
        }

        return false;
    }
}
