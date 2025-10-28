<?php

namespace App\Services;

use App\Models\SimulationHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExportService
{
    protected string $exportPath = 'exports';
    protected int $expirationHours = 24;

    /**
     * Export simulation to PDF format
     *
     * @param SimulationHistory $simulation
     * @param array $options
     * @return array
     */
    public function exportPdf(SimulationHistory $simulation, array $options = []): array
    {
        try {
            // Validate simulation has output data
            if (!$simulation->output_data || $simulation->status !== 'completed') {
                throw new \Exception('Simulation must be completed before export');
            }

            // Prepare data for export
            $data = $this->prepareExportData($simulation, $options);

            // Generate PDF document
            $filename = $this->generateFilename($simulation, 'pdf');
            $filepath = "{$this->exportPath}/{$filename}";
            
            // Ensure export directory exists
            if (!Storage::disk('local')->exists($this->exportPath)) {
                Storage::disk('local')->makeDirectory($this->exportPath);
            }

            // Generate PDF content and save as binary
            $content = $this->generatePdfContent($data);
            Storage::disk('local')->put($filepath, $content);

            $downloadUrl = $this->generateDownloadUrl($filename);
            $expiresAt = now()->addHours($this->expirationHours);

            Log::info('PDF export created', [
                'simulation_id' => $simulation->id,
                'filename' => $filename,
                'expires_at' => $expiresAt,
            ]);

            return [
                'success' => true,
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'format' => 'pdf',
                'file_size_bytes' => Storage::disk('local')->size($filepath),
                'expires_at' => $expiresAt->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error('PDF export failed', [
                'simulation_id' => $simulation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Export simulation to Word format
     *
     * @param SimulationHistory $simulation
     * @param array $options
     * @return array
     */
    public function exportWord(SimulationHistory $simulation, array $options = []): array
    {
        try {
            // Validate simulation has output data
            if (!$simulation->output_data || $simulation->status !== 'completed') {
                throw new \Exception('Simulation must be completed before export');
            }

            // Prepare data for export
            $data = $this->prepareExportData($simulation, $options);

            // Generate Word document directly to file
            $filename = $this->generateFilename($simulation, 'docx');
            $filepath = "{$this->exportPath}/{$filename}";
            
            // Ensure export directory exists
            if (!Storage::disk('local')->exists($this->exportPath)) {
                Storage::disk('local')->makeDirectory($this->exportPath);
            }

            // Get the full storage path
            $fullPath = Storage::disk('local')->path($filepath);
            
            // Generate Word document directly to storage
            $this->generateWordDocument($data, $fullPath);

            $downloadUrl = $this->generateDownloadUrl($filename);
            $expiresAt = now()->addHours($this->expirationHours);

            Log::info('Word export created', [
                'simulation_id' => $simulation->id,
                'filename' => $filename,
                'expires_at' => $expiresAt,
            ]);

            return [
                'success' => true,
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'format' => 'docx',
                'file_size_bytes' => Storage::disk('local')->size($filepath),
                'expires_at' => $expiresAt->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error('Word export failed', [
                'simulation_id' => $simulation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Export simulation to image format (PNG)
     *
     * @param SimulationHistory $simulation
     * @param array $options
     * @return array
     */
    public function exportImage(SimulationHistory $simulation, array $options = []): array
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Image export requires GD extension');
        }

        try {
            if (!$simulation->output_data || $simulation->status !== 'completed') {
                throw new \Exception('Simulation must be completed before export');
            }

            $data = $this->prepareExportData($simulation, $options);

            $filename = $this->generateFilename($simulation, 'png');
            $filepath = "{$this->exportPath}/{$filename}";

            $content = $this->generateImageContent($data);
            Storage::disk('local')->put($filepath, $content);

            $downloadUrl = $this->generateDownloadUrl($filename);
            $expiresAt = now()->addHours($this->expirationHours);

            Log::info('Image export created', [
                'simulation_id' => $simulation->id,
                'filename' => $filename,
                'expires_at' => $expiresAt,
            ]);

            return [
                'success' => true,
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'format' => 'png',
                'file_size_bytes' => Storage::disk('local')->size($filepath),
                'expires_at' => $expiresAt->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Image export failed', [
                'simulation_id' => $simulation->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Export simulation to JSON format
     *
     * @param SimulationHistory $simulation
     * @param array $options
     * @return array
     */
    public function exportJson(SimulationHistory $simulation, array $options = []): array
    {
        try {
            // Prepare data for export
            $data = $this->prepareExportData($simulation, $options);

            // Generate JSON file
            $filename = $this->generateFilename($simulation, 'json');
            $filepath = "{$this->exportPath}/{$filename}";

            $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Storage::disk('local')->put($filepath, $content);

            $downloadUrl = $this->generateDownloadUrl($filename);
            $expiresAt = now()->addHours($this->expirationHours);

            return [
                'success' => true,
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'format' => 'json',
                'file_size_bytes' => Storage::disk('local')->size($filepath),
                'expires_at' => $expiresAt->toIso8601String(),
            ];

        } catch (\Exception $e) {
            Log::error('JSON export failed', [
                'simulation_id' => $simulation->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Prepare export data based on selected sections
     *
     * @param SimulationHistory $simulation
     * @param array $options
     * @return array
     */
    protected function prepareExportData(SimulationHistory $simulation, array $options): array
    {
        $sections = $options['sections'] ?? [
            'product_overview',
            'ingredients',
            'market_analysis',
            'pricing',
            'references',
            'marketing',
        ];

        $outputData = $simulation->output_data;
        $inputData = $simulation->input_data;

        $data = [
            'simulation_id' => 'sim_' . str_pad($simulation->id, 16, '0', STR_PAD_LEFT),
            'generated_at' => $simulation->processing_completed_at?->toIso8601String(),
            'processing_time' => ($simulation->processing_duration_seconds ?? 0) . ' seconds',
        ];

        // Product Overview
        if (in_array('product_overview', $sections)) {
            $data['product_overview'] = [
                'product_name' => $outputData['selected_name'] ?? $outputData['product_name'] ?? 'N/A',
                'tagline' => $outputData['selected_tagline'] ?? $outputData['tagline'] ?? 'N/A',
                'description' => $outputData['description'] ?? 'N/A',
                'alternative_names' => $outputData['product_names'] ?? $outputData['alternative_names'] ?? [],
                'formulation_type' => $inputData['bentuk_formulasi'] ?? 'N/A',
                'volume' => ($inputData['volume'] ?? 'N/A') . ' ' . ($inputData['volume_unit'] ?? ''),
                'target_market' => [
                    'gender' => $inputData['target_gender'] ?? 'N/A',
                    'age_ranges' => $inputData['target_usia'] ?? [],
                    'country' => $inputData['target_negara'] ?? 'N/A',
                ],
            ];
        }

        // Ingredients
        if (in_array('ingredients', $sections)) {
            $data['ingredients'] = [
                'active_ingredients' => $outputData['ingredients_analysis']['active_ingredients'] ?? [],
                'supporting_ingredients' => $outputData['ingredients_analysis']['supporting_ingredients'] ?? [],
                'compatibility_score' => $outputData['ingredients_analysis']['compatibility_score'] ?? 'N/A',
                'safety_assessment' => $outputData['ingredients_analysis']['safety_assessment'] ?? 'N/A',
            ];
        }

        // Market Analysis
        if (in_array('market_analysis', $sections)) {
            $data['market_analysis'] = $outputData['market_analysis'] ?? [];
        }

        // Pricing
        if (in_array('pricing', $sections)) {
            $data['pricing'] = $outputData['price_estimation'] ?? [];
        }

        // Scientific References
        if (in_array('references', $sections)) {
            $data['scientific_references'] = $outputData['scientific_refs'] ?? [];
        }

        // Marketing
        if (in_array('marketing', $sections)) {
            $data['marketing'] = [
                'copy' => $outputData['marketing_copywriting']['body_copy'] ?? $outputData['marketing_copy'] ?? 'N/A',
                'headline' => $outputData['marketing_copywriting']['headline'] ?? 'N/A',
                'sub_headline' => $outputData['marketing_copywriting']['sub_headline'] ?? 'N/A',
                'key_selling_points' => $outputData['marketing_suggestions']['key_selling_points'] ?? [],
                'target_channels' => $outputData['marketing_suggestions']['target_channels'] ?? [],
                'social_captions' => $outputData['marketing_copywriting']['social_media_captions'] ?? [],
                'email_subjects' => $outputData['marketing_copywriting']['email_subject_lines'] ?? [],
                'whatsapp_cta' => $outputData['cta_whatsapp_url'] ?? null,
            ];
        }

        return $data;
    }

    /**
     * Generate PDF content using DomPDF
     *
     * @param array $data
     * @return string
     */
    protected function generatePdfContent(array $data): string
    {
        try {
            // Create comprehensive HTML content
            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Simulation Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        h2 { color: #34495e; font-size: 20px; margin: 25px 0 15px 0; border-bottom: 2px solid #ecf0f1; padding-bottom: 8px; }
        h3 { color: #34495e; font-size: 16px; margin: 20px 0 10px 0; }
        .info { margin: 8px 0; }
        .section { margin-bottom: 25px; page-break-inside: avoid; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .pricing-grid { display: table; width: 100%; margin: 15px 0; }
        .pricing-item { display: table-cell; width: 33.33%; padding: 15px; text-align: center; border: 1px solid #ddd; }
        .pricing-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .pricing-value { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .list-item { margin: 5px 0; padding-left: 15px; }
        .trending-item { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .trend-status { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .status-peak { background: #fee; color: #c00; }
        .status-rising { background: #ffe4cc; color: #d84315; }
        .status-steady { background: #e3f2fd; color: #1976d2; }
        .status-declining { background: #f5f5f5; color: #666; }
    </style>
</head>
<body>
    <h1>AI Skincare Product Simulator</h1>
    <h2>Simulation Report</h2>
    
    <div class="section">
        <h3>Simulation Details</h3>
        <div class="info"><strong>Simulation ID:</strong> ' . htmlspecialchars($data['simulation_id'] ?? 'N/A') . '</div>
        <div class="info"><strong>Generated At:</strong> ' . htmlspecialchars($data['generated_at'] ?? 'N/A') . '</div>
        <div class="info"><strong>Processing Time:</strong> ' . htmlspecialchars($data['processing_time'] ?? 'N/A') . '</div>
    </div>';
            
            // Product Overview
            if (isset($data['product_overview'])) {
                $po = $data['product_overview'];
                $html .= '
    <div class="section">
        <h2>Product Overview</h2>
        <div class="info"><strong>Product Name:</strong> ' . htmlspecialchars($po['product_name'] ?? 'N/A') . '</div>
        <div class="info"><strong>Tagline:</strong> ' . htmlspecialchars($po['tagline'] ?? 'N/A') . '</div>
        <div class="info"><strong>Description:</strong><br>' . nl2br(htmlspecialchars($po['description'] ?? 'N/A')) . '</div>';
                
                if (isset($po['alternative_names']) && count($po['alternative_names']) > 0) {
                    $html .= '<div class="info"><strong>Alternative Names:</strong> ' . implode(', ', array_map('htmlspecialchars', $po['alternative_names'])) . '</div>';
                }
                
                if (isset($po['target_market'])) {
                    $tm = $po['target_market'];
                    $html .= '
        <h3>Target Market</h3>
        <div class="info"><strong>Gender:</strong> ' . htmlspecialchars($tm['gender'] ?? 'N/A') . '</div>
        <div class="info"><strong>Age Ranges:</strong> ' . htmlspecialchars(implode(', ', $tm['age_ranges'] ?? [])) . '</div>
        <div class="info"><strong>Country:</strong> ' . htmlspecialchars($tm['country'] ?? 'N/A') . '</div>';
                }
                
                $html .= '</div>';
            }
            
            // Ingredients Analysis
            if (isset($data['ingredients'])) {
                $ingredients = $data['ingredients'];
                $html .= '
    <div class="section">
        <h2>Ingredients Analysis</h2>';
                
                if (isset($ingredients['active_ingredients']) && count($ingredients['active_ingredients']) > 0) {
                    $html .= '
        <h3>Active Ingredients</h3>
        <table>
            <tr><th>Ingredient</th><th>INCI Name</th><th>Function</th><th>Concentration</th></tr>';
                    
                    foreach ($ingredients['active_ingredients'] as $ingredient) {
                        $html .= '<tr>
                            <td>' . htmlspecialchars($ingredient['name'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($ingredient['inci_name'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($ingredient['function'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($ingredient['concentration'] ?? 'N/A') . '</td>
                        </tr>';
                    }
                    
                    $html .= '</table>';
                }
                
                if (isset($ingredients['compatibility_score'])) {
                    $html .= '<div class="info"><strong>Compatibility Score:</strong> ' . htmlspecialchars($ingredients['compatibility_score']) . '</div>';
                }
                
                if (isset($ingredients['safety_assessment'])) {
                    $html .= '<div class="info"><strong>Safety Assessment:</strong> ' . htmlspecialchars($ingredients['safety_assessment']) . '</div>';
                }
                
                $html .= '</div>';
            }
            
            // Market Analysis
            if (isset($data['market_analysis'])) {
                $ma = $data['market_analysis'];
                $html .= '
    <div class="section">
        <h2>Market Analysis</h2>';
                
                if (isset($ma['target_price_range'])) {
                    $pricing = $ma['target_price_range'];
                    $hpp = $pricing['min'] ?? 0;
                    $srp = $pricing['recommended'] ?? 0;
                    $margin = $srp > 0 ? round((($srp - $hpp) / $srp) * 100) : 0;
                    
                    $html .= '
        <h3>Pricing Analysis</h3>
        <div class="pricing-grid">
            <div class="pricing-item">
                <div class="pricing-label">HPP (Cost)</div>
                <div class="pricing-value">IDR ' . number_format($hpp, 0, ',', '.') . '</div>
            </div>
            <div class="pricing-item">
                <div class="pricing-label">SRP (Retail)</div>
                <div class="pricing-value">IDR ' . number_format($srp, 0, ',', '.') . '</div>
            </div>
            <div class="pricing-item">
                <div class="pricing-label">Margin</div>
                <div class="pricing-value">' . $margin . '%</div>
            </div>
        </div>';
                }
                
                if (isset($ma['competitor_analysis']) && count($ma['competitor_analysis']) > 0) {
                    $html .= '
        <h3>Competitor Analysis</h3>
        <table>
            <tr><th>Brand</th><th>Marketplace</th><th>Positioning</th><th>Price</th><th>Volume</th></tr>';
                    
                    foreach ($ma['competitor_analysis'] as $competitor) {
                        $html .= '<tr>
                            <td>' . htmlspecialchars($competitor['brand'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($competitor['marketplace'] ?? 'N/A') . '</td>
                            <td>' . htmlspecialchars($competitor['positioning'] ?? 'N/A') . '</td>
                            <td>IDR ' . number_format($competitor['price'] ?? 0, 0, ',', '.') . '</td>
                            <td>' . htmlspecialchars($competitor['volume'] ?? 'N/A') . '</td>
                        </tr>';
                    }
                    
                    $html .= '</table>';
                }
                
                if (isset($data['strategy'])) {
                    $html .= '<div class="info"><strong>Distribution Strategy:</strong> ' . htmlspecialchars($data['strategy']) . '</div>';
                }
                
                $html .= '</div>';
            }
            
            // Market Potential
            if (isset($data['market_potential'])) {
                $mp = $data['market_potential'];
                $html .= '
    <div class="section">
        <h2>Market Potential</h2>';
                
                if (isset($mp['total_addressable_market'])) {
                    $tam = $mp['total_addressable_market'];
                    $html .= '
        <h3>Total Addressable Market (TAM)</h3>
        <div class="info"><strong>Segment:</strong> ' . htmlspecialchars($tam['segment'] ?? 'N/A') . '</div>
        <div class="info"><strong>Estimated Size:</strong> ' . number_format($tam['estimated_size'] ?? 0) . ' customers</div>
        <div class="info"><strong>Market Value:</strong> IDR ' . number_format($tam['value_idr'] ?? 0, 0, ',', '.') . '</div>';
                }
                
                if (isset($mp['revenue_projections'])) {
                    $rp = $mp['revenue_projections'];
                    $html .= '
        <h3>Revenue Projections</h3>
        <div class="info"><strong>Monthly Units:</strong> ' . number_format($rp['monthly_units'] ?? 0) . '</div>
        <div class="info"><strong>Monthly Revenue:</strong> IDR ' . number_format($rp['monthly_revenue'] ?? 0, 0, ',', '.') . '</div>
        <div class="info"><strong>Yearly Revenue:</strong> IDR ' . number_format($rp['yearly_revenue'] ?? 0, 0, ',', '.') . '</div>';
                }
                
                if (isset($mp['growth_opportunities']) && count($mp['growth_opportunities']) > 0) {
                    $html .= '<h3>Growth Opportunities</h3>';
                    foreach ($mp['growth_opportunities'] as $opportunity) {
                        $html .= '<div class="list-item">• ' . htmlspecialchars($opportunity) . '</div>';
                    }
                }
                
                if (isset($mp['risk_factors']) && count($mp['risk_factors']) > 0) {
                    $html .= '<h3>Risk Factors</h3>';
                    foreach ($mp['risk_factors'] as $risk) {
                        $html .= '<div class="list-item">• ' . htmlspecialchars($risk) . '</div>';
                    }
                }
                
                $html .= '</div>';
            }
            
            // Key Trends
            if (isset($data['key_trends'])) {
                $kt = $data['key_trends'];
                $html .= '
    <div class="section">
        <h2>Key Market Trends</h2>';
                
                if (isset($kt['trending_ingredients']) && count($kt['trending_ingredients']) > 0) {
                    $html .= '<h3>Trending Ingredients</h3>';
                    foreach ($kt['trending_ingredients'] as $ingredient) {
                        $status = $ingredient['trend_status'] ?? '';
                        $statusClass = 'status-' . strtolower($status);
                        $html .= '<div class="trending-item">
                            <strong>' . htmlspecialchars($ingredient['name'] ?? 'N/A') . '</strong>
                            <span class="trend-status ' . $statusClass . '">' . htmlspecialchars($status) . '</span><br>
                            <small>Search Trend: ' . htmlspecialchars($ingredient['google_search_trend'] ?? 'N/A') . ' | 
                            Awareness: ' . htmlspecialchars($ingredient['consumer_awareness'] ?? 'N/A') . '</small>
                        </div>';
                    }
                }
                
                if (isset($kt['market_movements']) && count($kt['market_movements']) > 0) {
                    $html .= '<h3>Market Movements</h3>';
                    foreach ($kt['market_movements'] as $movement) {
                        $html .= '<div class="list-item">• ' . htmlspecialchars($movement) . '</div>';
                    }
                }
                
                if (isset($kt['competitive_landscape'])) {
                    $html .= '<h3>Competitive Landscape</h3>
                    <div class="info">' . htmlspecialchars($kt['competitive_landscape']) . '</div>';
                }
                
                $html .= '</div>';
            }
            
            // Marketing Copywriting
            if (isset($data['marketing'])) {
                $marketing = $data['marketing'];
                $html .= '
    <div class="section">
        <h2>Marketing Copywriting</h2>';
                
                if (isset($marketing['headline']) && $marketing['headline'] !== 'N/A') {
                    $html .= '<h3>Headline</h3>
                    <div class="info" style="font-size: 16px; font-weight: bold; color: #2c3e50;">' . htmlspecialchars($marketing['headline']) . '</div>';
                }
                
                if (isset($marketing['sub_headline']) && $marketing['sub_headline'] !== 'N/A') {
                    $html .= '<h3>Sub Headline</h3>
                    <div class="info" style="font-size: 14px; color: #7f8c8d;">' . htmlspecialchars($marketing['sub_headline']) . '</div>';
                }
                
                if (isset($marketing['copy']) && $marketing['copy'] !== 'N/A') {
                    $html .= '<h3>Main Copy</h3>
                    <div class="info">' . nl2br(htmlspecialchars($marketing['copy'])) . '</div>';
                }
                
                if (isset($marketing['key_selling_points']) && count($marketing['key_selling_points']) > 0) {
                    $html .= '<h3>Key Selling Points</h3>';
                    foreach ($marketing['key_selling_points'] as $point) {
                        $html .= '<div class="list-item">• ' . htmlspecialchars($point) . '</div>';
                    }
                }
                
                if (isset($marketing['target_channels']) && count($marketing['target_channels']) > 0) {
                    $html .= '<h3>Target Channels</h3>
                    <div class="info">' . implode(', ', array_map('htmlspecialchars', $marketing['target_channels'])) . '</div>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '
</body>
</html>';

            // Generate PDF using DomPDF
            $pdf = Pdf::loadHTML($html);
            
            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'Arial',
            ]);
            
            return $pdf->output();
            
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($data),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Fallback to simple text content
            return $this->generateFallbackContent($data);
        }
    }

    /**
     * Generate Word document directly to file path
     *
     * @param array $data
     * @param string $filepath
     * @return void
     */
    protected function generateWordDocument(array $data, string $filepath): void
    {
        try {
            $phpWord = new PhpWord();
            
            // Set document properties
            $phpWord->getDocInfo()->setCreator('AI Skincare Product Simulator');
            $phpWord->getDocInfo()->setTitle('Simulation Report - ' . ($data['simulation_id'] ?? 'N/A'));
            $phpWord->getDocInfo()->setDescription('AI-generated skincare product development report');
            
            // Add a section
            $section = $phpWord->addSection([
                'marginTop' => 720,
                'marginBottom' => 720,
                'marginLeft' => 720,
                'marginRight' => 720,
            ]);
            
            // Header
            $section->addTitle('AI Skincare Product Simulator', 1);
            $section->addText('Comprehensive Product Development Report', [
                'size' => 14,
                'color' => '7f8c8d',
                'italic' => true,
            ]);
            $section->addTextBreak(2);
            
            // Simulation Info
            $section->addTitle('Simulation Details', 2);
            $section->addText('Simulation ID: ' . ($data['simulation_id'] ?? 'N/A'));
            $section->addText('Generated At: ' . ($data['generated_at'] ?? 'N/A'));
            $section->addText('Processing Time: ' . ($data['processing_time'] ?? 'N/A'));
            $section->addTextBreak();
            
            // Product Overview
            if (isset($data['product_overview'])) {
                $section->addTitle('Product Overview', 2);
                $section->addText('Product Name: ' . ($data['product_overview']['product_name'] ?? 'N/A'), [
                    'bold' => true,
                    'size' => 14,
                ]);
                $section->addText('Tagline: ' . ($data['product_overview']['tagline'] ?? 'N/A'), [
                    'italic' => true,
                    'color' => '7f8c8d',
                ]);
                $section->addText('Description: ' . ($data['product_overview']['description'] ?? 'N/A'));
                
                if (isset($data['product_overview']['target_market'])) {
                    $section->addText('Target Gender: ' . ($data['product_overview']['target_market']['gender'] ?? 'N/A'));
                    $section->addText('Target Age: ' . implode(', ', $data['product_overview']['target_market']['age_ranges'] ?? []));
                    $section->addText('Target Country: ' . ($data['product_overview']['target_market']['country'] ?? 'N/A'));
                }
                $section->addTextBreak();
            }
            
            // Ingredients Analysis
            if (isset($data['ingredients'])) {
                $section->addTitle('Ingredients Analysis', 2);
                
                if (isset($data['ingredients']['active_ingredients']) && count($data['ingredients']['active_ingredients']) > 0) {
                    $section->addText('Active Ingredients:', ['bold' => true]);
                    
                    // Create table for ingredients
                    $table = $section->addTable([
                        'borderSize' => 6,
                        'borderColor' => '999999',
                        'cellMargin' => 80,
                    ]);
                    
                    $table->addRow();
                    $table->addCell(2000)->addText('Ingredient', ['bold' => true]);
                    $table->addCell(2000)->addText('INCI Name', ['bold' => true]);
                    $table->addCell(3000)->addText('Function', ['bold' => true]);
                    $table->addCell(1500)->addText('Concentration', ['bold' => true]);
                    
                    foreach ($data['ingredients']['active_ingredients'] as $ingredient) {
                        $table->addRow();
                        $table->addCell(2000)->addText($ingredient['name'] ?? 'N/A');
                        $table->addCell(2000)->addText($ingredient['inci_name'] ?? 'N/A');
                        $table->addCell(3000)->addText($ingredient['function'] ?? 'N/A');
                        $table->addCell(1500)->addText($ingredient['concentration'] ?? 'N/A');
                    }
                    
                    $section->addTextBreak();
                }
                
                if (isset($data['ingredients']['compatibility_score'])) {
                    $section->addText('Compatibility Score: ' . $data['ingredients']['compatibility_score'] . '/10', [
                        'bold' => true,
                        'color' => '2c3e50',
                    ]);
                }
                
                if (isset($data['ingredients']['safety_assessment'])) {
                    $section->addText('Safety Assessment: ' . $data['ingredients']['safety_assessment']);
                }
                $section->addTextBreak();
            }
            
            // Market Analysis
            if (isset($data['market_analysis'])) {
                $section->addTitle('Market Analysis', 2);
                
                if (isset($data['market_analysis']['target_price_range'])) {
                    $pricing = $data['market_analysis']['target_price_range'];
                    $hpp = $pricing['min'] ?? 0;
                    $srp = $pricing['recommended'] ?? 0;
                    $margin = $srp > 0 ? round((($srp - $hpp) / $srp) * 100) : 0;
                    
                    $section->addText('Pricing Analysis:', ['bold' => true]);
                    $section->addText('HPP (Cost): IDR ' . number_format($hpp, 0, ',', '.'), [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('SRP (Retail): IDR ' . number_format($srp, 0, ',', '.'), [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('Margin: ' . $margin . '%', [
                        'indentation' => ['left' => 360],
                    ]);
                }
                
                if (isset($data['market_analysis']['competitor_analysis']) && count($data['market_analysis']['competitor_analysis']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Competitor Analysis:', ['bold' => true]);
                    
                    $table = $section->addTable([
                        'borderSize' => 6,
                        'borderColor' => '999999',
                        'cellMargin' => 80,
                    ]);
                    
                    $table->addRow();
                    $table->addCell(2000)->addText('Brand', ['bold' => true]);
                    $table->addCell(2000)->addText('Marketplace', ['bold' => true]);
                    $table->addCell(2500)->addText('Positioning', ['bold' => true]);
                    $table->addCell(1500)->addText('Price', ['bold' => true]);
                    $table->addCell(1000)->addText('Volume', ['bold' => true]);
                    
                    foreach ($data['market_analysis']['competitor_analysis'] as $competitor) {
                        $table->addRow();
                        $table->addCell(2000)->addText($competitor['brand'] ?? 'N/A');
                        $table->addCell(2000)->addText($competitor['marketplace'] ?? 'N/A');
                        $table->addCell(2500)->addText($competitor['positioning'] ?? 'N/A');
                        $table->addCell(1500)->addText('IDR ' . number_format($competitor['price'] ?? 0, 0, ',', '.'));
                        $table->addCell(1000)->addText($competitor['volume'] ?? 'N/A');
                    }
                }
                
                if (isset($data['strategy'])) {
                    $section->addTextBreak();
                    $section->addText('Distribution Strategy: ' . $data['strategy']);
                }
                $section->addTextBreak();
            }
            
            // Market Potential
            if (isset($data['market_potential'])) {
                $section->addTitle('Market Potential', 2);
                
                if (isset($data['market_potential']['total_addressable_market'])) {
                    $tam = $data['market_potential']['total_addressable_market'];
                    $section->addText('Total Addressable Market (TAM):', ['bold' => true]);
                    $section->addText('Segment: ' . ($tam['segment'] ?? 'N/A'), [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('Estimated Size: ' . number_format($tam['estimated_size'] ?? 0) . ' customers', [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('Market Value: IDR ' . number_format($tam['value_idr'] ?? 0, 0, ',', '.'), [
                        'indentation' => ['left' => 360],
                    ]);
                }
                
                if (isset($data['market_potential']['revenue_projections'])) {
                    $rp = $data['market_potential']['revenue_projections'];
                    $section->addTextBreak();
                    $section->addText('Revenue Projections:', ['bold' => true]);
                    $section->addText('Monthly Units: ' . number_format($rp['monthly_units'] ?? 0), [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('Monthly Revenue: IDR ' . number_format($rp['monthly_revenue'] ?? 0, 0, ',', '.'), [
                        'indentation' => ['left' => 360],
                    ]);
                    $section->addText('Yearly Revenue: IDR ' . number_format($rp['yearly_revenue'] ?? 0, 0, ',', '.'), [
                        'indentation' => ['left' => 360],
                    ]);
                }
                
                if (isset($data['market_potential']['growth_opportunities']) && count($data['market_potential']['growth_opportunities']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Growth Opportunities:', ['bold' => true]);
                    foreach ($data['market_potential']['growth_opportunities'] as $opportunity) {
                        $section->addText('• ' . $opportunity, [
                            'indentation' => ['left' => 360],
                        ]);
                    }
                }
                
                if (isset($data['market_potential']['risk_factors']) && count($data['market_potential']['risk_factors']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Risk Factors:', ['bold' => true]);
                    foreach ($data['market_potential']['risk_factors'] as $risk) {
                        $section->addText('• ' . $risk, [
                            'indentation' => ['left' => 360],
                        ]);
                    }
                }
                $section->addTextBreak();
            }
            
            // Key Trends
            if (isset($data['key_trends'])) {
                $section->addTitle('Key Market Trends', 2);
                
                if (isset($data['key_trends']['trending_ingredients']) && count($data['key_trends']['trending_ingredients']) > 0) {
                    $section->addText('Trending Ingredients:', ['bold' => true]);
                    foreach ($data['key_trends']['trending_ingredients'] as $ingredient) {
                        $section->addText('• ' . ($ingredient['name'] ?? 'N/A') . 
                            ' (' . ($ingredient['trend_status'] ?? 'N/A') . ')', [
                            'indentation' => ['left' => 360],
                        ]);
                        $section->addText('  Search Trend: ' . ($ingredient['google_search_trend'] ?? 'N/A') . 
                            ' | Awareness: ' . ($ingredient['consumer_awareness'] ?? 'N/A'), [
                            'indentation' => ['left' => 720],
                            'size' => 10,
                            'color' => '666666',
                        ]);
                    }
                }
                
                if (isset($data['key_trends']['market_movements']) && count($data['key_trends']['market_movements']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Market Movements:', ['bold' => true]);
                    foreach ($data['key_trends']['market_movements'] as $movement) {
                        $section->addText('• ' . $movement, [
                            'indentation' => ['left' => 360],
                        ]);
                    }
                }
                
                if (isset($data['key_trends']['competitive_landscape'])) {
                    $section->addTextBreak();
                    $section->addText('Competitive Landscape: ' . $data['key_trends']['competitive_landscape']);
                }
                $section->addTextBreak();
            }
            
            // Marketing Copywriting
            if (isset($data['marketing'])) {
                $section->addTitle('Marketing Copywriting', 2);
                
                if (isset($data['marketing']['headline']) && $data['marketing']['headline'] !== 'N/A') {
                    $section->addText('Headline:', ['bold' => true]);
                    $section->addText($data['marketing']['headline'], [
                        'indentation' => ['left' => 360],
                        'size' => 14,
                        'color' => '2c3e50',
                    ]);
                }
                
                if (isset($data['marketing']['sub_headline']) && $data['marketing']['sub_headline'] !== 'N/A') {
                    $section->addText('Sub Headline:', ['bold' => true]);
                    $section->addText($data['marketing']['sub_headline'], [
                        'indentation' => ['left' => 360],
                        'size' => 12,
                        'color' => '7f8c8d',
                    ]);
                }
                
                if (isset($data['marketing']['copy']) && $data['marketing']['copy'] !== 'N/A') {
                    $section->addTextBreak();
                    $section->addText('Main Copy:', ['bold' => true]);
                    $section->addText($data['marketing']['copy'], [
                        'indentation' => ['left' => 360],
                    ]);
                }
                
                if (isset($data['marketing']['key_selling_points']) && count($data['marketing']['key_selling_points']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Key Selling Points:', ['bold' => true]);
                    foreach ($data['marketing']['key_selling_points'] as $point) {
                        $section->addText('• ' . $point, [
                            'indentation' => ['left' => 360],
                        ]);
                    }
                }
                
                if (isset($data['marketing']['target_channels']) && count($data['marketing']['target_channels']) > 0) {
                    $section->addTextBreak();
                    $section->addText('Target Channels: ' . implode(', ', $data['marketing']['target_channels']));
                }
                $section->addTextBreak();
            }
            
            // Pricing
            if (isset($data['pricing'])) {
                $section->addTitle('Pricing Analysis', 2);
                if (isset($data['pricing']['estimated_cost']['total_hpp'])) {
                    $section->addText('Total HPP: ' . $data['pricing']['estimated_cost']['total_hpp'], [
                        'bold' => true,
                        'size' => 14,
                    ]);
                }
                if (isset($data['pricing']['recommended_retail'])) {
                    $section->addText('Recommended Retail: ' . $data['pricing']['recommended_retail']);
                }
                $section->addTextBreak();
            }
            
            // Scientific References
            if (isset($data['scientific_references']) && count($data['scientific_references']) > 0) {
                $section->addTitle('Scientific References', 2);
                foreach ($data['scientific_references'] as $reference) {
                    $section->addText('• ' . ($reference['title'] ?? 'N/A'), [
                        'bold' => true,
                    ]);
                    $section->addText('  ' . ($reference['authors'] ?? 'N/A') . ' - ' . ($reference['journal'] ?? 'N/A'), [
                        'indentation' => ['left' => 360],
                        'italic' => true,
                    ]);
                    $section->addTextBreak(1);
                }
            }
            
            // Marketing
            if (isset($data['marketing'])) {
                $section->addTitle('Marketing Strategy', 2);
                if (isset($data['marketing']['copy'])) {
                    $section->addText('Marketing Copy: ' . $data['marketing']['copy']);
                }
                if (isset($data['marketing']['key_selling_points'])) {
                    $section->addText('Key Selling Points:', ['bold' => true]);
                    foreach ($data['marketing']['key_selling_points'] as $point) {
                        $section->addText('• ' . $point, [
                            'indentation' => ['left' => 360],
                        ]);
                    }
                }
            }
            
            // Footer
            $section->addTextBreak(2);
            $section->addText('Generated by AI Skincare Product Simulator', [
                'size' => 10,
                'color' => '6c757d',
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $section->addText(now()->format('F j, Y \a\t g:i A'), [
                'size' => 10,
                'color' => '6c757d',
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            
            // Save directly to the target file path
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($filepath);
            
        } catch (\Exception $e) {
            Log::error('Word generation failed', [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($data),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate fallback content when PDF/Word generation fails
     *
     * @param array $data
     * @return string
     */
    protected function generateFallbackContent(array $data): string
    {
        $content = "AI SKINCARE PRODUCT SIMULATOR - SIMULATION REPORT\n";
        $content .= "=" . str_repeat("=", 60) . "\n\n";
        $content .= "Simulation ID: " . ($data['simulation_id'] ?? 'N/A') . "\n";
        $content .= "Generated At: " . ($data['generated_at'] ?? 'N/A') . "\n";
        $content .= "Processing Time: " . ($data['processing_time'] ?? 'N/A') . "\n\n";

        if (isset($data['product_overview'])) {
            $content .= "PRODUCT OVERVIEW\n";
            $content .= "-" . str_repeat("-", 60) . "\n";
            $content .= "Product Name: " . ($data['product_overview']['product_name'] ?? 'N/A') . "\n";
            $content .= "Tagline: " . ($data['product_overview']['tagline'] ?? 'N/A') . "\n";
            $content .= "Description: " . ($data['product_overview']['description'] ?? 'N/A') . "\n\n";
        }

        if (isset($data['ingredients'])) {
            $content .= "INGREDIENTS ANALYSIS\n";
            $content .= "-" . str_repeat("-", 60) . "\n";
            if (isset($data['ingredients']['compatibility_score'])) {
                $content .= "Compatibility Score: " . $data['ingredients']['compatibility_score'] . "/10\n";
            }
            if (isset($data['ingredients']['safety_assessment'])) {
                $content .= "Safety Assessment: " . $data['ingredients']['safety_assessment'] . "\n";
            }
            $content .= "\n";
        }

        if (isset($data['pricing'])) {
            $content .= "PRICING ANALYSIS\n";
            $content .= "-" . str_repeat("-", 60) . "\n";
            if (isset($data['pricing']['estimated_cost']['total_hpp'])) {
                $content .= "Total HPP: " . $data['pricing']['estimated_cost']['total_hpp'] . "\n";
            }
            if (isset($data['pricing']['recommended_retail'])) {
                $content .= "Recommended Retail: " . $data['pricing']['recommended_retail'] . "\n";
            }
            $content .= "\n";
        }

        $content .= "Generated by AI Skincare Product Simulator\n";
        $content .= "Report generated on: " . now()->format('F j, Y \a\t g:i A') . "\n";

        return $content;
    }

    /**
     * Generate simple PNG content summarizing the simulation
     *
     * @param array $data
     * @return string
     */
    protected function generateImageContent(array $data): string
    {
        $width = 1200;
        $height = 1600;
        $image = imagecreatetruecolor($width, $height);

        $background = imagecolorallocate($image, 249, 250, 251);
        $headingColor = imagecolorallocate($image, 249, 115, 22);
        $textColor = imagecolorallocate($image, 15, 23, 42);
        $mutedColor = imagecolorallocate($image, 100, 116, 139);

        imagefill($image, 0, 0, $background);
        imagestring($image, 5, 40, 30, 'AI SKINCARE PRODUCT SIMULATOR', $headingColor);

        $y = 80;
        $lineHeight = 22;

        $this->drawWrappedText($image, "Simulation ID: " . ($data['simulation_id'] ?? 'N/A'), 4, 40, $y, $lineHeight, $mutedColor);
        $this->drawWrappedText($image, "Generated At: " . ($data['generated_at'] ?? now()->toDateTimeString()), 4, 40, $y, $lineHeight, $mutedColor);
        $y += $lineHeight;

        $productName = $data['product_overview']['product_name'] ?? 'Unnamed Concept';
        $this->drawWrappedText($image, "Product: {$productName}", 5, 40, $y, $lineHeight, $textColor);

        if (!empty($data['product_overview']['tagline'])) {
            $this->drawWrappedText($image, $data['product_overview']['tagline'], 4, 40, $y, $lineHeight, $mutedColor);
        }

        $y += $lineHeight;
        $description = $data['product_overview']['description'] ?? 'Description pending...';
        $this->drawWrappedText($image, $description, 3, 40, $y, $lineHeight, $textColor, 90);

        $y += 3 * $lineHeight;
        $this->drawWrappedText($image, 'Ingredients Highlights:', 4, 40, $y, $lineHeight, $textColor);
        $ingredients = $data['ingredients']['items'] ?? [];
        foreach (array_slice($ingredients, 0, 6) as $ingredient) {
            $line = sprintf('- %s: %s', $ingredient['name'] ?? 'Ingredient', $ingredient['effect'] ?? 'Effect TBD');
            $this->drawWrappedText($image, $line, 3, 60, $y, $lineHeight, $mutedColor, 85);
        }

        $y += 2 * $lineHeight;
        $this->drawWrappedText($image, 'Pricing Summary:', 4, 40, $y, $lineHeight, $textColor);
        if (isset($data['pricing']['estimated_cost']['total_hpp'])) {
            $this->drawWrappedText($image, 'HPP: ' . $data['pricing']['estimated_cost']['total_hpp'], 3, 60, $y, $lineHeight, $mutedColor);
        }
        if (isset($data['pricing']['recommended_retail'])) {
            $this->drawWrappedText($image, 'SRP: ' . $data['pricing']['recommended_retail'], 3, 60, $y, $lineHeight, $mutedColor);
        }

        $y += 2 * $lineHeight;
        $this->drawWrappedText($image, 'Generated automatically by AI Skincare Product Simulator.', 3, 40, $y, $lineHeight, $mutedColor);

        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }

    /**
     * Helper to draw wrapped text on GD image
     *
     * @param resource $image
     */
    protected function drawWrappedText($image, string $text, int $font, int $x, int &$y, int $lineHeight, int $color, int $wrap = 70): void
    {
        $wrapped = wordwrap($text, $wrap);
        foreach (explode("\n", $wrapped) as $line) {
            imagestring($image, $font, $x, $y, $line, $color);
            $y += $lineHeight;
        }
    }

    /**
     * Generate unique filename for export
     *
     * @param SimulationHistory $simulation
     * @param string $extension
     * @return string
     */
    protected function generateFilename(SimulationHistory $simulation, string $extension): string
    {
        $simulationId = 'sim_' . str_pad($simulation->id, 16, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        
        return "{$simulationId}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Generate download URL for exported file
     *
     * @param string $filename
     * @return string
     */
    protected function generateDownloadUrl(string $filename): string
    {
        // TODO: Implement signed URL for secure downloads
        return url("/api/exports/download/{$filename}");
    }

    /**
     * Clean up expired exports
     *
     * @return int Number of files deleted
     */
    public function cleanupExpiredExports(): int
    {
        try {
            $files = Storage::disk('local')->files($this->exportPath);
            $deleted = 0;
            $expirationTime = now()->subHours($this->expirationHours);

            foreach ($files as $file) {
                $lastModified = Storage::disk('local')->lastModified($file);
                
                if ($lastModified < $expirationTime->timestamp) {
                    Storage::disk('local')->delete($file);
                    $deleted++;
                }
            }

            Log::info('Export cleanup completed', [
                'files_deleted' => $deleted,
            ]);

            return $deleted;

        } catch (\Exception $e) {
            Log::error('Export cleanup failed', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}

