<?php

use App\Models\Stores\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->domain = config("app.domain") ?: "edzeery.com";
    $this->user = \App\Models\User::query()->create([
        "name" => "Test User",
        "email" => "test-" . uniqid() . "@example.com",
        "password" => bcrypt("password"),
    ]);
    $this->store = Store::query()->create([
        "name" => "Demo Store",
        "slug" => "demo",
        "user_id" => $this->user->id,
        "currency_code" => "SAR",
        "locale" => "ar",
        "timezone" => "Asia/Riyadh",
        "is_active" => true,
    ]);
});

it("apex / shows landing", function () {
    $this->withServerVariables(["HTTP_HOST" => $this->domain])
        ->get("/")
        ->assertStatus(200);
});

it("demo subdomain / shows storefront", function () {
    $this->withServerVariables(["HTTP_HOST" => "demo." . $this->domain])
        ->get("/")
        ->assertStatus(200);
});

it("demo subdomain /contact-us returns 404", function () {
    $this->withServerVariables(["HTTP_HOST" => "demo." . $this->domain])
        ->get("/contact-us")
        ->assertStatus(404);
});

it("www apex redirects to landing", function () {
    $this->withServerVariables(["HTTP_HOST" => "www." . $this->domain])
        ->get("/")
        ->assertRedirect(route("landing"));
});

it("www slug rejected at creation", function () {
    expect(fn () => Store::query()->create([
        "name" => "Www",
        "slug" => "www",
        "user_id" => $this->user->id,
    ]))->toThrow(\Throwable::class);
});

it("merchant dashboard resolves", function () {
    $this->actingAs($this->user)
        ->withServerVariables(["HTTP_HOST" => $this->domain])
        ->get("/merchant/" . $this->store->id . "/dashboard")
        ->assertStatus(200);
});
