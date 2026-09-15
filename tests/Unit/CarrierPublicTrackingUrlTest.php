<?php

use App\Domains\Shipping\Models\Carrier;

it('builds a public tracking url by replacing the placeholder', function () {
    $carrier = new Carrier([
        'public_tracking_url_template' => 'https://track.example.test/?no={tracking_number}',
    ]);

    expect($carrier->publicTrackingUrl('AB 123 /X'))->toBe('https://track.example.test/?no=AB+123+%2FX');
});

it('supports the camelCase placeholder alias', function () {
    $carrier = new Carrier([
        'public_tracking_url_template' => 'https://track.example.test/track/{trackingNumber}',
    ]);

    expect($carrier->publicTrackingUrl('A1B2C3'))->toBe('https://track.example.test/track/A1B2C3');
});

it('returns null without a template or a tracking number', function () {
    $withoutTemplate = new Carrier();
    expect($withoutTemplate->publicTrackingUrl('ABC123'))->toBeNull();

    $withTemplate = new Carrier([
        'public_tracking_url_template' => 'https://track.example.test/?no={tracking_number}',
    ]);
    expect($withTemplate->publicTrackingUrl(''))->toBeNull();
    expect($withTemplate->publicTrackingUrl('   '))->toBeNull();
});