<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\IngredientCategorySeeder::class);
    }

    public function test_can_list_ingredients(): void
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'inci_name',
                        'description',
                        'effects',
                        'is_active',
                    ]
                ]
            ]);
    }

    public function test_can_search_ingredients_by_name(): void
    {
        Ingredient::factory()->create(['name' => 'Hyaluronic Acid']);
        Ingredient::factory()->create(['name' => 'Vitamin C']);

        $response = $this->getJson('/api/ingredients?search=Hyaluronic');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_ingredients_by_category(): void
    {
        $category = IngredientCategory::first();
        Ingredient::factory()->count(2)->create(['category_id' => $category->id]);
        Ingredient::factory()->create(); // Different category

        $response = $this->getJson("/api/ingredients?category_id={$category->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_ingredient_details(): void
    {
        $ingredient = Ingredient::factory()->create();

        $response = $this->getJson("/api/ingredients/{$ingredient->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $ingredient->id,
                'name' => $ingredient->name,
            ]);
    }

    public function test_can_create_ingredient_when_authenticated(): void
    {
        $user = User::factory()->create();
        $category = IngredientCategory::first();

        $ingredientData = [
            'name' => 'Test Ingredient',
            'inci_name' => 'TEST_INGREDIENT',
            'description' => 'A test ingredient',
            'effects' => ['Moisturizing', 'Hydrating'],
            'category_id' => $category->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/ingredients', $ingredientData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Ingredient',
                'inci_name' => 'TEST_INGREDIENT',
            ]);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Test Ingredient',
        ]);
    }

    public function test_cannot_create_ingredient_when_not_authenticated(): void
    {
        $category = IngredientCategory::first();

        $ingredientData = [
            'name' => 'Test Ingredient',
            'inci_name' => 'TEST_INGREDIENT',
            'category_id' => $category->id,
        ];

        $response = $this->postJson('/api/ingredients', $ingredientData);

        $response->assertStatus(401);
    }

    public function test_can_update_ingredient_when_authenticated(): void
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/ingredients/{$ingredient->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Name',
            ]);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_deactivate_ingredient(): void
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ingredients/{$ingredient->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'is_active' => false,
        ]);
    }

    public function test_can_get_related_ingredients(): void
    {
        $category = IngredientCategory::first();
        $ingredient = Ingredient::factory()->create(['category_id' => $category->id]);
        Ingredient::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/ingredients/{$ingredient->id}/related");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}

