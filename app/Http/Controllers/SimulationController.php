<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSimulationRequest;
use App\Http\Resources\SimulationResource;
use App\Models\SimulationHistory;
use App\Models\User;
use App\Repositories\GuestSessionRepository;
use App\Services\AuditLogService;
use App\Services\ExportService;
use App\Services\N8nService;
use App\Services\SimulationAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SimulationController extends Controller
{
    protected $guestSessionRepository;
    protected $auditLogService;
    protected $n8nService;
    protected $exportService;
    protected $simulationAnalyticsService;

    public function __construct(
        GuestSessionRepository $guestSessionRepository,
        AuditLogService $auditLogService,
        N8nService $n8nService,
        ExportService $exportService,
        SimulationAnalyticsService $simulationAnalyticsService
    ) {
        $this->guestSessionRepository = $guestSessionRepository;
        $this->auditLogService = $auditLogService;
        $this->n8nService = $n8nService;
        $this->exportService = $exportService;
        $this->simulationAnalyticsService = $simulationAnalyticsService;
    }

    /**
     * Store a new simulation request
     */
    public function store(StoreSimulationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Require authentication for simulation generation
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required to generate simulation',
                    'error' => 'AUTHENTICATION_REQUIRED',
                    'auth_required' => true,
                ], 401);
            }

            // Check user quota
            if (!$this->checkUserQuota($user)) {
                $this->auditLogService->logRateLimitHit($user, $request, 'api.simulations.store');

                return response()->json([
                    'success' => false,
                    'message' => 'Daily simulation quota exceeded',
                    'error' => 'QUOTA_EXCEEDED',
                    'quota_info' => [
                        'daily_limit' => $this->getDailyQuota($user),
                        'used_today' => $user->daily_simulation_count,
                        'tier' => $user->subscription_tier,
                    ],
                ], 429);
            }

            // Create simulation record
            $simulation = SimulationHistory::create([
                'user_id' => $user->id,
                'guest_session_id' => $request->input('guest_session_id'),
                'input_data' => $request->validated(),
                'status' => 'pending',
                'progress_metadata' => $this->buildInitialProgressMetadata(),
            ]);

            $this->simulationAnalyticsService->recordSimulationRequested();

            // Trigger n8n workflow
            try {
                $this->n8nService->triggerWorkflow($simulation);
            } catch (\Exception $e) {
                Log::error('Failed to trigger n8n workflow', [
                    'simulation_id' => $simulation->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start simulation processing',
                    'error' => 'WORKFLOW_TRIGGER_FAILED',
                ], 500);
            }

            // Increment user quota
            if ($user) {
                $this->incrementUserQuota($user);
            }

            // Log simulation creation
            $this->auditLogService->logSimulationCreated($simulation, $request);

            return response()->json([
                'success' => true,
                'message' => 'Simulation request accepted and processing',
                'data' => new SimulationResource($simulation),
            ], 202);

        } catch (\Exception $e) {
            Log::error('Simulation creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create simulation',
                'error' => 'SIMULATION_CREATION_FAILED',
            ], 500);
        }
    }

    /**
     * Show simulation results
     */
    public function show($id): JsonResponse
    {
        $simulation = SimulationHistory::find($id);

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found',
                'error' => 'NOT_FOUND',
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($simulation->user_id && (!$user || $simulation->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => $simulation->status === 'completed' 
                ? 'Simulation completed successfully' 
                : 'Simulation is still processing',
            'data' => new SimulationResource($simulation),
        ]);
    }

    /**
     * Get simulation status (lightweight)
     */
    public function status($id): JsonResponse
    {
        $simulation = SimulationHistory::find($id);

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found',
                'error' => 'NOT_FOUND',
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if ($simulation->user_id && (!$user || $simulation->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        $progressMetadata = $simulation->progress_metadata ?? [];

        $response = [
            'success' => true,
            'data' => [
                'simulation_id' => 'sim_' . str_pad($simulation->id, 16, '0', STR_PAD_LEFT),
                'status' => $simulation->status,
                'progress_percentage' => $this->calculateProgress($simulation),
                'current_step' => $progressMetadata['current_step'] ?? null,
                'steps_completed' => $progressMetadata['steps_completed'] ?? [],
                'steps_remaining' => $progressMetadata['steps_remaining'] ?? [],
                'last_updated_at' => $progressMetadata['updated_at'] ?? null,
                'estimated_completion' => $this->estimateCompletion($simulation),
            ],
        ];

        return response()->json($response);
    }

    /**
     * Get user simulation history
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $query = SimulationHistory::where('user_id', $user->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Pagination
        $perPage = min($request->input('per_page', 10), 50);
        $simulations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'simulations' => SimulationResource::collection($simulations),
                'pagination' => [
                    'current_page' => $simulations->currentPage(),
                    'per_page' => $simulations->perPage(),
                    'total' => $simulations->total(),
                    'total_pages' => $simulations->lastPage(),
                    'has_next' => $simulations->hasMorePages(),
                    'has_prev' => $simulations->currentPage() > 1,
                ],
            ],
        ]);
    }

    /**
     * Check if user has quota available
     */
    protected function checkUserQuota(User $user): bool
    {
        // Reset daily count if it's a new day
        if ($user->last_simulation_date && !$user->last_simulation_date->isSameDay(today())) {
            $user->update([
                'daily_simulation_count' => 0,
                'last_simulation_date' => today(),
            ]);
        }

        $dailyLimit = $this->getDailyQuota($user);
        return $user->daily_simulation_count < $dailyLimit;
    }

    /**
     * Increment user simulation quota
     */
    protected function incrementUserQuota(User $user): void
    {
        $user->forceFill([
            'daily_simulation_count' => $user->daily_simulation_count + 1,
            'last_simulation_date' => today(),
        ])->save();
    }

    /**
     * Get daily quota based on user tier
     */
    protected function getDailyQuota(User $user): int
    {
        return match ($user->subscription_tier) {
            'premium' => 200,
            'enterprise' => 1000,
            default => 50,
        };
    }

    /**
     * Calculate simulation progress
     */
    protected function calculateProgress(SimulationHistory $simulation): int
    {
        if (is_array($simulation->progress_metadata) && isset($simulation->progress_metadata['percentage'])) {
            return (int) max(0, min(100, $simulation->progress_metadata['percentage']));
        }

        // Enhanced progress calculation for async processing
        return match ($simulation->status) {
            'pending' => 0,
            'processing' => $this->calculateProcessingProgress($simulation),
            'completed' => 100,
            'failed' => 0,
            default => 0,
        };
    }

    /**
     * Calculate progress for processing simulations
     */
    protected function calculateProcessingProgress(SimulationHistory $simulation): int
    {
        if (!$simulation->processing_started_at) {
            return 10; // Job dispatched but not started
        }

        $elapsed = now()->diffInSeconds($simulation->processing_started_at);
        
        // Estimate progress based on elapsed time
        // n8n workflow typically takes 60-120 seconds
        if ($elapsed < 30) {
            return 25; // Initial processing
        } elseif ($elapsed < 60) {
            return 50; // Mid processing
        } elseif ($elapsed < 90) {
            return 75; // Near completion
        } else {
            return 90; // Almost done
        }
    }

    /**
     * Estimate completion time
     */
    protected function estimateCompletion(SimulationHistory $simulation): ?string
    {
        if (is_array($simulation->progress_metadata)) {
            $estimated = $simulation->progress_metadata['estimated_completion'] ?? null;

            if ($estimated && !in_array($simulation->status, ['completed', 'failed'], true)) {
                return $estimated;
            }
        }

        if ($simulation->status === 'completed' || $simulation->status === 'failed') {
            return null;
        }

        if (!$simulation->processing_started_at) {
            // Job not started yet, estimate 2-3 minutes total
            return now()->addMinutes(2)->toIso8601String();
        }

        $elapsed = now()->diffInSeconds($simulation->processing_started_at);
        
        // Enhanced estimation based on n8n workflow timing
        if ($elapsed < 30) {
            $remaining = 90; // Still early, estimate 90 seconds remaining
        } elseif ($elapsed < 60) {
            $remaining = 60; // Mid-way, estimate 60 seconds remaining
        } elseif ($elapsed < 90) {
            $remaining = 30; // Near completion, estimate 30 seconds remaining
        } else {
            $remaining = 10; // Should be done soon, minimum 10 seconds
        }

        return now()->addSeconds($remaining)->toIso8601String();
    }

    /**
     * Build default progress metadata payload.
     */
    protected function buildInitialProgressMetadata(string $currentStep = 'queued'): array
    {
        return [
            'percentage' => 0,
            'current_step' => $currentStep,
            'steps_completed' => [],
            'steps_remaining' => [],
            'estimated_completion' => now()->addSeconds(120)->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Restore guest session data to authenticated user
     */
    public function fromGuest(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = Auth::user();
        $sessionId = $request->session_id;

        // Get and validate guest session data
        $formData = $this->guestSessionRepository->getValidatedFormData($sessionId);

        if (!$formData) {
            return response()->json([
                'message' => 'Guest session not found or invalid',
                'error' => 'SESSION_NOT_FOUND',
            ], 404);
        }

        // Restore the data to user
        $restoredData = $this->guestSessionRepository->restoreToUser($sessionId, $user->id);

        if (!$restoredData) {
            return response()->json([
                'message' => 'Failed to restore guest session',
                'error' => 'RESTORE_FAILED',
            ], 500);
        }

        // Log guest session restoration
        $this->auditLogService->logGuestSessionRestoration($user, $request, $sessionId);

        // Here you would typically queue the simulation processing
        // For now, we'll just return the restored data
        return response()->json([
            'message' => 'Guest session restored successfully',
            'form_data' => $restoredData,
            'user' => $user,
        ]);
    }

    /**
     * Save guest session data
     */
    public function saveGuestSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'form_data' => 'required|array',
        ]);

        $sessionId = $request->session_id;
        $formData = $request->form_data;

        // Validate form data structure
        if (!$this->guestSessionRepository->validateFormData($formData)) {
            return response()->json([
                'message' => 'Invalid form data structure',
                'error' => 'INVALID_FORM_DATA',
            ], 400);
        }

        // Store guest session
        $guestSession = $this->guestSessionRepository->store($sessionId, $formData);

        return response()->json([
            'message' => 'Guest session saved successfully',
            'session_id' => $sessionId,
            'expires_at' => $guestSession->expires_at,
        ]);
    }

    /**
     * Get guest session data
     */
    public function getGuestSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $sessionId = $request->session_id;
        $guestSession = $this->guestSessionRepository->get($sessionId);

        if (!$guestSession) {
            return response()->json([
                'message' => 'Guest session not found',
                'error' => 'SESSION_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'session_id' => $sessionId,
            'form_data' => $guestSession->form_data,
            'expires_at' => $guestSession->expires_at,
            'remaining_time' => $guestSession->getRemainingTime(),
        ]);
    }

    /**
     * Delete guest session
     */
    public function deleteGuestSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $sessionId = $request->session_id;
        $deleted = $this->guestSessionRepository->delete($sessionId);

        if (!$deleted) {
            return response()->json([
                'message' => 'Guest session not found',
                'error' => 'SESSION_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'message' => 'Guest session deleted successfully',
        ]);
    }

    /**
     * Get guest session statistics (admin only)
     */
    public function getGuestSessionStats(): JsonResponse
    {
        $stats = $this->guestSessionRepository->getStats();

        return response()->json([
            'guest_session_stats' => $stats,
        ]);
    }

    /**
     * Export simulation results
     */
    public function export(Request $request, $id): JsonResponse
    {
        $simulation = SimulationHistory::find($id);

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found',
                'error' => 'NOT_FOUND',
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if (!$user || ($simulation->user_id && $simulation->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        // Validate simulation is completed
        if ($simulation->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Simulation must be completed before export',
                'error' => 'SIMULATION_NOT_COMPLETED',
            ], 400);
        }

        // Validate export format
        $format = $request->input('format', 'pdf');
        if (!in_array($format, ['pdf', 'docx', 'json'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid export format. Supported: pdf, docx, json',
                'error' => 'INVALID_FORMAT',
            ], 400);
        }

        try {
            // Prepare export options
            $options = [
                'sections' => $request->input('sections', [
                    'product_overview',
                    'ingredients',
                    'market_analysis',
                    'pricing',
                    'references',
                    'marketing',
                ]),
            ];

            // Generate export based on format
            $result = match ($format) {
                'pdf' => $this->exportService->exportPdf($simulation, $options),
                'docx' => $this->exportService->exportWord($simulation, $options),
                'json' => $this->exportService->exportJson($simulation, $options),
            };

            Log::info('Simulation exported', [
                'simulation_id' => $simulation->id,
                'user_id' => $user->id,
                'format' => $format,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export generated successfully',
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Export failed', [
                'simulation_id' => $simulation->id,
                'format' => $format,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate export',
                'error' => 'EXPORT_FAILED',
            ], 500);
        }
    }

    /**
     * Regenerate simulation with alternative results
     */
    public function regenerate(Request $request, $id): JsonResponse
    {
        $simulation = SimulationHistory::find($id);

        if (!$simulation) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation not found',
                'error' => 'NOT_FOUND',
            ], 404);
        }

        // Check authorization
        $user = Auth::user();
        if (!$user || ($simulation->user_id && $simulation->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'error' => 'UNAUTHORIZED',
            ], 403);
        }

        // Validate original simulation is completed
        if ($simulation->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Can only regenerate completed simulations',
                'error' => 'SIMULATION_NOT_COMPLETED',
            ], 400);
        }

        // Check user quota for regeneration
        if (!$this->checkUserQuota($user)) {
            $this->auditLogService->logRateLimitHit($user, $request, 'api.simulations.regenerate');

            return response()->json([
                'success' => false,
                'message' => 'Daily simulation quota exceeded',
                'error' => 'QUOTA_EXCEEDED',
                'quota_info' => [
                    'daily_limit' => $this->getDailyQuota($user),
                    'used_today' => $user->daily_simulation_count,
                    'tier' => $user->subscription_tier,
                ],
            ], 429);
        }

        try {
            // Create new simulation with same input data
            $newSimulation = SimulationHistory::create([
                'user_id' => $user->id,
                'guest_session_id' => $simulation->guest_session_id,
                'input_data' => $simulation->input_data,
                'status' => 'pending',
                'progress_metadata' => $this->buildInitialProgressMetadata('regeneration_queued'),
            ]);

            $this->simulationAnalyticsService->recordSimulationRequested(true);

            // Add regeneration context for n8n
            $regenerationOptions = [
                'is_regeneration' => true,
                'original_simulation_id' => $simulation->id,
                'variation_type' => $request->input('variation_type', 'alternative'), // alternative, improved, etc.
            ];

            // Trigger n8n workflow with regeneration context
            try {
                $this->n8nService->triggerWorkflow($newSimulation, [
                    'regeneration' => $regenerationOptions,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to trigger regeneration workflow', [
                    'simulation_id' => $newSimulation->id,
                    'original_id' => $simulation->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to start regeneration',
                    'error' => 'WORKFLOW_TRIGGER_FAILED',
                ], 500);
            }

            // Increment user quota
            $this->incrementUserQuota($user);

            // Log regeneration
            Log::info('Simulation regenerated', [
                'original_id' => $simulation->id,
                'new_id' => $newSimulation->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Regeneration started successfully',
                'data' => new SimulationResource($newSimulation),
                'original_simulation_id' => $simulation->id,
            ], 202);

        } catch (\Exception $e) {
            Log::error('Regeneration failed', [
                'original_id' => $simulation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate simulation',
                'error' => 'REGENERATION_FAILED',
            ], 500);
        }
    }
}
