<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\GuestSessionRepository;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SimulationController extends Controller
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
}
