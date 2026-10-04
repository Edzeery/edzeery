<?php

/*
|--------------------------------------------------------------------------
| Volt Layout Declaration Guard
|--------------------------------------------------------------------------
|
| There is no config/livewire.php, so Livewire silently falls back to its
| default layout (components.layouts.app -- the marketing/landing shell with
| navbar + footer). A Volt page that imports `layout()` but never calls it
| therefore renders inside the wrong chrome with no error at all: the store
| panel loses its sidebar and topbar.
|
| We deliberately do NOT configure a global default layout, because that
| would mask the same mistake on storefront and account pages. Instead every
| routed Volt component is required to declare its layout explicitly.
|
*/

/**
 * The only layouts a routed Volt page is allowed to declare.
 */
function voltAllowedLayoutNames(): array
{
    return ['store', 'storefront', 'account', 'guest', 'merchant', 'panel'];
}

/**
 * Resolve a Volt component name to its blade file under resources/views/livewire.
 */
function voltComponentFile(string $name): string
{
    return resource_path('views/livewire/' . str_replace('.', '/', $name) . '.blade.php');
}

/**
 * Collect every Volt component name referenced by a Volt::route(...) call in
 * the given route files. Parsed from source rather than from the router so
 * that a route hidden behind a broken/duplicated group is still covered.
 *
 * @return array<string, string> component name => "routes/<file>:<line>"
 */
function voltRoutedComponents(array $routeFiles): array
{
    $pattern = '/Volt::route\s*\(\s*'
        . '(?:\'[^\']*\'|"[^"]*")'   // uri
        . '\s*,\s*'
        . '\'([A-Za-z0-9_.\-]+)\''   // component name
        . '\s*\)/';

    $components = [];

    foreach ($routeFiles as $file) {
        $source = file_get_contents($file);

        expect($source)->toBeString("Route file [{$file}] must be readable.");

        if (preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as [$name, $offset]) {
                $components[$name] = basename($file) . ':'
                    . substr_count(substr($source, 0, $offset), "\n") + 1;
            }
        }
    }

    return $components;
}

test('every routed Volt page declares an explicit layout', function () {
    $routeFiles = glob(base_path('routes/*.php'));

    expect($routeFiles)->not->toBeEmpty();

    $components = voltRoutedComponents($routeFiles);

    // Guard against the regex silently matching nothing.
    expect($components)->not->toBeEmpty('No Volt::route() calls were discovered in routes/*.php.');

    $allowed = voltAllowedLayoutNames();
    $missing = [];
    $disallowed = [];

    foreach ($components as $name => $origin) {
        $file = voltComponentFile($name);

        if (! is_file($file)) {
            $missing[$name] = $origin . ' -> component file not found: ' . $file;

            continue;
        }

        $source = file_get_contents($file);

        // A UTF-8 BOM before the opening tag can break the compiled template,
        // so a BOM-prefixed component is treated as a hard failure too.
        if (str_starts_with($source, "\xEF\xBB\xBF")) {
            $missing[$name] = $origin . ' -> file starts with a UTF-8 BOM.';
        }

        preg_match_all("/layout\s*\(\s*'components\.layouts\.([a-z]+)'/", $source, $layoutMatches);

        if ($layoutMatches[1] === []) {
            $missing[$name] = $origin . ' -> no layout() call.';

            continue;
        }

        foreach ($layoutMatches[1] as $layout) {
            if (! in_array($layout, $allowed, true)) {
                $disallowed[$name] = $origin . " -> layout 'components.layouts.{$layout}' is not allowed.";
            }
        }
    }

    expect($missing)->toBe([], "Volt pages missing a layout declaration:\n" . implode("\n", $missing));
    expect($disallowed)->toBe([], "Volt pages declaring a disallowed layout:\n" . implode("\n", $disallowed));
});

test('the allowed layout list is exactly the set of shipped panel layouts', function () {
    $layoutsDir = base_path('resources/views/components/layouts');

    foreach (voltAllowedLayoutNames() as $name) {
        expect(is_file("{$layoutsDir}/{$name}.blade.php"))->toBeTrue(
            "Allowed layout [components.layouts.{$name}] has no component view."
        );
    }

    expect(is_file('config/livewire.php'))->toBeFalse(
        'A global Livewire default layout would hide missing per-page layout() calls.'
    );
});

test('the merchant dashboard declares the store panel layout', function () {
    $file = voltComponentFile('merchant.dashboard');

    expect(is_file($file))->toBeTrue();

    expect(file_get_contents($file))
        ->toMatch("/layout\s*\(\s*'components\.layouts\.store'\s*\)/");
});