<?php
use Illuminate\Support\Str;

if (!function_exists('icon')) {
    function icon(string $name, string $set = null, string $classes = null): string
    {
        $config = config('icons');
        $set = $set ?: $config['default_set'];

        $icons = $config[$set] ?? [];
        $icon = $icons[$name] ?? $icons['default'] ?? null;

        if (!$icon) return '';

        $extra = $classes ? ' '.$classes : '';

        if ($set === 'ion') {
            return "<ion-icon name=\"{$icon}\" class=\"{$classes}\"></ion-icon>";
        }

        if ($set === 'lucide') {
            return "<i data-lucide=\"{$icon}\" class=\"{$classes}\"></i>";
        }

        if ($set === 'hero') {
            return "<x-heroicon-o-{$icon} class=\"{$classes}\" />";
        }

        $prefix = $set === 'fa' ? 'fas' : ($set === 'bi' ? 'bi' : '');

        return "<i class=\"{$prefix} {$icon}{$extra}\"></i>";
    }
}

if (!function_exists('getIconHtml')) {
    function getIconHtml(string $name, ?string $set = null, ?string $classes = null): string
    {
        if (Str::startsWith($name, '<')) {
            return $name;
        }

        if ($set === 'svg') {
            return svg_icon($name, $classes ?? '') ?? '';
        }

        return icon($name, $set, $classes ?? 'w-4 h-4');
    }
}
