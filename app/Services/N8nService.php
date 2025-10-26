<?php

namespace App\Services;

use App\Models\SimulationHistory;
use App\Services\SimulationAnalyticsService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class N8nService
{
    protected string $baseUrl;
    protected string $webhookUrl;
    protected ?string $apiKey;
    protected int $timeout;
    protected array $failoverConfig;
    protected WhatsAppService $whatsAppService;
    protected SimulationAnalyticsService $simulationAnalyticsService;

    public function __construct(
        WhatsAppService $whatsAppService,
        SimulationAnalyticsService $simulationAnalyticsService
    )
    {
        $this->baseUrl = config('services.n8n.base_url');
        $this->webhookUrl = config('services.n8n.webhook_url');
        $this->apiKey = config('services.n8n.api_key');
        $this->timeout = config('services.n8n.timeout', 150);
        $this->failoverConfig = config('services.n8n.failover', [
            'enabled' => true,
            'provider_order' => [],
            'max_retries' => 2,
        ]);
        $this->whatsAppService = $whatsAppService;
        $this->simulationAnalyticsService = $simulationAnalyticsService;
    }

    /**
     * Trigger n8n workflow for simulation processing
     *
     * @param SimulationHistory $simulation
     * @param array<string, mixed> $context
     * @return array
     * @throws \Exception
     */
    public function triggerWorkflow(SimulationHistory $simulation, array $context = []): array
    {
        try {
            $workflowId = Str::uuid()->toString();
            
            // Update simulation with workflow ID
            $simulation->update([
                'n8n_workflow_id' => $workflowId,
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            // Prepare payload for n8n
            $payload = $this->preparePayload($simulation, $workflowId, $context);

            // Send to n8n webhook
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->post($this->webhookUrl, $payload);

            if (!$response->successful()) {
                throw new \Exception('n8n workflow trigger failed: ' . $response->body());
            }

            Log::info('n8n workflow triggered', [
                'simulation_id' => $simulation->id,
                'workflow_id' => $workflowId,
            ]);

            return [
                'success' => true,
                'workflow_id' => $workflowId,
                'message' => 'Workflow triggered successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to trigger n8n workflow', [
                'simulation_id' => $simulation->id,
                'error' => $e->getMessage(),
            ]);

            // Update simulation status to failed
            $simulation->update([
                'status' => 'failed',
                'error_details' => [
                    'error' => 'n8n_trigger_failed',
                    'message' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Handle webhook response from n8n
     *
     * @param array $data
     * @return bool
     */
    public function handleWebhook(array $data): bool
    {
        try {
            // Validate webhook signature
            if (!$this->validateWebhookSignature($data)) {
                Log::warning('Invalid webhook signature', ['data' => $data]);
                return false;
            }

            $workflowId = $data['workflow_id'] ?? null;
            $status = $data['status'] ?? null;
            $resultData = $data['result_data'] ?? null;
            $errorDetails = $data['error_details'] ?? null;

            if (!$workflowId) {
                Log::error('Webhook missing workflow_id', ['data' => $data]);
                return false;
            }

            // Find simulation by workflow ID
            $simulation = SimulationHistory::where('n8n_workflow_id', $workflowId)->first();

            if (!$simulation) {
                Log::error('Simulation not found for workflow', ['workflow_id' => $workflowId]);
                return false;
            }

            // Update simulation based on status
            if ($status === 'completed') {
                $this->handleSuccess($simulation, $resultData, $data);
            } elseif (in_array($status, ['failed', 'timeout'], true)) {
                $this->handleFailure($simulation, $errorDetails);
            } else {
                $this->handleProgress($simulation, $data);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to handle n8n webhook', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            return false;
        }
    }

    /**
     * Get workflow status from n8n
     *
     * @param string $workflowId
     * @return array
     */
    public function getWorkflowStatus(string $workflowId): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders($this->getHeaders())
                ->get("{$this->baseUrl}/workflow/{$workflowId}/status");

            if (!$response->successful()) {
                return [
                    'status' => 'unknown',
                    'message' => 'Failed to fetch workflow status',
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Failed to get workflow status', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare payload for n8n workflow
     *
     * @param SimulationHistory $simulation
     * @param string $workflowId
     * @return array
     */
    protected function preparePayload(SimulationHistory $simulation, string $workflowId, array $context = []): array
    {
        return [
            'workflow_id' => $workflowId,
            'simulation_id' => $simulation->id,
            'user_id' => $simulation->user_id,
            'form_data' => $simulation->input_data,
            'processing_options' => [
                'ai_provider_preference' => 'auto',
                'include_scientific_refs' => true,
                'include_market_analysis' => true,
                'cache_duration' => 3600,
                'quality_level' => 'standard',
            ],
            'failover' => $this->failoverConfig,
            'context' => array_filter($context),
            'callback_url' => route('api.n8n.webhook'),
        ];
    }

    /**
     * Handle successful workflow completion
     *
     * @param SimulationHistory $simulation
     * @param array $resultData
     * @param array $metadata
     * @return void
     */
    protected function handleSuccess(SimulationHistory $simulation, array $resultData, array $metadata): void
    {
        $processingDuration = $metadata['processing_time_seconds'] ??
            now()->diffInSeconds($simulation->processing_started_at);

        $simulation->loadMissing('user');
        $resultData = $this->ensureWhatsAppCta($simulation, $resultData);

        $progressMetadata = $this->mergeProgressMetadata($simulation, [
            'percentage' => 100,
            'current_step' => 'completed',
            'steps_completed' => $metadata['steps_completed'] ?? ($simulation->progress_metadata['steps_completed'] ?? []),
            'steps_remaining' => [],
            'estimated_completion' => now()->toIso8601String(),
        ]);

        $simulation->update([
            'status' => 'completed',
            'output_data' => $resultData,
            'processing_completed_at' => now(),
            'processing_duration_seconds' => $processingDuration,
            'progress_metadata' => $progressMetadata,
        ]);

        $this->simulationAnalyticsService->recordSimulationCompleted($processingDuration);

        Log::info('Simulation completed successfully', [
            'simulation_id' => $simulation->id,
            'workflow_id' => $simulation->n8n_workflow_id,
            'duration' => $processingDuration,
        ]);
    }

    /**
     * Handle workflow failure
     *
     * @param SimulationHistory $simulation
     * @param array|null $errorDetails
     * @return void
     */
    protected function handleFailure(SimulationHistory $simulation, ?array $errorDetails): void
    {
        $progressMetadata = $this->mergeProgressMetadata($simulation, [
            'current_step' => 'failed',
            'steps_remaining' => [],
        ]);

        $simulation->update([
            'status' => 'failed',
            'error_details' => $errorDetails ?? [
                'error' => 'workflow_failed',
                'message' => 'Workflow processing failed',
            ],
            'progress_metadata' => $progressMetadata,
        ]);

        $this->simulationAnalyticsService->recordSimulationFailed();

        Log::error('Simulation failed', [
            'simulation_id' => $simulation->id,
            'workflow_id' => $simulation->n8n_workflow_id,
            'error' => $errorDetails,
        ]);
    }

    /**
     * Handle workflow progress update
     *
     * @param SimulationHistory $simulation
     * @param array $data
     * @return void
     */
    protected function handleProgress(SimulationHistory $simulation, array $data): void
    {
        $progressPayload = $data['progress'] ?? [];

        $percentage = $progressPayload['percentage'] ??
            $data['progress'] ??
            ($simulation->progress_metadata['percentage'] ?? 50);

        $progressMetadata = $this->mergeProgressMetadata($simulation, [
            'percentage' => (int) $percentage,
            'current_step' => $progressPayload['current_step'] ?? $data['current_step'] ?? 'processing',
            'steps_completed' => $progressPayload['completed_steps'] ?? $data['completed_steps'] ??
                ($simulation->progress_metadata['steps_completed'] ?? []),
            'steps_remaining' => $progressPayload['remaining_steps'] ?? $data['remaining_steps'] ??
                ($simulation->progress_metadata['steps_remaining'] ?? []),
            'estimated_completion' => $progressPayload['estimated_completion'] ?? $data['estimated_completion'] ??
                ($simulation->progress_metadata['estimated_completion'] ?? null),
        ]);

        $simulation->update([
            'progress_metadata' => $progressMetadata,
        ]);

        Log::info('Simulation progress update', [
            'simulation_id' => $simulation->id,
            'progress' => $progressMetadata,
        ]);
    }

    /**
     * Merge new progress metadata with existing defaults.
     *
     * @param array<string, mixed> $overrides
     */
    protected function mergeProgressMetadata(SimulationHistory $simulation, array $overrides): array
    {
        $defaults = [
            'percentage' => 0,
            'current_step' => 'processing',
            'steps_completed' => [],
            'steps_remaining' => [],
            'estimated_completion' => null,
        ];

        $existing = is_array($simulation->progress_metadata) ? $simulation->progress_metadata : [];

        $metadata = array_merge($defaults, $existing, $overrides);
        $metadata['percentage'] = (int) max(0, min(100, $metadata['percentage']));
        $metadata['updated_at'] = now()->toIso8601String();

        if (!empty($metadata['estimated_completion'])) {
            if ($metadata['estimated_completion'] instanceof \DateTimeInterface) {
                $metadata['estimated_completion'] = $metadata['estimated_completion']->format(DATE_ATOM);
            } elseif (is_numeric($metadata['estimated_completion'])) {
                $metadata['estimated_completion'] = now()
                    ->addSeconds((int) $metadata['estimated_completion'])
                    ->toIso8601String();
            }
        }

        return $metadata;
    }

    /**
     * Ensure result payload contains a WhatsApp CTA URL.
     *
     * @param array<string, mixed> $resultData
     * @return array<string, mixed>
     */
    protected function ensureWhatsAppCta(SimulationHistory $simulation, array $resultData): array
    {
        if (!empty($resultData['cta_whatsapp_url'])) {
            return $resultData;
        }

        $simulation->loadMissing('user');

        $inputData = is_array($simulation->input_data) ? $simulation->input_data : [];
        $productName = $resultData['product_name']
            ?? ($inputData['product_name'] ?? null)
            ?? ($inputData['fungsi_produk'][0] ?? null)
            ?? 'Produk AI';

        $cta = $this->whatsAppService->generateCtaUrl([
            'product_name' => $productName,
            'simulation_id' => $this->formatSimulationPublicId($simulation),
            'user_name' => $simulation->user?->name,
            'company' => $simulation->user?->company,
        ]);

        if ($cta) {
            $resultData['cta_whatsapp_url'] = $cta;
        }

        return $resultData;
    }

    /**
     * Helper to format the public simulation identifier.
     */
    protected function formatSimulationPublicId(SimulationHistory $simulation): string
    {
        return 'sim_' . str_pad((string) $simulation->id, 16, '0', STR_PAD_LEFT);
    }

    /**
     * Validate webhook signature
     *
     * @param array $data
     * @return bool
     */
    protected function validateWebhookSignature(array $data): bool
    {
        // If no API key configured, skip validation (development mode)
        if (!$this->apiKey) {
            return true;
        }

        $signature = $data['signature'] ?? null;
        if (!$signature) {
            return false;
        }

        // Verify HMAC signature
        $payload = json_encode($data['payload'] ?? $data);
        $expectedSignature = hash_hmac('sha256', $payload, $this->apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get headers for n8n API requests
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = "Bearer {$this->apiKey}";
        }

        return $headers;
    }
}

