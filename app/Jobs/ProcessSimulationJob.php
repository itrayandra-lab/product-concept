<?php

namespace App\Jobs;

use App\Models\SimulationHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessSimulationJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 150;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SimulationHistory $simulation
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update simulation status to processing
            $this->simulation->update([
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            Log::info('Starting n8n workflow processing', [
                'simulation_id' => $this->simulation->id,
                'workflow_id' => $this->simulation->n8n_workflow_id,
            ]);

            // Prepare payload for n8n workflow
            $payload = $this->preparePayload();

            // Get n8n configuration
            $webhookUrl = config('services.n8n.webhook_url');
            $timeout = config('services.n8n.timeout', 150);
            $headers = $this->getHeaders();

            // Send request to n8n webhook
            $response = Http::timeout($timeout)
                ->withHeaders($headers)
                ->post($webhookUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception('n8n workflow request failed: ' . $response->status() . ' - ' . $response->body());
            }

            Log::info('n8n workflow request sent successfully', [
                'simulation_id' => $this->simulation->id,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            // Handle the response directly from n8n
            $responseData = $response->json();
            
            if (isset($responseData['product_names']) && isset($responseData['ingredients_analysis'])) {
                // This is a complete response from n8n
                $this->simulation->update([
                    'status' => 'completed',
                    'output_data' => $responseData,
                    'processing_completed_at' => now(),
                ]);

                Log::info('Simulation completed successfully with n8n response', [
                    'simulation_id' => $this->simulation->id,
                    'has_product_names' => isset($responseData['product_names']),
                    'has_ingredients_analysis' => isset($responseData['ingredients_analysis']),
                ]);
            } else {
                // Response doesn't look like complete data, might be acknowledgment
                Log::warning('n8n response does not contain expected data structure', [
                    'simulation_id' => $this->simulation->id,
                    'response_keys' => array_keys($responseData),
                ]);
            }

        } catch (Throwable $e) {
            Log::error('ProcessSimulationJob failed', [
                'simulation_id' => $this->simulation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update simulation status to failed
            $this->simulation->update([
                'status' => 'failed',
                'error_details' => [
                    'error' => 'n8n_workflow_failed',
                    'message' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessSimulationJob failed permanently', [
            'simulation_id' => $this->simulation->id,
            'exception' => $exception?->getMessage(),
        ]);

        // Update simulation status to failed
        $this->simulation->update([
            'status' => 'failed',
            'error_details' => [
                'error' => 'job_failed',
                'message' => $exception?->getMessage() ?? 'Job failed after maximum attempts',
                'failed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Prepare payload for n8n workflow
     */
    protected function preparePayload(): array
    {
        $formData = $this->simulation->input_data;

        return [
            'workflow_id' => $this->simulation->n8n_workflow_id,
            'simulation_id' => $this->simulation->id,
            'user_id' => $this->simulation->user_id,
            'form_data' => [
                'fungsi_produk' => $formData['fungsi_produk'] ?? [],
                'bentuk_formulasi' => $formData['bentuk_formulasi'] ?? '',
                'target_gender' => $formData['target_gender'] ?? '',
                'target_usia' => $formData['target_usia'] ?? [],
                'target_negara' => $formData['target_negara'] ?? 'Indonesia',
                'deskripsi_formula' => $formData['deskripsi_formula'] ?? '',
                'bahan_aktif' => $formData['bahan_aktif'] ?? [],
            ],
            'processing_options' => [
                'ai_provider_preference' => 'auto',
                'include_scientific_refs' => true,
                'include_market_analysis' => true,
                'cache_duration' => 3600,
                'quality_level' => 'standard',
            ],
        ];
    }

    /**
     * Get headers for n8n API requests
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $apiKey = config('services.n8n.api_key');
        if ($apiKey) {
            $headers['Authorization'] = "Bearer {$apiKey}";
        }

        return $headers;
    }
}
