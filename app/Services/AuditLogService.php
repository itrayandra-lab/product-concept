<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log authentication events
     */
    public function logAuthEvent(
        string $eventType,
        ?User $user = null,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'table_name' => 'users',
            'record_id' => $user?->id,
            'old_values' => null,
            'new_values' => $user ? [
                'user_id' => $user->id,
                'email' => $user->email,
                'subscription_tier' => $user->subscription_tier,
            ] : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Log user registration
     */
    public function logRegistration(User $user, Request $request): AuditLog
    {
        return $this->logAuthEvent('user_registered', $user, $request, [
            'registration_method' => 'email',
            'terms_accepted' => $user->terms_accepted,
        ]);
    }

    /**
     * Log user login
     */
    public function logLogin(User $user, Request $request, string $method = 'email'): AuditLog
    {
        return $this->logAuthEvent('user_login', $user, $request, [
            'login_method' => $method,
            'remember_me' => $request->boolean('remember'),
        ]);
    }

    /**
     * Log user logout
     */
    public function logLogout(User $user, Request $request, bool $allDevices = false): AuditLog
    {
        return $this->logAuthEvent('user_logout', $user, $request, [
            'logout_all_devices' => $allDevices,
        ]);
    }

    /**
     * Log password reset request
     */
    public function logPasswordResetRequest(string $email, Request $request): AuditLog
    {
        return $this->logAuthEvent('password_reset_requested', null, $request, [
            'email' => $email,
        ]);
    }

    /**
     * Log password reset completion
     */
    public function logPasswordReset(User $user, Request $request): AuditLog
    {
        return $this->logAuthEvent('password_reset_completed', $user, $request);
    }

    /**
     * Log Google OAuth login
     */
    public function logGoogleOAuth(User $user, Request $request, bool $isNewUser = false): AuditLog
    {
        return $this->logAuthEvent('google_oauth_login', $user, $request, [
            'is_new_user' => $isNewUser,
            'provider' => 'google',
        ]);
    }

    /**
     * Log guest session restoration
     */
    public function logGuestSessionRestoration(User $user, Request $request, string $sessionId): AuditLog
    {
        return $this->logAuthEvent('guest_session_restored', $user, $request, [
            'guest_session_id' => $sessionId,
        ]);
    }

    /**
     * Log simulation rate limit hit
     */
    public function logRateLimitHit(User $user, Request $request, string $endpoint): AuditLog
    {
        return $this->logAuthEvent('rate_limit_hit', $user, $request, [
            'endpoint' => $endpoint,
            'subscription_tier' => $user->subscription_tier,
            'daily_simulation_count' => $user->daily_simulation_count,
        ]);
    }

    /**
     * Get audit logs for a user
     */
    public function getUserLogs(User $user, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs by event type
     */
    public function getLogsByEventType(string $eventType, int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('event_type', $eventType)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old audit logs (older than specified days)
     */
    public function cleanupOldLogs(int $days = 90): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}
