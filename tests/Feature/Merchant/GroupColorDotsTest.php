<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Support\StoreRoles;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\SystemStatusesSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| PHASE 37-N3 - group dots paint inline
|--------------------------------------------------------------------------
|
| The group cards, the chart legend and the team rows all have to carry the
| same inline background-color built from the single config map. A dynamic
| utility class would never be compiled by Tailwind, so the "other" dot used
| to render colourless; these tests pin the inline value instead.
|
*/

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(SystemStatusesSeeder::class);
    $this->seed(PlansSeeder::class);
});

function gcdOwnerStore(): array
{
    $user = roleUser('merchant');
    $user->assignRole(Role::findOrCreate(StoreRoleEnum::OWNER->value, 'merchant'));

    $store = Store::create([
        'user_id' => $user->id,
        'name' => 'Group Colour Store',
        'slug' => 'group-colour-'.uniqid(),
        'status' => 'active',
    ]);

    $membership = StoreMembership::create([
        'store_id' => $store->id,
        'user_id' => $user->id,
        'invited_by' => $user->id,
        'is_active' => true,
        'role' => StoreRoleEnum::OWNER->value,
    ]);

    $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

    return [$user, $store, $membership];
}

function gcdStatusId(string $key): string
{
    return DB::table('statuses')->where('type', 'order')->where('key', $key)->value('id');
}

function gcdOrder(Store $store, ?string $membershipId, string $statusKey, string $number): Order
{
    return Order::create([
        'store_id' => $store->id,
        'number' => $number,
        'total_amount' => 500,
        'status_id' => gcdStatusId($statusKey),
        'delivery_type' => 'home',
        'assigned_to_membership_id' => $membershipId,
    ]);
}

function gcdCss(string $token): string
{
    return "background-color: rgb(var(--edz-color-{$token}))";
}

test('the group cards paint all seven group colours inline', function () {
    [$user, $store] = gcdOwnerStore();

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate']))
        ->assertOk()
        ->getContent();

    // The rate cards render every group even at zero, so this proves the whole
    // map paints - including "other", the dot that used to fall out of the CSS.
    foreach (config('analytics.group_colors') as $group => $token) {
        expect(str_contains($html, gcdCss($token)))
            ->toBeTrue("the card for '{$group}' did not paint {$token}");
    }
});

test('the legend and the team rows share the same inline group colour', function () {
    [$user, $store, $membership] = gcdOwnerStore();

    gcdOrder($store, $membership->id, 'confirmed', 'GCD-1');
    gcdOrder($store, $membership->id, 'canceled', 'GCD-2');
    gcdOrder($store, $membership->id, 'pending', 'GCD-3');

    $html = $this->actingAs($user)
        ->withSession(['current_store_id' => $store->id])
        ->get(route('merchant.dashboard', ['store' => $store->slug, 'ct' => 'rate']))
        ->assertOk()
        ->getContent();

    // confirmed appears in the group card, the trend legend, the doughnut legend
    // and the member's team row - so it must show the inline colour more than once.
    expect(substr_count($html, gcdCss('success-600')))->toBeGreaterThanOrEqual(3);

    // The legend and team rows name the present groups, so their dots are inline too.
    foreach (['success-600', 'warning-600', 'gray-700'] as $token) {
        expect(str_contains($html, gcdCss($token)))->toBeTrue();
    }
});

test('the dashboard views never build a Tailwind colour class by concatenation', function () {
    $files = glob(resource_path('views/livewire/merchant/dashboard/partials/*.blade.php')) ?: [];
    $files[] = resource_path('views/livewire/merchant/dashboard.blade.php');

    $patterns = [
        "/'bg-'\s*\./" => 'concatenates a bg- class with a dot',
        '/"bg-"\s*\./' => 'concatenates a bg- class with a dot',
        '/bg-\{\s*\$/u' => 'interpolates a bg- class',
        '/bg-\$\{/u' => 'interpolates a bg- class',
    ];

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);
        $name = basename($file);

        foreach ($patterns as $pattern => $message) {
            expect(preg_match($pattern, $source))->toBe(0, "{$name} {$message}");
        }
    }
});
