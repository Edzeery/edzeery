<?php

namespace App\Domains\Analytics\Support;

/**
 * PHASE 37-N3 — the server-side half of the one-colour-per-group map.
 *
 * The group dots in the group cards, the chart legend and the team rows used
 * to be built as 'bg-'.$token Tailwind classes. Tailwind only compiles a class
 * it can read as a literal string, so those dynamic names silently dropped out
 * of the CSS (the "other" dot rendered colourless). This helper hands the view
 * the design token straight as a CSS value instead, so a dot always paints and
 * can never drift from the token the charts read client-side.
 */
final class GroupColor
{
    /** The design token behind a group ('success-600', …), if the group exists. */
    public static function token(string $group): ?string
    {
        $colors = config('analytics.group_colors') + config('analytics.delivery_group_colors');

        $token = $colors[$group] ?? null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * The inline background-color for a group's dot. The token ships as an
     * "r, g, b" triplet, so the design-token variable wraps it in rgb().
     */
    public static function css(string $group, string $fallback = 'currentColor'): string
    {
        $token = self::token($group);

        return $token === null ? $fallback : "rgb(var(--edz-color-{$token}))";
    }
}
