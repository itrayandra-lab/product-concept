<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\GuestSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+1234567890',
            'company' => 'Test Company',
            'terms_accepted' => true,
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'company',
                    'subscription_tier',
                    'permissions',
                    'terms_accepted',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'subscription_tier' => 'free',
            'terms_accepted' => true,
        ]);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user_registered',
            'table_name' => 'users',
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
            'remember' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user_login',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user_logout',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_logout_all_devices()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out from all devices']);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'user_logout',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        
        // Create a refresh token
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;
        
        // Create an access token for authentication
        $accessToken = $user->createToken('access-token', ['*'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'refresh_token',
                'expires_in',
            ]);
    }

    public function test_guest_session_can_be_saved()
    {
        $sessionId = 'test-session-123';
        $formData = [
            'product_name' => 'Test Product',
            'target_demographic' => 'adults',
            'skin_type' => 'normal',
            'skin_concerns' => 'aging',
            'ingredients' => ['vitamin_c', 'hyaluronic_acid'],
            'product_type' => 'serum',
            'packaging_type' => 'dropper',
            'price_range' => 'premium',
            'brand_positioning' => 'luxury',
            'marketing_message' => 'Anti-aging serum',
            'target_market' => 'US',
            'regulatory_requirements' => 'FDA',
            'sustainability_goals' => 'eco-friendly',
            'innovation_focus' => 'scientific',
            'budget_constraints' => 'high',
            'timeline' => '6_months',
            'success_metrics' => 'sales',
            'competitive_analysis' => 'premium_brands',
        ];

        $response = $this->postJson('/api/guest/save-form-data', [
            'form_data' => $formData,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'guest_session_id',
                    'form_progress',
                    'expires_at',
                ],
            ]);

        $this->assertDatabaseHas('guest_sessions', [
            'session_id' => $response->json('data.guest_session_id'),
        ]);
    }

    public function test_guest_session_can_be_restored()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        
        $sessionId = 'test-session-123';
        $formData = [
            'product_name' => 'Test Product',
            'target_demographic' => 'adults',
            'skin_type' => 'normal',
            'skin_concerns' => 'aging',
            'ingredients' => ['vitamin_c', 'hyaluronic_acid'],
            'product_type' => 'serum',
            'packaging_type' => 'dropper',
            'price_range' => 'premium',
            'brand_positioning' => 'luxury',
            'marketing_message' => 'Anti-aging serum',
            'target_market' => 'US',
            'regulatory_requirements' => 'FDA',
            'sustainability_goals' => 'eco-friendly',
            'innovation_focus' => 'scientific',
            'budget_constraints' => 'high',
            'timeline' => '6_months',
            'success_metrics' => 'sales',
            'competitive_analysis' => 'premium_brands',
        ];

        // Save guest session first
        GuestSession::create([
            'session_id' => $sessionId,
            'form_data' => $formData,
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/simulations/generate-from-guest', [
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'form_data',
                    'user',
                ],
            ]);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'guest_session_restored',
            'user_id' => $user->id,
        ]);
    }

    public function test_rate_limiting_works()
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'daily_simulation_count' => 3, // At limit
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/simulations/generate-from-guest', [
            'session_id' => 'test-session',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'error',
                'details',
            ]);
    }

    public function test_validation_errors()
    {
        // Test registration validation
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'terms_accepted' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'terms_accepted']);

        // Test login validation
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
