<?php

namespace Database\Seeders;

use App\Models\IngredientCategory;
use Illuminate\Database\Seeder;

class IngredientCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Humectants',
                'slug' => 'humectants',
                'description' => 'Ingredients that attract and retain moisture in the skin',
                'color_hex' => '#3B82F6',
                'sort_order' => 10,
            ],
            [
                'name' => 'Emollients',
                'slug' => 'emollients',
                'description' => 'Ingredients that soften and smooth the skin',
                'color_hex' => '#10B981',
                'sort_order' => 20,
            ],
            [
                'name' => 'Antioxidants',
                'slug' => 'antioxidants',
                'description' => 'Ingredients that protect skin from free radical damage',
                'color_hex' => '#F59E0B',
                'sort_order' => 30,
            ],
            [
                'name' => 'Active Ingredients',
                'slug' => 'active-ingredients',
                'description' => 'Potent ingredients that address specific skin concerns',
                'color_hex' => '#EF4444',
                'sort_order' => 40,
            ],
            [
                'name' => 'Preservatives',
                'slug' => 'preservatives',
                'description' => 'Ingredients that prevent microbial growth',
                'color_hex' => '#8B5CF6',
                'sort_order' => 50,
            ],
            [
                'name' => 'Surfactants',
                'slug' => 'surfactants',
                'description' => 'Cleansing and emulsifying agents',
                'color_hex' => '#06B6D4',
                'sort_order' => 60,
            ],
            [
                'name' => 'Thickeners',
                'slug' => 'thickeners',
                'description' => 'Ingredients that increase product viscosity',
                'color_hex' => '#64748B',
                'sort_order' => 70,
            ],
            [
                'name' => 'Vitamins',
                'slug' => 'vitamins',
                'description' => 'Essential nutrients for skin health',
                'color_hex' => '#EC4899',
                'sort_order' => 80,
            ],
            [
                'name' => 'Peptides',
                'slug' => 'peptides',
                'description' => 'Amino acid chains that support skin structure',
                'color_hex' => '#14B8A6',
                'sort_order' => 90,
            ],
            [
                'name' => 'Botanical Extracts',
                'slug' => 'botanical-extracts',
                'description' => 'Plant-derived ingredients with various benefits',
                'color_hex' => '#84CC16',
                'sort_order' => 100,
            ],
        ];

        foreach ($categories as $category) {
            IngredientCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}

