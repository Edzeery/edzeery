<?php

/*
|--------------------------------------------------------------------------
| PHASE 37-M2 (D3) - accessible colour for the confirmation dashboard
|--------------------------------------------------------------------------
|
| The group colours live in config/analytics.php as theme tokens, and the
| tokens themselves live in resources/css/app.css as "r, g, b" triplets. This
| suite reads the one and resolves the other, so a future palette change that
| drops a legend dot or a text label below the WCAG thresholds fails here
| instead of shipping invisibly. The thresholds are: a chart colour >= 3:1 (a
| non-text graphic) and a secondary text colour >= 4.5:1, both on the light
| card.
|
*/

/** Split app.css into its :root and .dark token maps of "r, g, b" triplets. */
function edzCssTokenMaps(string $file): array
{
    $css = (string) file_get_contents($file);
    $darkAt = strpos($css, '.dark {');

    $parse = function (string $block): array {
        preg_match_all(
            '/--edz-color-([a-z0-9-]+)\s*:\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*;/',
            $block,
            $matches,
            PREG_SET_ORDER,
        );

        $tokens = [];
        foreach ($matches as $row) {
            $tokens[$row[1]] = [(int) $row[2], (int) $row[3], (int) $row[4]];
        }

        return $tokens;
    };

    return [
        'light' => $parse($darkAt === false ? $css : substr($css, 0, $darkAt)),
        'dark' => $darkAt === false ? [] : $parse(substr($css, $darkAt)),
    ];
}

/** WCAG relative luminance of an [r, g, b] triplet. */
function edzCssLuminance(array $rgb): float
{
    $channels = array_map(function (int $value): float {
        $v = $value / 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    }, $rgb);

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/** WCAG contrast ratio between two "r, g, b" colours. */
function edzContrast(array $a, array $b): float
{
    $la = edzCssLuminance($a);
    $lb = edzCssLuminance($b);

    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

test('every D3 group colour clears 3:1 on the light card', function () {
    $tokens = edzCssTokenMaps(resource_path('css/app.css'))['light'];
    $white = [255, 255, 255];

    foreach (config('analytics.group_colors') as $group => $token) {
        expect(array_key_exists($token, $tokens))
            ->toBeTrue("group '{$group}' names the undefined token '{$token}'");

        $ratio = round(edzContrast($tokens[$token], $white), 2);

        expect($ratio)->toBeGreaterThanOrEqual(
            3.0,
            "group '{$group}' ({$token}) is only {$ratio}:1 on white",
        );
    }
});

test('secondary text tokens clear 4.5:1 on the light card', function () {
    $tokens = edzCssTokenMaps(resource_path('css/app.css'))['light'];
    $white = [255, 255, 255];

    foreach (['text-muted', 'gray-500', 'gray-700'] as $token) {
        expect($tokens)->toHaveKey($token);

        $ratio = round(edzContrast($tokens[$token], $white), 2);

        expect($ratio)->toBeGreaterThanOrEqual(4.5, "text token '{$token}' is only {$ratio}:1 on white");
    }
});

test('the dark theme keeps its secondary text readable', function () {
    $maps = edzCssTokenMaps(resource_path('css/app.css'));

    // A dark card with a light-grey label: the pair the theme actually paints.
    $ratio = round(edzContrast($maps['dark']['text-muted'], $maps['dark']['surface']), 2);

    expect($ratio)->toBeGreaterThanOrEqual(4.5, "dark text-muted is only {$ratio}:1 on the dark card");
});
