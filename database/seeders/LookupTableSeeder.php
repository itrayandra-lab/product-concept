<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LookupTableSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIngredientCategories();
        $this->seedProductTypes();
        $this->seedProductFunctions();
        $this->seedTargetDemographics();
        $this->seedPackagingTypes();
    }

    private function seedIngredientCategories(): void
    {
        $data = [
            ['name' => 'Hydration Booster', 'slug' => 'hydration-booster', 'description' => 'Humectants and water-binding actives', 'color_hex' => '#38bdf8', 'sort_order' => 1],
            ['name' => 'Brightening Complex', 'slug' => 'brightening-complex', 'description' => 'Pigmentation and radiance support', 'color_hex' => '#fbbf24', 'sort_order' => 2],
            ['name' => 'Anti-Aging Peptides', 'slug' => 'anti-aging-peptides', 'description' => 'Collagen and elasticity boosters', 'color_hex' => '#a855f7', 'sort_order' => 3],
            ['name' => 'Acne Defense', 'slug' => 'acne-defense', 'description' => 'Blemish control and sebum balancing', 'color_hex' => '#fb7185', 'sort_order' => 4],
            ['name' => 'Barrier Repair', 'slug' => 'barrier-repair', 'description' => 'Ceramides, lipids, and soothing actives', 'color_hex' => '#f97316', 'sort_order' => 5],
            ['name' => 'Soothing Botanicals', 'slug' => 'soothing-botanicals', 'description' => 'Redness relief and calming extracts', 'color_hex' => '#4ade80', 'sort_order' => 6],
            ['name' => 'Gentle Exfoliants', 'slug' => 'gentle-exfoliants', 'description' => 'AHAs, BHAs, and enzymes', 'color_hex' => '#facc15', 'sort_order' => 7],
            ['name' => 'Sun Defense', 'slug' => 'sun-defense', 'description' => 'UV protection boosters', 'color_hex' => '#f472b6', 'sort_order' => 8],
            ['name' => 'Antioxidant Shield', 'slug' => 'antioxidant-shield', 'description' => 'Free radical neutralizers', 'color_hex' => '#34d399', 'sort_order' => 9],
            ['name' => 'Oil Control', 'slug' => 'oil-control', 'description' => 'Mattifying and pore-refining agents', 'color_hex' => '#0ea5e9', 'sort_order' => 10],
        ];

        $this->upsert('ingredient_categories', $data, ['slug'], ['description', 'color_hex', 'sort_order', 'is_active', 'updated_at']);
    }

    private function seedProductTypes(): void
    {
        $names = [
            'Serum', 'Krim', 'Lotion', 'Gel', 'Essence', 'Ampoule', 'Toner', 'Face Oil', 'Cleansing Balm',
            'Micellar Water', 'Sheet Mask', 'Sleeping Mask', 'Eye Cream', 'Spot Treatment', 'Body Lotion',
            'Emulsion', 'Sun Cream', 'Face Mist', 'Clay Mask', 'Booster Drops',
        ];

        $data = [];
        foreach ($names as $index => $name) {
            $data[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "{$name} untuk kebutuhan perawatan kulit profesional.",
                'typical_ingredients' => json_encode([]),
                'formulation_guidelines' => json_encode([]),
                'sort_order' => $index + 1,
            ];
        }

        $this->upsert('product_types', $data, ['slug'], ['description', 'typical_ingredients', 'formulation_guidelines', 'sort_order', 'is_active', 'updated_at']);
    }

    private function seedProductFunctions(): void
    {
        $functions = [
            'Melembabkan', 'Mencerahkan', 'Anti-aging', 'Anti-acne', 'Soothing', 'Firming', 'Oil Control', 'Barrier Repair',
            'Exfoliating', 'Even Tone', 'Hyperpigmentation Care', 'Pore Refining', 'Anti-Inflammatory', 'Sun Protection',
            'Texture Smoothing', 'Glow Boost', 'Repairing', 'Nourishing', 'Sebum Balancing', 'Blue Light Defense',
        ];

        $data = [];
        foreach ($functions as $index => $name) {
            $data[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Fungsi {$name} untuk simulasi AI.",
                'compatible_types' => json_encode([]),
                'recommended_ingredients' => json_encode([]),
                'sort_order' => $index + 1,
            ];
        }

        $this->upsert('product_functions', $data, ['slug'], ['description', 'compatible_types', 'recommended_ingredients', 'sort_order', 'is_active', 'updated_at']);
    }

    private function seedTargetDemographics(): void
    {
        $rows = [
            ['type' => 'gender', 'value' => 'all', 'display_name' => 'Semua Gender'],
            ['type' => 'gender', 'value' => 'female', 'display_name' => 'Wanita'],
            ['type' => 'gender', 'value' => 'male', 'display_name' => 'Pria'],
            ['type' => 'gender', 'value' => 'non-binary', 'display_name' => 'Non-Binary'],
            ['type' => 'age', 'value' => '12-17', 'display_name' => 'Usia 12-17'],
            ['type' => 'age', 'value' => '18-24', 'display_name' => 'Usia 18-24'],
            ['type' => 'age', 'value' => '25-34', 'display_name' => 'Usia 25-34'],
            ['type' => 'age', 'value' => '35-44', 'display_name' => 'Usia 35-44'],
            ['type' => 'age', 'value' => '45-54', 'display_name' => 'Usia 45-54'],
            ['type' => 'age', 'value' => '55+', 'display_name' => 'Usia 55+'],
            ['type' => 'skin_type', 'value' => 'dry', 'display_name' => 'Kulit Kering'],
            ['type' => 'skin_type', 'value' => 'oily', 'display_name' => 'Kulit Berminyak'],
            ['type' => 'skin_type', 'value' => 'combination', 'display_name' => 'Kulit Kombinasi'],
            ['type' => 'skin_type', 'value' => 'sensitive', 'display_name' => 'Kulit Sensitif'],
            ['type' => 'skin_type', 'value' => 'normal', 'display_name' => 'Kulit Normal'],
        ];

        $data = [];
        foreach ($rows as $index => $row) {
            $data[] = array_merge($row, [
                'metadata' => json_encode([]),
                'sort_order' => $index + 1,
            ]);
        }

        $this->upsert('target_demographics', $data, ['type', 'value'], ['display_name', 'metadata', 'sort_order', 'is_active', 'updated_at']);
    }

    private function seedPackagingTypes(): void
    {
        $names = [
            'Airless Pump', 'Dropper Bottle', 'Glass Jar', 'Plastic Jar', 'Tube', 'Stick Applicator', 'Mist Spray',
            'Refill Pouch', 'Foam Bottle', 'Roller Bottle', 'Compact Case', 'Sachet', 'Ampoule Vial', 'Dual-Chamber Pump',
            'Bottle with Pump', 'Twist Cap Bottle',
        ];

        $data = [];
        foreach ($names as $index => $name) {
            $data[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "{$name} untuk kemasan skincare dengan standar higienis.",
                'specifications' => json_encode([]),
                'cost_factors' => json_encode([]),
                'sort_order' => $index + 1,
            ];
        }

        $this->upsert('packaging_types', $data, ['slug'], ['description', 'specifications', 'cost_factors', 'sort_order', 'is_active', 'updated_at']);
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string> $uniqueBy
     * @param list<string> $updateColumns
     */
    private function upsert(string $table, array $rows, array $uniqueBy, array $updateColumns): void
    {
        $now = now();
        $payload = array_map(static function (array $row) use ($now) {
            return array_merge([
                'is_active' => $row['is_active'] ?? true,
                'created_at' => $row['created_at'] ?? $now,
                'updated_at' => $row['updated_at'] ?? $now,
            ], $row);
        }, $rows);

        DB::table($table)->upsert($payload, $uniqueBy, $updateColumns);
    }
}
