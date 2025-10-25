<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScientificReference extends Model
{
    /** @use HasFactory<\Database\Factories\ScientificReferenceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'abstract',
        'authors',
        'journal',
        'publication_date',
        'doi',
        'pubmed_id',
        'url',
        'reference_type',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'authors' => 'array',
            'publication_date' => 'date',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the ingredients that reference this scientific reference.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(
            Ingredient::class,
            'ingredient_references',
            'reference_id',
            'ingredient_id'
        )->withPivot('relevance_level', 'notes')
          ->withTimestamps();
    }

    /**
     * Scope a query to filter by reference type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('reference_type', $type);
    }

    /**
     * Scope a query to search references by title or abstract.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('abstract', 'like', "%{$search}%")
              ->orWhere('doi', 'like', "%{$search}%")
              ->orWhere('pubmed_id', 'like', "%{$search}%");
        });
    }
}

