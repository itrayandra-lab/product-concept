<?php

namespace App\Services;

use App\Models\SimulationMetric;
use Illuminate\Support\Carbon;

class SimulationAnalyticsService
{
    /**
     * Record simulation request metrics.
     */
    public function recordSimulationRequested(bool $isRegeneration = false): void
    {
        $metric = $this->getOrCreateMetricForDate(now());

        $metric->increment('requested_count');

        if ($isRegeneration) {
            $metric->increment('regenerated_count');
        }
    }

    /**
     * Record a completed simulation.
     */
    public function recordSimulationCompleted(?int $durationSeconds = null): void
    {
        $metric = $this->getOrCreateMetricForDate(now());
        $metric->increment('completed_count');

        if ($durationSeconds !== null) {
            $metric->increment('total_processing_seconds', max(0, $durationSeconds));
        }

        $metric->refresh();

        if ($metric->completed_count > 0) {
            $average = (int) round(
                $metric->total_processing_seconds / max(1, $metric->completed_count)
            );

            $metric->update(['average_processing_seconds' => $average]);
        }
    }

    /**
     * Record a failed simulation.
     */
    public function recordSimulationFailed(): void
    {
        $metric = $this->getOrCreateMetricForDate(now());
        $metric->increment('failed_count');
    }

    /**
     * Get summary metrics for dashboard use.
     *
     * @return array<string, mixed>
     */
    public function getSummary(int $days = 7): array
    {
        $metrics = SimulationMetric::where(
            'metric_date',
            '>=',
            now()->subDays(max(0, $days - 1))->toDateString()
        )
            ->orderBy('metric_date', 'desc')
            ->get();

        return [
            'days' => $metrics->count(),
            'totals' => [
                'requested' => (int) $metrics->sum('requested_count'),
                'completed' => (int) $metrics->sum('completed_count'),
                'failed' => (int) $metrics->sum('failed_count'),
                'regenerated' => (int) $metrics->sum('regenerated_count'),
            ],
            'average_processing_seconds' => (int) round($metrics->avg('average_processing_seconds') ?? 0),
        ];
    }

    /**
     * Helper to fetch or create metric row for the provided date.
     */
    protected function getOrCreateMetricForDate(Carbon $date): SimulationMetric
    {
        $attributes = ['metric_date' => $date->toDateString()];

        SimulationMetric::query()->updateOrInsert($attributes, []);

        return SimulationMetric::firstWhere($attributes);
    }
}
