<?php

use App\Enums\Store\StoreRoleEnum;
use App\Http\Middleware\ResolveStoreFromSubdomain;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreContext;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    app(StoreContext::class)->clear();
});

function t03Store(User $owner, string $name): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'status' => 'active',
    ]);
}

function t03Membership(User $user, Store $store): StoreMembership
{
    if (! $user->hasAnyRoleForGuard([StoreRoleEnum::OWNER->value], 'merchant')) {
        $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));
    }

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $store->user_id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return $membership;
}

function t03SubdomainRequest(Store $store): Request
{
    $request = Request::create(
        'https://'.$store->slug.'.'.config('app.domain').'/probe',
        'GET'
    );

    $route = new \Illuminate\Routing\Route('GET', '/probe', []);
    $route->bind($request);
    $route->setParameter('store', $store->slug);

    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('refuses a foreign X-Store-Id with a 403', function () {
    $ownerA = roleUser('merchant');
    $ownerB = roleUser('merchant');
    $storeA = t03Store($ownerA, 'Store A');
    $storeB = t03Store($ownerB, 'Store B');

    t03Membership($ownerA, $storeA);

    Sanctum::actingAs($ownerA);

    $this->getJson('/api/v1/user', ['X-Store-Id' => $storeB->id])
        ->assertForbidden();

    expect(app(StoreContext::class)->get())->toBeNull();
});

it('accepts the callers own store through X-Store-Id', function () {
    $ownerA = roleUser('merchant');
    $storeA = t03Store($ownerA, 'Store A');

    t03Membership($ownerA, $storeA);

    Sanctum::actingAs($ownerA);

    $this->getJson('/api/v1/user', ['X-Store-Id' => $storeA->id])
        ->assertOk();
});

it('never infers the store from the host for API traffic', function () {
    $ownerA = roleUser('merchant');
    $storeA = t03Store($ownerA, 'Store A');

    t03Membership($ownerA, $storeA);

    Sanctum::actingAs($ownerA);

    // A store subdomain host but no explicit header must NOT resolve a store.
    $this->withHeaders(['Host' => $storeA->slug.'.'.config('app.domain')])
        ->getJson('/api/v1/user')
        ->assertStatus(422);
});

it('does not fall back to the subdomain store when a foreign header is sent', function () {
    $ownerA = roleUser('merchant');
    $ownerB = roleUser('merchant');
    $storeA = t03Store($ownerA, 'Store A');
    $storeB = t03Store($ownerB, 'Store B');

    t03Membership($ownerA, $storeA);

    Sanctum::actingAs($ownerA);

    // Host belongs to A (owner IS a member) but the header points at B:
    // the request must be rejected, never silently downgraded to A.
    $this->withHeaders([
        'Host' => $storeA->slug.'.'.config('app.domain'),
        'X-Store-Id' => $storeB->id,
    ])->getJson('/api/v1/user')->assertForbidden();
});

it('resolves a guest storefront from the subdomain without membership', function () {
    $ownerB = roleUser('merchant');
    $storeB = t03Store($ownerB, 'Store B');

    $request = t03SubdomainRequest($storeB);

    $response = app(ResolveStoreFromSubdomain::class)->handle($request, function () {
        return new Response('ok');
    });

    expect($response->getStatusCode())->toBe(200);
    expect(app(StoreContext::class)->get()?->id)->toBe($storeB->id);
    expect(session()->has('current_store_id'))->toBeFalse();
});
