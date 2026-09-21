<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\WithFileUploads;

    public $user;

    public $subscription;

    public $billingAddress;

    public $plans;

    public $payments;

    public $stores;

    public $editBilling;

    public $billing_name;

    public $billing_company;

    public $billing_vat_number;

    public $billing_country;

    public $billing_state;

    public $billing_city;

    public $billing_address_line_1;

    public $billing_address_line_2;

    public $billing_zip;

    public $billing_phone;

    public $selectedBillingPeriod;

    public $showManualPayment;

    public $manualMethod;

    public $manualReference;

    public $manualProofFile;

    public $pendingReviewPayments;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function changePlan(string $planId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('changePlan'))->execute(...$arguments);
    }

    public function cancelSubscription(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('cancelSubscription'))->execute(...$arguments);
    }

    public function openEditBilling(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openEditBilling'))->execute(...$arguments);
    }

    public function openManualPayment(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openManualPayment'))->execute(...$arguments);
    }

    public function submitManualPayment(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitManualPayment'))->execute(...$arguments);
    }

    public function saveBilling(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveBilling'))->execute(...$arguments);
    }

};