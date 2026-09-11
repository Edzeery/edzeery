<?php

use App\Domains\Product\Support\ProductWizardSteps;
use App\Services\ProductService;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreRolesAndPermissionsSeeder;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(StoreRolesAndPermissionsSeeder::class);
    $this->seed(PlansSeeder::class);
});

function wizardStepLabels(): array
{
    return array_map(
        fn (string $label) => str_replace('&', '&amp;', $label),
        [
            __('products.step_basic_info'),
            __('products.step_pricing'),
            __('products.step_options'),
            __('products.step_inventory'),
            __('products.step_review'),
        ],
    );
}

test('wizard renders the five dynamic steps in canonical order', function () {
    [$user, $store] = skuUser();

    $html = Volt::test('merchant.products.form')->html();

    $position = -1;
    foreach (wizardStepLabels() as $label) {
        $pos = strpos($html, $label);
        expect($pos)->not->toBeFalse("Step label \"{$label}\" not rendered");
        expect($pos)->toBeGreaterThan($position);
        $position = $pos;
    }
});

test('a locked step cannot be navigated to at fresh mount', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    $volt->call('goToStep', ProductWizardSteps::STEP_REVIEW);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC)
        ->assertNotDispatched('error')
        ->assertHasNoErrors()
        ->assertDispatched('swal');
});

test('completed steps unlock and free navigation preserves state', function () {
    [$user, $store] = skuUser();

    $volt = Volt::test('merchant.products.form');

    // Step 3 is unreachable until steps 1 and 2 are validated.
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC);

    // Validate step 1 and advance to step 2.
    $volt->set(['name' => 'Free Nav Product', 'slug' => 'free-nav-product'])
        ->call('nextStep');

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertSet('validated_steps', [ProductWizardSteps::STEP_BASIC]);

    // Step 3 still locked, step 1 unlocked (backwards navigation ok).
    $volt->call('goToStep', ProductWizardSteps::STEP_OPTIONS);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING);

    $volt->call('goToStep', ProductWizardSteps::STEP_BASIC);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_BASIC);

    // Forward to step 2 again and validate it.
    $volt->call('goToStep', ProductWizardSteps::STEP_PRICING);
    $volt->assertSet('currentStep', ProductWizardSteps::STEP_PRICING)
        ->assertSet('validated_steps', [
            ProductWizardSteps::STEP_BASIC,
            ProductWizardSteps::STEP_PRICING,
        ]);

    // Step 3 is now reachable.
    $volt->set(['price' => 100, 'cost_price' => 50])
        ->call('goToStep', ProductWizardSteps::STEP_OPTIONS);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_OPTIONS);
});

test('edit mode pre-unlocks every step for free navigation', function () {
    [$user, $store] = skuUser();
    $service = app(ProductService::class);

    $product = $service->create($store, dataShape('edit-free-nav'));

    $volt = Volt::test('merchant.products.form', ['product' => $product]);

    $volt->assertSet('validated_steps', ProductWizardSteps::ids());

    $volt->call('goToStep', ProductWizardSteps::STEP_REVIEW);

    $volt->assertSet('currentStep', ProductWizardSteps::STEP_REVIEW)
        ->assertNotDispatched('swal');
});