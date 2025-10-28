<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimulationHistory extends Model
{
    /** @use HasFactory<\Database\Factories\SimulationHistoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'guest_session_id',
        'input_data',
        'output_data',
        'status',
        'n8n_workflow_id',
        'processing_started_at',
        'processing_completed_at',
        'processing_duration_seconds',
        'error_details',
        'progress_metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_data' => 'array',
            'output_data' => 'array',
            'error_details' => 'array',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'processing_duration_seconds' => 'integer',
            'progress_metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the simulation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the guest session associated with the simulation.
     */
    public function guestSession(): BelongsTo
    {
        return $this->belongsTo(GuestSession::class, 'guest_session_id', 'session_id');
    }

    /**
     * Get the ingredients used in this simulation.
     */
    public function simulationIngredients(): HasMany
    {
        return $this->hasMany(SimulationIngredient::class, 'simulation_id');
    }

    /**
     * Scope a query to only include completed simulations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed simulations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
