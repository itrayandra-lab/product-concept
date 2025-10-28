<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'form_data',
        'form_step',
        'completed_steps',
        'form_progress',
        'expires_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'completed_steps' => 'array',
        'form_progress' => 'float',
        'expires_at' => 'datetime',
    ];

    /**
     * Scope to get active (non-expired) sessions
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired sessions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get remaining time until expiration
     */
    public function getRemainingTime(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->expires_at->diffInMinutes(now());
    }
}
