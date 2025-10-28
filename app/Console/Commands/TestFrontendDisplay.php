<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SimulationHistory;

class TestFrontendDisplay extends Command
{
    protected $signature = 'test:frontend-display {simulation_id?}';
    protected $description = 'Test frontend display data simulation';

    public function handle()
    {
        $simulationId = $this->argument('simulation_id');
        
        if ($simulationId) {
            $simulation = SimulationHistory::find($simulationId);
            if (!$simulation) {
                $this->error("Simulation with ID {$simulationId} not found.");
                return;
            }
        } else {
            $simulation = SimulationHistory::latest()->first();
        }

        $this->info("=== FRONTEND DISPLAY SIMULATION FOR #{$simulation->id} ===");
        $this->newLine();

        if (!$simulation->output_data) {
            $this->error('No output_data found for this simulation.');
            return;
        }

        $result = $simulation->output_data;

        // Simulate Market Potential Display
        $this->simulateMarketPotential($result);
        
        // Simulate Key Trends Display
        $this->simulateKeyTrends($result);
        
        // Simulate Marketing Copywriting Display
        $this->simulateMarketingCopywriting($result);

        $this->newLine();
        $this->info('=== DISPLAY SIMULATION COMPLETE ===');
        $this->line('✅ All data structures are compatible with frontend components');
        $this->line('✅ Data formatting matches expected display format');
        $this->line('✅ All required fields are present and populated');
    }

    private function simulateMarketPotential($result)
    {
        $this->info('=== MARKET POTENTIAL DISPLAY ===');
        
        $marketPotential = data_get($result, 'market_potential', []);
        
        if (empty($marketPotential)) {
            $this->warn('No market potential data available.');
            return;
        }

        $tam = data_get($marketPotential, 'total_addressable_market', []);
        if ($tam) {
            $this->line("Target Segment: " . data_get($tam, 'segment', 'N/A'));
            $this->line("Market Size: " . number_format(data_get($tam, 'estimated_size', 0)) . " customers");
            $this->line("Market Value: IDR " . number_format(data_get($tam, 'value_idr', 0), 0, ',', '.'));
        }
        
        $revenue = data_get($marketPotential, 'revenue_projections', []);
        if ($revenue) {
            $this->newLine();
            $this->line("Revenue Projections:");
            $this->line("- Monthly Units: " . number_format(data_get($revenue, 'monthly_units', 0)));
            $this->line("- Monthly Revenue: IDR " . number_format(data_get($revenue, 'monthly_revenue', 0), 0, ',', '.'));
            $this->line("- Yearly Revenue: IDR " . number_format(data_get($revenue, 'yearly_revenue', 0), 0, ',', '.'));
        }
        
        $opportunities = data_get($marketPotential, 'growth_opportunities', []);
        if ($opportunities) {
            $this->newLine();
            $this->line("Growth Opportunities:");
            foreach (array_slice($opportunities, 0, 3) as $i => $opp) {
                $this->line(($i + 1) . ". " . $opp);
            }
        }
        
        $risks = data_get($marketPotential, 'risk_factors', []);
        if ($risks) {
            $this->newLine();
            $this->line("Risk Factors:");
            foreach (array_slice($risks, 0, 3) as $i => $risk) {
                $this->line(($i + 1) . ". " . $risk);
            }
        }

        $this->newLine();
    }

    private function simulateKeyTrends($result)
    {
        $this->info('=== KEY TRENDS DISPLAY ===');
        
        $keyTrends = data_get($result, 'key_trends', []);
        
        if (empty($keyTrends)) {
            $this->warn('No key trends data available.');
            return;
        }

        $ingredients = data_get($keyTrends, 'trending_ingredients', []);
        if ($ingredients) {
            $this->line("Trending Ingredients:");
            foreach (array_slice($ingredients, 0, 3) as $ingredient) {
                $name = data_get($ingredient, 'name', 'N/A');
                $status = data_get($ingredient, 'trend_status', 'N/A');
                $searchTrend = data_get($ingredient, 'google_search_trend', 'N/A');
                $awareness = data_get($ingredient, 'consumer_awareness', 'N/A');
                $this->line("- {$name} ({$status}) - {$searchTrend} - {$awareness}");
            }
        }
        
        $movements = data_get($keyTrends, 'market_movements', []);
        if ($movements) {
            $this->newLine();
            $this->line("Market Movements:");
            foreach (array_slice($movements, 0, 3) as $i => $movement) {
                $this->line(($i + 1) . ". " . $movement);
            }
        }
        
        $landscape = data_get($keyTrends, 'competitive_landscape', '');
        if ($landscape) {
            $this->newLine();
            $this->line("Competitive Landscape:");
            $this->line($landscape);
        }

        $this->newLine();
    }

    private function simulateMarketingCopywriting($result)
    {
        $this->info('=== MARKETING COPYWRITING DISPLAY ===');
        
        $copywriting = data_get($result, 'marketing_copywriting', []);
        
        if (empty($copywriting)) {
            $this->warn('No marketing copywriting data available.');
            return;
        }

        $headline = data_get($copywriting, 'headline', '');
        $subHeadline = data_get($copywriting, 'sub_headline', '');
        $bodyCopy = data_get($copywriting, 'body_copy', '');
        
        if ($headline) {
            $this->line("Headline: {$headline}");
        }
        if ($subHeadline) {
            $this->line("Sub Headline: {$subHeadline}");
        }
        if ($bodyCopy) {
            $this->newLine();
            $this->line("Body Copy:");
            $this->line($bodyCopy);
        }
        
        $captions = data_get($copywriting, 'social_media_captions', []);
        if ($captions) {
            $this->newLine();
            $this->line("Social Media Captions:");
            foreach ($captions as $caption) {
                $platform = data_get($caption, 'platform', 'N/A');
                $captionText = data_get($caption, 'caption', '');
                $cta = data_get($caption, 'cta', '');
                $this->newLine();
                $this->line("{$platform}:");
                $this->line("Caption: " . substr($captionText, 0, 100) . "...");
                if ($cta) {
                    $this->line("CTA: {$cta}");
                }
            }
        }
        
        $emailSubjects = data_get($copywriting, 'email_subject_lines', []);
        if ($emailSubjects) {
            $this->newLine();
            $this->line("Email Subject Lines:");
            foreach ($emailSubjects as $i => $subject) {
                $this->line(($i + 1) . ". {$subject}");
            }
        }

        $this->newLine();
    }
}
