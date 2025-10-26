<?php

namespace App\Repositories;

use App\Models\GuestSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuestSessionRepository
{
    /**
     * Store guest session data with progress tracking
     */
    public function store(string $sessionId, array $formData, ?string $formStep = null, ?array $completedSteps = null): GuestSession
    {
        $progress = $this->calculateProgress($formData);
        
        $data = [
            'form_data' => $formData,
            'form_progress' => $progress,
            'expires_at' => now()->addHours(24),
            'updated_at' => now(),
        ];
        
        if ($formStep !== null) {
            $data['form_step'] = $formStep;
        }
        
        if ($completedSteps !== null) {
            $data['completed_steps'] = $completedSteps;
        }
        
        return GuestSession::updateOrCreate(
            ['session_id' => $sessionId],
            $data
        );
    }

    /**
     * Retrieve guest session data
     */
    public function get(string $sessionId): ?GuestSession
    {
        return GuestSession::where('session_id', $sessionId)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Delete guest session data
     */
    public function delete(string $sessionId): bool
    {
        return GuestSession::where('session_id', $sessionId)->delete() > 0;
    }

    /**
     * Restore guest session data to user
     */
    public function restoreToUser(string $sessionId, int $userId): ?array
    {
        $guestSession = $this->get($sessionId);
        
        if (!$guestSession) {
            return null;
        }

        // Store the form data for processing
        $formData = $guestSession->form_data;
        
        // Delete the guest session after restoration
        $this->delete($sessionId);

        return $formData;
    }

    /**
     * Clean up expired guest sessions
     */
    public function cleanupExpired(): int
    {
        return GuestSession::where('expires_at', '<=', now())->delete();
    }

    /**
     * Get guest session statistics
     */
    public function getStats(): array
    {
        $total = GuestSession::count();
        $active = GuestSession::where('expires_at', '>', now())->count();
        $expired = GuestSession::where('expires_at', '<=', now())->count();

        return [
            'total_sessions' => $total,
            'active_sessions' => $active,
            'expired_sessions' => $expired,
        ];
    }

    /**
     * Validate form data structure
     */
    public function validateFormData(array $formData): bool
    {
        $requiredFields = [
            'product_name',
            'target_demographic',
            'skin_type',
            'skin_concerns',
            'ingredients',
            'product_type',
            'packaging_type',
            'price_range',
            'brand_positioning',
            'marketing_message',
            'target_market',
            'regulatory_requirements',
            'sustainability_goals',
            'innovation_focus',
            'budget_constraints',
            'timeline',
            'success_metrics',
            'competitive_analysis',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($formData[$field]) || empty($formData[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get form data with validation
     */
    public function getValidatedFormData(string $sessionId): ?array
    {
        $guestSession = $this->get($sessionId);
        
        if (!$guestSession) {
            return null;
        }

        $formData = $guestSession->form_data;
        
        if (!$this->validateFormData($formData)) {
            return null;
        }

        return $formData;
    }
    
    /**
     * Calculate form progress based on completed fields
     */
    public function calculateProgress(array $formData): float
    {
        $totalFields = 18;
        $completedFields = 0;
        
        $fields = [
            'product_name',
            'target_demographic',
            'skin_type',
            'skin_concerns',
            'ingredients',
            'product_type',
            'packaging_type',
            'price_range',
            'brand_positioning',
            'marketing_message',
            'target_market',
            'regulatory_requirements',
            'sustainability_goals',
            'innovation_focus',
            'budget_constraints',
            'timeline',
            'success_metrics',
            'competitive_analysis',
        ];
        
        foreach ($fields as $field) {
            if (isset($formData[$field]) && !empty($formData[$field])) {
                $completedFields++;
            }
        }
        
        return round(($completedFields / $totalFields) * 100, 2);
    }
    
    /**
     * Get completed steps from form data
     */
    public function getCompletedSteps(array $formData): array
    {
        $steps = [];
        
        // Basic Info Step
        if (!empty($formData['product_name']) && !empty($formData['product_type'])) {
            $steps[] = 'basic';
        }
        
        // Target Market Step
        if (!empty($formData['target_demographic']) && !empty($formData['target_market'])) {
            $steps[] = 'target';
        }
        
        // Ingredients Step
        if (!empty($formData['ingredients']) && !empty($formData['skin_concerns'])) {
            $steps[] = 'ingredients';
        }
        
        // Advanced Step
        if (!empty($formData['regulatory_requirements']) && !empty($formData['budget_constraints'])) {
            $steps[] = 'advanced';
        }
        
        return $steps;
    }
}
