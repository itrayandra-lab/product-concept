<?php

namespace Tests\Unit;

use App\Jobs\ProcessSimulationJob;
use App\Models\SimulationHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessSimulationJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_simulation_and_calls_n8n_webhook(): void
    {
        Http::fake([
            'https://n8n-gczfssttvtzs.nasgor.sumopod.my.id/webhook/lbf_product' => Http::response(['ok' => true], 200)
        ]);

        $user = User::factory()->create();
        $simulation = SimulationHistory::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'n8n_workflow_id' => 'test-workflow-123',
            'input_data' => [
                'fungsi_produk' => ['Melembabkan', 'Mencerahkan'],
                'bentuk_formulasi' => 'Serum',
                'target_gender' => 'Semua Gender',
                'target_usia' => ['25-30 tahun'],
                'deskripsi_formula' => 'Test formula',
                'bahan_aktif' => [
                    ['name' => 'Hyaluronic Acid', 'concentration' => 2.0],
                    ['name' => 'Niacinamide', 'concentration' => 5.0]
                ],
            ],
        ]);

        $job = new ProcessSimulationJob($simulation);
        $job->handle();

        // Assert simulation status was updated
        $simulation->refresh();
        $this->assertEquals('processing', $simulation->status);
        $this->assertNotNull($simulation->processing_started_at);

        // Assert HTTP request was made to n8n
        Http::assertSent(function ($request) {
            $body = $request->data();
            
            return $request->url() === 'https://n8n-gczfssttvtzs.nasgor.sumopod.my.id/webhook/lbf_product'
                && $request->method() === 'POST'
                && isset($body['workflow_id'])
                && isset($body['simulation_id'])
                && isset($body['form_data'])
                && $body['form_data']['fungsi_produk'] === ['Melembabkan', 'Mencerahkan'];
        });
    }

    /** @test */
    public function it_handles_n8n_request_failure(): void
    {
        Http::fake([
            'https://n8n-gczfssttvtzs.nasgor.sumopod.my.id/webhook/lbf_product' => Http::response(['error' => 'Internal Server Error'], 500)
        ]);

        $user = User::factory()->create();
        $simulation = SimulationHistory::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'n8n_workflow_id' => 'test-workflow-123',
            'input_data' => ['fungsi_produk' => ['Test']],
        ]);

        $job = new ProcessSimulationJob($simulation);

        $this->expectException(\Exception::class);
        $job->handle();

        // Assert simulation status was updated to failed
        $simulation->refresh();
        $this->assertEquals('failed', $simulation->status);
        $this->assertNotNull($simulation->error_details);
        $this->assertEquals('n8n_workflow_failed', $simulation->error_details['error']);
    }

    /** @test */
    public function it_handles_job_failure_callback(): void
    {
        $user = User::factory()->create();
        $simulation = SimulationHistory::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'n8n_workflow_id' => 'test-workflow-123',
        ]);

        $job = new ProcessSimulationJob($simulation);
        $job->failed(new \Exception('Test failure'));

        // Assert simulation status was updated to failed
        $simulation->refresh();
        $this->assertEquals('failed', $simulation->status);
        $this->assertNotNull($simulation->error_details);
        $this->assertEquals('job_failed', $simulation->error_details['error']);
        $this->assertEquals('Test failure', $simulation->error_details['message']);
    }
}
