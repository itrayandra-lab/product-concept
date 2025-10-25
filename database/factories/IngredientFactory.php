<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Ingredient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $effects = fake()->randomElements([
            'Moisturizing', 'Anti-aging', 'Brightening', 'Acne Treatment',
            'Antioxidant', 'Anti-inflammatory', 'Exfoliating', 'Soothing',
            'Hydrating', 'Firming', 'Pore Minimizing', 'Oil Control'
        ], fake()->numberBetween(1, 4));

        return [
            'name' => ucfirst($name),
            'inci_name' => strtoupper(Str::slug($name, '_')),
            'description' => fake()->paragraph(),
            'effects' => $effects,
            'safety_notes' => fake()->sentence(),
            'concentration_ranges' => [
                'serum' => ['min' => 0.5, 'max' => 5.0],
                'cream' => ['min' => 1.0, 'max' => 10.0],
            ],
            'category_id' => IngredientCategory::factory(),
            'is_active' => true,
            'scientific_references' => [],
        ];
    }

    /**
     * Indicate that the ingredient is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the ingredient has no category.
     */
    public function withoutCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
        ]);
    }

    /**
     * Set specific effects for the ingredient.
     */
    public function withEffects(array $effects): static
    {
        return $this->state(fn (array $attributes) => [
            'effects' => $effects,
        ]);
    }
}

