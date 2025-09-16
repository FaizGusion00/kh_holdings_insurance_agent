<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'is_read', 'data', 'read_at'
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'data' => 'json',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}