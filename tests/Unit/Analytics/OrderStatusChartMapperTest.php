<?php

use App\Domains\Analytics\Support\OrderStatusChartMapper;
use App\Domains\Status\StatusResolver;
use App\Domains\Status\Support\ResolvedStatus;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\User;
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

// StatusResolver memoises in static properties that survive between tests in
// the same process, so a seeded label would otherwise leak into later cases.
beforeEach(function () {
    StatusResolver::flush();
});

function mapStatusRows(array $rows, ?string $storeId = null): Collection
{
    return app(OrderStatusChartMapper::class)->map(
        collect(array_map(fn (array $row) => (object) $row, $rows)),
        $storeId
    );
}

/**
 * A store-configured status row, the way <x-status> sees it.
 *
 * The colour has to be a real status-kit variant name: ResolvedStatus reads
 * config("status-kit-statuses.general.$color") and silently falls back to gray
 * for anything it does not know, which would hide a broken colour lookup.
 */
function seedStatus(string $key, string $label, ?string $storeId = null, string $color = 'warning'): void
{
    Status::query()->create([
        'store_id' => $storeId,
        'type' => 'order',
        'key' => $key,
        'label' => $label,
        'color' => $color,
        'is_system' => false,
        'sort_order' => 1,
    ]);
}

it('maps a known key with its colour', function () {
    $res = mapStatusRows([['key' => 'pending', 'count' => 5]]);

    expect($res)->toHaveCount(1);
    expect($res[0]->key)->toBe('pending');
    expect($res[0]->count)->toBe(5);
    expect($res[0]->label)->not->toBeNull();
    expect($res[0]->hex)->toMatch('/^#/');
});

it('takes the label from StatusResolver, not from a translation file', function () {
    // The old implementation looked up "statuses.order.pending", a namespace
    // that does not exist, so the label was never translated at all.
    seedStatus('pending', 'بانتظار التأكيد', color: 'amber');

    $res = mapStatusRows([['key' => 'pending', 'count' => 5]]);

    expect($res[0]->label)->toBe('بانتظار التأكيد');
    expect($res[0]->label)->not->toContain('statuses.order');
});

it('shows an Arabic label in an Arabic UI', function () {
    seedStatus('pending', 'بانتظار التأكيد', null, 'warning');

    app()->setLocale('ar');

    $res = mapStatusRows([['key' => 'pending', 'count' => 1]]);

    expect($res[0]->label)->toBe('بانتظار التأكيد');
});

it('prefers the store status row over the system row', function () {
    $user = User::query()->create([
        'name' => 'Analyst',
        'email' => 'analyst-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
    ]);

    $store = Store::query()->create([
        'name' => 'Mapper Store',
        'slug' => 'mapper-'.uniqid(),
        'currency_code' => 'SAR',
        'locale' => 'ar',
        'is_active' => true,
        'user_id' => $user->id,
    ]);

    seedStatus('pending', 'System Label', null, 'info');
    seedStatus('pending', 'بانتظار التأكيد', (string) $store->id, 'warning');

    $res = mapStatusRows([['key' => 'pending', 'count' => 1]], (string) $store->id);

    expect($res[0]->label)->toBe('بانتظار التأكيد');
});

it('falls back to the flat status translation when the resolver label is empty', function () {
    app()->setLocale('en');

    // No status row, so the resolver has nothing store specific to offer and
    // the kit label wins; whatever it is, it must never be the raw key.
    $res = mapStatusRows([['key' => 'pending', 'count' => 1]]);

    expect($res[0]->label)->toBe('Pending');
});

it('falls back for unknown key', function () {
    $res = mapStatusRows([['key' => 'no_such_key_x123', 'count' => 2]]);

    expect($res[0]->label)->toBe('No Such Key X123');
    expect($res[0]->hex)->toBe('#9ca3af');
});

it('handles cancelled alias', function () {
    seedStatus('cancelled', 'ملغاة', color: 'danger');

    $res = mapStatusRows([['key' => 'cancelled', 'count' => 1]]);

    expect($res[0]->key)->toBe('cancelled');
    expect($res[0]->label)->toBe('ملغاة');
    expect($res[0]->hex)->toMatch('/^#/');
});

it('reads the American spelling from its own store row', function () {
    seedStatus('canceled', 'ملغاة', color: 'danger');

    $res = mapStatusRows([['key' => 'canceled', 'count' => 1]]);

    expect($res[0]->label)->toBe('ملغاة');
});

it('gives both spellings a colour from the store row', function () {
    seedStatus('cancelled', 'ملغاة', color: 'danger');

    $res = mapStatusRows([['key' => 'cancelled', 'count' => 1]]);

    // A raw Status row carries no hex column, so this proves the model was
    // normalised into a ResolvedStatus instead of read straight off the row.
    expect($res[0]->hex)->toBe(ResolvedStatus::fromModel(
        Status::query()->where('type', 'order')->where('key', 'cancelled')->first()
    )->hex);
    expect($res[0]->hex)->not->toBe('#9ca3af');
});
