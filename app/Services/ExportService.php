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

            // Generate PDF content (placeholder - requires dompdf or similar)
            $filename = $this->generateFilename($simulation, 'pdf');
            $filepath = "{$this->exportPath}/{$filename}";

            // For now, create a JSON file as placeholder
            // In production, use a PDF library like dompdf or wkhtmltopdf
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

            // Generate Word content (placeholder - requires PHPWord)
            $filename = $this->generateFilename($simulation, 'docx');
            $filepath = "{$this->exportPath}/{$filename}";

            // For now, create a rich text file as placeholder
            // In production, use PHPWord library
            $content = $this->generateWordContent($data);
            Storage::disk('local')->put($filepath, $content);

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
            'processing_time' => $simulation->processing_duration_seconds . ' seconds',
        ];

        // Product Overview
        if (in_array('product_overview', $sections)) {
            $data['product_overview'] = [
                'product_name' => $outputData['product_name'] ?? 'N/A',
                'tagline' => $outputData['tagline'] ?? 'N/A',
                'description' => $outputData['description'] ?? 'N/A',
                'alternative_names' => $outputData['alternative_names'] ?? [],
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
                'copy' => $outputData['marketing_copy'] ?? 'N/A',
                'key_selling_points' => $outputData['marketing_suggestions']['key_selling_points'] ?? [],
                'target_channels' => $outputData['marketing_suggestions']['target_channels'] ?? [],
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
            // Generate PDF using DomPDF with Blade template
            $pdf = Pdf::loadView('exports.simulation', $data);
            
            // Set PDF options
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);
            
            return $pdf->output();
            
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($data),
            ]);
            
            // Fallback to simple text content
            return $this->generateFallbackContent($data);
        }
    }

    /**
     * Generate Word content using PHPWord
     *
     * @param array $data
     * @return string
     */
    protected function generateWordContent(array $data): string
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
                
                if (isset($data['ingredients']['active_ingredients'])) {
                    $section->addText('Active Ingredients:', ['bold' => true]);
                    foreach ($data['ingredients']['active_ingredients'] as $ingredient) {
                        $section->addText('• ' . ($ingredient['name'] ?? 'N/A') . 
                            ' (' . ($ingredient['concentration'] ?? 'N/A') . ')', [
                            'indentation' => ['left' => 360],
                        ]);
                    }
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
                if (isset($data['market_analysis']['competitor_analysis'])) {
                    $section->addText('Competitor Analysis: ' . $data['market_analysis']['competitor_analysis']);
                }
                if (isset($data['market_analysis']['market_trends'])) {
                    $section->addText('Market Trends: ' . $data['market_analysis']['market_trends']);
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
                'alignment' => 'center',
            ]);
            $section->addText(now()->format('F j, Y \a\t g:i A'), [
                'size' => 10,
                'color' => '6c757d',
                'alignment' => 'center',
            ]);
            
            // Save to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'word_export_');
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempFile);
            
            $content = file_get_contents($tempFile);
            unlink($tempFile);
            
            return $content;
            
        } catch (\Exception $e) {
            Log::error('Word generation failed', [
                'error' => $e->getMessage(),
                'data_keys' => array_keys($data),
            ]);
            
            // Fallback to simple text content
            return $this->generateFallbackContent($data);
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

