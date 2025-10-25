<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'inci_name',
        'description',
        'effects',
        'safety_notes',
        'concentration_ranges',
        'category_id',
        'is_active',
        'scientific_references',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effects' => 'array',
            'concentration_ranges' => 'array',
            'scientific_references' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category that the ingredient belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id');
    }

    /**
     * Get the simulation ingredients that use this ingredient.
     */
    public function simulationIngredients(): HasMany
    {
        return $this->hasMany(SimulationIngredient::class);
    }

    /**
     * Get the scientific references for the ingredient.
     */
    public function references(): BelongsToMany
    {
        return $this->belongsToMany(
            ScientificReference::class,
            'ingredient_references',
            'ingredient_id',
            'reference_id'
        )->withPivot('relevance_level', 'notes')
          ->withTimestamps();
    }

    /**
     * Scope a query to only include active ingredients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search ingredients by name or INCI name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('inci_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
            
            // Only use JSON_SEARCH for MySQL
            if (config('database.default') === 'mysql') {
                $q->orWhereRaw("JSON_SEARCH(effects, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            } else {
                // Fallback for SQLite and other databases
                $q->orWhere('effects', 'like', "%{$search}%");
            }
        });
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}

