<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'metric_date',
        'requested_count',
        'completed_count',
        'failed_count',
        'regenerated_count',
        'total_processing_seconds',
        'average_processing_seconds',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'requested_count' => 'integer',
            'completed_count' => 'integer',
            'failed_count' => 'integer',
            'regenerated_count' => 'integer',
            'total_processing_seconds' => 'integer',
            'average_processing_seconds' => 'integer',
        ];
    }
}
