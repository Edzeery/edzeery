<?php

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Models\Stores\Team\MembershipProductScope;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * The backfill is deliberately non-destructive: manager rows are COPIED (never
 * moved) into membership_product_scopes, staff rows are never touched, and a
 * rerun must not duplicate anything.
 */
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    $this->owner = User::factory()->create();
    $this->store = Store::create([
        'user_id' => $this->owner->id,
        'name' => 'Backfill Store',
        'slug' => 'backfill-'.uniqid(),
        'status' => 'active',
    ]);
});

function backfillMembership(StoreMembership $membership, Product $product): ConfirmationProductAssignment
{
    return ConfirmationProductAssignment::create([
        'store_id' => $membership->store_id,
        'membership_id' => $membership->id,
        'product_id' => $product->id,
    ]);
}

function backfillProduct(Store $store, string $name): Product
{
    return Product::create([
        'store_id' => $store->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.uniqid(),
        'sku' => 'BF-'.strtoupper(Str::random(6)),
        'type' => 'simple',
        'price' => 100,
        'is_active' => true,
    ]);
}

function runBackfill(): void
{
    $migration = require database_path('migrations/2026_09_21_000002_backfill_manager_product_scopes.php');

    $migration->up();
}

it('copies manager rows into the scope table and leaves the originals intact', function () {
    $manager = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $this->owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::MANAGER->value,
    ]);

    $productA = backfillProduct($this->store, 'Scoped A');
    $productB = backfillProduct($this->store, 'Scoped B');

    backfillMembership($manager, $productA);
    backfillMembership($manager, $productB);

    runBackfill();

    expect(MembershipProductScope::query()->count())->toBe(2)
        ->and(MembershipProductScope::query()
            ->where('membership_id', $manager->id)
            ->where('store_id', $this->store->id)
            ->orderBy('product_id')
            ->pluck('product_id')
            ->all())->toBe([$productA->id, $productB->id])
        // Non-destructive: the specialist rows are still there, unchanged.
        ->and(ConfirmationProductAssignment::query()->count())->toBe(2)
        ->and(ConfirmationProductAssignment::query()->where('membership_id', $manager->id)->count())->toBe(2)
        ->and(MembershipProductScope::query()->whereNotNull('created_by_membership_id')->count())->toBe(0);
});

it('never copies staff (or owner/admin) rows', function () {
    $staff = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $this->owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::STAFF->value,
    ]);

    $admin = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $this->owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::ADMIN->value,
    ]);

    $product = backfillProduct($this->store, 'Unambiguous');

    backfillMembership($staff, $product);
    backfillMembership($admin, $product);

    runBackfill();

    expect(MembershipProductScope::query()->count())->toBe(0)
        ->and(ConfirmationProductAssignment::query()->count())->toBe(2);
});

it('is idempotent when rerun', function () {
    $manager = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $this->owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::MANAGER->value,
    ]);

    $product = backfillProduct($this->store, 'Rerun');

    backfillMembership($manager, $product);

    runBackfill();
    runBackfill();
    runBackfill();

    expect(MembershipProductScope::query()->count())->toBe(1)
        ->and(ConfirmationProductAssignment::query()->count())->toBe(1);
});

it('picks up manager rows added after the first run', function () {
    $manager = StoreMembership::create([
        'store_id' => $this->store->id,
        'user_id' => User::factory()->create()->id,
        'invited_by' => $this->owner->id,
        'is_active' => true,
        'role' => StoreRoleEnum::MANAGER->value,
    ]);

    $product = backfillProduct($this->store, 'Late');

    backfillMembership($manager, $product);

    runBackfill();
    runBackfill();

    expect(MembershipProductScope::query()->count())->toBe(1);
});
