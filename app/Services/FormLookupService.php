<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormLookupService
{
    private const CACHE_KEY = 'simulator.form-lookups';
    private const CACHE_TTL_SECONDS = 900;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            $ageRanges = $this->mapDemographic('age');

            return [
                'productTypes' => $this->mapSimpleTable('product_types'),
                'productFunctions' => $this->mapSimpleTable('product_functions'),
                'packagingTypes' => $this->mapSimpleTable('packaging_types'),
                'targetGenders' => $this->mapDemographic('gender'),
                'targetAgeRanges' => $ageRanges,
                'ageRanges' => $ageRanges,
                'countries' => config('simulator.supported_countries', []),
                'finishingOptions' => config('simulator.finishing_options', []),
                'textureOptions' => config('simulator.texture_options', []),
                'aromaOptions' => config('simulator.aroma_options', []),
                'claims' => config('simulator.default_claims', []),
                'certifications' => config('simulator.default_certifications', []),
            ];
        });
    }

    /**
     * @return array<int, array{name: string, slug: string, label: string, value: string}>
     */
    private function mapSimpleTable(string $table): array
    {
        return $this->orderedQuery($table)
            ->map(static function (object $row): array {
                $label = $row->name ?? $row->display_name ?? $row->value ?? 'Item';

                return [
                    'name' => $label,
                    'slug' => $row->slug ?? Str::slug($label),
                    'label' => $label,
                    'value' => $row->value ?? $label,
                    'description' => $row->description ?? null,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{name: string, label: string, value: string}>
     */
    private function mapDemographic(string $type): array
    {
        return $this->orderedQuery('target_demographics')
            ->where('type', $type)
            ->map(static function (object $row): array {
                $label = $row->display_name ?? $row->value ?? $row->name ?? 'Item';

                return [
                    'name' => $label,
                    'label' => $label,
                    'value' => $row->value ?? $label,
                ];
            })
            ->all();
    }

    private function orderedQuery(string $table): Collection
    {
        return DB::table($table)
            ->select('*')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
