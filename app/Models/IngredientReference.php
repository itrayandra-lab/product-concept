<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientReference extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingredient_references';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ingredient_id',
        'reference_id',
        'relevance_level',
        'notes',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the ingredient that owns the reference.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Get the scientific reference.
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(ScientificReference::class, 'reference_id');
    }
}

