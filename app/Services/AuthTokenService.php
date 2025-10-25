<?php

namespace App\Services;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthTokenService
{
    /**
     * Create access and refresh tokens for user
     */
    public function createTokens(User $user, bool $remember = false): array
    {
        // Create access token (2 hours)
        $accessToken = $user->createToken('access-token', ['*'], now()->addHours(2));
        
        // Create refresh token (30 days if remember, 7 days otherwise)
        $refreshExpiration = $remember ? now()->addDays(30) : now()->addDays(7);
        $refreshToken = $user->createToken('refresh-token', ['refresh'], $refreshExpiration);

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'expires_in' => 7200, // 2 hours in seconds
        ];
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(User $user, string $refreshToken): array
    {
        // Find the refresh token
        $token = PersonalAccessToken::findToken($refreshToken);
        
        if (!$token || !in_array('refresh', $token->abilities)) {
            throw new \Exception('Invalid refresh token');
        }

        // Check if token is expired
        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            throw new \Exception('Refresh token expired');
        }

        // Delete the old refresh token
        $token->delete();

        // Create new tokens
        $newTokens = $this->createTokens($user, $token->expires_at ? $token->expires_at->diffInDays(now()) > 7 : false);

        // Update user simulation counters
        $this->updateUserCounters($user);

        return $newTokens;
    }

    /**
     * Revoke all tokens for user
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Revoke specific token
     */
    public function revokeToken(User $user, string $tokenId): void
    {
        $user->tokens()->where('id', $tokenId)->delete();
    }

    /**
     * Update user simulation counters
     */
    private function updateUserCounters(User $user): void
    {
        $today = now()->toDateString();
        
        if ($user->last_simulation_date !== $today) {
            $user->update([
                'daily_simulation_count' => 0,
                'last_simulation_date' => $today,
            ]);
        }
    }

    /**
     * Check if user can perform simulation based on tier
     */
    public function canPerformSimulation(User $user): bool
    {
        $tier = $user->subscription_tier;
        $dailyCount = $user->daily_simulation_count;
        
        $limits = [
            'free' => 3,
            'premium' => 20,
            'enterprise' => 100,
        ];

        return $dailyCount < ($limits[$tier] ?? 3);
    }

    /**
     * Increment simulation count for user
     */
    public function incrementSimulationCount(User $user): void
    {
        $user->increment('daily_simulation_count');
    }

    /**
     * Get user's token usage statistics
     */
    public function getTokenStats(User $user): array
    {
        $tokens = $user->tokens();
        
        return [
            'total_tokens' => $tokens->count(),
            'active_tokens' => $tokens->where('expires_at', '>', now())->count(),
            'expired_tokens' => $tokens->where('expires_at', '<=', now())->count(),
            'daily_simulations' => $user->daily_simulation_count,
            'subscription_tier' => $user->subscription_tier,
        ];
    }
}
