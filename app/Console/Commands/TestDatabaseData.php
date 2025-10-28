<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SimulationHistory;

class TestDatabaseData extends Command
{
    protected $signature = 'test:database-data';
    protected $description = 'Test database data structure and content';

    public function handle()
    {
        $this->info('=== DATABASE DATA VERIFICATION ===');
        $this->newLine();

        try {
            // Check total simulations
            $totalSimulations = SimulationHistory::count();
            $this->info("Total simulations in database: {$totalSimulations}");
            $this->newLine();

            if ($totalSimulations > 0) {
                // Get latest simulation
                $latestSimulation = SimulationHistory::latest()->first();
                
                $this->info('=== LATEST SIMULATION DATA ===');
                $this->line("ID: {$latestSimulation->id}");
                $this->line("Status: {$latestSimulation->status}");
                $this->line("Created: {$latestSimulation->created_at}");
                $this->line("Has output_data: " . (!is_null($latestSimulation->output_data) ? 'Yes' : 'No'));
                $this->newLine();

                if ($latestSimulation->output_data) {
                    $outputData = $latestSimulation->output_data;
                    $this->info('=== OUTPUT DATA STRUCTURE ===');
                    $this->line("Available keys: " . implode(', ', array_keys($outputData)));
                    $this->newLine();

                    // Check for new market analysis fields
                    $this->info('=== MARKET ANALYSIS FIELDS CHECK ===');
                    $this->line("Has market_potential: " . (isset($outputData['market_potential']) ? 'Yes' : 'No'));
                    $this->line("Has key_trends: " . (isset($outputData['key_trends']) ? 'Yes' : 'No'));
                    $this->line("Has marketing_copywriting: " . (isset($outputData['marketing_copywriting']) ? 'Yes' : 'No'));
                    $this->newLine();

                    // If market_potential exists, show structure
                    if (isset($outputData['market_potential'])) {
                        $this->info('=== MARKET_POTENTIAL STRUCTURE ===');
                        $marketPotential = $outputData['market_potential'];
                        $this->line("Keys: " . implode(', ', array_keys($marketPotential)));
                        
                        if (isset($marketPotential['total_addressable_market'])) {
                            $tam = $marketPotential['total_addressable_market'];
                            $this->line("TAM - Keys: " . implode(', ', array_keys($tam)));
                            $this->line("TAM - Market Size: " . ($tam['estimated_size'] ?? 'N/A'));
                            $this->line("TAM - Market Value: " . ($tam['value_idr'] ?? 'N/A'));
                        }
                        
                        if (isset($marketPotential['revenue_projections'])) {
                            $revenue = $marketPotential['revenue_projections'];
                            $this->line("Revenue - Monthly Units: " . ($revenue['monthly_units'] ?? 'N/A'));
                            $this->line("Revenue - Monthly Revenue: " . ($revenue['monthly_revenue'] ?? 'N/A'));
                            $this->line("Revenue - Yearly Revenue: " . ($revenue['yearly_revenue'] ?? 'N/A'));
                        }
                        $this->newLine();
                    }

                    // If key_trends exists, show structure
                    if (isset($outputData['key_trends'])) {
                        $this->info('=== KEY_TRENDS STRUCTURE ===');
                        $keyTrends = $outputData['key_trends'];
                        $this->line("Keys: " . implode(', ', array_keys($keyTrends)));
                        
                        if (isset($keyTrends['trending_ingredients'])) {
                            $ingredients = $keyTrends['trending_ingredients'];
                            $this->line("Trending Ingredients Count: " . count($ingredients));
                            if (count($ingredients) > 0) {
                                $this->line("First ingredient: " . ($ingredients[0]['name'] ?? 'N/A'));
                            }
                        }
                        $this->newLine();
                    }

                    // If marketing_copywriting exists, show structure
                    if (isset($outputData['marketing_copywriting'])) {
                        $this->info('=== MARKETING_COPYWRITING STRUCTURE ===');
                        $copywriting = $outputData['marketing_copywriting'];
                        $this->line("Keys: " . implode(', ', array_keys($copywriting)));
                        
                        if (isset($copywriting['headline'])) {
                            $this->line("Headline: " . substr($copywriting['headline'], 0, 50) . "...");
                        }
                        
                        if (isset($copywriting['social_media_captions'])) {
                            $captions = $copywriting['social_media_captions'];
                            $this->line("Social Media Captions Count: " . count($captions));
                        }
                        $this->newLine();
                    }

                    // Show sample of existing fields for comparison
                    $this->info('=== EXISTING FIELDS SAMPLE ===');
                    $existingFields = ['product_names', 'selected_name', 'taglines', 'description', 'ingredients_analysis', 'market_analysis'];
                    foreach ($existingFields as $field) {
                        if (isset($outputData[$field])) {
                            $this->line("✓ {$field}: " . (is_array($outputData[$field]) ? 'Array' : 'String'));
                        } else {
                            $this->line("✗ {$field}: Missing");
                        }
                    }

                    // Show all available fields
                    $this->newLine();
                    $this->info('=== ALL AVAILABLE FIELDS ===');
                    foreach (array_keys($outputData) as $key) {
                        $this->line("- {$key}");
                    }
                } else {
                    $this->warn('No output_data found for latest simulation.');
                }
            } else {
                $this->warn('No simulations found in database.');
            }

            $this->newLine();
            $this->info('=== VERIFICATION COMPLETE ===');

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
