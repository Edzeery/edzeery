<?php

use App\Models\Concerns\BelongsToStore;
use App\Models\Concerns\UsesStoreOrGlobalScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * T-02 architecture guard.
 *
 * Every Eloquent model whose table carries a `store_id` column must either
 * register a tenant scope (BelongsToStore / UsesStoreOrGlobalScope) or be
 * listed below with the reason it is safe to leave unscoped. This makes the
 * "scope every store_id model" rule mechanical instead of a convention, and
 * forces any new store_id table to make the decision explicitly.
 *
 * @var array<string, string> model FQCN => why it is intentionally unscoped
 */
$allowlist = [
    // Membership rows are resolved to decide the tenant, so scoping them would
    // be circular (you cannot look up the membership that authorises the store).
    App\Models\Stores\Team\StoreMembership::class => 'used to resolve/authorise store context; scoping is circular',

    // === Deferred to a dedicated context-wiring pass (see STATUS 2026-10-10) ===
    // The order/catalog/shipping aggregates are read across jobs, webhooks,
    // seeders and ~40 feature-test fixtures that build data before any store
    // context is set. Scoping them requires wiring StoreContext through those
    // flows first, otherwise reads collapse to zero rows.
    App\Models\Orders\Order::class => 'deferred: order aggregate, wire StoreContext through jobs/webhooks/tests',
    App\Models\Orders\OrderItem::class => 'deferred: pure child of Order',
    App\Models\Orders\OrderTracking::class => 'deferred: order aggregate, wire StoreContext through jobs/webhooks/tests',
    App\Models\Customer::class => 'deferred: order aggregate, wire StoreContext through jobs/webhooks/tests',
    App\Models\Brand::class => 'deferred: catalog relation, needs parent-anchor bypass',
    App\Models\Category::class => 'deferred: catalog relation, needs parent-anchor bypass',
    App\Models\Products\ProductVariant::class => 'deferred: catalog relation, needs parent-anchor bypass',
    App\Models\Products\ProductOption::class => 'deferred: catalog relation, needs parent-anchor bypass',
    App\Models\Status::class => 'deferred: nullable store_id reference table',
    App\Domains\Shipping\Models\ShippingProvider::class => 'deferred: read across order flows',
    App\Domains\Shipping\Models\StopdeskPoint::class => 'deferred: read across order flows',
    App\Domains\Shipping\Models\DeliveryRider::class => 'deferred: read across order flows',

    // ── Pure children / pivots: never addressed by user, inherit their parent ──
    App\Models\CategoryProduct::class => 'pure child: Category/Product pivot',
    App\Models\Products\ProductImage::class => 'pure child of Product',
    App\Models\Products\ProductOptionValue::class => 'pure child of ProductOption',
    App\Models\Orders\OrderEvent::class => 'pure child: order event log',
    App\Models\Orders\OrderTrackingHistory::class => 'pure child: tracking event log',
    App\Models\Stores\Team\MembershipProductScope::class => 'child of StoreMembership (authorisation)',
    App\Domains\Shipping\Models\DeliveryRateCity::class => 'pure child of DeliveryRate',

    // ── Store-owned settings/records, always read through a resolved Store ──
    App\Models\Stores\StoreSeo::class => 'store-owned settings, read via resolved Store',
    App\Models\Stores\StoreSetting::class => 'store-owned settings, read via resolved Store',
    App\Models\Stores\StoreThemeSetting::class => 'store-owned settings, read via resolved Store',
    App\Models\Stores\StoreStatusHistory::class => 'store-owned audit log',
    App\Models\Stores\StoreUserRequest::class => 'store-owned join request record',

    // ── Platform billing, not tenant store data ──
    App\Models\billing\Payment::class => 'platform subscription billing, not tenant order data',

    // ── Deferred aggregate roots (T-02 batches 2 & 4) ──
    App\Models\InventoryMovement::class => 'deferred (batch 2): inventory aggregate',
    App\Models\Invoice::class => 'deferred (batch 2): billing aggregate',
    App\Domains\Order\Models\ConfirmationProductAssignment::class => 'deferred (batch 2): confirmation config',
    App\Domains\Order\Models\ConfirmationShift::class => 'deferred (batch 2): confirmation config',
    App\Domains\Shipping\Models\CarrierSyncRun::class => 'deferred (batch 4): shipping sync log',
    App\Domains\Shipping\Models\DeliveryPriceList::class => 'deferred (batch 4): shipping config',
    App\Domains\Shipping\Models\DeliveryRate::class => 'deferred (batch 4): shipping config',
    App\Domains\Shipping\Models\ShippingRate::class => 'deferred (batch 4): shipping config',
];

it('scopes every store_id model or lists it in the allowlist with a reason', function () use ($allowlist) {
    $directories = array_merge(
        [app_path('Models')],
        glob(app_path('Domains/*/Models')) ?: []
    );

    $models = collect($directories)
        ->filter(fn (string $dir) => is_dir($dir))
        ->flatMap(fn (string $dir) => File::allFiles($dir))
        ->filter(fn ($file) => $file->getExtension() === 'php')
        ->map(function ($file) {
            $short = $file->getFilenameWithoutExtension();

            // Never autoload a file unless it actually declares the class we
            // expect; otherwise helper/trait files get re-included and blow up.
            $contents = File::get($file->getRealPath());

            if (! preg_match('/^\s*(?:final\s+|abstract\s+)?class\s+'.preg_quote($short, '/').'\b/m', $contents)) {
                return null;
            }

            $relative = str_replace(
                [app_path().DIRECTORY_SEPARATOR, '.php'],
                ['', ''],
                $file->getRealPath()
            );

            return 'App\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
        })
        ->filter()
        ->filter(fn (string $class) => class_exists($class)
            && is_subclass_of($class, Model::class)
            && ! (new ReflectionClass($class))->isAbstract())
        ->unique()
        ->values();

    $scoped = [];
    $offenders = [];

    foreach ($models as $class) {
        $instance = new $class;
        $table = $instance->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'store_id')) {
            continue;
        }

        $traits = class_uses_recursive($class);

        if (isset($traits[BelongsToStore::class]) || isset($traits[UsesStoreOrGlobalScope::class])) {
            $scoped[] = $class;

            continue;
        }

        if (isset($allowlist[$class])) {
            continue;
        }

        $offenders[] = $class;
    }

    expect($offenders)->toBe(
        [],
        "These models have a store_id column but no tenant scope and no allowlist entry:\n - "
        .implode("\n - ", $offenders)
    );
});
