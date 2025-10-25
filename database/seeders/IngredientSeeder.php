<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories for assignment
        $humectants = IngredientCategory::where('slug', 'humectants')->first();
        $emollients = IngredientCategory::where('slug', 'emollients')->first();
        $antioxidants = IngredientCategory::where('slug', 'antioxidants')->first();
        $actives = IngredientCategory::where('slug', 'active-ingredients')->first();
        $vitamins = IngredientCategory::where('slug', 'vitamins')->first();
        $peptides = IngredientCategory::where('slug', 'peptides')->first();

        $ingredients = [
            // Humectants
            [
                'name' => 'Hyaluronic Acid',
                'inci_name' => 'SODIUM_HYALURONATE',
                'description' => 'A powerful humectant that can hold up to 1000x its weight in water, providing deep hydration to the skin.',
                'effects' => ['Hydrating', 'Moisturizing', 'Plumping'],
                'safety_notes' => 'Generally safe for all skin types. Non-irritating.',
                'concentration_ranges' => [
                    'serum' => ['min' => 0.1, 'max' => 2.0],
                    'cream' => ['min' => 0.5, 'max' => 1.5],
                ],
                'category_id' => $humectants?->id,
            ],
            [
                'name' => 'Glycerin',
                'inci_name' => 'GLYCERIN',
                'description' => 'A classic humectant that draws moisture into the skin and maintains hydration.',
                'effects' => ['Hydrating', 'Moisturizing', 'Soothing'],
                'safety_notes' => 'Very safe and well-tolerated. Suitable for sensitive skin.',
                'concentration_ranges' => [
                    'serum' => ['min' => 2.0, 'max' => 5.0],
                    'cream' => ['min' => 3.0, 'max' => 10.0],
                ],
                'category_id' => $humectants?->id,
            ],

            // Antioxidants
            [
                'name' => 'Vitamin C (L-Ascorbic Acid)',
                'inci_name' => 'ASCORBIC_ACID',
                'description' => 'A potent antioxidant that brightens skin, boosts collagen production, and protects against environmental damage.',
                'effects' => ['Brightening', 'Antioxidant', 'Anti-aging', 'Collagen Boosting'],
                'safety_notes' => 'Can be irritating at high concentrations. pH-dependent stability. Use with caution on sensitive skin.',
                'concentration_ranges' => [
                    'serum' => ['min' => 5.0, 'max' => 20.0],
                    'cream' => ['min' => 3.0, 'max' => 10.0],
                ],
                'category_id' => $antioxidants?->id ?? $vitamins?->id,
            ],

            // Active Ingredients
            [
                'name' => 'Niacinamide',
                'inci_name' => 'NIACINAMIDE',
                'description' => 'A form of Vitamin B3 that improves skin texture, minimizes pores, and regulates oil production.',
                'effects' => ['Brightening', 'Pore Minimizing', 'Oil Control', 'Anti-inflammatory'],
                'safety_notes' => 'Generally well-tolerated. Safe for sensitive skin.',
                'concentration_ranges' => [
                    'serum' => ['min' => 2.0, 'max' => 10.0],
                    'cream' => ['min' => 2.0, 'max' => 5.0],
                ],
                'category_id' => $actives?->id ?? $vitamins?->id,
            ],
            [
                'name' => 'Retinol',
                'inci_name' => 'RETINOL',
                'description' => 'A form of Vitamin A that accelerates cell turnover, reduces fine lines, and improves skin texture.',
                'effects' => ['Anti-aging', 'Exfoliating', 'Acne Treatment', 'Texture Improvement'],
                'safety_notes' => 'Can cause irritation, dryness, and sensitivity. Start with low concentrations. Not recommended during pregnancy.',
                'concentration_ranges' => [
                    'serum' => ['min' => 0.1, 'max' => 1.0],
                    'cream' => ['min' => 0.25, 'max' => 0.5],
                ],
                'category_id' => $actives?->id,
            ],

            // Emollients
            [
                'name' => 'Squalane',
                'inci_name' => 'SQUALANE',
                'description' => 'A lightweight, non-comedogenic oil that mimics skin\'s natural sebum and provides long-lasting hydration.',
                'effects' => ['Moisturizing', 'Softening', 'Non-comedogenic'],
                'safety_notes' => 'Very safe and well-tolerated. Suitable for all skin types including acne-prone.',
                'concentration_ranges' => [
                    'serum' => ['min' => 1.0, 'max' => 5.0],
                    'cream' => ['min' => 2.0, 'max' => 10.0],
                ],
                'category_id' => $emollients?->id,
            ],

            // Peptides
            [
                'name' => 'Matrixyl 3000',
                'inci_name' => 'PALMITOYL_TRIPEPTIDE_1',
                'description' => 'A peptide complex that stimulates collagen and elastin production, reducing the appearance of wrinkles.',
                'effects' => ['Anti-aging', 'Firming', 'Wrinkle Reduction'],
                'safety_notes' => 'Generally safe. Well-tolerated by most skin types.',
                'concentration_ranges' => [
                    'serum' => ['min' => 3.0, 'max' => 8.0],
                    'cream' => ['min' => 2.0, 'max' => 5.0],
                ],
                'category_id' => $peptides?->id,
            ],
        ];

        foreach ($ingredients as $ingredientData) {
            Ingredient::updateOrCreate(
                ['inci_name' => $ingredientData['inci_name']],
                $ingredientData
            );
        }
    }
}

