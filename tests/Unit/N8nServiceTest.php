<?php

namespace Tests\Unit;

use App\Models\SimulationHistory;
use App\Services\N8nService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class N8nServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_includes_failover_and_context_when_triggering_workflow(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $simulation = SimulationHistory::factory()->create([
            'status' => 'pending',
        ]);

        /** @var N8nService $service */
        $service = $this->app->make(N8nService::class);

        $service->triggerWorkflow($simulation, [
            'regeneration' => ['is_regeneration' => true],
        ]);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return isset($body['failover'], $body['context']['regeneration'])
                && $body['failover']['enabled'] === true
                && in_array('openai', $body['failover']['provider_order'])
                && $body['context']['regeneration']['is_regeneration'] === true;
        });
    }
}
