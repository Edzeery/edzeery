<?php

use App\Models\Stores\Store;

it('builds the store subdomain from the configured app url', function (string $appUrl, string $expected) {
    config(['app.url' => $appUrl]);

    $store = Store::make(['slug' => 'acme']);

    expect($store->public_url)->toBe($expected);
})->with([
    'production apex domain' => ['https://edzeery.com', 'https://acme.edzeery.com'],
    'app on a subdomain host' => ['https://app.example.com', 'https://acme.example.com'],
    'local laragon dev host' => ['http://edzeery.test', 'http://acme.edzeery.test'],
    'custom port is preserved' => ['http://edzeery.test:8080', 'http://acme.edzeery.test:8080'],
    'localhost falls back to slug.localhost' => ['http://localhost', 'http://acme.localhost'],
]);

it('keeps preview and copy links consistent with public_url', function () {
    config(['app.url' => 'https://edzeery.com']);

    $store = Store::make(['slug' => 'acme', 'status' => 'active']);

    // The storefront settings page consumes public_url for the iframe src,
    // copy button and open-store links — all through this single accessor.
    expect($store->public_url)->toBe('https://acme.edzeery.com')
        ->and($store->public_url . '?preview=1')->toBe('https://acme.edzeery.com?preview=1');
});
