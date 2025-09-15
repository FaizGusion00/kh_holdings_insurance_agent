<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hospital_id',
        'clinic_id',
        'case_number',
        'case_type',
        'diagnosis',
        'treatment',
        'admission_date',
        'discharge_date',
        'total_cost',
        'claim_amount',
        'status',
        'notes',
        'documents',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'total_cost' => 'decimal:2',
        'claim_amount' => 'decimal:2',
        'documents' => 'array',
    ];

    /**
     * Get the user this case belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hospital where this case occurred.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the clinic where this case occurred.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by case type.
     */
    public function scopeByCaseType($query, $type)
    {
        return $query->where('case_type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('admission_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter pending claims.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter approved claims.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter rejected claims.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if case is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if case is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if case is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get case duration in days.
     */
    public function getDurationDaysAttribute()
    {
        if (!$this->admission_date || !$this->discharge_date) {
            return null;
        }

        return $this->admission_date->diffInDays($this->discharge_date);
    }

    /**
     * Get formatted total cost.
     */
    public function getFormattedTotalCostAttribute()
    {
        return 'RM ' . number_format($this->total_cost, 2);
    }

    /**
     * Get formatted claim amount.
     */
    public function getFormattedClaimAmountAttribute()
    {
        return 'RM ' . number_format($this->claim_amount, 2);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'under_review' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get healthcare facility name.
     */
    public function getHealthcareFacilityNameAttribute()
    {
        if ($this->hospital) {
            return $this->hospital->name;
        }

        if ($this->clinic) {
            return $this->clinic->name;
        }

        return 'Unknown';
    }
}


