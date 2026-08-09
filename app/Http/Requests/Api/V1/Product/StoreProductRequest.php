<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $storeId = currentStoreId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('products', 'slug')->where(fn ($query) => $query->where('store_id', $storeId)),
            ],
            'sku' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'sku')->where(fn ($query) => $query->where('store_id', $storeId)),
            ],
            'barcode' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['simple', 'variable'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'brand_id' => [
                'nullable',
                Rule::exists('brands', 'id')->where(fn ($query) => $query->where('store_id', $storeId)),
            ],
            'primary_category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('store_id', $storeId)),
            ],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->filled('slug') ? (string) $this->string('slug') : Str::slug((string) $this->string('name')),
            'type' => $this->input('type', 'simple'),
            'unit' => $this->input('unit', 'piece'),
            'is_active' => $this->boolean('is_active', true),
            'is_featured' => $this->boolean('is_featured', false),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
