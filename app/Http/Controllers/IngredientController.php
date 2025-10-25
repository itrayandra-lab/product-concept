<?php

namespace App\Http\Controllers;

use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IngredientController extends Controller
{
    /**
     * Display a listing of ingredients with search and filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Ingredient::with('category');

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $is_active = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $is_active);
        } else {
            // Default to active ingredients only
            $query->active();
        }

        // Order by name
        $query->orderBy('name');

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $ingredients = $query->paginate($perPage);

        return IngredientResource::collection($ingredients);
    }

    /**
     * Store a newly created ingredient.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inci_name' => 'required|string|max:255|unique:ingredients,inci_name',
            'description' => 'nullable|string',
            'effects' => 'nullable|array',
            'safety_notes' => 'nullable|string',
            'concentration_ranges' => 'nullable|array',
            'category_id' => 'nullable|exists:ingredient_categories,id',
            'is_active' => 'boolean',
            'scientific_references' => 'nullable|array',
        ]);

        $ingredient = Ingredient::create($validated);
        $ingredient->load('category');

        return response()->json([
            'message' => 'Ingredient created successfully',
            'data' => new IngredientResource($ingredient),
        ], 201);
    }

    /**
     * Display the specified ingredient.
     */
    public function show(string $id): JsonResponse
    {
        $ingredient = Ingredient::with(['category', 'references'])
            ->findOrFail($id);

        return response()->json([
            'data' => new IngredientResource($ingredient),
        ]);
    }

    /**
     * Display the specified ingredient by INCI name.
     */
    public function showByInci(string $inci_name): JsonResponse
    {
        $ingredient = Ingredient::with(['category', 'references'])
            ->where('inci_name', $inci_name)
            ->firstOrFail();

        return response()->json([
            'data' => new IngredientResource($ingredient),
        ]);
    }

    /**
     * Update the specified ingredient.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ingredient = Ingredient::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'inci_name' => 'sometimes|required|string|max:255|unique:ingredients,inci_name,' . $id,
            'description' => 'nullable|string',
            'effects' => 'nullable|array',
            'safety_notes' => 'nullable|string',
            'concentration_ranges' => 'nullable|array',
            'category_id' => 'nullable|exists:ingredient_categories,id',
            'is_active' => 'boolean',
            'scientific_references' => 'nullable|array',
        ]);

        $ingredient->update($validated);
        $ingredient->load('category');

        return response()->json([
            'message' => 'Ingredient updated successfully',
            'data' => new IngredientResource($ingredient),
        ]);
    }

    /**
     * Remove the specified ingredient (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $ingredient = Ingredient::findOrFail($id);
        
        // Instead of hard delete, we deactivate the ingredient
        $ingredient->update(['is_active' => false]);

        return response()->json([
            'message' => 'Ingredient deactivated successfully',
        ]);
    }

    /**
     * Get related ingredients from the same category.
     */
    public function related(string $id): AnonymousResourceCollection
    {
        $ingredient = Ingredient::findOrFail($id);
        
        $relatedIngredients = Ingredient::active()
            ->where('category_id', $ingredient->category_id)
            ->where('id', '!=', $id)
            ->limit(5)
            ->get();

        return IngredientResource::collection($relatedIngredients);
    }
}

