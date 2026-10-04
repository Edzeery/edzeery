<?php

/*
|--------------------------------------------------------------------------
| PHASE 37-H / 37-I
|--------------------------------------------------------------------------
|
| Mojibake guard. Several blade and lang files were once saved through a
| non-UTF-8 editor, so a character that exists in one code page landed in
| another: an em dash saved as Windows-1252, a middle dot saved through
| Windows-1256. The damage is invisible in a diff and only shows up as garbage
| in the browser, so it is pinned by a test instead of by review.
|
| Two rules keep this file itself trustworthy:
|
|   1. Every pattern is an escaped byte string. Quoting the real characters
|      would put the damage under test inside the test.
|   2. A pattern is only listed if its bytes cannot occur in a correctly saved
|      file. Arabic and French text is legitimately multi-byte, so a pattern
|      built from real text of those languages would fire on good files.
|
| This test deliberately uses no framework binding: it only touches the
| filesystem, and tests/Unit is not bootstrapped by tests/Pest.php.
|
*/

/**
 * Decoded byte sequences that can only come from a mis-decoded save, with the
 * damage each one represents.
 *
 * @return array<string, string>
 */
function mojibakeSequences(): array
{
    return [
        // U+2014 EM DASH and the cp1252 smart quotes, saved as Windows-1252.
        "\xC3\xA2\xE2\x82\xAC" => 'mis-decoded em dash or smart quote (Windows-1252)',
        // U+2013 EN DASH, saved as Windows-1252.
        "\xC3\xA2\xE2\x80\x93" => 'mis-decoded en dash (Windows-1252)',
        // U+00E9 e-acute, saved as Latin-1.
        "\xC3\x83\xC2\xA9" => 'mis-decoded e-acute (Latin-1)',
        // U+00B7 MIDDLE DOT, saved as Latin-1.
        "\xC3\x98\xC2\xA7" => 'mis-decoded middle dot (Latin-1)',
        // U+00B7 MIDDLE DOT, saved as Windows-1256. Same damage, other code page.
        "\xD8\xA2\xC2\xB7" => 'mis-decoded middle dot (Windows-1256)',
        // U+2190-U+2193 arrows, saved as Windows-1252.
        "\xC3\xA2\xE2\x84\x90" => 'mis-decoded up arrow (Windows-1252)',
        "\xC3\xA2\xE2\x84\x94" => 'mis-decoded down arrow (Windows-1252)',
        // U+25B2 / U+25BC triangles, saved as Windows-1252.
        "\xC3\xA2\xE2\x96\xB2" => 'mis-decoded up triangle (Windows-1252)',
        "\xC3\xA2\xE2\x96\xBC" => 'mis-decoded down triangle (Windows-1252)',
    ];
}

/** Project root, resolved from this file so no framework binding is needed. */
function projectPath(string $relative = ''): string
{
    $root = dirname(__DIR__, 2);

    return $relative === '' ? $root : $root.DIRECTORY_SEPARATOR.ltrim($relative, '/\\');
}

/**
 * @return array<string, array<int, string>>
 */
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

        $raw = file_get_contents($file->getPathname());

        // A mis-decoded save can also leave the file as invalid UTF-8.
        if (! mb_check_encoding($raw, 'UTF-8')) {
            $found[$file->getPathname()][] = 'invalid UTF-8';

            continue;
        }

        foreach ($sequences as $sequence => $why) {
            $hits = substr_count($raw, $sequence);

            if ($hits > 0) {
                $found[$file->getPathname()][] = sprintf('"%s" x%d (%s)', $sequence, $hits, $why);
            }
        }
    }

    ksort($found);

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

it('still detects damage that is present', function () {
    // Guards the guard: a pattern table that had lost its bytes would make the
    // two tests above pass on a broken repository.
    $scratch = projectPath('storage/framework/testing/mojibake-probe');

    if (! is_dir($scratch)) {
        mkdir($scratch, 0777, true);
    }

    $damaged = $scratch.'/damaged.php';
    file_put_contents($damaged, "<?php\n// broken: \xC3\xA2\xE2\x82\xAC em dash\n");

    try {
        $offenders = mojibakeFilesIn($scratch);

        // The iterator reports a native path, which differs from the forward
        // slashes used to build the scratch directory, so match on the basename.
        expect($offenders)->toHaveCount(1);

        $reported = (string) array_key_first($offenders);

        expect(basename(str_replace('/', '\\', $reported)))->toBe('damaged.php')
            ->and(implode(' ', $offenders[$reported]))->toContain('Windows-1252');
    } finally {
        @unlink($damaged);
        @rmdir($scratch);
    }
});
