<?php

use App\Domains\Billing\Actions\SubmitManualPaymentAction;
use App\Domains\Billing\Actions\ReviewManualPaymentAction;
use App\Domains\Billing\Events\PaymentSucceeded;
use App\Domains\Billing\Events\PaymentRejected;
use App\Domains\Billing\Events\PaymentSubmitted;
use App\Enums\SubscriptionPayment\StatusPaymentEnum;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

test('submit manual payment creates pending_review payment', function () {
    $user = User::factory()->create();
    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'pending',
        'is_trial' => true,
        'starts_at' => now(),
        'trial_ends_at' => now()->addDays(14),
    ]);

    Event::fake([PaymentSubmitted::class]);

    $payment = app(SubmitManualPaymentAction::class)->execute(
        subscription: $sub,
        method: \App\Domains\Billing\Enums\ManualPaymentMethodEnum::BARIDIMOB,
        referenceNumber: 'BARIDI-123456',
    );

    $this->assertInstanceOf(Payment::class, $payment);
    $this->assertEquals(StatusPaymentEnum::PENDING_REVIEW, $payment->status);
    $this->assertEquals('baridimob', $payment->manual_method);
    $this->assertEquals('BARIDI-123456', $payment->reference_number);
    $this->assertEquals('manual', $payment->gateway);
    $this->assertNull($payment->paid_at);

    Event::assertDispatched(PaymentSubmitted::class);
});

test('approve manual payment activates subscription via event', function () {
    $user = User::factory()->create();
    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'pending',
        'is_trial' => false,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $payment = Payment::create([
        'user_id' => $user->id,
        'subscription_id' => $sub->id,
        'plan_price_id' => $planPrice?->id,
        'gateway' => 'manual',
        'status' => StatusPaymentEnum::PENDING_REVIEW,
        'amount' => $planPrice->price ?? 1000,
        'currency' => 'DZD',
        'manual_method' => 'ccp',
        'reference_number' => 'CCP-999999',
    ]);

    Event::fake([PaymentSucceeded::class]);

    $reviewer = User::factory()->create();
    $approved = app(ReviewManualPaymentAction::class)->approve($payment, $reviewer);

    $this->assertEquals(StatusPaymentEnum::PAID, $approved->status);
    $this->assertNotNull($approved->paid_at);
    $this->assertEquals($reviewer->id, $approved->reviewed_by);
    $this->assertNotNull($approved->reviewed_at);

    Event::assertDispatched(PaymentSucceeded::class);
});

test('reject manual payment records rejection', function () {
    $user = User::factory()->create();
    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'pending',
        'is_trial' => false,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $payment = Payment::create([
        'user_id' => $user->id,
        'subscription_id' => $sub->id,
        'plan_price_id' => $planPrice?->id,
        'gateway' => 'manual',
        'status' => StatusPaymentEnum::PENDING_REVIEW,
        'amount' => $planPrice->price ?? 1000,
        'currency' => 'DZD',
        'manual_method' => 'bank_transfer',
        'reference_number' => 'BANK-777777',
    ]);

    Event::fake([PaymentRejected::class]);

    $reviewer = User::factory()->create();
    $rejected = app(ReviewManualPaymentAction::class)->reject($payment, $reviewer, 'Invalid reference number');

    $this->assertEquals(StatusPaymentEnum::CANCELED, $rejected->status);
    $this->assertEquals($reviewer->id, $rejected->reviewed_by);
    $this->assertEquals('Invalid reference number', $rejected->rejection_reason);
    $this->assertNotNull($rejected->reviewed_at);

    Event::assertDispatched(PaymentRejected::class);
});

test('cannot approve already-paid payment', function () {
    $user = User::factory()->create();
    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'active',
        'is_trial' => false,
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $payment = Payment::create([
        'user_id' => $user->id,
        'subscription_id' => $sub->id,
        'plan_price_id' => $planPrice?->id,
        'gateway' => 'manual',
        'status' => StatusPaymentEnum::PAID,
        'amount' => 1000,
        'currency' => 'DZD',
        'paid_at' => now(),
    ]);

    $reviewer = User::factory()->create();

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    app(ReviewManualPaymentAction::class)->approve($payment, $reviewer);
});

test('billing page shows manual payment section', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/merchant/account/billing');

    $response->assertOk();
    $response->assertSee('Submit Manual Payment');
    $response->assertSee('Pay Now');
    $response->assertSee('BaridiMob');
    $response->assertSee('CCP');
    $response->assertSee('Bank Transfer');
});

test('manual payment submission via livewire', function () {
    $user = User::factory()->create();
    $plan = Plan::first();
    $planPrice = $plan->prices()->first();

    Subscription::where('user_id', $user->id)->delete();

    $sub = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'plan_price_id' => $planPrice?->id,
        'status' => 'pending',
        'is_trial' => true,
        'starts_at' => now(),
        'trial_ends_at' => now()->addDays(14),
    ]);

    Livewire::actingAs($user)
        ->test('account.billing')
        ->set('manualMethod', 'ccp')
        ->set('manualReference', 'CCP-555555')
        ->call('submitManualPayment');

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'subscription_id' => $sub->id,
        'manual_method' => 'ccp',
        'reference_number' => 'CCP-555555',
        'gateway' => 'manual',
    ]);

    $payment = Payment::where('user_id', $user->id)
        ->where('manual_method', 'ccp')
        ->first();
    $this->assertNotNull($payment);
    $this->assertEquals(StatusPaymentEnum::PENDING_REVIEW, $payment->status);
});
