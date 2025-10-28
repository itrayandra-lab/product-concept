<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\GuestSessionRepository;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestSessionController extends Controller
{
    protected $guestSessionRepository;
    protected $auditLogService;

    public function __construct(
        GuestSessionRepository $guestSessionRepository,
        AuditLogService $auditLogService
    ) {
        $this->guestSessionRepository = $guestSessionRepository;
        $this->auditLogService = $auditLogService;
    }

    /**
     * Save guest session form data
     * POST /api/guest/save-form-data
     */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'form_data' => 'required|array',
            'form_step' => 'nullable|string',
            'completed_steps' => 'nullable|array',
        ]);

        $sessionId = $request->header('X-Guest-Session') 
                     ?? $request->input('session_id') 
                     ?? 'guest_' . time() . '_' . bin2hex(random_bytes(8));
        
        $formData = $request->input('form_data');
        $formStep = $request->input('form_step');
        $completedSteps = $request->input('completed_steps');

        try {
            // Store guest session with progress tracking
            $guestSession = $this->guestSessionRepository->store(
                $sessionId,
                $formData,
                $formStep,
                $completedSteps
            );

            return response()->json([
                'success' => true,
                'message' => 'Form data saved successfully',
                'data' => [
                    'guest_session_id' => $sessionId,
                    'form_progress' => $guestSession->form_progress,
                    'completed_steps' => $guestSession->completed_steps,
                    'expires_at' => $guestSession->expires_at->toIso8601String(),
                    'remaining_time' => $guestSession->getRemainingTime(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save form data',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get guest session data
     * GET /api/guest/session/{session_id}
     */
    public function show(string $sessionId): JsonResponse
    {
        $guestSession = $this->guestSessionRepository->get($sessionId);

        if (!$guestSession) {
            return response()->json([
                'success' => false,
                'message' => 'Guest session not found or expired',
                'errors' => ['SESSION_NOT_FOUND'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest session retrieved successfully',
            'data' => [
                'session_id' => $sessionId,
                'form_data' => $guestSession->form_data,
                'form_step' => $guestSession->form_step,
                'form_progress' => $guestSession->form_progress,
                'completed_steps' => $guestSession->completed_steps,
                'expires_at' => $guestSession->expires_at->toIso8601String(),
                'remaining_time' => $guestSession->getRemainingTime(),
            ],
        ], 200);
    }

    /**
     * Delete guest session
     * DELETE /api/guest/session/{session_id}
     */
    public function destroy(string $sessionId): JsonResponse
    {
        $deleted = $this->guestSessionRepository->delete($sessionId);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Guest session not found',
                'errors' => ['SESSION_NOT_FOUND'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Guest session deleted successfully',
        ], 200);
    }

    /**
     * Restore guest session to authenticated user and generate simulation
     * POST /api/simulations/generate-from-guest
     * Requires authentication
     */
    public function generateFromGuest(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = Auth::user();
        $sessionId = $request->input('session_id');

        if (!$this->hasQuotaAvailable($user)) {
            $this->auditLogService->logRateLimitHit($user, $request, 'api.simulations.generate-from-guest');
            Log::info('generate-from-guest quota block', [
                'user_id' => $user?->id,
                'count' => $user?->daily_simulation_count,
                'limit' => $this->getDailyQuota($user),
            ]);

            return response()->json([
                'message' => 'Daily simulation quota exceeded',
                'error' => 'QUOTA_EXCEEDED',
                'details' => ['MAX_DAILY_SIMULATIONS_REACHED'],
                'quota_info' => [
                    'daily_limit' => $this->getDailyQuota($user),
                    'used_today' => $user->daily_simulation_count,
                    'tier' => $user->subscription_tier,
                ],
            ], 429);
        }

        // Get and validate guest session data
        $formData = $this->guestSessionRepository->getValidatedFormData($sessionId);

        if (!$formData) {
            return response()->json([
                'success' => false,
                'message' => 'Guest session not found, expired, or invalid',
                'errors' => ['SESSION_NOT_FOUND_OR_INVALID'],
            ], 404);
        }

        // Restore the data to user
        $restoredData = $this->guestSessionRepository->restoreToUser($sessionId, $user->id);

        if (!$restoredData) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore guest session',
                'errors' => ['RESTORE_FAILED'],
            ], 500);
        }

        // Log guest session restoration
        $this->auditLogService->logGuestSessionRestoration($user, $request, $sessionId);

        // TODO: Queue simulation processing
        // For now, just return the restored data
        
        return response()->json([
            'success' => true,
            'message' => 'Form data berhasil dipulihkan! Simulasi sedang diproses.',
            'data' => [
                'form_data' => $restoredData,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 200);
    }

    /**
     * Get guest session statistics (admin only)
     * GET /api/guest/stats
     */
    public function stats(): JsonResponse
    {
        // TODO: Add admin authorization check
        
        $stats = $this->guestSessionRepository->getStats();

        return response()->json([
            'success' => true,
            'message' => 'Guest session statistics retrieved successfully',
            'data' => $stats,
        ], 200);
    }

    protected function hasQuotaAvailable(User $user): bool
    {
        if ($user->last_simulation_date && !$user->last_simulation_date->isSameDay(today())) {
            $user->update([
                'daily_simulation_count' => 0,
                'last_simulation_date' => today(),
            ]);
        }

        return $user->daily_simulation_count < $this->getDailyQuota($user);
    }

    protected function getDailyQuota(User $user): int
    {
        return match ($user->subscription_tier) {
            'premium' => 200,
            'enterprise' => 1000,
            default => 50,
        };
    }
}

