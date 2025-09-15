<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'action_text',
        'is_read',
        'is_important',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to filter read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter important notifications.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope to filter non-expired notifications.
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to get recent notifications (last 100).
     */
    public function scopeRecent($query, $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Check if notification is expired.
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get time ago string.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get notification icon based on type.
     */
    public function getIconAttribute()
    {
        return match($this->type) {
            'commission' => '💰',
            'policy' => '📋',
            'purchase' => '🛒',
            'expiry' => '⚠️',
            'payment' => '💳',
            'renewal' => '🔄',
            'system' => '⚙️',
            'referral' => '👥',
            'wallet' => '💼',
            default => '📢'
        };
    }

    /**
     * Get notification color based on type.
     */
    public function getColorAttribute()
    {
        return match($this->type) {
            'commission' => 'text-green-600',
            'policy' => 'text-blue-600',
            'purchase' => 'text-purple-600',
            'expiry' => 'text-red-600',
            'payment' => 'text-emerald-600',
            'renewal' => 'text-orange-600',
            'system' => 'text-gray-600',
            'referral' => 'text-indigo-600',
            'wallet' => 'text-yellow-600',
            default => 'text-gray-600'
        };
    }

    /**
     * Get notification background color based on type.
     */
    public function getBackgroundColorAttribute()
    {
        return match($this->type) {
            'commission' => 'bg-green-50 border-green-200',
            'policy' => 'bg-blue-50 border-blue-200',
            'purchase' => 'bg-purple-50 border-purple-200',
            'expiry' => 'bg-red-50 border-red-200',
            'payment' => 'bg-emerald-50 border-emerald-200',
            'renewal' => 'bg-orange-50 border-orange-200',
            'system' => 'bg-gray-50 border-gray-200',
            'referral' => 'bg-indigo-50 border-indigo-200',
            'wallet' => 'bg-yellow-50 border-yellow-200',
            default => 'bg-gray-50 border-gray-200'
        };
    }
}