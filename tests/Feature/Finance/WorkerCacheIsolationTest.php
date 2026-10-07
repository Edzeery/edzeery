<?php

use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreContext;
use App\Support\StoreScopedCache;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\SyncJob;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(Database\Seeders\SystemStatusesSeeder::class);
});

function fwiStore(): array
{
    $user = roleUser('merchant');

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Isolation Store',
        'slug' => 'iso-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => 'owner',
    ]);

    return [$user, $store, $membership];
}

function fwiDirtyState(Store $store, StoreMembership $membership): void
{
    StoreScopedCache::$canStoreUser = (string) $membership->user_id;
    StoreScopedCache::$canStore = [$store->id => true];

    app(StoreContext::class)->set($store);
    app()->instance('currentMembership', $membership);
}

function fwiAssertFlushed(): void
{
    expect(StoreScopedCache::$canStore)->toBe([])
        ->and(StoreScopedCache::$canStoreUser)->toBeNull()
        ->and(app(StoreContext::class)->get())->toBeNull()
        ->and(app()->bound('currentMembership'))->toBeFalse();
}

test('flushing the store-scoped cache resets every request-scoped piece', function () {
    [, $store, $membership] = fwiStore();
    fwiDirtyState($store, $membership);

    StoreScopedCache::flush();

    fwiAssertFlushed();
});

test('a job processed from the queue flushes request-scoped caches at its boundaries', function () {
    [, $store, $membership] = fwiStore();
    fwiDirtyState($store, $membership);

    // Simulate a real queue-worker boundary: the JobProcessing / JobProcessed
    // events the provider hooks fire around a job read from the queue
    // (non-"sync" connection). dispatch() on the test's sync driver executes
    // inline and is deliberately exempt, so exercise the worker path directly.
    $job = new SyncJob(app(), json_encode(['job' => 'foo', 'data' => [], 'attempts' => 1]), 'database', 'default');
    event(new JobProcessing('database', $job));
    event(new JobProcessed('database', $job));

    fwiAssertFlushed();
});

test('an inline sync job keeps the enclosing request context', function () {
    [, $store, $membership] = fwiStore();
    fwiDirtyState($store, $membership);

    dispatch(function () {
        // nothing to do — an inline (sync) dispatch must NOT clear the
        // request's StoreContext, or storefront flows dispatching jobs
        // in-request would lose their store mid-request.
    });

    expect(StoreScopedCache::$canStore)->toBe([$store->id => true])
        ->and(app(StoreContext::class)->get()?->id)->toBe($store->id)
        ->and(app()->bound('currentMembership'))->toBeTrue();
});
