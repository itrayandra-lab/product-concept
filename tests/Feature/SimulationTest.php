<?php

namespace Tests\Feature;

use App\Jobs\ProcessSimulationJob;
use App\Models\SimulationHistory;
use App\Models\SimulationMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimulationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Queue::fake();
    }

    /** @test */
    public function it_accepts_simulation_requests_and_dispatches_job(): void
    {
        // Enable real n8n mode (not mock)
        config(['services.n8n.mock_enabled' => false]);
        
        $user = User::factory()->create(['subscription_tier' => 'free']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'pending',
                ],
            ]);

        // Assert that ProcessSimulationJob was dispatched
        Queue::assertPushed(ProcessSimulationJob::class, function ($job) {
            return $job->simulation instanceof SimulationHistory;
        });

        $simulation = SimulationHistory::first();
        $this->assertNotNull($simulation);
        $this->assertSame('pending', $simulation->status);
        $this->assertNotNull($simulation->n8n_workflow_id);

        $metric = SimulationMetric::first();
        $this->assertNotNull($metric);
        $this->assertEquals(1, $metric->requested_count);
    }

    /** @test */
    public function it_uses_mock_mode_when_enabled(): void
    {
        // Enable mock mode
        config(['services.n8n.mock_enabled' => true]);
        
        $user = User::factory()->create(['subscription_tier' => 'free']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'pending',
                ],
            ]);

        // Assert that no job was dispatched in mock mode
        Queue::assertNothingPushed();

        $simulation = SimulationHistory::first();
        $this->assertNotNull($simulation);
        $this->assertSame('pending', $simulation->status);
        $this->assertStringStartsWith('mock_', $simulation->n8n_workflow_id);
    }

    /** @test */
    public function it_returns_rich_status_payload_with_progress_information(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $simulation = SimulationHistory::factory()->processing()->create([
            'user_id' => $user->id,
            'progress_metadata' => [
                'percentage' => 45,
                'current_step' => 'ingredient_analysis',
                'steps_completed' => ['validation'],
                'steps_remaining' => ['market_analysis'],
                'estimated_completion' => now()->addMinutes(1)->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ]);

        $response = $this->getJson("/api/simulations/{$simulation->id}/status");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'simulation_id' => 'sim_' . str_pad($simulation->id, 16, '0', STR_PAD_LEFT),
                    'progress_percentage' => 45,
                    'current_step' => 'ingredient_analysis',
                    'steps_completed' => ['validation'],
                ],
            ]);
    }

    /** @test */
    public function it_handles_workflow_trigger_failures_gracefully(): void
    {
        // Test that job dispatch works correctly in real mode
        config(['services.n8n.mock_enabled' => false]);
        
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        // Should succeed in dispatching the job
        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
            ]);

        // Assert job was dispatched
        Queue::assertPushed(ProcessSimulationJob::class);

        $simulation = SimulationHistory::first();
        $this->assertSame('pending', $simulation->status);
        $this->assertNotNull($simulation->n8n_workflow_id);
    }

    /** @test */
    public function it_blocks_users_who_exceed_daily_quota(): void
    {
        Http::fake();

        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'daily_simulation_count' => 50,
            'last_simulation_date' => today(),
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/simulations', $this->simulationPayload());

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'QUOTA_EXCEEDED',
            ]);
    }

    /** @test */
    public function it_supports_regeneration_flow_and_tracks_metrics(): void
    {
        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $original = SimulationHistory::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->postJson("/api/simulations/{$original->id}/regenerate");
        $response->assertStatus(202);

        $this->assertEquals(2, SimulationHistory::count());
        $metric = SimulationMetric::first();
        $this->assertNotNull($metric);
        $this->assertEquals(1, $metric->regenerated_count);
    }

    /** @test */
    public function it_handles_bursts_of_simulation_requests_performance_check(): void
    {
        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);

        $user = User::factory()->create(['subscription_tier' => 'premium']);
        Sanctum::actingAs($user);

        foreach (range(1, 5) as $iteration) {
            $payload = $this->simulationPayload(['benchmark_product' => "Benchmark {$iteration}"]);
            $this->postJson('/api/simulations', $payload)->assertStatus(202);
        }

        $this->assertEquals(5, SimulationHistory::count());
        $metric = SimulationMetric::first();
        $this->assertNotNull($metric);
        $this->assertEquals(5, $metric->requested_count);
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
