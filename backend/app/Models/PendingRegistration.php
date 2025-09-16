<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'registration_batch_id',
        'clients_data',
        'total_amount',
        'status',
        'payment_transaction_id',
        'expires_at'
    ];

    protected $casts = [
        'clients_data' => 'array',
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime'
    ];

    /**
     * Generate a unique registration batch ID
     */
    public static function generateBatchId()
    {
        return 'REG_' . date('Ymd') . '_' . strtoupper(Str::random(8));
    }

    /**
     * Relationships
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Check if registration has expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark registration as expired
     */
    public function markExpired()
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Get client count
     */
    public function getClientCount()
    {
        return count($this->clients_data);
    }

    /**
     * Validate client emails are unique in the system
     */
    public function validateClientEmails()
    {
        $emails = collect($this->clients_data)->pluck('email');
        $existingEmails = User::whereIn('email', $emails)->pluck('email');
        
        return $existingEmails->isEmpty();
    }

    /**
     * Validate client NRICs are unique in the system
     */
    public function validateClientNrices()
    {
        $nrics = collect($this->clients_data)->pluck('nric');
        $existingNrics = User::whereIn('nric', $nrics)->pluck('nric');
        
        return $existingNrics->isEmpty();
    }
}