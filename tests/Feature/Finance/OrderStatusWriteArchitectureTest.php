<?php

use Illuminate\Support\Facades\File;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function fawScan(array $paths): array
{
    $allowed = ['OrderService.php', 'OrderObserver.php'];

    $needle = "'status_id'";
    $massWrite = [
        '->update(', '->forceUpdate(', '->save(', '->forceSave(',
        '->create(', '->forceCreate(', '->insert(', '->updateOrCreate(',
        '->firstOrCreate(', '->fill(',
    ];

    $offenders = [];

    foreach ($paths as $path) {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if (in_array($file->getBasename(), $allowed, true)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $hits = [];

            // Direct attribute assignment: $order->status_id = ...
            if (preg_match('/->status_id\s*=/', $content)) {
                $hits[] = 'direct-assign';
            }

            // Assignment from another entity: 'status_id' => $other->status_id
            if (preg_match('/[\'"]status_id[\'"]\s*=>\s*\$[a-z_][a-z0-9_]*->/', $content)) {
                $hits[] = 'copy-assign';
            }

            // Mass-assignment writes that include the order status in their args.
            if (str_contains($content, $needle)) {
                foreach ($massWrite as $token) {
                    if (preg_match(
                        '/'.preg_quote($token, '/').'[\s\S]{0,160}'.preg_quote($needle, '/').'\s*=>/U',
                        $content
                    )) {
                        $hits[] = trim($token).'+status_id';
                        break;
                    }
                }
            }

            if ($hits !== []) {
                $offenders[] = $file->getRelativePathname().' ('.implode(', ', array_unique($hits)).')';
            }
        }
    }

    return $offenders;
}

test('only the order service and observer may write an order status in application code', function () {
    $offenders = fawScan([app_path()]);

    expect($offenders)->toBe([]);
});

test('no order status is written directly in blade outside the storefront creation', function () {
    $offenders = fawScan([resource_path('views')]);

    // The storefront submit is the single sanctioned creation: it sets the
    // initial pending status at insert time, never transitions an order.
    $offenders = array_values(array_filter(
        $offenders,
        fn (string $entry): bool => ! str_contains($entry, 'order-form.blade.php')
    ));

    expect($offenders)->toBe([]);
});

test('the allowlisted writers still contain their sanctioned write patterns', function () {
    $service = file_get_contents(app_path('Domains/Order/Services/OrderService.php'));
    $observer = file_get_contents(app_path('Observers/OrderObserver.php'));

    expect($service)->toContain("'status_id'")
        ->and($observer)->toContain('$order->status_id =');
});
