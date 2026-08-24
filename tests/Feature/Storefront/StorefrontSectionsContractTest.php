<?php

use App\Support\Storefront\StorefrontSections;

covers(StorefrontSections::class);

test('normalize fills missing sections from defaults', function () {
    $normalized = StorefrontSections::normalize(null);

    expect(array_keys($normalized))->toBe(array_keys(StorefrontSections::defaults()))
        ->and($normalized['social_proof']['items'])->toHaveCount(StorefrontSections::ITEMS_LIMIT)
        ->and($normalized['faq']['items'])->toHaveCount(StorefrontSections::ITEMS_LIMIT)
        ->and($normalized['hero']['title'])->toBe('');
});

test('normalize drops unknown section keys and unknown fields', function () {
    $normalized = StorefrontSections::normalize([
        'evil_key' => ['title' => 'injected'],
        'hero' => ['title' => 'Custom', 'rogue_field' => '<script>', 'button_text' => 123],
        'cta' => 'not-an-array',
    ]);

    expect($normalized)->not->toHaveKey('evil_key')
        ->and($normalized['hero'])->toBe(['title' => 'Custom', 'description' => '', 'button_text' => '123'])
        ->and($normalized['cta'])->toBe(StorefrontSections::defaults()['cta']);
});

test('normalize clamps and pads list items to the fixed limit', function () {
    $defaults = StorefrontSections::defaults()['faq']['items'];

    $padded = StorefrontSections::normalize([
        'faq' => ['items' => [$defaults[0]]],
    ]);

    $clamped = StorefrontSections::normalize([
        'faq' => ['items' => array_merge($defaults, $defaults)],
    ]);

    expect($padded['faq']['items'])->toHaveCount(3)
        ->and($padded['faq']['items'][1])->toBe($defaults[1])
        ->and($padded['faq']['items'][2])->toBe($defaults[2])
        ->and($clamped['faq']['items'])->toHaveCount(3);
});

test('normalize preserves custom item values while keeping exact shape', function () {
    $custom = StorefrontSections::normalize([
        'social_proof' => [
            'items' => [
                ['title' => 'Fast', 'description' => '24h', 'icon' => 'truck'],
            ],
        ],
    ]);

    expect($custom['social_proof']['items'][0]['title'])->toBe('Fast')
        ->and($custom['social_proof']['items'][0]['description'])->toBe('24h')
        ->and($custom['social_proof']['items'][0]['icon'])->toBe('truck')
        ->and(array_keys($custom['social_proof']['items'][0]))->toBe(['title', 'description', 'icon'])
        ->and($custom['social_proof']['items'][1])->toBe(StorefrontSections::defaults()['social_proof']['items'][1]);
});

test('theme data validation accepts a well-formed payload', function () {
    StorefrontSections::assertValidThemeData([
        'primary_color' => '#4f46e5',
        'secondary_color' => '#7c3aed',
        'font_family' => 'Cairo',
        'homepage_sections' => ['hero', 'faq'],
        'section_content' => ['hero' => ['title' => 'Hi']],
    ]);

    expect(true)->toBeTrue();
});

test('theme data validation rejects bad colors, fonts and section keys', function (array $payload) {
    StorefrontSections::assertValidThemeData($payload);
})->throws(Illuminate\Validation\ValidationException::class)->with([
    fn () => ['primary_color' => 'javascript:alert(1)'],
    fn () => ['font_family' => 'Comic Sans MS'],
    fn () => ['homepage_sections' => 'not-an-array'],
    fn () => ['section_content' => 'not-an-array-at-all'],
]);

it('maps whitelisted fonts to google font urls and skips bundled ones', function () {
    expect(StorefrontSections::googleFontUrl('Cairo'))->toContain('family=Cairo')
        ->and(StorefrontSections::googleFontUrl('Inter'))->toBeNull()
        ->and(StorefrontSections::googleFontUrl('Comic Sans MS'))->toBeNull();
});
