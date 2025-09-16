<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone_number', 'role', 'permissions',
        'is_active', 'password'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'json',
        ];
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }
}