<?php

namespace Database\Factories;

use App\Models\ScientificReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScientificReference>
 */
class ScientificReferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ScientificReference::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(10),
            'abstract' => fake()->paragraphs(3, true),
            'authors' => [
                ['name' => fake()->name(), 'affiliation' => fake()->company()],
                ['name' => fake()->name(), 'affiliation' => fake()->company()],
            ],
            'journal' => fake()->words(3, true) . ' Journal',
            'publication_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'doi' => '10.' . fake()->numberBetween(1000, 9999) . '/' . fake()->word() . '.' . fake()->year(),
            'pubmed_id' => (string) fake()->numberBetween(10000000, 99999999),
            'url' => fake()->url(),
            'reference_type' => fake()->randomElement(['journal', 'clinical_trial', 'review', 'book', 'patent', 'other']),
            'metadata' => [
                'volume' => fake()->numberBetween(1, 50),
                'issue' => fake()->numberBetween(1, 12),
                'pages' => fake()->numberBetween(1, 50) . '-' . fake()->numberBetween(51, 100),
            ],
        ];
    }

    /**
     * Indicate that the reference is a journal article.
     */
    public function journal(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'journal',
        ]);
    }

    /**
     * Indicate that the reference is a clinical trial.
     */
    public function clinicalTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'clinical_trial',
        ]);
    }

    /**
     * Indicate that the reference is a review.
     */
    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'review',
        ]);
    }
}

