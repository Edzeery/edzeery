<?php

use App\Domains\Analytics\Support\OrderStatusChartMapper;
use Illuminate\Support\Collection;

it('maps known key with translation', function () {
    $rows = collect([
        (object) ['key' => 'pending', 'count' => 5],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res)->toHaveCount(1);
    expect($res[0]->key)->toBe('pending');
    expect($res[0]->count)->toBe(5);
    expect($res[0]->label)->not->toBeNull();
    expect($res[0]->hex)->toMatch('/^#/');
});

it('falls back for unknown key', function () {
    $rows = collect([
        (object) ['key' => 'no_such_key_x123', 'count' => 2],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res[0]->label)->toBe('No Such Key X123');
    expect($res[0]->hex)->toBe('#9ca3af');
});

it('handles cancelled alias', function () {
    $rows = collect([
        (object) ['key' => 'cancelled', 'count' => 1],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res[0]->key)->toBe('cancelled');
    expect($res[0]->label)->not->toBeNull();
    expect($res[0]->hex)->toMatch('/^#/');
});
