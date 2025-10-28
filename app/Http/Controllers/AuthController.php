<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Sanctum\HasApiTokens;
use App\Services\AuthTokenService;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'company' => $request->company,
            'terms_accepted' => false,
            'terms_accepted_at' => null,
            'subscription_tier' => 'free',
            'permissions' => ['basic'],
            'daily_simulation_count' => 0,
            'last_simulation_date' => null,
        ]);

        // Auto-login after registration
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        // Log registration event
        $this->auditLogService->logRegistration($user, $request);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        // Rate limiting
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        
        // Create token with appropriate expiration based on remember flag
        $expiration = $request->remember ? now()->addDays(30) : now()->addHours(2);
        $token = $user->createToken('auth-token', ['*'], $expiration)->plainTextToken;

        // Update last simulation date if needed
        if (!$user->last_simulation_date) {
            $user->update(['last_simulation_date' => now()]);
        }

        // Log login event
        $this->auditLogService->logLogin($user, $request);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        // Log logout event
        $this->auditLogService->logLogout($user, $request);

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        // Log logout all event
        $this->auditLogService->logLogout($user, $request, true);

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            // Log password reset request
            $this->auditLogService->logPasswordResetRequest($request->email, $request);

            return response()->json([
                'message' => 'Password reset link sent to your email',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Find user and log password reset completion
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $this->auditLogService->logPasswordReset($user, $request);
            }

            return response()->json([
                'message' => 'Password has been reset successfully',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();

            $isNewUser = false;
            if ($user) {
                // Update existing user with Google data
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'subscription_tier' => 'free',
                    'permissions' => ['basic'],
                    'daily_simulation_count' => 0,
                    'last_simulation_date' => now(),
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                ]);
                $isNewUser = true;
            }

            // Log user in with session (remember me = true)
            Auth::login($user, true);

            // Log Google OAuth event
            $this->auditLogService->logGoogleOAuth($user, request(), $isNewUser);

            return redirect()->to('/simulator')->with('success', 'Login dengan Google berhasil!');

        } catch (\Exception $e) {
            return redirect()->to('/login')->with('error', 'Login dengan Google gagal: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        try {
            $authTokenService = new AuthTokenService();
            $tokens = $authTokenService->refreshAccessToken($request->user(), $request->refresh_token);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in' => $tokens['expires_in'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
