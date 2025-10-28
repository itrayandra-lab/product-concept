<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SimulationHistory;

class TestFrontendData extends Command
{
    protected $signature = 'test:frontend-data {simulation_id?}';
    protected $description = 'Test frontend data structure for market analysis components';

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

        $this->info("=== FRONTEND DATA TEST FOR SIMULATION #{$simulation->id} ===");
        $this->newLine();

        if (!$simulation->output_data) {
            $this->error('No output_data found for this simulation.');
            return;
        }

        $result = $simulation->output_data;

        // Test Market Potential Component
        $this->testMarketPotential($result);
        
        // Test Key Trends Component
        $this->testKeyTrends($result);
        
        // Test Marketing Copywriting Component
        $this->testMarketingCopywriting($result);

        $this->newLine();
        $this->info('=== FRONTEND DATA TEST COMPLETE ===');
    }

    private function testMarketPotential($result)
    {
        $this->info('=== MARKET POTENTIAL COMPONENT TEST ===');
        
        $marketPotential = data_get($result, 'market_potential', []);
        
        if (empty($marketPotential)) {
            $this->warn('❌ market_potential data is missing');
            return;
        }

        $this->line('✅ market_potential data exists');

        // Test TAM data
        $tam = data_get($marketPotential, 'total_addressable_market', []);
        if (empty($tam)) {
            $this->warn('❌ total_addressable_market is missing');
        } else {
            $this->line('✅ total_addressable_market exists');
            $this->line("   - Segment: " . (data_get($tam, 'segment', 'N/A')));
            $this->line("   - Market Size: " . number_format(data_get($tam, 'estimated_size', 0)));
            $this->line("   - Market Value: IDR " . number_format(data_get($tam, 'value_idr', 0), 0, ',', '.'));
        }

        // Test Revenue Projections
        $revenue = data_get($marketPotential, 'revenue_projections', []);
        if (empty($revenue)) {
            $this->warn('❌ revenue_projections is missing');
        } else {
            $this->line('✅ revenue_projections exists');
            $this->line("   - Monthly Units: " . number_format(data_get($revenue, 'monthly_units', 0)));
            $this->line("   - Monthly Revenue: IDR " . number_format(data_get($revenue, 'monthly_revenue', 0), 0, ',', '.'));
            $this->line("   - Yearly Revenue: IDR " . number_format(data_get($revenue, 'yearly_revenue', 0), 0, ',', '.'));
        }

        // Test Growth Opportunities
        $opportunities = data_get($marketPotential, 'growth_opportunities', []);
        if (empty($opportunities)) {
            $this->warn('❌ growth_opportunities is missing');
        } else {
            $this->line('✅ growth_opportunities exists (' . count($opportunities) . ' items)');
            foreach (array_slice($opportunities, 0, 2) as $i => $opp) {
                $this->line("   " . ($i + 1) . ". " . substr($opp, 0, 60) . "...");
            }
        }

        // Test Risk Factors
        $risks = data_get($marketPotential, 'risk_factors', []);
        if (empty($risks)) {
            $this->warn('❌ risk_factors is missing');
        } else {
            $this->line('✅ risk_factors exists (' . count($risks) . ' items)');
        }

        $this->newLine();
    }

    private function testKeyTrends($result)
    {
        $this->info('=== KEY TRENDS COMPONENT TEST ===');
        
        $keyTrends = data_get($result, 'key_trends', []);
        
        if (empty($keyTrends)) {
            $this->warn('❌ key_trends data is missing');
            return;
        }

        $this->line('✅ key_trends data exists');

        // Test Trending Ingredients
        $ingredients = data_get($keyTrends, 'trending_ingredients', []);
        if (empty($ingredients)) {
            $this->warn('❌ trending_ingredients is missing');
        } else {
            $this->line('✅ trending_ingredients exists (' . count($ingredients) . ' items)');
            foreach (array_slice($ingredients, 0, 3) as $i => $ingredient) {
                $name = data_get($ingredient, 'name', 'N/A');
                $status = data_get($ingredient, 'trend_status', 'N/A');
                $searchTrend = data_get($ingredient, 'google_search_trend', 'N/A');
                $this->line("   " . ($i + 1) . ". {$name} ({$status}) - {$searchTrend}");
            }
        }

        // Test Market Movements
        $movements = data_get($keyTrends, 'market_movements', []);
        if (empty($movements)) {
            $this->warn('❌ market_movements is missing');
        } else {
            $this->line('✅ market_movements exists (' . count($movements) . ' items)');
        }

        // Test Competitive Landscape
        $landscape = data_get($keyTrends, 'competitive_landscape', '');
        if (empty($landscape)) {
            $this->warn('❌ competitive_landscape is missing');
        } else {
            $this->line('✅ competitive_landscape exists');
            $this->line("   - " . substr($landscape, 0, 80) . "...");
        }

        $this->newLine();
    }

    private function testMarketingCopywriting($result)
    {
        $this->info('=== MARKETING COPYWRITING COMPONENT TEST ===');
        
        $copywriting = data_get($result, 'marketing_copywriting', []);
        
        if (empty($copywriting)) {
            $this->warn('❌ marketing_copywriting data is missing');
            return;
        }

        $this->line('✅ marketing_copywriting data exists');

        // Test Headline
        $headline = data_get($copywriting, 'headline', '');
        if (empty($headline)) {
            $this->warn('❌ headline is missing');
        } else {
            $this->line('✅ headline exists');
            $this->line("   - " . substr($headline, 0, 60) . "...");
        }

        // Test Sub Headline
        $subHeadline = data_get($copywriting, 'sub_headline', '');
        if (empty($subHeadline)) {
            $this->warn('❌ sub_headline is missing');
        } else {
            $this->line('✅ sub_headline exists');
        }

        // Test Body Copy
        $bodyCopy = data_get($copywriting, 'body_copy', '');
        if (empty($bodyCopy)) {
            $this->warn('❌ body_copy is missing');
        } else {
            $this->line('✅ body_copy exists (' . strlen($bodyCopy) . ' characters)');
        }

        // Test Social Media Captions
        $captions = data_get($copywriting, 'social_media_captions', []);
        if (empty($captions)) {
            $this->warn('❌ social_media_captions is missing');
        } else {
            $this->line('✅ social_media_captions exists (' . count($captions) . ' platforms)');
            foreach ($captions as $caption) {
                $platform = data_get($caption, 'platform', 'N/A');
                $captionText = data_get($caption, 'caption', '');
                $this->line("   - {$platform}: " . substr($captionText, 0, 40) . "...");
            }
        }

        // Test Email Subject Lines
        $emailSubjects = data_get($copywriting, 'email_subject_lines', []);
        if (empty($emailSubjects)) {
            $this->warn('❌ email_subject_lines is missing');
        } else {
            $this->line('✅ email_subject_lines exists (' . count($emailSubjects) . ' subjects)');
            foreach (array_slice($emailSubjects, 0, 3) as $i => $subject) {
                $this->line("   " . ($i + 1) . ". " . substr($subject, 0, 50) . "...");
            }
        }

        $this->newLine();
    }
}
