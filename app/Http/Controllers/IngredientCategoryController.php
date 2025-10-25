<?php

namespace App\Http\Controllers;

use App\Http\Resources\IngredientCategoryResource;
use App\Models\IngredientCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class IngredientCategoryController extends Controller
{
    /**
     * Display a listing of ingredient categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = IngredientCategory::withCount('ingredients');

        // Filter by active status
        if ($request->has('is_active')) {
            $is_active = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $is_active);
        } else {
            $query->active();
        }

        // Order by sort order
        $query->ordered();

        $categories = $query->get();

        return IngredientCategoryResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ingredient_categories,name',
            'slug' => 'nullable|string|max:255|unique:ingredient_categories,slug',
            'description' => 'nullable|string',
            'color_hex' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Auto-generate slug if not provided
        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = IngredientCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => new IngredientCategoryResource($category),
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(string $id): JsonResponse
    {
        $category = IngredientCategory::withCount('ingredients')
            ->findOrFail($id);

        return response()->json([
            'data' => new IngredientCategoryResource($category),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = IngredientCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:ingredient_categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:ingredient_categories,slug,' . $id,
            'description' => 'nullable|string',
            'color_hex' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Auto-generate slug if name changed but slug not provided
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new IngredientCategoryResource($category),
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = IngredientCategory::findOrFail($id);

        // Check if category has ingredients
        if ($category->ingredients()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with existing ingredients',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}

