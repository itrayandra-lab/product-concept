<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'simulation_id' => 'sim_' . str_pad($this->id, 16, '0', STR_PAD_LEFT),
            'status' => $this->status,
            'user_id' => $this->user_id,
            'guest_session_id' => $this->guest_session_id,
            
            // Input data
            'input_data' => $this->input_data,
            
            // Output data (only if completed)
            'output_data' => $this->when(
                $this->status === 'completed' && $this->output_data,
                $this->output_data
            ),
            
            // Processing metadata
            'processing' => [
                'started_at' => $this->processing_started_at?->toIso8601String(),
                'completed_at' => $this->processing_completed_at?->toIso8601String(),
                'duration_seconds' => $this->processing_duration_seconds,
                'workflow_id' => $this->n8n_workflow_id,
            ],
            'progress' => $this->progressPayload(),
            
            // Error details (only if failed)
            'error_details' => $this->when(
                $this->status === 'failed' && $this->error_details,
                $this->error_details
            ),

            'lead_generation' => [
                'whatsapp_cta_url' => $this->output_data['cta_whatsapp_url'] ?? null,
            ],
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Action URLs
            'urls' => [
                'self' => route('api.simulations.show', $this->id),
                'status' => route('api.simulations.status', $this->id),
                'regenerate' => $this->when(
                    $this->status === 'completed',
                    route('api.simulations.regenerate', $this->id)
                ),
                'export' => $this->when(
                    $this->status === 'completed',
                    route('api.simulations.export', $this->id)
                ),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'request_id' => $request->header('X-Request-ID', uniqid('req_')),
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Build progress payload for consistent responses.
     *
     * @return array<string, mixed>
     */
    protected function progressPayload(): array
    {
        $progress = is_array($this->progress_metadata) ? $this->progress_metadata : [];

        return [
            'percentage' => (int) ($progress['percentage'] ?? $this->fallbackProgressPercentage()),
            'current_step' => $progress['current_step'] ?? null,
            'steps_completed' => $progress['steps_completed'] ?? [],
            'steps_remaining' => $progress['steps_remaining'] ?? [],
            'estimated_completion' => $progress['estimated_completion'] ?? null,
            'last_updated_at' => $progress['updated_at'] ?? null,
        ];
    }

    /**
     * Fallback percentage when metadata not available.
     */
    protected function fallbackProgressPercentage(): int
    {
        return match ($this->status) {
            'completed' => 100,
            'processing' => 50,
            default => 0,
        };
    }
}

