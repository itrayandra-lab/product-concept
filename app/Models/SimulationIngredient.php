<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationIngredient extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'simulation_ingredients';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'simulation_id',
        'ingredient_id',
        'concentration_percentage',
        'concentration_unit',
        'custom_notes',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * Updated_at is not needed for this table.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'concentration_percentage' => 'float',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the simulation that owns the ingredient.
     */
    public function simulation(): BelongsTo
    {
        return $this->belongsTo(SimulationHistory::class, 'simulation_id');
    }

    /**
     * Get the ingredient.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}

