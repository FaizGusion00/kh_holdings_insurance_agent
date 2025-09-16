<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'address', 'city', 'state', 'postal_code',
        'phone_number', 'fax', 'email', 'website', 'services_offered',
        'is_panel', 'is_active', 'latitude', 'longitude', 'operating_hours'
    ];

    protected function casts(): array
    {
        return [
            'is_panel' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'services_offered' => 'json',
            'operating_hours' => 'json',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePanel($query)
    {
        return $query->where('is_panel', true);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }
}