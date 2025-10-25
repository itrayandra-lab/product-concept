<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'inci_name' => $this->inci_name,
            'description' => $this->description,
            'effects' => $this->effects ?? [],
            'safety_notes' => $this->safety_notes,
            'concentration_ranges' => $this->concentration_ranges ?? [],
            'is_active' => $this->is_active,
            'scientific_references' => $this->scientific_references ?? [],
            'category' => $this->when($this->relationLoaded('category'), function () {
                return $this->category ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'color_hex' => $this->category->color_hex,
                ] : null;
            }),
            'references' => $this->when($this->relationLoaded('references'), function () {
                return ScientificReferenceResource::collection($this->references);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

