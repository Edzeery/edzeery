<?php

use Illuminate\Support\Facades\File;

test('no livewire blade ships statement-leading async handlers', function () {
    $directory = __DIR__ . '/../../resources/views/livewire';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $violations = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        if (preg_match('/(?:x-on:[\w.:-]+|@[\w.:-]+)\s*=\s*"\s*(if|for|foreach|while|return)\s*\(/', $content, $matches)) {
            $violations[] = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname()) . ' (' . $matches[1] . ')';
        }
    }

    expect($violations)->toBe([], 'Alpine handler attributes must contain expressions, not statements. Livewire 3 compiles x-on values into `new AsyncFunction("return (SOURCE)")` — a leading `if (...)` throws SyntaxError and silently kills the button. Wrap in an IIFE: (async () => { if (...) await $wire.action() })(). Offenders: ' . implode('; ', $violations));
});

test('no livewire blade interpolates blade syntax inside js-bearing attributes', function () {
    $directory = __DIR__ . '/../../resources/views/livewire';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $jsAttributes = ['x-on:[\w.:-]+', '@[\w.:-]+', 'x-init', 'x-data', 'x-show', 'x-if', 'x-text', 'x-bind:class'];
    $violations = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        foreach ($jsAttributes as $attr) {
            if (preg_match('/(?:' . $attr . ')\s*=\s*"[^"]*@(?:js|json|class)\(/', $content, $matches)) {
                $violations[] = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
                break;
            }
        }
    }

    expect($violations)->toBe([], 'Blade interpolation (@js/@json/@class) must never appear inside JS-bearing Alpine attributes — pass server values via data-* attributes and read them with $el.dataset. Offenders: ' . implode('; ', $violations));
});
