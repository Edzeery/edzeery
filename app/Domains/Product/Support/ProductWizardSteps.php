<?php

namespace App\Domains\Product\Support;

use Closure;
use Illuminate\Validation\Rule;

/**
 * Single source of truth for the product creation wizard's step definitions.
 *
 * Each step carries its own label, icon, partial view and per-step validation
 * resolver. The order in this list is the order the steps render in.
 */
final class ProductWizardSteps
{
    public const STEP_BASIC = 1;

    public const STEP_IMAGES = 2;

    public const STEP_PRICING = 3;

    public const STEP_OPTIONS = 4;

    public const STEP_INVENTORY = 5;

    public const STEP_REVIEW = 6;

    public const LAST_STEP = self::STEP_REVIEW;

    /**
     * Ordered definitions for every wizard step.
     *
     * @return array<int, array{id:int, label:string, icon:string, partial:string}>
     */
    public static function all(): array
    {
        return [
            self::STEP_BASIC => [
                'id' => self::STEP_BASIC,
                'label' => __('products.step_basic_info'),
                'icon' => 'information-circle',
                'partial' => 'livewire.merchant.products.form.step-basic',
            ],
            self::STEP_IMAGES => [
                'id' => self::STEP_IMAGES,
                'label' => __('products.step_images'),
                'icon' => 'image',
                'partial' => 'livewire.merchant.products.form.step-images',
            ],
            self::STEP_PRICING => [
                'id' => self::STEP_PRICING,
                'label' => __('products.step_pricing'),
                'icon' => 'currency-dollar',
                'partial' => 'livewire.merchant.products.form.step-pricing',
            ],
            self::STEP_OPTIONS => [
                'id' => self::STEP_OPTIONS,
                'label' => __('products.step_options'),
                'icon' => 'adjustments',
                'partial' => 'livewire.merchant.products.form.step-options',
            ],
            self::STEP_INVENTORY => [
                'id' => self::STEP_INVENTORY,
                'label' => __('products.step_inventory'),
                'icon' => 'archive-box',
                'partial' => 'livewire.merchant.products.form.step-inventory',
            ],
            self::STEP_REVIEW => [
                'id' => self::STEP_REVIEW,
                'label' => __('products.step_review'),
                'icon' => 'check-circle',
                'partial' => 'livewire.merchant.products.form.step-review',
            ],
        ];
    }

    /** @return array<int, int> */
    public static function ids(): array
    {
        return array_keys(self::all());
    }

    public static function count(): int
    {
        return count(self::all());
    }

    public static function isExistingStep(int $step): bool
    {
        return isset(self::all()[$step]);
    }

    /**
     * Per-step validation resolver.
     *
     * Returns the rules that must pass before the wizard may advance from (or
     * freely navigate to) the given step. Steps without input (options, review)
     * resolve to an empty rule set.
     *
     * @param array{store_id?: string|null, product_id?: string|null, min_order_qty?: int|string|null} $context
     * @return array<string, mixed>
     */
    public static function rulesFor(int $step, array $context = []): array
    {
        $storeId = $context['store_id'] ?? null;
        $productId = $context['product_id'] ?? null;
        $minOrderQty = $context['min_order_qty'] ?? null;

        return match ($step) {
            self::STEP_BASIC => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->where('store_id', $storeId)->whereNull('deleted_at')->ignore($productId)],
                'brand_id' => ['nullable', 'string', 'max:255'],
                'categories' => ['nullable', 'array'],
                'short_description' => ['nullable', 'string', 'max:500'],
                'description' => ['nullable', 'string'],
            ],
            self::STEP_IMAGES => [],
            self::STEP_PRICING => [
                'price' => ['nullable', 'numeric', 'min:0'],
                'compare_price' => ['nullable', 'numeric', 'min:0'],
                'cost_price' => ['nullable', 'numeric', 'min:0'],
                'min_order_qty' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'max_order_qty' => [
                    'nullable', 'integer', 'min:1', 'max:100000',
                    function (string $attribute, mixed $value, Closure $fail) use ($minOrderQty): void {
                        if ($value !== null && (int) $minOrderQty !== 0 && (int) $value < (int) ($minOrderQty ?? 1)) {
                            $fail(__('merchant_panel.max_below_min_error'));
                        }
                    },
                ],
            ],
            self::STEP_OPTIONS => [],
            self::STEP_INVENTORY => [
                'stock' => ['nullable', 'integer', 'min:0'],
                'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
                'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->where('store_id', $storeId)->whereNull('deleted_at')->ignore($productId)],
                'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')->where('store_id', $storeId)->whereNull('deleted_at')->ignore($productId)],
            ],
            default => [],
        };
    }
}