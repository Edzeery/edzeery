<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Services\OrderTrackingService;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTracking;

/**
 * Rider assignment, tracking-number generation and the order edit form cascade
 * (providers / cities / stopdesk offices) — extracted out of
 * tracking/index.blade.php. The edit modal is shared with the orders page via
 * HasOrderProductPicker; those picker methods are not redefined here.
 */
trait TrackingRiderFormConcern
{
    // Unique, rider-scoped tracking number (HM/SD prefix by delivery type) used when
    // handing an order to a delivery rider. Printed on our label as a scannable
    // Code128 barcode. Uniqueness is guarded against every other tracking number.
    public function generateRiderTrackingNumber(Order $order): string
    {
        return app(OrderTrackingService::class)->generateRiderTrackingNumber($order);
    }

    public function assignRider(string $orderId, ?string $riderId = null): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_ASSIGN->value), 403);

        $order = Order::where('store_id', currentStoreId())->find($orderId);

        if (! $order) {
            return;
        }

        if ($riderId !== null && $order->shipping_provider_id) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.rider_has_provider')]);
            return;
        }

        if ($riderId !== null) {
            $rider = app(DeliveryRiderService::class)->findForStore($riderId, currentStoreId());

            if (! $rider) {
                return;
            }

            // Every order handed to a rider gets its own tracking number immediately.
            app(OrderTrackingService::class)->ensureRiderTracking($order, $this->generateRiderTrackingNumber($order));
        }

        $order->update(['delivery_rider_id' => $riderId]);

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('order_flow.rider_saved')]);

        $this->loadRiderOptions();
        $this->loadShipments();

        if ($this->drawerOrderId === $orderId) {
            $this->openDrawer($orderId);
        }
    }

    // ——— Order edit modal (ported from the orders page) ———
    // Single source for the store's default shipping company: the explicit
    // is_default flag wins, otherwise the first active provider.
    public function storeDefaultProviderId(): string
    {
        return (string) \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->value('id');
    }

    public function formShipmentTypeOptions(): array
    {
        $defaultOptions = [
            ['value' => 'delivery', 'label' => __('merchant_panel.delivery')],
            ['value' => 'exchange', 'label' => __('merchant_panel.exchange_label')],
            ['value' => 'pickup', 'label' => __('merchant_panel.pickup_label')],
        ];

        $providerId = $this->form['shipping_provider_id'] ?? null;

        if (! $providerId) {
            return $defaultOptions;
        }

        $provider = \App\Domains\Shipping\Models\ShippingProvider::query()
            ->where('store_id', currentStoreId())
            ->with('carrier')
            ->find($providerId);

        $carrier = $provider?->carrier;

        if (! $carrier) {
            return $defaultOptions;
        }

        $caps = $carrier->capabilityList();

        $enabled = array_filter([
            'delivery' => $caps['delivery'] ?? false,
            'exchange' => $caps['exchange'] ?? false,
            'pickup'   => $caps['pickup'] ?? false,
        ]);

        $options = array_values(array_filter(
            $defaultOptions,
            fn ($opt) => ! empty($enabled[$opt['value']]),
        ));

        if ($options === []) {
            $options = $defaultOptions;
        }

        $availableValues = array_column($options, 'value');
        $current = (string) ($this->form['shipment_type'] ?? 'delivery');

        if (! in_array($current, $availableValues, true)) {
            $this->form['shipment_type'] = 'delivery';
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.shipment_type_reset_for_carrier')]);
        }

        return $options;
    }

    // ——— City cascade ———

    public function cityOptionsFor(string $stateId, string $type, string $providerId): array
    {
        $cities = \App\Models\Locations\City::where('state_id', $stateId)->orderBy('name')->get()->toArray();

        if ($type === 'home' && $providerId !== '') {
            $covered = $this->homeCoveredCityIds($providerId, $stateId);

            if ($covered !== null) {
                $coveredIds = $covered->map(fn ($cid) => (string) $cid)->all();

                $cities = collect($cities)
                    ->filter(fn ($city) => in_array((string) $city['id'], $coveredIds, true))
                    ->values()
                    ->toArray();

                if ($cities === []) {
                    $this->formCoverageHint = 'no_home_coverage';
                }
            }
        }

        if ($type === 'stopdesk' && $providerId !== '') {
            $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
                ->where('is_active', true)
                ->with('carrier')
                ->find($providerId);

            if ($provider) {
                if ($provider->carrier) {
                    try {
                        $state = \App\Models\Locations\State::find($stateId);
                        app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, null);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("office sync failed for provider [{$providerId}] while loading communes: " . $e->getMessage());
                    }
                }

                $coveredCityIds = \App\Domains\Shipping\Models\StopdeskPoint::communitiesCoveredFor(
                    currentStoreId(),
                    $providerId,
                    $stateId
                );

                if ($coveredCityIds !== []) {
                    $cities = collect($cities)
                        ->filter(fn ($city) => in_array($city['id'], $coveredCityIds, true))
                        ->values()
                        ->toArray();
                    $this->formCoverageHint = '';
                } else {
                    $cities = [];
                    $this->formCoverageHint = 'no_company_coverage';
                }
            }
        }

        return $cities;
    }

    public function loadFormCitiesLazy(string $scope): array
    {
        [$type, $providerId, $stateId] = array_pad(explode('|', $scope, 3), 3, '');

        if ($stateId === '') {
            return [];
        }

        $type = $type !== '' ? $type : (string) ($this->form['delivery_type'] ?? 'home');
        $providerId = $providerId !== '' ? $providerId : (string) ($this->form['shipping_provider_id'] ?? '');

        $cities = $this->cityOptionsFor($stateId, $type, $providerId);

        $this->formCities = $cities;

        return $cities;
    }

    public function loadCities(string $stateId, bool $resetCity = true): void
    {
        if (empty($stateId)) {
            $this->formCities = [];
            if ($resetCity) {
                $this->form['city_id'] = '';
            }
            $this->rebuildFormOffices();
            return;
        }

        $cities = $this->cityOptionsFor(
            $stateId,
            (string) ($this->form['delivery_type'] ?? 'home'),
            (string) ($this->form['shipping_provider_id'] ?? ''),
        );

        $previousCity = (string) ($this->form['city_id'] ?? '');

        if ($resetCity) {
            $this->form['city_id'] = '';
            $previousCity = '';
        }

        $this->formCities = $previousCity !== ''
            ? collect($cities)
                ->filter(fn ($ct) => (string) $ct['id'] === $previousCity)
                ->values()
                ->all()
            : [];

        if ($previousCity !== '' && $this->formCities === []) {
            $kept = \App\Models\Locations\City::find($previousCity);
            if ($kept) {
                $this->formCities = [['id' => $kept->id, 'name' => $kept->name]];
            }
        }
    }

    public function officeOptionsFor(string $providerId, string $stateId, ?string $cityId): array
    {
        return \App\Domains\Shipping\Models\StopdeskPoint::scopedOfficeOptions(
            currentStoreId(),
            $providerId,
            $stateId,
            $cityId !== '' ? $cityId : null,
        )->all();
    }

    public function loadFormOfficesLazy(string $scope): array
    {
        [$providerId, $stateId, $cityId] = array_pad(explode('|', $scope, 4), 4, '');

        if ($providerId === '' || $stateId === '') {
            return [];
        }

        $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
            ->where('is_active', true)
            ->find($providerId);

        if ($provider) {
            try {
                $state = \App\Models\Locations\State::find($stateId);
                $city = $cityId !== '' ? \App\Models\Locations\City::find($cityId) : null;
                app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, $city);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("office sync failed for provider [{$providerId}] while loading offices lazily: " . $e->getMessage());
            }
        }

        return $this->officeOptionsFor($providerId, $stateId, $cityId !== '' ? $cityId : null);
    }

    public function rebuildFormOffices(): void
    {
        $wasSelected = (string) ($this->form['stopdesk_point_id'] ?? '');

        if (empty($this->form['shipping_provider_id'])) {
            $this->formOffices = [];
            $this->formHasOffices = false;
            $this->form['stopdesk_point_id'] = '';
            return;
        }

        $query = \App\Domains\Shipping\Models\StopdeskPoint::query()->where('store_id', currentStoreId())->where('shipping_provider_id', $this->form['shipping_provider_id'])->where('is_active', true)->with('city:id,name');

        $stateId = $this->form['state_id'] ?? null;
        if (empty($stateId)) {
            $this->formOffices = [];
            $this->formHasOffices = false;
            $this->form['stopdesk_point_id'] = '';
            return;
        }

        $query->where('state_id', $stateId);

        $cityId = $this->form['city_id'] ?? null;
        if (!empty($cityId)) {
            $query->where(fn($q) => $q->where('city_id', $cityId)->orWhereNull('city_id'));
            $query->orderByRaw('(city_id = ?) DESC, (city_id IS NULL) ASC, (external_code = \'\') ASC, external_code, name', [(int) $cityId]);
        } else {
            $query->orderByRaw('(external_code = \'\') ASC, external_code, name');
        }

        $offices = $query->get();

        $officeOptions = $offices
            ->map(function ($office) use ($cityId) {
                $hint = trim(($office->city?->name ?? '') . ($office->address ? ' — ' . $office->address : ''), ' —');

                return [
                    'value' => (string) $office->id,
                    'label' => $office->name,
                    'code' => $office->external_code !== null && $office->external_code !== ''
                        ? (string) $office->external_code
                        : null,
                    'hint' => $hint !== '' ? $hint : null,
                ];
            })
            ->all();

        $cityOffices = empty($cityId) ? collect() : $offices->filter(fn($office) => $office->city_id === $cityId);

        if (($this->form['delivery_type'] ?? null) === 'stopdesk' && $cityOffices->count() === 1) {
            $this->form['stopdesk_point_id'] = (string) $cityOffices->first()->id;
        } elseif (!collect($officeOptions)->contains(fn($o) => $o['value'] === (string) ($this->form['stopdesk_point_id'] ?? ''))) {
            if ($wasSelected !== '' && $this->form['stopdesk_point_id'] === $wasSelected) {
                $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.office_reset_for_destination')]);
            }
            $this->form['stopdesk_point_id'] = '';
        }

        $this->formHasOffices = $officeOptions !== [];
        $this->formOffices = collect($officeOptions)
            ->filter(fn($o) => $o['value'] === (string) ($this->form['stopdesk_point_id'] ?? ''))
            ->values()
            ->all();
    }

    public function loadFormOffices(?string $providerId = null, bool $preserveOffice = false): void
    {
        if ($providerId !== null) {
            $this->form['shipping_provider_id'] = $providerId;
        }

        if (($this->form['delivery_type'] ?? null) !== 'stopdesk') {
            $this->form['stopdesk_point_id'] = '';
            $this->formOffices = [];
            $this->formHasOffices = false;

            return;
        }

        if (!$preserveOffice) {
            $this->form['stopdesk_point_id'] = '';
        }

        if (empty($this->form['shipping_provider_id'])) {
            $this->formOffices = [];
            return;
        }

        $storeId = currentStoreId();
        $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)->where('is_active', true)->find($this->form['shipping_provider_id']);

        if (!$provider) {
            $this->formOffices = [];
            return;
        }

        $this->loadingOffices = true;

        try {
            $state = !empty($this->form['state_id']) ? \App\Models\Locations\State::find($this->form['state_id']) : null;
            $city = !empty($this->form['city_id']) ? \App\Models\Locations\City::find($this->form['city_id']) : null;

            app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, $city);

            $this->rebuildFormOffices();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("office sync failed for provider [{$provider->id}]: " . $e->getMessage());
            $this->rebuildFormOffices();
        } finally {
            $this->loadingOffices = false;
        }
    }

    // ——— Carrier-scoped office cascade (available wilayas / communes per carrier) ———

    public function providerOfficeStates(string $providerId)
    {
        $pointIds = \App\Domains\Shipping\Models\StopdeskPoint::query()
            ->where('store_id', currentStoreId())
            ->where('shipping_provider_id', $providerId)
            ->where('is_active', true)
            ->whereNotNull('state_id')
            ->distinct()
            ->pluck('state_id');

        $rateIds = \App\Domains\Shipping\Models\DeliveryRate::query()
            ->where('store_id', currentStoreId())
            ->where('shipping_provider_id', $providerId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNotNull('office_cost')->orWhereNotNull('free_above'))
            ->distinct()
            ->pluck('state_id');

        return $pointIds->merge($rateIds)->unique()->values();
    }

    public function providerHomeStates(string $providerId)
    {
        $storeId = currentStoreId();

        $stateIds = \App\Domains\Shipping\Models\DeliveryRate::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
            ->distinct()
            ->pluck('state_id');

        $cityStateIds = \App\Domains\Shipping\Models\DeliveryRateCity::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
            ->distinct()
            ->pluck('state_id');

        $legacyStateIds = \App\Domains\Shipping\Models\ShippingRate::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('state_id');

        $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)
            ->where('is_active', true)
            ->find($providerId);

        if ($provider && $provider->flat_rate !== null) {
            return null;
        }

        $covered = $stateIds->merge($cityStateIds)->merge($legacyStateIds)->unique()->values();

        return $covered->isEmpty() ? null : $covered;
    }

    public function homeCoveredCityIds(string $providerId, string $stateId)
    {
        $storeId = currentStoreId();

        $stateRate = \App\Domains\Shipping\Models\DeliveryRate::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
            ->first();

        if ($stateRate) {
            return null;
        }

        $cityIds = \App\Domains\Shipping\Models\DeliveryRateCity::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
            ->distinct()
            ->pluck('city_id');

        if ($cityIds->isNotEmpty()) {
            return $cityIds;
        }

        $legacyState = \App\Domains\Shipping\Models\ShippingRate::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->whereNull('city_id')
            ->where('is_active', true)
            ->first();

        if ($legacyState) {
            return null;
        }

        $legacyCities = \App\Domains\Shipping\Models\ShippingRate::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->whereNotNull('city_id')
            ->where('is_active', true)
            ->distinct()
            ->pluck('city_id');

        if ($legacyCities->isNotEmpty()) {
            return $legacyCities;
        }

        return null;
    }

    public function loadFormScope(): void
    {
        $this->formAvailableStates = [];
        $this->formCoverageHint = '';

        $type = (string) ($this->form['delivery_type'] ?? 'home');
        $providerId = (string) ($this->form['shipping_provider_id'] ?? '');

        if ($providerId === '') {
            return;
        }

        $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', currentStoreId())
            ->where('is_active', true)
            ->with('carrier')
            ->find($providerId);

        if (! $provider) {
            return;
        }

        if ($type === 'home') {
            $stateIds = $this->providerHomeStates($providerId);

            if ($stateIds === null) {
                return;
            }

            $this->formAvailableStates = \App\Models\Locations\State::whereIn('id', $stateIds)
                ->active()
                ->orderedByCode()
                ->get(['id', 'name', 'state_code'])
                ->toArray();

            return;
        }

        if (! $provider->carrier) {
            return;
        }

        $stateIds = $this->providerOfficeStates($providerId);

        if ($stateIds->isEmpty()) {
            $this->formCoverageHint = 'no_company_coverage';
            return;
        }

        $this->formAvailableStates = \App\Models\Locations\State::whereIn('id', $stateIds)
            ->active()
            ->orderedByCode()
            ->get(['id', 'state_code', 'name'])
            ->toArray();
    }

    public function releaseStaleDestination(): void
    {
        $stateId = (string) ($this->form['state_id'] ?? '');
        $cleared = false;

        if ($stateId !== '' && $this->formAvailableStates !== [] && ! collect($this->formAvailableStates)->contains(fn ($st) => (string) $st['id'] === $stateId)) {
            $this->form['state_id'] = '';
            $this->form['city_id'] = '';
            $this->form['stopdesk_point_id'] = '';
            $this->formCities = [];
            $this->formOffices = [];
            $cleared = true;
        }

        $cityId = (string) ($this->form['city_id'] ?? '');
        if (! $cleared && $cityId !== '' && (string) ($this->form['state_id'] ?? '') !== '') {
            $scopedCities = $this->cityOptionsFor(
                (string) $this->form['state_id'],
                (string) ($this->form['delivery_type'] ?? 'home'),
                (string) ($this->form['shipping_provider_id'] ?? ''),
            );

            if (! collect($scopedCities)->contains(fn ($ct) => (string) $ct['id'] === $cityId)) {
                $this->form['city_id'] = '';
                $this->form['stopdesk_point_id'] = '';
                $this->formCities = [];
                $this->formOffices = [];
                $cleared = true;
            }
        }

        if ($cleared) {
            $this->dispatch('swal:toast', ['icon' => 'warning', 'title' => __('order_flow.destination_reset_for_carrier')]);
        }
    }

    public function applyProviderScope(?string $providerId = null, bool $preserveOffice = false): void
    {
        if ($providerId !== null) {
            $this->form['shipping_provider_id'] = $providerId;
        }

        $type = (string) ($this->form['delivery_type'] ?? 'home');

        $this->loadFormScope();
        $this->loadCities((string) ($this->form['state_id'] ?? ''));
        $this->releaseStaleDestination();

        if ($type !== 'stopdesk') {
            $this->form['stopdesk_point_id'] = '';
            $this->formOffices = [];
        }

        $this->loadFormOffices($this->form['shipping_provider_id'], $preserveOffice);
    }

    public function changeDeliveryType(string $type): void
    {
        $this->form['delivery_type'] = $type;

        if (!empty($this->form['shipping_provider_id'])) {
            $this->applyProviderScope(preserveOffice: true);
        } else {
            $this->form['stopdesk_point_id'] = '';
            $this->formOffices = [];
            $this->loadFormScope();
            $this->rebuildFormOffices();
        }
    }

    // Exclusive carrier partner segment (company / rider) in the shared order
    // edit form: picking one partner clears the other + the office.
    public function switchFormPartner(string $type): void
    {
        $this->formPartnerType = $type === 'rider' ? 'rider' : 'provider';

        if ($this->formPartnerType === 'rider') {
            $this->form['shipping_provider_id'] = '';
            $this->form['stopdesk_point_id'] = '';
            $this->formOffices = [];
            $this->formHasOffices = false;

            return;
        }

        $this->form['delivery_rider_id'] = '';
        $this->form['stopdesk_point_id'] = '';
        $this->formOffices = [];
        $this->formHasOffices = false;
    }

    public function refreshFormOffices(): void
    {
        if (empty($this->form['shipping_provider_id'])) {
            return;
        }

        $storeId = currentStoreId();
        $provider = \App\Domains\Shipping\Models\ShippingProvider::where('store_id', $storeId)->where('is_active', true)->find($this->form['shipping_provider_id']);

        if (!$provider) {
            return;
        }

        $this->loadingOffices = true;

        try {
            $state = !empty($this->form['state_id']) ? \App\Models\Locations\State::find($this->form['state_id']) : null;
            $city = !empty($this->form['city_id']) ? \App\Models\Locations\City::find($this->form['city_id']) : null;

            app(\App\Domains\Shipping\Services\StopdeskOfficeSync::class)->sync($provider, $state, $city, refresh: true);

            $this->formOfficesVersion++;

            $this->rebuildFormOffices();
            $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.offices_updated')]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("office refresh failed for provider [{$provider->id}]: " . $e->getMessage());
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.office_sync_failed')]);
        } finally {
            $this->loadingOffices = false;
        }
    }

    public function refreshFormDuplicateWarnings(): void
    {
        $items = collect($this->form['items'] ?? []);

        $variantIds = $items->pluck('product_variant_id')->filter()->values()->all();
        $productIds = $items->pluck('product_id')->filter()->values()->all();

        if (empty($variantIds) && empty($productIds) && empty($this->form['customer_phone'] ?? '')) {
            $this->formDuplicateWarnings = [];
            return;
        }

        $candidate = [
            'store_id' => currentStoreId(),
            'exclude_id' => $this->editingOrderId ?? null,
            'customer_phone' => $this->form['customer_phone'] ?? null,
            'items' => $items
                ->map(fn($item) => [
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_id' => $item['product_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                ])
                ->values()
                ->all(),
        ];

        $service = app(\App\Domains\Order\Services\OrderDuplicateService::class);
        $this->formDuplicateWarnings = $service->findSimilar($candidate);
    }

    public function recalculateOrderShipping(Order $order): void
    {
        if (!$order->store) {
            return;
        }

        $items = collect($order->items);
        $productIds = $items->pluck('product_id')->filter()->values()->toArray();
        $subtotal = (float) $items->sum('subtotal');

        $result = app(\App\Domains\Shipping\Services\ShippingCostCalculator::class)->calculate(
            $order->store,
            $order->state_id,
            $order->city_id,
            $subtotal,
            $productIds,
            $order->shipping_provider_id ?: null,
            $order->delivery_type ?: Order::DELIVERY_HOME,
        );

        $order->update(['shipping_cost' => (float) ($result['cost'] ?? 0)]);
    }

    // ——— Edit modal open/submit — Tracking-scoped: the grid only holds carrier-
    //      workflow orders, so the orders-page "block shipped+" guard is replaced by
    //      a terminal-state guard (delivered/returned are final and locked).
    public function openEditModal(string $orderId): void
    {
        if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $order = Order::with(['customer', 'items.product', 'items.variant'])
            ->where('store_id', currentStoreId())
            ->findOrFail($orderId);

        if (in_array($order->status?->key, ['delivered', 'returned'], true)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_terminal')]);
            return;
        }

        $this->editingOrderId = $order->id;
        $this->form = [
            'customer_name' => $order->customer?->name ?? '',
            'customer_phone' => $order->customer?->phone ?? '',
            'phone_secondary' => $order->phone_secondary ?? '',
            'address' => $order->address ?? '',
            'state_id' => $order->state_id ?? '',
            'city_id' => $order->city_id ?? '',
            'delivery_type' => $order->delivery_type,
            'shipment_type' => $order->shipment_type ?? 'delivery',
            'payment_method' => $order->payment_method,
            'discount_type' => $order->discount_type,
            'discount_value' => $order->discount_value,
            'discount_reason' => $order->discount_reason ?? '',
            'notes' => $order->notes ?? '',
            'weight_kg' => $order->weight_kg ?? '',
            'shipping_provider_id' => $order->shipping_provider_id ?? '',
            'delivery_rider_id' => $order->delivery_rider_id ?? '',
            'stopdesk_point_id' => $order->stopdesk_point_id ?? '',
            'items' => $order->items
                ->map(
                    fn($i) => [
                        'product_variant_id' => $i->product_variant_id,
                        'product_id' => $i->product_id,
                        'name' => ($i->product?->name ?? '') . ' — ' . ($i->variant?->name ?? ''),
                        'sku' => $i->variant?->sku ?? '',
                        'price' => $i->price,
                        'quantity' => $i->quantity,
                        'stock' => $i->variant?->stock ?? 0,
                        'weight' => $i->variant?->weight ?? 0,
                        'image_url' => $i->product?->primaryImage?->path ? \Illuminate\Support\Facades\Storage::disk('public')->url($i->product->primaryImage->path) : asset('img/icons/noimg.png'),
                    ],
                )
                ->toArray(),
        ];

        $this->formPartnerType = blank($order->delivery_rider_id) ? 'provider' : 'rider';

        if (blank($this->form['delivery_rider_id'] ?? null) && blank($this->form['shipping_provider_id'] ?? null)) {
            $this->form['shipping_provider_id'] = $this->storeDefaultProviderId();
        }

        $this->formProductResults = [];
        $this->formProductView = 'list';
        $this->formSelectedProduct = null;
        $this->formDuplicateWarnings = [];
        $this->syncFormSelectedItems();
        $this->loadProducts();
        $this->refreshFormDuplicateWarnings();
        $this->showEditModal = true;

        $this->loadFormScope();
        $this->loadCities((string) ($this->form['state_id'] ?? ''), resetCity: false);
        $this->releaseStaleDestination();

        $this->formOffices = [];
        $this->loadFormOffices($order->shipping_provider_id, preserveOffice: true);
    }

    public function submitEdit(): void
    {
        if (! canStore(StorePermissionEnum::ORDER_MANAGE->value)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('messages.permission_denied')]);
            return;
        }

        $order = Order::where('store_id', currentStoreId())->findOrFail($this->editingOrderId);

        if (in_array($order->status?->key, ['delivered', 'returned'], true)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.cannot_edit_terminal')]);
            return;
        }

        \Illuminate\Support\Facades\Validator::make($this->form, [
            'customer_phone' => 'required|string|max:20|regex:/^0[5-7]\d{8}$/',
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'delivery_type' => 'required|in:home,stopdesk',
            'shipment_type' => 'required|in:delivery,exchange,pickup',
            'payment_method' => 'required|in:cod',
            'address' => 'required_if:delivery_type,home|nullable|string|max:1000',
            'state_id' => 'required_if:delivery_type,home|nullable|exists:states,id',
            'city_id' => 'required_if:delivery_type,home|nullable|exists:cities,id',
            'discount_type' => 'nullable|in:amount,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_reason' => 'nullable|string|max:255',
            'shipping_provider_id' => 'nullable|string|exists:shipping_providers,id',
            'delivery_rider_id' => 'nullable|string|exists:delivery_riders,id',
            'stopdesk_point_id' => 'nullable|string|exists:stopdesk_points,id',
        ])->validate();

        // Exclusive carrier partner: a company and a rider can never coexist.
        if (filled($this->form['shipping_provider_id'] ?? null) && filled($this->form['delivery_rider_id'] ?? null)) {
            $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('order_flow.partner_exclusive')]);
            return;
        }

        // A rider carries to the address: clear the office on the rider leg.
        $isRiderLeg = filled($this->form['delivery_rider_id'] ?? null);
        if ($isRiderLeg) {
            $this->form['stopdesk_point_id'] = '';
            $this->form['shipping_provider_id'] = '';
        }

        $storeId = currentStoreId();

        foreach (['shipping_provider_id', 'delivery_rider_id', 'stopdesk_point_id'] as $shipField) {
            if (filled($this->form[$shipField] ?? null)) {
                $model = $shipField === 'stopdesk_point_id' ? \App\Domains\Shipping\Models\StopdeskPoint::class
                    : ($shipField === 'delivery_rider_id' ? \App\Domains\Shipping\Models\DeliveryRider::class : \App\Domains\Shipping\Models\ShippingProvider::class);
                $model::where('store_id', $storeId)->findOrFail($this->form[$shipField]);
            }
        }

        $customer = \App\Models\Customer::firstOrCreate(
            ['store_id' => $storeId, 'phone' => $this->form['customer_phone']],
            [
                'name' => $this->form['customer_name'],
                'phone' => $this->form['customer_phone'],
                'address' => $this->form['address'],
                'state_id' => $this->form['state_id'] ?: null,
                'city_id' => $this->form['city_id'] ?: null,
                'status' => true,
            ],
        );

        $total = collect($this->form['items'])->sum(fn($i) => $i['price'] * $i['quantity']);

        app(\App\Domains\Order\Services\OrderService::class)->updateOrder($order, [
            'customer_id' => $customer->id,
            'total_amount' => $total,
            'state_id' => $this->form['state_id'] ?: null,
            'city_id' => $this->form['city_id'] ?: null,
            'address' => $this->form['address'],
            'delivery_type' => $this->form['delivery_type'],
            'shipment_type' => $this->form['shipment_type'],
            'payment_method' => $this->form['payment_method'],
            'discount_type' => $this->form['discount_type'],
            'discount_value' => $this->form['discount_value'] ?: null,
            'discount_reason' => $this->form['discount_reason'] ?: null,
            'notes' => $this->form['notes'],
            'phone_secondary' => $this->form['phone_secondary'],
            'weight_kg' => $this->form['weight_kg'] ?: 1.00,
            'shipping_provider_id' => $isRiderLeg ? null : ($this->form['shipping_provider_id'] ?: null),
            'delivery_rider_id' => $isRiderLeg ? $this->form['delivery_rider_id'] : null,
            'stopdesk_point_id' => ! $isRiderLeg && $this->form['delivery_type'] === 'stopdesk' ? ($this->form['stopdesk_point_id'] ?: null) : null,
        ]);

        $incomingVariantIds = collect($this->form['items'])->pluck('product_variant_id')->filter()->toArray();
        $existingItems = $order->items()->get()->keyBy('product_variant_id');

        $variantMap = \App\Models\Products\ProductVariant::whereIn('id', $incomingVariantIds)->get()->keyBy('id');

        foreach ($this->form['items'] as $idx => $itemData) {
            $vid = $itemData['product_variant_id'] ?? null;
            if (!$vid || !$variantMap->has($vid)) {
                continue;
            }
            $variant = $variantMap[$vid];

            $this->form['items'][$idx]['price'] = $variant->price ?? $itemData['price'];
            $this->form['items'][$idx]['product_id'] = $variant->product_id;

            $prevQty = 0;
            if (isset($existingItems[$vid])) {
                $prevQty = $existingItems[$vid]->quantity;
            }
            $delta = $itemData['quantity'] - $prevQty;
            if ($delta > 0 && \App\Domains\Cart\Support\OrderRules::tracksInventory($order->store) && !\App\Domains\Cart\Support\OrderRules::allowsBackorder($order->store)) {
                $available = (int) $variant->stock;
                if ($available < $delta) {
                    $this->dispatch('swal:toast', ['icon' => 'error', 'title' => __('merchant_panel.insufficient_stock', ['variant' => $variant->name, 'available' => max(0, $available)])]);
                    return;
                }
            }
        }

        foreach ($existingItems as $variantId => $item) {
            if (!in_array($variantId, $incomingVariantIds)) {
                $item->delete();
            }
        }

        foreach ($this->form['items'] as $itemData) {
            $existingItem = $order
                ->items()
                ->where('product_variant_id', $itemData['product_variant_id'] ?? null)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['quantity'] * $itemData['price'],
                ]);
            } else {
                $order->items()->create([
                    'store_id' => $storeId,
                    'product_variant_id' => $itemData['product_variant_id'],
                    'product_id' => $itemData['product_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemData['quantity'] * $itemData['price'],
                ]);
            }
        }

        $order->refresh();
        $this->recalculateOrderShipping($order);

        $this->showEditModal = false;
        $this->editingOrderId = null;
        $this->page = 1;
        $this->loadShipments();

        if ($this->drawerOrderId !== null && (string) $this->drawerOrderId === (string) $order->id) {
            $this->openDrawer((string) $order->id);
        }

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant.order_updated')]);
    }
}