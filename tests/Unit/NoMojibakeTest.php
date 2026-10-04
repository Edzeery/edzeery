<?php

/*
|--------------------------------------------------------------------------
| PHASE 37-H
|--------------------------------------------------------------------------
|
| Mojibake guard. Several blade and lang files were once saved through a
| non-UTF-8 editor and now carry byte sequences such as "â€”" (an em dash read
| as Windows-1252) or "Ø§" (a middle dot read as an Arabic code page). The
| damage is invisible in a diff and only shows up as garbage in the browser, so
| it is pinned by a test instead of by review.
|
| This test deliberately uses no framework binding: it only touches the
| filesystem, and tests/Unit is not bootstrapped by tests/Pest.php.
|
*/

/**
 * Byte sequences that can only come from a mis-decoded save. Matched on the
 * decoded string, because that is how they appear in the browser.
 */
function mojibakeSequences(): array
{
    return [
        'â€' => 'mis-decoded em/en dash or quote (Windows-1252)',
        'â–' => 'mis-decoded en dash (Windows-1252)',
        'Ã©' => 'mis-decoded é (Latin-1/Windows-1252)',
        'Ø§' => 'mis-decoded middle dot (Arabic code page)',
        // A middle dot read through Windows-1256 instead of UTF-8. Same damage
        // as the entry above, different code page.
        'آ·' => 'mis-decoded middle dot (Windows-1256)',
    ];
}

/** Project root, resolved from this file so no framework binding is needed. */
function projectPath(string $relative = ''): string
{
    $root = dirname(__DIR__, 2);

    return $relative === '' ? $root : $root.DIRECTORY_SEPARATOR.ltrim($relative, '/\\');
}

function mojibakeFilesIn(string $directory): array
{
    if (! is_dir($directory)) {
        return [];
    }

    $sequences = mojibakeSequences();
    $found = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if ($contents === false || ! mb_check_encoding($contents, 'UTF-8')) {
            $found[$file->getPathname()][] = 'not valid UTF-8';

            continue;
        }

        foreach (array_keys($sequences) as $sequence) {
            $count = substr_count($contents, $sequence);

            if ($count > 0) {
                $found[$file->getPathname()][] = sprintf('"%s" x%d', $sequence, $count);
            }
        }
    }

    return $found;
}

it('finds no mojibake in the views', function () {
    $offenders = mojibakeFilesIn(projectPath('resources/views'));

    expect($offenders)->toBe([], "Mojibake found:\n".print_r($offenders, true));
});

it('finds no mojibake in the language files', function () {
    $offenders = mojibakeFilesIn(projectPath('resources/lang'));

    expect($offenders)->toBe([], "Mojibake found:\n".print_r($offenders, true));
});
