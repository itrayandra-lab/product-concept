<?php

namespace Tests\Unit;

use App\Models\SimulationHistory;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_exports_completed_simulations_to_json(): void
    {
        Storage::fake('local');

        $simulation = SimulationHistory::factory()->create([
            'status' => 'completed',
        ]);

        /** @var ExportService $service */
        $service = $this->app->make(ExportService::class);

        $result = $service->exportJson($simulation);

        $this->assertTrue($result['success']);
        $this->assertStringEndsWith('.json', $result['filename']);
        Storage::disk('local')->assertExists('exports/' . $result['filename']);
    }

    /** @test */
    public function it_deletes_expired_export_files(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('exports/old.json', '{}');

        Carbon::setTestNow(now()->addHours(48));

        /** @var ExportService $service */
        $service = $this->app->make(ExportService::class);

        $deleted = $service->cleanupExpiredExports();

        $this->assertSame(1, $deleted);
        Storage::disk('local')->assertMissing('exports/old.json');

        Carbon::setTestNow();
    }
}
