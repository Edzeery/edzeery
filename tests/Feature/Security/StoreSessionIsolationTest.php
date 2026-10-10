<?php

use App\Enums\Store\StoreRoleEnum;
use App\Http\Middleware\ResolveStoreFromSubdomain;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreContext;
use App\Support\StoreResolver;
use App\Support\StoreRoles;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    app(StoreContext::class)->clear();
});

function s03Store(User $owner, string $name): Store
{
    return Store::create([
        'user_id' => $owner->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'status' => 'active',
    ]);
}

function s03Membership(User $user, Store $store): StoreMembership
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

/**
 * A request for the storefront subdomain router with the {store:slug} route
 * parameter bound, so ResolveStoreFromSubdomain can resolve the store model.
 */
function s03SubdomainRequest(Store $store): Request
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

it('does not resolve a poisoned current_store_id for a user without active membership', function () {
    $ownerA = roleUser('merchant');
    $ownerB = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    $storeB = s03Store($ownerB, 'Store B');

    // The attacker (owner of A) somehow holds B's id in the session.
    Auth::login($ownerA);
    session(['current_store_id' => $storeB->id]);

    expect(StoreResolver::resolve())->toBeNull();
    expect(session()->has('current_store_id'))->toBeFalse();
    expect(currentStore())->toBeNull();
});

it('resolves the session store when the user has an active membership', function () {
    $ownerA = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    s03Membership($ownerA, $storeA);

    Auth::login($ownerA);
    session(['current_store_id' => $storeA->id]);

    expect(StoreResolver::resolve()?->id)->toBe($storeA->id);
    expect(session('current_store_id'))->toBe($storeA->id);
});

it('refuses the merchant dashboard of a foreign store and clears the poisoned session', function () {
    $ownerA = roleUser('merchant');
    $ownerB = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    $storeB = s03Store($ownerB, 'Store B');

    s03Membership($ownerA, $storeA);

    $this->actingAs($ownerA)
        ->withSession(['current_store_id' => $storeB->id])
        ->get(route('merchant.dashboard', ['store' => $storeB->slug]))
        ->assertForbidden();

    expect(session()->has('current_store_id'))->toBeFalse();
});

it('keeps a legitimate store in the session and resolves it later', function () {
    $ownerA = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    s03Membership($ownerA, $storeA);

    $this->actingAs($ownerA)
        ->withSession(['current_store_id' => $storeA->id])
        ->get(route('merchant.dashboard', ['store' => $storeA->slug]))
        ->assertOk();

    // Membership-confirmed merchant mount leaves the store resolvable later.
    expect(session('current_store_id'))->toBe($storeA->id);
    expect(StoreResolver::resolve()?->id)->toBe($storeA->id);
});

it('does not write the subdomain store id into the session for a non-member', function () {
    $ownerA = roleUser('merchant');
    $ownerB = roleUser('merchant');
    $storeB = s03Store($ownerB, 'Store B');

    Auth::login($ownerA);

    $request = s03SubdomainRequest($storeB);

    $response = app(ResolveStoreFromSubdomain::class)->handle($request, function ($request) {
        return new Response('ok');
    });

    expect($response->getStatusCode())->toBe(200);

    // The middleware resolves the subdomain store but must NOT persist it for
    // a user who does not belong to it.
    expect(app(StoreContext::class)->get()?->id)->toBe($storeB->id);
    expect(session()->has('current_store_id'))->toBeFalse();
});

it('writes the subdomain store id into the session for an active member', function () {
    $ownerA = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    s03Membership($ownerA, $storeA);

    Auth::login($ownerA);

    $request = s03SubdomainRequest($storeA);

    $response = app(ResolveStoreFromSubdomain::class)->handle($request, function ($request) {
        return new Response('ok');
    });

    expect($response->getStatusCode())->toBe(200);
    expect(session('current_store_id'))->toBe($storeA->id);
});

it('ignores an inactive membership in both the resolver and the middleware', function () {
    $ownerA = roleUser('merchant');
    $storeA = s03Store($ownerA, 'Store A');
    s03Membership($ownerA, $storeA)->forceFill(['is_active' => false])->save();
    app(StoreContext::class)->clear();

    Auth::login($ownerA);
    session(['current_store_id' => $storeA->id]);

    expect(StoreResolver::resolve())->toBeNull();
    expect(session()->has('current_store_id'))->toBeFalse();
});
