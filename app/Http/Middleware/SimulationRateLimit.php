<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuthTokenService;
use Symfony\Component\HttpFoundation\Response;

class SimulationRateLimit
{
    protected $authTokenService;

    public function __construct(AuthTokenService $authTokenService)
    {
        $this->authTokenService = $authTokenService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Check if user can perform simulation
        if (!$this->authTokenService->canPerformSimulation($user)) {
            $tier = $user->subscription_tier;
            $dailyCount = $user->daily_simulation_count;
            
            $limits = [
                'free' => 3,
                'premium' => 20,
                'enterprise' => 100,
            ];

            $limit = $limits[$tier] ?? 3;

            return response()->json([
                'message' => 'Daily simulation limit exceeded',
                'error' => 'RATE_LIMIT_EXCEEDED',
                'details' => [
                    'current_count' => $dailyCount,
                    'daily_limit' => $limit,
                    'subscription_tier' => $tier,
                    'reset_time' => now()->addDay()->startOfDay()->toISOString(),
                ],
            ], 429);
        }

        // Process the request
        $response = $next($request);

        // If the request was successful, increment the simulation count
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->authTokenService->incrementSimulationCount($user);
        }

        return $response;
    }
}
