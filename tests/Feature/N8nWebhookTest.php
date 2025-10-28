<?php

namespace Tests\Feature;

use App\Models\SimulationHistory;
use App\Models\SimulationMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class N8nWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_simulation_and_metrics_when_webhook_completes(): void
    {
        config([
            'services.whatsapp.enabled' => true,
            'services.whatsapp.business_number' => '628123456789',
        ]);

        $simulation = SimulationHistory::factory()->processing()->create([
            'n8n_workflow_id' => 'wf_test_123',
            'status' => 'processing',
            'progress_metadata' => [
                'percentage' => 40,
                'current_step' => 'ai_generation',
                'updated_at' => now()->toIso8601String(),
            ],
        ]);

        $payload = [
            'workflow_id' => 'wf_test_123',
            'status' => 'completed',
            'result_data' => [
                'product_name' => 'Radiant Glow Serum',
                'marketing_suggestions' => [
                    'key_selling_points' => ['Dermatologist tested'],
                ],
            ],
            'processing_time_seconds' => 65,
        ];

        $this->postJson('/api/n8n/webhook', $payload)
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $simulation->refresh();

        $this->assertSame('completed', $simulation->status);
        $this->assertNotEmpty($simulation->output_data['cta_whatsapp_url']);
        $this->assertEquals(100, $simulation->progress_metadata['percentage']);

        $metric = SimulationMetric::first();
        $this->assertNotNull($metric);
        $this->assertEquals(1, $metric->completed_count);
        $this->assertEquals(65, $metric->average_processing_seconds);
    }
}
