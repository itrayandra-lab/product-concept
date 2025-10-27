<?php

namespace App\Console\Commands;

use App\Models\SimulationHistory;
use Illuminate\Console\Command;

class FixSimulationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulation:fix-status {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix simulation status to completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        $simulation = SimulationHistory::find($id);
        
        if (!$simulation) {
            $this->error("Simulation #{$id} not found!");
            return 1;
        }
        
        $this->info("Found simulation #{$id}");
        $this->info("Current status: {$simulation->status}");
        
        // Update to completed
        $simulation->update([
            'status' => 'completed',
            'processing_completed_at' => now(),
        ]);
        
        $this->info("✅ Simulation #{$id} updated to completed status!");
        
        return 0;
    }
}
