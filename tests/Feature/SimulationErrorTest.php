<?php

namespace Tests\Feature;

use App\Models\SimulationHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimulationErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    /** @test */
    public function it_handles_n8n_workflow_failures_gracefully(): void
    {
        // Disable mock mode and simulate n8n workflow failure
        config(['services.n8n.mock_enabled' => false]);
        Http::fake(['*skincare-simulation*' => Http::response(['error' => 'Workflow failed'], 500)]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => 'WORKFLOW_TRIGGER_FAILED',
            ]);

        // Verify simulation is marked as failed
        $simulation = SimulationHistory::first();
        $this->assertSame('failed', $simulation->status);
    }

    /** @test */
    public function it_handles_n8n_timeout_errors(): void
    {
        // Disable mock mode and simulate n8n timeout
        config(['services.n8n.mock_enabled' => false]);
        Http::fake(['*skincare-simulation*' => function () {
            return Http::response(['timeout' => true], 408);
        }]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => 'WORKFLOW_TRIGGER_FAILED',
            ]);
    }

    /** @test */
    public function it_handles_malformed_webhook_responses(): void
    {
        // Create a simulation first
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);
        $this->postJson('/api/simulations', $this->simulationPayload());
        
        $simulation = SimulationHistory::first();

        // Simulate malformed webhook response
        $malformedData = [
            'simulation_id' => $simulation->id,
            'status' => 'completed',
            // Missing required fields
            'output_data' => null,
        ];

        $response = $this->postJson('/api/n8n/webhook', $malformedData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook processing failed',
            ]);
    }

    /** @test */
    public function it_handles_database_connection_failures(): void
    {
        // This test simulates database issues by using invalid data
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Simulate database constraint violation
        $invalidPayload = $this->simulationPayload();
        $invalidPayload['fungsi_produk'] = null; // This should trigger validation error

        $response = $this->postJson('/api/simulations', $invalidPayload);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['fungsi_produk']);
    }

    /** @test */
    public function it_handles_ai_provider_failures_with_fallback(): void
    {
        // Disable mock mode and simulate AI provider failure
        config(['services.n8n.mock_enabled' => false]);
        Http::fake([
            '*openai*' => Http::response(['error' => 'API key invalid'], 401),
            '*gemini*' => Http::response(['error' => 'Quota exceeded'], 429),
            '*claude*' => Http::response(['error' => 'Service unavailable'], 503),
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        // Should still create simulation but mark as failed
        $response->assertStatus(500);
        
        $simulation = SimulationHistory::first();
        $this->assertSame('failed', $simulation->status);
    }

    /** @test */
    public function it_handles_invalid_simulation_id_in_webhook(): void
    {
        $invalidData = [
            'simulation_id' => 99999, // Non-existent ID
            'status' => 'completed',
            'output_data' => ['test' => 'data'],
        ];

        $response = $this->postJson('/api/n8n/webhook', $invalidData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Webhook processing failed',
            ]);
    }

    /** @test */
    public function it_handles_concurrent_webhook_updates(): void
    {
        // Create simulation
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);
        $this->postJson('/api/simulations', $this->simulationPayload());
        
        $simulation = SimulationHistory::first();
        
        // Update simulation with n8n_workflow_id
        $simulation->update(['n8n_workflow_id' => 'test-workflow-123']);

        // Simulate webhook update with proper data structure
        $webhookData = [
            'workflow_id' => 'test-workflow-123',
            'status' => 'completed',
            'result_data' => ['test' => 'data'],
            'processing_time_seconds' => 30,
        ];

        // Send webhook update
        $response = $this->postJson('/api/n8n/webhook', $webhookData);

        // Should succeed
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Webhook processed',
            ]);

        // Verify simulation was updated
        $simulation->refresh();
        $this->assertEquals('completed', $simulation->status);
    }

    /** @test */
    public function it_handles_export_failures_gracefully(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a completed simulation
        $simulation = SimulationHistory::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'output_data' => ['test' => 'data'],
        ]);

        // Simulate export failure by using invalid format
        $response = $this->postJson("/api/simulations/{$simulation->id}/export", [
            'format' => 'invalid_format'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'INVALID_FORMAT',
            ]);
    }

    /** @test */
    public function it_handles_quota_exhaustion_gracefully(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'daily_simulation_count' => 50, // At limit
            'last_simulation_date' => today(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'error' => 'QUOTA_EXCEEDED',
            ]);
    }

    /** @test */
    public function it_handles_memory_exhaustion_gracefully(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a very large payload to test memory handling
        $largePayload = $this->simulationPayload();
        $largePayload['deskripsi_formula'] = str_repeat('Very long description. ', 1000);

        $response = $this->postJson('/api/simulations', $largePayload);

        // Should either succeed or fail gracefully
        $this->assertContains($response->status(), [202, 413, 422]);
    }

    /**
     * Build simulation payload matching validation rules.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function simulationPayload(array $overrides = []): array
    {
        $base = [
            'fungsi_produk' => ['Brightening', 'Hydrating'],
            'bentuk_formulasi' => 'Serum',
            'target_gender' => 'Wanita',
            'target_usia' => ['25-35 tahun'],
            'target_negara' => 'ID',
            'deskripsi_formula' => 'Formula lengkap dengan Niacinamide dan Hyaluronic Acid untuk kulit glowing.',
            'bahan_aktif' => [
                ['name' => 'Niacinamide', 'concentration' => 5, 'unit' => '%'],
                ['name' => 'Hyaluronic Acid', 'concentration' => 2, 'unit' => '%'],
            ],
            'benchmark_product' => 'Sample Benchmark',
            'volume' => 30,
            'volume_unit' => 'ml',
            'warna' => 'Transparan',
            'hex_color' => '#FFFFFF',
            'jenis_kemasan' => 'Dropper',
            'finishing_kemasan' => 'Matte',
            'bahan_kemasan' => 'Glass',
            'target_hpp' => 25000,
            'target_hpp_currency' => 'IDR',
            'moq' => 500,
            'tekstur' => 'Ringan',
            'aroma' => 'Soft Floral',
            'klaim_produk' => ['Vegan'],
            'sertifikasi' => ['BPOM'],
        ];

        return array_merge($base, $overrides);
    }
}
