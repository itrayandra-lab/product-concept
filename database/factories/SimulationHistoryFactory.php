<?php

namespace Database\Factories;

use App\Models\SimulationHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SimulationHistory>
 */
class SimulationHistoryFactory extends Factory
{
    protected $model = SimulationHistory::class;

    public function definition(): array
    {
        $startedAt = now()->subSeconds($this->faker->numberBetween(10, 60));
        $completedAt = $startedAt->copy()->addSeconds($this->faker->numberBetween(30, 90));

        return [
            'user_id' => null,
            'guest_session_id' => null,
            'input_data' => [
                'fungsi_produk' => ['Brightening', 'Hydrating'],
                'bentuk_formulasi' => 'Serum',
                'target_gender' => 'Semua Gender',
                'target_usia' => ['25-35 tahun'],
                'target_negara' => 'ID',
                'deskripsi_formula' => $this->faker->paragraph(3),
                'bahan_aktif' => [
                    ['name' => 'Niacinamide', 'concentration' => 5, 'unit' => '%'],
                ],
                'volume' => 30,
                'volume_unit' => 'ml',
                'jenis_kemasan' => 'Dropper',
            ],
            'output_data' => [
                'product_name' => 'Luminous Glow Serum',
                'tagline' => 'Radiance in every drop',
                'description' => $this->faker->paragraph(),
                'ingredients_analysis' => [
                    'active_ingredients' => [
                        ['name' => 'Niacinamide', 'benefits' => ['Brightening']],
                    ],
                    'compatibility_score' => 9.1,
                    'safety_assessment' => 'Formulation suitable for daily use.',
                ],
                'market_analysis' => [
                    'category' => 'Premium Serum',
                ],
                'price_estimation' => [
                    'estimated_cost' => ['total_hpp' => 'IDR 25,000'],
                ],
                'marketing_copy' => 'Glow brighter with clinically backed actives.',
                'marketing_suggestions' => [
                    'key_selling_points' => ['Clinically supported', 'Gentle for all skin types'],
                    'target_channels' => ['Instagram', 'TikTok'],
                ],
                'cta_whatsapp_url' => 'https://wa.me/628123456789?text=Halo',
            ],
            'status' => 'completed',
            'n8n_workflow_id' => $this->faker->uuid(),
            'processing_started_at' => $startedAt,
            'processing_completed_at' => $completedAt,
            'processing_duration_seconds' => $completedAt->diffInSeconds($startedAt),
            'progress_metadata' => [
                'percentage' => 100,
                'current_step' => 'completed',
                'steps_completed' => ['ai_generation', 'market_analysis'],
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Indicate the simulation is still processing.
     */
    public function processing(): static
    {
        return $this->state(function () {
            return [
                'status' => 'processing',
                'processing_completed_at' => null,
                'processing_duration_seconds' => null,
                'progress_metadata' => [
                    'percentage' => 40,
                    'current_step' => 'ingredient_analysis',
                    'steps_completed' => ['validation'],
                    'updated_at' => now()->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * Attach a guest session relationship.
     */
    public function withGuestSession(?string $sessionId = null): static
    {
        return $this->state(function () use ($sessionId) {
            return [
                'guest_session_id' => $sessionId ?? 'guest_' . $this->faker->unique()->numerify('########'),
            ];
        });
    }
}
