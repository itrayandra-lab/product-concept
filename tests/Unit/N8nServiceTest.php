<?php

namespace Tests\Unit;

use App\Jobs\ProcessSimulationJob;
use App\Models\SimulationHistory;
use App\Services\N8nService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class N8nServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_job_when_triggering_workflow_in_real_mode(): void
    {
        Queue::fake();
        
        // Disable mock mode for this test to test real job dispatch
        config(['services.n8n.mock_enabled' => false]);

        $simulation = SimulationHistory::factory()->create([
            'status' => 'pending',
        ]);

        /** @var N8nService $service */
        $service = $this->app->make(N8nService::class);

        $result = $service->triggerWorkflow($simulation, [
            'regeneration' => ['is_regeneration' => true],
        ]);

        // Assert job was dispatched
        Queue::assertPushed(ProcessSimulationJob::class, function ($job) use ($simulation) {
            return $job->simulation->id === $simulation->id;
        });

        // Assert return value
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('dispatched', $result['message']);
        $this->assertNotNull($result['workflow_id']);

        // Assert simulation was updated
        $simulation->refresh();
        $this->assertEquals('pending', $simulation->status);
        $this->assertNotNull($simulation->n8n_workflow_id);
    }

    /** @test */
    public function it_uses_mock_mode_when_enabled(): void
    {
        Queue::fake();
        
        // Enable mock mode
        config(['services.n8n.mock_enabled' => true]);

        $simulation = SimulationHistory::factory()->create([
            'status' => 'pending',
        ]);

        /** @var N8nService $service */
        $service = $this->app->make(N8nService::class);

        $result = $service->triggerWorkflow($simulation);

        // Assert no job was dispatched in mock mode
        Queue::assertNothingPushed();

        // Assert return value
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('mock', $result['message']);
        $this->assertStringStartsWith('mock_', $result['workflow_id']);

        // Assert simulation was updated
        $simulation->refresh();
        $this->assertEquals('pending', $simulation->status);
        $this->assertStringStartsWith('mock_', $simulation->n8n_workflow_id);
    }
}
