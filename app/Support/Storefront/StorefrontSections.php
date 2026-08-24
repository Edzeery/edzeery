<?php

namespace App\Support\Storefront;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Single source of truth for storefront homepage sections & theme options.
 *
 * Consumed by:
 *  - Merchant settings page (resources/views/livewire/merchant/storefront-settings*)
 *  - Storefront templates (resources/views/livewire/storefront/templates/*)
 *  - StoreThemeSetting model (last-resort persistence guard)
 *  - StorefrontThemeService (write path)
 *
 * Contract: section_content always contains every key of defaults(),
 * each with the exact same scalar fields, and exactly 3 normalized items
 * for list-based sections. Consumers may still defensively fall back.
 */
final class StorefrontSections
{
    public const DEFAULT_PRIMARY_COLOR = '#4f46e5';
    public const DEFAULT_SECONDARY_COLOR = '#7c3aed';
    public const DEFAULT_FONT_FAMILY = 'Cairo';

    /** Valid #RGB / #RRGGBB hex color. */
    public const HEX_PATTERN = '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/';

    /** Allowed homepage section keys. */
    public const ALL = ['hero', 'social_proof', 'faq', 'cta', 'categories', 'brands', 'description'];

    /** Sections enabled for freshly-created themes. */
    public const DEFAULT_ENABLED = ['hero', 'social_proof', 'faq', 'cta'];

    /**
     * Sections rendered when a store has no theme row yet, per landing
     * template. Single source of truth for every storefront template's
     * fallback so they can never drift apart again.
     */
    public static function defaultSectionsFor(string $template): array
    {
        return match ($template) {
            'catalog' => ['hero', 'categories', 'social_proof'],
            'brand' => ['hero', 'brands', 'social_proof'],
            default => self::DEFAULT_ENABLED,
        };
    }

    /** Number of items rendered for list-based sections (social_proof, faq). */
    public const ITEMS_LIMIT = 3;

    /**
     * Icon names merchants may pick for social_proof items.
     * Every entry must resolve to a glyph in the x-edz.icon component map
     * (resources/views/components/edz/icon.blade.php); a contract test
     * guards this invariant. Aliases are intentionally excluded — the
     * picker stores canonical names only.
     */
    public const ICONS = [
        'shield-check', 'lock-closed', 'banknotes', 'credit-card',
        'truck', 'refresh', 'star', 'check-badge', 'check-circle', 'check',
        'package', 'bag', 'cart', 'cube', 'tag', 'ribbon', 'megaphone',
        'phone', 'map-pin', 'building-store', 'storefront', 'users', 'user',
        'globe', 'help-circle', 'info-circle',
    ];

    /** Max length guards per free-text field family. Public: the editor
     *  partials read these for native maxlength + live char counters so the
     *  UI can never drift from validation. */
    public const TEXT_LIMITS = [
        'title' => 120,
        'description' => 500,
        'button_text' => 60,
        'question' => 200,
        'answer' => 800,
        'item_title' => 80,
        'item_description' => 160,
    ];

    /**
     * Font families merchants may choose from.
     * Inter is loaded globally via app.css; the rest are lazy-loaded per store.
     */
    public const FONTS = [
        'Cairo',
        'Tajawal',
        'Inter',
        'Poppins',
        'Playfair Display',
        'Montserrat',
        'Lato',
        'Nunito',
    ];

    /**
     * Translated default content used to prefill the editor and as
     * normalization baseline. Icon names must exist in x-edz.icon map.
     */
    public static function defaults(): array
    {
        return [
            'hero' => ['title' => '', 'description' => '', 'button_text' => ''],
            'social_proof' => [
                'title' => __('storefront.why_customers_love_us'),
                'items' => [
                    ['title' => __('storefront.secure_payment'), 'description' => __('storefront.pay_on_delivery'), 'icon' => 'shield-check'],
                    ['title' => __('storefront.fast_delivery'), 'description' => __('storefront.across_the_country'), 'icon' => 'truck'],
                    ['title' => __('storefront.easy_returns'), 'description' => __('storefront.hassle_free_policy'), 'icon' => 'refresh'],
                ],
            ],
            'faq' => [
                'title' => __('storefront.faq'),
                'items' => [
                    ['question' => __('storefront.faq_delivery_q'), 'answer' => __('storefront.faq_delivery_a')],
                    ['question' => __('storefront.faq_payment_q'), 'answer' => __('storefront.faq_payment_a')],
                    ['question' => __('storefront.faq_return_q'), 'answer' => __('storefront.faq_return_a')],
                ],
            ],
            'cta' => [
                'title' => __('storefront.ready_to_order'),
                'description' => __('storefront.get_yours_now'),
                'button_text' => __('storefront.order_now'),
            ],
            'categories' => ['title' => __('storefront.categories')],
            'brands' => ['title' => __('storefront.collections')],
            'description' => ['title' => __('storefront.product_details')],
            // Template-scoped setting (not a toggleable homepage section):
            // which product the single_product template showcases.
            // Empty string = automatic (first active product).
            'single_product' => ['product_id' => ''],
        ];
    }

    /**
     * Force arbitrary input into the canonical contract shape:
     * - drops unknown section keys
     * - fills missing fields/items from defaults
     * - clamps list items to ITEMS_LIMIT
     * - coerces every stored field to a trimmed string
     */
    public static function normalize(?array $content): array
    {
        $content = is_array($content) ? $content : [];
        $normalized = [];

        foreach (self::defaults() as $key => $defaultShape) {
            $incoming = $content[$key] ?? null;
            if (! is_array($incoming)) {
                $incoming = [];
            }

            $section = [];

            foreach ($defaultShape as $field => $preset) {
                if ($field === 'items' && is_array($preset)) {
                    $section[$field] = self::normalizeItems($incoming[$field] ?? [], $preset);
                } else {
                    $section[$field] = self::scalar($incoming[$field] ?? null, (string) $preset);
                }
            }

            $normalized[$key] = $section;
        }

        return $normalized;
    }

    /**
     * Component-side validation rules (theme payload).
     * Content rules assume normalization ran first (exact known shape).
     *
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        $inAll = 'in:' . implode(',', self::ALL);

        return [
            'primary_color' => ['required', 'regex:' . self::HEX_PATTERN],
            'secondary_color' => ['required', 'regex:' . self::HEX_PATTERN],
            'font_family' => ['required', 'in:' . implode(',', self::FONTS)],
            'sections' => ['required', 'array'],
            'sections.*' => ['string', $inAll],
            'section_content' => ['required', 'array'],

            'section_content.hero.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.hero.description' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['description']],
            'section_content.hero.button_text' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['button_text']],

            'section_content.social_proof.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.social_proof.items' => ['required', 'array', 'size:' . self::ITEMS_LIMIT],
            'section_content.social_proof.items.*.title' => ['required', 'string', 'max:' . self::TEXT_LIMITS['item_title']],
            'section_content.social_proof.items.*.description' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['item_description']],
            'section_content.social_proof.items.*.icon' => ['nullable', 'string', 'in:' . implode(',', self::ICONS)],

            'section_content.faq.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.faq.items' => ['required', 'array', 'size:' . self::ITEMS_LIMIT],
            'section_content.faq.items.*.question' => ['required', 'string', 'max:' . self::TEXT_LIMITS['question']],
            'section_content.faq.items.*.answer' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['answer']],

            'section_content.cta.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.cta.description' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['description']],
            'section_content.cta.button_text' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['button_text']],

            'section_content.categories.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.brands.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],
            'section_content.description.title' => ['nullable', 'string', 'max:' . self::TEXT_LIMITS['title']],

            // Format-only: ownership/activeness is enforced at save time.
            'section_content.single_product.product_id' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * Persistence-time guardrail for raw theme attributes (model hook).
     * Sanitizes list payloads defensively (legacy rows may contain stale
     * keys), then enforces type/format safety on everything else.
     *
     * @throws ValidationException
     */
    public static function assertValidThemeData(array $theme): void
    {
        if (isset($theme['homepage_sections']) && is_array($theme['homepage_sections'])) {
            $theme['homepage_sections'] = array_values(array_intersect(self::ALL, $theme['homepage_sections']));
        }

        if (isset($theme['section_content']) && is_array($theme['section_content'])) {
            foreach ($theme['section_content'] as $key => $value) {
                $allowed = in_array($key, self::ALL, true) || $key === 'single_product';

                if (! is_string($key) || ! $allowed) {
                    unset($theme['section_content'][$key]);
                }
            }
        }

        Validator::make($theme, [
            'primary_color' => ['nullable', 'regex:' . self::HEX_PATTERN],
            'secondary_color' => ['nullable', 'regex:' . self::HEX_PATTERN],
            'font_family' => ['nullable', 'in:' . implode(',', self::FONTS)],
            'homepage_sections' => ['nullable', 'array'],
            'homepage_sections.*' => ['string', 'in:' . implode(',', self::ALL)],
            'section_content' => ['nullable', 'array'],
            'section_content.*' => ['array'],
        ])->validate();
    }

    /**
     * Google Fonts stylesheet URL for a whitelisted family, or null when
     * the family is already bundled (no extra network request needed).
     */
    public static function googleFontUrl(string $fontFamily): ?string
    {
        $queries = [
            'Cairo' => 'family=Cairo:wght@400;600;700',
            'Tajawal' => 'family=Tajawal:wght@400;500;700',
            'Poppins' => 'family=Poppins:wght@400;500;600;700',
            'Playfair Display' => 'family=Playfair+Display:wght@400;600;700',
            'Montserrat' => 'family=Montserrat:wght@400;500;600;700',
            'Lato' => 'family=Lato:wght@400;700',
            'Nunito' => 'family=Nunito:wght@400;600;700',
        ];

        $query = $queries[$fontFamily] ?? null;

        return $query ? 'https://fonts.googleapis.com/css2?' . $query . '&display=swap' : null;
    }

    /**
     * @param  array<int, mixed>  $incoming
     * @param  array<int, array<string, string>>  $presets
     * @return list<array<string, string>>
     */
    private static function normalizeItems(mixed $incoming, array $presets): array
    {
        $items = is_array($incoming) ? array_values($incoming) : [];
        $normalized = [];

        for ($i = 0; $i < self::ITEMS_LIMIT; $i++) {
            $shape = $presets[$i] ?? $presets[0] ?? [];
            $raw = $items[$i] ?? null;
            if (! is_array($raw)) {
                $raw = [];
            }

            $entry = [];
            foreach ($shape as $field => $preset) {
                $entry[$field] = self::scalar($raw[$field] ?? null, (string) $preset);
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

    private static function scalar(mixed $value, string $fallback): string
    {
        if (! isset($value) || ! is_scalar($value)) {
            return $fallback;
        }

        return trim((string) $value);
    }
}
