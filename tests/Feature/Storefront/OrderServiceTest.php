<?php

use App\Domains\Order\Services\OrderService;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\User;

use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Database\Seeders\StatusesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);

    $this->user = User::factory()->create();
    $this->store = Store::create([
        'user_id' => $this->user->id,
        'name' => 'Test Store',
        'slug' => 'test-store-' . uniqid(),
        'status' => 'active',
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'pending',
        'label' => 'Pending',
        'color' => 'gray',
        'is_system' => true,
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'confirmed',
        'label' => 'Confirmed',
        'color' => 'blue',
        'is_system' => true,
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'preparing',
        'label' => 'Preparing',
        'color' => 'yellow',
        'is_system' => true,
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'shipped',
        'label' => 'Shipped',
        'color' => 'purple',
        'is_system' => true,
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'delivered',
        'label' => 'Delivered',
        'color' => 'green',
        'is_system' => true,
    ]);

    Status::create([
        'type' => 'order',
        'key' => 'cancelled',
        'label' => 'Cancelled',
        'color' => 'red',
        'is_system' => true,
    ]);

    $pendingStatus = Status::where('type', 'order')->where('key', 'pending')->first();

    $this->order = Order::create([
        'store_id' => $this->store->id,
        'customer_id' => null,
        'status_id' => $pendingStatus->id,
        'number' => 'ORD-' . now()->format('Ymd') . '-0001',
        'total_amount' => 3000,
        'delivery_type' => 'home',
        'payment_method' => 'cod',
    ]);

    $this->service = app(OrderService::class);
});

test('available transitions from pending', function () {
    $transitions = $this->service->availableTransitions($this->order);

    expect($transitions)->toContain('confirmed');
    expect($transitions)->toContain('cancelled');
    expect($transitions)->not->toContain('preparing');
});

test('confirm moves to confirmed', function () {
    $order = $this->service->confirm($this->order);

    expect($order->fresh()->status->key)->toBe('confirmed');
});

test('start preparing moves to preparing', function () {
    $this->service->confirm($this->order);
    $order = $this->service->startPreparing($this->order->fresh());

    expect($order->fresh()->status->key)->toBe('preparing');
});

test('ship moves to shipped', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $order = $this->service->ship($this->order->fresh());

    expect($order->fresh()->status->key)->toBe('shipped');
});

test('deliver moves to delivered', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $this->service->ship($this->order->fresh());
    $order = $this->service->deliver($this->order->fresh());

    expect($order->fresh()->status->key)->toBe('delivered');
});

test('cancel moves to cancelled', function () {
    $order = $this->service->cancel($this->order);

    expect($order->fresh()->status->key)->toBe('cancelled');
});

test('transition records status history', function () {
    $this->service->confirm($this->order);

    $history = OrderStatusHistory::where('order_id', $this->order->id)->first();
    expect($history)->not->toBeNull();
    expect($history->status_id)->toBe($this->order->fresh()->status_id);
});

test('full lifecycle records multiple history entries', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $this->service->ship($this->order->fresh());
    $this->service->deliver($this->order->fresh());

    $count = OrderStatusHistory::where('order_id', $this->order->id)->count();
    expect($count)->toBeGreaterThanOrEqual(4);
});

test('available transitions from confirmed', function () {
    $this->service->confirm($this->order);
    $transitions = $this->service->availableTransitions($this->order->fresh());

    expect($transitions)->toContain('preparing');
    expect($transitions)->toContain('cancelled');
    expect($transitions)->not->toContain('confirmed');
});

test('available transitions from preparing', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $transitions = $this->service->availableTransitions($this->order->fresh());

    expect($transitions)->toContain('shipped');
    expect($transitions)->toContain('cancelled');
});

test('available transitions from shipped', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $this->service->ship($this->order->fresh());
    $transitions = $this->service->availableTransitions($this->order->fresh());

    expect($transitions)->toContain('delivered');
    expect($transitions)->toContain('returned');
});

test('available transitions from delivered is empty', function () {
    $this->service->confirm($this->order);
    $this->service->startPreparing($this->order->fresh());
    $this->service->ship($this->order->fresh());
    $this->service->deliver($this->order->fresh());
    $transitions = $this->service->availableTransitions($this->order->fresh());

    expect($transitions)->toBeEmpty();
});
