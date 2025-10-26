<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GuestSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class GuestFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Clear any existing guest sessions
        DB::table('guest_sessions')->truncate();
    }

    /** @test */
    public function it_can_save_guest_form_data()
    {
        $formData = $this->getSampleFormData();

        $response = $this->postJson('/api/guest/save-form-data', [
            'form_data' => $formData,
            'form_step' => 'basic',
            'completed_steps' => ['basic'],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Form data saved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'guest_session_id',
                    'form_progress',
                    'completed_steps',
                    'expires_at',
                    'remaining_time',
                ],
            ]);

        $this->assertDatabaseHas('guest_sessions', [
            'session_id' => $response->json('data.guest_session_id'),
        ]);
    }

    /** @test */
    public function it_can_retrieve_guest_session_data()
    {
        $formData = $this->getSampleFormData();
        $sessionId = 'guest_' . time() . '_test123';

        GuestSession::create([
            'session_id' => $sessionId,
            'form_data' => $formData,
            'form_step' => 'basic',
            'form_progress' => 50.00,
            'completed_steps' => ['basic', 'target'],
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->getJson("/api/guest/session/{$sessionId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'session_id' => $sessionId,
                    'form_step' => 'basic',
                    'form_progress' => 50.00,
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_guest_session()
    {
        $response = $this->getJson('/api/guest/session/nonexistent_session_id');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Guest session not found or expired',
            ]);
    }

    /** @test */
    public function it_can_delete_guest_session()
    {
        $sessionId = 'guest_' . time() . '_test456';
        
        GuestSession::create([
            'session_id' => $sessionId,
            'form_data' => $this->getSampleFormData(),
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->deleteJson("/api/guest/session/{$sessionId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Guest session deleted successfully',
            ]);

        $this->assertDatabaseMissing('guest_sessions', [
            'session_id' => $sessionId,
        ]);
    }

    /** @test */
    public function it_updates_existing_guest_session_on_duplicate_save()
    {
        $sessionId = 'guest_' . time() . '_test789';
        $initialData = $this->getSampleFormData();
        $initialData['product_name'] = 'Initial Product';

        // First save
        $this->postJson('/api/guest/save-form-data', [
            'session_id' => $sessionId,
            'form_data' => $initialData,
        ]);

        // Second save with updated data
        $updatedData = $initialData;
        $updatedData['product_name'] = 'Updated Product';

        $response = $this->postJson('/api/guest/save-form-data', [
            'session_id' => $sessionId,
            'form_data' => $updatedData,
            'form_step' => 'advanced',
        ]);

        $response->assertStatus(200);

        // Verify only one session exists with updated data
        $this->assertEquals(1, GuestSession::where('session_id', $sessionId)->count());
        
        $session = GuestSession::where('session_id', $sessionId)->first();
        $this->assertEquals('Updated Product', $session->form_data['product_name']);
        $this->assertEquals('advanced', $session->form_step);
    }

    /** @test */
    public function it_calculates_form_progress_correctly()
    {
        $partialData = [
            'product_name' => 'Test Product',
            'product_type' => 'Serum',
            'target_demographic' => 'Adults',
            // Only 3 out of 18 fields filled
        ];

        $response = $this->postJson('/api/guest/save-form-data', [
            'form_data' => $partialData,
        ]);

        $response->assertStatus(200);
        
        $progress = $response->json('data.form_progress');
        $this->assertGreaterThan(0, $progress);
        $this->assertLessThan(100, $progress);
    }

    /** @test */
    public function it_can_generate_simulation_from_guest_session()
    {
        $user = User::factory()->create();
        $sessionId = 'guest_' . time() . '_testgen';
        $formData = $this->getSampleFormData();

        GuestSession::create([
            'session_id' => $sessionId,
            'form_data' => $formData,
            'form_progress' => 100.00,
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/simulations/generate-from-guest', [
                'session_id' => $sessionId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Form data berhasil dipulihkan! Simulasi sedang diproses.',
            ]);

        // Verify guest session was deleted after restoration
        $this->assertDatabaseMissing('guest_sessions', [
            'session_id' => $sessionId,
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_generate_from_guest()
    {
        $sessionId = 'guest_' . time() . '_noauth';

        $response = $this->postJson('/api/simulations/generate-from-guest', [
            'session_id' => $sessionId,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_expired_guest_sessions()
    {
        $sessionId = 'guest_' . time() . '_expired';
        
        GuestSession::create([
            'session_id' => $sessionId,
            'form_data' => $this->getSampleFormData(),
            'expires_at' => now()->subHours(1), // Expired 1 hour ago
        ]);

        $response = $this->getJson("/api/guest/session/{$sessionId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Guest session not found or expired',
            ]);
    }

    /** @test */
    public function it_can_get_guest_session_statistics()
    {
        $user = User::factory()->create();

        // Create some test sessions
        GuestSession::create([
            'session_id' => 'active_session_1',
            'form_data' => $this->getSampleFormData(),
            'expires_at' => now()->addHours(24),
        ]);

        GuestSession::create([
            'session_id' => 'expired_session_1',
            'form_data' => $this->getSampleFormData(),
            'expires_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/guest/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sessions',
                    'active_sessions',
                    'expired_sessions',
                ],
            ]);
    }

    /** @test */
    public function it_validates_form_data_structure()
    {
        $invalidData = [
            'invalid_field' => 'value',
            // Missing required fields
        ];

        $response = $this->postJson('/api/guest/save-form-data', [
            'form_data' => $invalidData,
        ]);

        // Should still save (validation happens on restoration, not on save)
        $response->assertStatus(200);
    }

    /**
     * Get sample form data for testing
     */
    protected function getSampleFormData(): array
    {
        return [
            'product_name' => 'Test Skincare Product',
            'target_demographic' => 'Adults 25-40',
            'skin_type' => 'Combination',
            'skin_concerns' => ['Aging', 'Hydration'],
            'ingredients' => ['Niacinamide', 'Hyaluronic Acid'],
            'product_type' => 'Serum',
            'packaging_type' => 'Dropper Bottle',
            'price_range' => '100000-200000',
            'brand_positioning' => 'Premium',
            'marketing_message' => 'Revolutionary anti-aging formula',
            'target_market' => 'Indonesia',
            'regulatory_requirements' => 'BPOM Certified',
            'sustainability_goals' => 'Eco-friendly packaging',
            'innovation_focus' => 'Advanced delivery system',
            'budget_constraints' => '50000000',
            'timeline' => '6 months',
            'success_metrics' => 'Customer satisfaction > 90%',
            'competitive_analysis' => 'Mid-range competitor analysis',
        ];
    }
}

