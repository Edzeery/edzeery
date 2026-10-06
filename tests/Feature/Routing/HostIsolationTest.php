<?php

use App\Models\Stores\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->domain = config("app.domain") ?: "edzeery.com";
    $this->user = \App\Models\User::factory()->create(["name" => "Test User"]);
    $this->store = Store::query()->create([
        "name" => "Demo Store",
        "slug" => "acme",
        "user_id" => $this->user->id,
        "status" => "active",
        "landing_template" => "catalog",
    ]);
});

it("apex / shows landing", function () {
    $this->get("http://" . $this->domain . "/")
        ->assertStatus(200);
});

it("subdomain / shows storefront", function () {
    $this->get("http://" . $this->store->slug . "." . $this->domain . "/")
        ->assertStatus(200);
});

it("subdomain /contact-us returns 404", function () {
    $this->get("http://" . $this->store->slug . "." . $this->domain . "/contact-us")
        ->assertStatus(404);
});

it("www apex redirects to landing", function () {
    $this->get("http://www." . $this->domain . "/")
        ->assertRedirect(route("landing"));
});

it("www slug rejected at creation", function () {
    expect(fn () => Store::query()->create([
        "name" => "Www",
        "slug" => "www",
        "user_id" => $this->user->id,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

it("merchant dashboard resolves", function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\StoreRolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\SystemStatusesSeeder::class);
    $this->seed(\Database\Seeders\PlansSeeder::class);

    $this->user->assignRole(\Spatie\Permission\Models\Role::findOrCreate(\App\Enums\Platform\UserRoleEnum::MERCHANT->value, 'web'));
    $this->user->assignRole(\Spatie\Permission\Models\Role::findOrCreate(\App\Enums\Store\StoreRoleEnum::OWNER->value, 'merchant'));

    $membership = \App\Models\Stores\Team\StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => $this->user->id,
        'invited_by' => $this->store->user_id,
        'is_active' => true,
        'role' => \App\Enums\Store\StoreRoleEnum::OWNER->value,
    ]);
    $membership->syncPermissions(\App\Support\StoreRoles::permissions(\App\Enums\Store\StoreRoleEnum::OWNER));

    $this->actingAs($this->user)
        ->get("/merchant/" . $this->store->slug . "/dashboard")
        ->assertStatus(200);
});
