<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSimulationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Brand & Product Name
            'nama_brand' => ['required', 'string', 'max:255'],
            'nama_produk' => ['required', 'string', 'max:255'],
            
            // Core Fields (Required)
            'fungsi_produk' => ['required', 'array', 'min:1', 'max:6'],
            'fungsi_produk.*' => ['required', 'string', 'max:100'],
            
            'bentuk_formulasi' => ['required', 'string', 'max:100'],
            
            'target_gender' => ['required', 'string', Rule::in([
                'Semua Gender',
                'Wanita',
                'Pria',
                'Non-Binary',
            ])],
            
            'target_usia' => ['required', 'array', 'min:1'],
            'target_usia.*' => ['required', 'string', 'max:50'],
            
            'target_negara' => ['required', 'string', 'max:100'],
            
            'deskripsi_formula' => ['required', 'string', 'min:50', 'max:2000'],
            
            'bahan_aktif' => ['required', 'array', 'min:1', 'max:10'],
            'bahan_aktif.*.name' => ['required', 'string', 'max:255'],
            'bahan_aktif.*.concentration' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'bahan_aktif.*.unit' => ['nullable', 'string', Rule::in(['%', 'mg', 'g', 'ml', 'ppm'])],
            
            // Optional Advanced Configuration
            'benchmark_product' => ['nullable', 'string', 'max:255'],
            
            'volume' => ['required', 'numeric', 'min:0.1'],
            'volume_unit' => ['required', 'string', Rule::in(['ml', 'gram', 'oz', 'unit'])],
            
            'hex_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            
            'jenis_kemasan' => ['required', 'string', 'max:255'],
            'finishing_kemasan' => ['nullable', 'string', Rule::in([
                'Matte',
                'Glossy',
                'Satin',
                'Frosted',
                'Metallic',
            ])],
            'bahan_kemasan' => ['nullable', 'string', 'max:500'],
            
            'target_hpp' => ['nullable', 'integer', 'min:1000'],
            'target_hpp_currency' => ['nullable', 'string', Rule::in(['IDR', 'USD', 'EUR'])],
            
            'moq' => ['nullable', 'integer', 'min:100'],
            
            'tekstur' => ['nullable', 'string', 'max:255'],
            'aroma' => ['nullable', 'string', 'max:255'],
            
            'klaim_produk' => ['nullable', 'array', 'max:10'],
            'klaim_produk.*' => ['string', 'max:255'],
            
            'sertifikasi' => ['nullable', 'array', 'max:10'],
            'sertifikasi.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_brand.required' => 'Brand name is required.',
            'nama_produk.required' => 'Product name is required.',
            
            'fungsi_produk.required' => 'At least one product function is required.',
            'fungsi_produk.min' => 'At least one product function must be selected.',
            'fungsi_produk.max' => 'Maximum 6 product functions can be selected.',
            
            'bentuk_formulasi.required' => 'Product formulation type is required.',
            
            'target_gender.required' => 'Target gender is required.',
            'target_gender.in' => 'Invalid target gender selected.',
            
            'target_usia.required' => 'At least one target age range is required.',
            'target_usia.min' => 'At least one target age range must be selected.',
            
            'target_negara.required' => 'Target country is required.',
            
            'deskripsi_formula.required' => 'Product description is required.',
            'deskripsi_formula.min' => 'Product description must be at least 50 characters.',
            'deskripsi_formula.max' => 'Product description cannot exceed 2000 characters.',
            
            'bahan_aktif.required' => 'At least one active ingredient is required.',
            'bahan_aktif.min' => 'At least one active ingredient must be provided.',
            'bahan_aktif.max' => 'Maximum 10 active ingredients can be added.',
            'bahan_aktif.*.name.required' => 'Ingredient name is required.',
            'bahan_aktif.*.concentration.numeric' => 'Ingredient concentration must be a number.',
            'bahan_aktif.*.concentration.min' => 'Ingredient concentration must be at least 0.01.',
            'bahan_aktif.*.concentration.max' => 'Ingredient concentration cannot exceed 100.',
            
            'volume.required' => 'Product volume is required.',
            'volume.min' => 'Product volume must be at least 0.1.',
            'volume_unit.required' => 'Volume unit is required.',
            
            'jenis_kemasan.required' => 'Packaging type is required.',
            
            'hex_color.required' => 'Product color code is required.',
            'hex_color.regex' => 'Color code must be in hex format (e.g., #FFFFFF).',
            
            'target_hpp.min' => 'Target HPP must be at least 1000.',
            'moq.min' => 'Minimum order quantity must be at least 100.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_brand' => 'brand name',
            'nama_produk' => 'product name',
            'fungsi_produk' => 'product functions',
            'bentuk_formulasi' => 'formulation type',
            'target_gender' => 'target gender',
            'target_usia' => 'target age ranges',
            'target_negara' => 'target country',
            'deskripsi_formula' => 'product description',
            'bahan_aktif' => 'active ingredients',
            'volume' => 'product volume',
            'volume_unit' => 'volume unit',
            'hex_color' => 'color code',
            'jenis_kemasan' => 'packaging type',
            'finishing_kemasan' => 'packaging finish',
            'bahan_kemasan' => 'packaging material',
            'target_hpp' => 'target HPP',
            'moq' => 'minimum order quantity',
            'tekstur' => 'texture',
            'aroma' => 'fragrance',
            'klaim_produk' => 'product claims',
            'sertifikasi' => 'certifications',
        ];
    }
}

