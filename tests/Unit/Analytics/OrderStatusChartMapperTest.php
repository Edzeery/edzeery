<?php

use App\Domains\Analytics\Support\OrderStatusChartMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

/*
|--------------------------------------------------------------------------
| PHASE 37-G
|--------------------------------------------------------------------------
|
| This file lives in tests/Unit, which tests/Pest.php does not bootstrap, so
| it could only ever run when an earlier Feature test happened to have booted
| the container and set the Eloquent connection resolver in the same process.
| Order dependent, so it is bound to the app explicitly.
|
*/

uses(Tests\TestCase::class, RefreshDatabase::class);

it("maps known key with translation", function () {
    $rows = collect([
        (object) ["key" => "pending", "count" => 5],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res)->toHaveCount(1);
    expect($res[0]->key)->toBe("pending");
    expect($res[0]->count)->toBe(5);
    expect($res[0]->label)->not->toBeNull();
    expect($res[0]->hex)->toMatch("/^#/");
});

it("falls back for unknown key", function () {
    $rows = collect([
        (object) ["key" => "no_such_key_x123", "count" => 2],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res[0]->label)->toBe("No Such Key X123");
    expect($res[0]->hex)->toBe("#9ca3af");
});

it("handles cancelled alias", function () {
    $rows = collect([
        (object) ["key" => "cancelled", "count" => 1],
    ]);
    $mapper = app(OrderStatusChartMapper::class);
    $res = $mapper->map($rows, null);
    expect($res[0]->key)->toBe("cancelled");
    expect($res[0]->label)->not->toBeNull();
    expect($res[0]->hex)->toMatch("/^#/");
});
