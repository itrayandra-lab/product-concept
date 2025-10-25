<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScientificReferenceResource extends JsonResource
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
            'title' => $this->title,
            'abstract' => $this->abstract,
            'authors' => $this->authors ?? [],
            'journal' => $this->journal,
            'publication_date' => $this->publication_date?->format('Y-m-d'),
            'doi' => $this->doi,
            'pubmed_id' => $this->pubmed_id,
            'url' => $this->url,
            'reference_type' => $this->reference_type,
            'metadata' => $this->metadata ?? [],
            'pivot' => $this->when($this->pivot, function () {
                return [
                    'relevance_level' => $this->pivot->relevance_level,
                    'notes' => $this->pivot->notes,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

