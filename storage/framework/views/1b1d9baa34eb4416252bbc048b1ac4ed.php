<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $showCreateModal;

    public $showEditModal;

    public $showProductPickerModal;

    public $showVariantPickerModal;

    public $editingOrderId;

    public $form;

    public $formProductResults;

    public $formProductView;

    public $formSelectedProduct;

    public $formSelectedItems;

    public $editProviders;

    public $editDesks;

    public $allStates;

    public $allCities;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function syncFormSelectedItems(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('syncFormSelectedItems'))->execute(...$arguments);
    }

    public function getCurrentMembership(): ?\App\Models\Stores\Team\StoreMembership
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('getCurrentMembership'))->execute(...$arguments);
    }

    public function loadCities(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadCities'))->execute(...$arguments);
    }

    public function openCreateModal(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openCreateModal'))->execute(...$arguments);
    }

    public function loadProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadProducts'))->execute(...$arguments);
    }

    public function selectProduct(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProduct'))->execute(...$arguments);
    }

    public function backToProducts(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('backToProducts'))->execute(...$arguments);
    }

    public function addFormItem(string $variantId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addFormItem'))->execute(...$arguments);
    }

    public function addFormItemByBarcode(string $code): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('addFormItemByBarcode'))->execute(...$arguments);
    }

    public function removeFormItem(int $index): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('removeFormItem'))->execute(...$arguments);
    }

    public function updateFormItemQty(int $index, int $qty): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateFormItemQty'))->execute(...$arguments);
    }

    public function updateFormItemPrice(int $index, $price): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateFormItemPrice'))->execute(...$arguments);
    }

    public function submitCreate(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitCreate'))->execute(...$arguments);
    }

    public function openEditModal(string $orderId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openEditModal'))->execute(...$arguments);
    }

    public function submitEdit(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('submitEdit'))->execute(...$arguments);
    }

    public function getListeners()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\ResolveListeners)->execute(...$arguments);
    }

    public function ordersFormOpenCreateHandler()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallListener('orders-form-open-create'))->execute(...$arguments);
    }

    public function ordersFormOpenEditHandler(string $orderId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallListener('orders-form-open-edit'))->execute(...$arguments);
    }

};