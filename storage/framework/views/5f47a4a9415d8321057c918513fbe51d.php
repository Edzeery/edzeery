<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $providers;

    public $states;

    public $selectedProviderId;

    public $syncing;

    public $ratesByState;

    public $showStatePopup;

    public $popupStateId;

    public $popupStateName;

    public $popupCities;

    public $popupCitiesWithPrices;

    public $popupCenters;

    public $applyAllHomeCost;

    public $stateOfficeCost;

    public $stateDefaultCenterId;

    public $tab;

    public $lists;

    public $listsLoaded;

    public $selectedListId;

    public $listRatesByState;

    public $showListModal;

    public $editingListId;

    public $listName;

    public $listSelectedProductIds;

    public $listSelectedProducts;

    public $listProductSearch;

    public $showListStatePopup;

    public $listPopupStateId;

    public $listPopupStateName;

    public $listPopupCitiesWithPrices;

    public $listApplyAllHomeCost;

    public $listStateOfficeCost;

    public function mount(): void
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadData(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadData'))->execute(...$arguments);
    }

    public function loadRates(string $providerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadRates'))->execute(...$arguments);
    }

    public function selectProvider(string $providerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectProvider'))->execute(...$arguments);
    }

    public function updateStateCost(string $stateId, string $field, ?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateStateCost'))->execute(...$arguments);
    }

    public function syncProvider(\App\Domains\Shipping\Contracts\DeliveryRatesManager $manager): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('syncProvider'))->execute(...$arguments);
    }

    public function openStatePopup(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openStatePopup'))->execute(...$arguments);
    }

    public function saveMunicipalityCost(string $stateId, string $cityId, ?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveMunicipalityCost'))->execute(...$arguments);
    }

    public function applyAllHomeCost(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('applyAllHomeCost'))->execute(...$arguments);
    }

    public function saveStateOffice(string $stateId, ?string $officeCost): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveStateOffice'))->execute(...$arguments);
    }

    public function saveDefaultCenter(string $stateId, string $centerId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveDefaultCenter'))->execute(...$arguments);
    }

    public function closeStatePopup(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeStatePopup'))->execute(...$arguments);
    }

    public function loadLists(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadLists'))->execute(...$arguments);
    }

    public function setTab(string $tab): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('setTab'))->execute(...$arguments);
    }

    public function selectList(string $listId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('selectList'))->execute(...$arguments);
    }

    public function loadListRates(string $listId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('loadListRates'))->execute(...$arguments);
    }

    #[\Livewire\Attributes\Computed()]
    public function listProductResults()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('listProductResults'))->execute(...$arguments);
    }

    public function openListModal(?string $listId = NULL): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openListModal'))->execute(...$arguments);
    }

    public function closeListModal(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeListModal'))->execute(...$arguments);
    }

    public function toggleListProduct(string $productId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleListProduct'))->execute(...$arguments);
    }

    public function saveList(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveList'))->execute(...$arguments);
    }

    public function toggleListActive(string $listId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('toggleListActive'))->execute(...$arguments);
    }

    public function deleteList(string $listId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('deleteList'))->execute(...$arguments);
    }

    public function updateListStateCost(string $stateId, string $field, ?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('updateListStateCost'))->execute(...$arguments);
    }

    public function openListStatePopup(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('openListStatePopup'))->execute(...$arguments);
    }

    public function saveListMunicipalityCost(string $stateId, string $cityId, ?string $value): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveListMunicipalityCost'))->execute(...$arguments);
    }

    public function applyAllListHomeCost(string $stateId): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('applyAllListHomeCost'))->execute(...$arguments);
    }

    public function saveListOffice(string $stateId, ?string $officeCost): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('saveListOffice'))->execute(...$arguments);
    }

    public function closeListStatePopup(): void
    {
        $arguments = [static::$__context, $this, func_get_args()];

        (new Actions\CallMethod('closeListStatePopup'))->execute(...$arguments);
    }

};