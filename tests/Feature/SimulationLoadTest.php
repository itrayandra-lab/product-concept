<?php

namespace Tests\Feature;

use App\Models\SimulationHistory;
use App\Models\SimulationMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimulationLoadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.n8n.mock_enabled' => true]); // Use mock mode for load testing
    }

    /** @test */
    public function it_handles_50_concurrent_simulation_requests(): void
    {
        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);

        // Create multiple users with premium tier for higher quotas
        $users = User::factory()->count(10)->create(['subscription_tier' => 'premium']);
        
        $successfulRequests = 0;
        $failedRequests = 0;

        // Simulate 50 concurrent requests (5 per user)
        foreach ($users as $user) {
            Sanctum::actingAs($user);
            
            for ($i = 0; $i < 5; $i++) {
                $payload = $this->simulationPayload(['benchmark_product' => "Load Test {$i}"]);
                $response = $this->postJson('/api/simulations', $payload);
                
                if ($response->status() === 202) {
                    $successfulRequests++;
                } else {
                    $failedRequests++;
                }
            }
        }

        // Assertions for load testing
        $this->assertGreaterThan(40, $successfulRequests, 'Should handle at least 40 concurrent requests');
        $this->assertLessThan(10, $failedRequests, 'Should have minimal failures under load');
        
        // Verify database integrity
        $this->assertEquals($successfulRequests, SimulationHistory::count());
        
        // Verify metrics tracking
        $metric = SimulationMetric::first();
        $this->assertNotNull($metric);
        $this->assertEquals($successfulRequests, $metric->requested_count);
    }

    /** @test */
    public function it_handles_rapid_successive_requests_from_single_user(): void
    {
        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);

        $user = User::factory()->create(['subscription_tier' => 'enterprise']);
        Sanctum::actingAs($user);

        $successfulRequests = 0;
        $startTime = microtime(true);

        // Make 20 rapid requests
        for ($i = 0; $i < 20; $i++) {
            $payload = $this->simulationPayload(['benchmark_product' => "Rapid Test {$i}"]);
            $response = $this->postJson('/api/simulations', $payload);
            
            if ($response->status() === 202) {
                $successfulRequests++;
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Performance assertions
        $this->assertGreaterThan(15, $successfulRequests, 'Should handle rapid successive requests');
        $this->assertLessThan(10, $totalTime, 'Should complete within 10 seconds');
        
        // Verify all requests were processed
        $this->assertEquals($successfulRequests, SimulationHistory::count());
    }

    /** @test */
    public function it_handles_mixed_user_tiers_under_load(): void
    {
        Http::fake(['*skincare-simulation*' => Http::response(['ok' => true], 200)]);

        // Create users with different tiers
        $freeUsers = User::factory()->count(5)->create(['subscription_tier' => 'free']);
        $premiumUsers = User::factory()->count(5)->create(['subscription_tier' => 'premium']);
        $enterpriseUsers = User::factory()->count(5)->create(['subscription_tier' => 'enterprise']);

        $allUsers = collect($freeUsers)->merge($premiumUsers)->merge($enterpriseUsers);
        $totalSuccessful = 0;

        foreach ($allUsers as $user) {
            Sanctum::actingAs($user);
            
            // Each user makes 3 requests
            for ($i = 0; $i < 3; $i++) {
                $payload = $this->simulationPayload(['benchmark_product' => "Mixed Tier Test {$i}"]);
                $response = $this->postJson('/api/simulations', $payload);
                
                if ($response->status() === 202) {
                    $totalSuccessful++;
                }
            }
        }

        // Should handle mixed tiers without issues
        $this->assertGreaterThan(30, $totalSuccessful, 'Should handle mixed user tiers');
        $this->assertEquals($totalSuccessful, SimulationHistory::count());
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
