<?php

use App\Domains\Analytics\Support\GroupColor;

uses(Tests\TestCase::class);

/*
|--------------------------------------------------------------------------
| PHASE 37-N3 - the one-colour-per-group map, server side
|--------------------------------------------------------------------------
|
| The group dots must paint from the design token, never from a dynamically
| built Tailwind class (Tailwind cannot see those, so they drop out of the CSS).
| This asserts the helper hands the token over as a CSS value and that every
| token it names is actually defined in app.css.
|
*/

test('every group resolves to its configured design token as an rgb() value', function () {
    $colors = config('analytics.group_colors');

    expect($colors)->toHaveKeys(['confirmed', 'pending', 'failed', 'canceled', 'duplicate', 'deferred', 'other']);

    foreach ($colors as $group => $token) {
        expect(GroupColor::token($group))->toBe($token)
            ->and(GroupColor::css($group))->toBe("rgb(var(--edz-color-{$token}))");
    }

    expect(GroupColor::token('nope'))->toBeNull()
        ->and(GroupColor::css('nope', 'fallback'))->toBe('fallback');
});

test('every group colour token is defined in app.css', function () {
    $css = (string) file_get_contents(resource_path('css/app.css'));

    foreach (config('analytics.group_colors') as $group => $token) {
        expect(str_contains($css, "--edz-color-{$token}:"))
            ->toBeTrue("group '{$group}' names an undefined token '{$token}'");
    }
});
