<?php

namespace App\Livewire\Concerns;

use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Enums\Store\StorePermissionEnum;

/**
 * Per-tab grid column preferences (view_key tracking_carrier / tracking_rider),
 * table settings modal and the rider options list — extracted out of
 * tracking/index.blade.php.
 */
trait TrackingColumnConcern
{
    public function trackingColumns(): array
    {
        $base = [
            ['key' => 'number', 'label_key' => 'number', 'required' => true],
            ['key' => 'tracking_number', 'label_key' => 'tracking_number_copy', 'required' => true],
            ['key' => 'customer', 'label_key' => 'customer', 'required' => true],
            ['key' => 'state', 'label_key' => 'state', 'required' => true],
            ['key' => 'city', 'label_key' => 'city'],
            ['key' => 'total', 'label_key' => 'total', 'required' => true],
            ['key' => 'tracking_status', 'label_key' => 'tracking_status', 'required' => true],
            ['key' => 'assigned_to', 'label_key' => 'assigned_agent'],
            ['key' => 'confirmed_by', 'label_key' => 'confirmed_by'],
            ['key' => 'notes', 'label_key' => 'notes'],
            ['key' => 'shipping_date', 'label_key' => 'date'],
            ['key' => 'actions', 'label_key' => 'actions', 'required' => true],
        ];

        if ($this->trackingTab === 'rider') {
            return array_merge(
                array_slice($base, 0, 7),
                [['key' => 'delivery_rider', 'label_key' => 'rider_name', 'required' => true]],
                array_slice($base, 7),
            );
        }

        return array_merge(
            array_slice($base, 0, 7),
            [['key' => 'provider', 'label_key' => 'shipping_provider', 'required' => true]],
            array_slice($base, 7),
        );
    }

    public function trackingDefaultOrder(): array
    {
        return array_values(array_intersect(
            $this->trackingTab === 'rider'
                ? ['number', 'tracking_number', 'customer', 'state', 'city', 'total', 'tracking_status', 'delivery_rider', 'assigned_to', 'confirmed_by', 'notes', 'shipping_date', 'actions']
                : ['number', 'tracking_number', 'customer', 'state', 'city', 'total', 'tracking_status', 'provider', 'assigned_to', 'confirmed_by', 'notes', 'shipping_date', 'actions'],
            collect($this->trackingColumns())->pluck('key')->all(),
        ));
    }

    public function trackingViewKey(): string
    {
        return $this->trackingTab === 'rider' ? 'tracking_rider' : 'tracking_carrier';
    }

    public function getMembership(): ?\App\Models\Stores\Team\StoreMembership
    {
        return \App\Models\Stores\Team\StoreMembership::where('store_id', currentStoreId())
            ->where('user_id', auth()->id())
            ->first();
    }

    public function loadTrackingTabPreferences(): void
    {
        $validKeys = collect($this->trackingColumns())->pluck('key')->all();
        $required = collect($this->trackingColumns())->where('required', true)->pluck('key')->all();
        $defaults = $this->trackingDefaultOrder();
        $prefsVersion = 2;

        $membership = $this->getMembership();
        if (! $membership) {
            $this->visibleColumns = $defaults;
            $this->tableStyle = 'default';
            return;
        }

        $pref = \App\Domains\Order\Models\UserColumnPreference::where('membership_id', $membership->id)
            ->where('view_key', $this->trackingViewKey())
            ->first();

        $stored = is_array($pref?->visible_columns)
            ? array_values(array_intersect($pref->visible_columns, $validKeys))
            : [];

        if (! $pref || (int) ($pref->prefs_version ?? 0) !== $prefsVersion || empty($stored)) {
            $stored = $defaults;
            $pref?->update(['visible_columns' => $stored, 'prefs_version' => $prefsVersion]);
        }

        $ordered = $stored;
        foreach ($defaults as $pos => $key) {
            if (in_array($key, $ordered, true) || ! in_array($key, $required, true)) {
                continue;
            }
            $insertAt = min($pos, count($ordered));
            array_splice($ordered, $insertAt, 0, [$key]);
        }
        foreach ($required as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        $this->visibleColumns = $ordered;
        $this->tableStyle = $pref?->table_style === 'status' ? 'status' : 'default';
    }

    public function saveColumnPreferences(): void
    {
        $membership = $this->getMembership();
        if (! $membership) {
            return;
        }

        $validKeys = collect($this->trackingColumns())->pluck('key')->all();
        $required = collect($this->trackingColumns())->where('required', true)->pluck('key')->all();
        $defaults = $this->trackingDefaultOrder();

        $ordered = array_values(array_intersect($this->visibleColumns, $validKeys));

        // Required columns can never be hidden — force them back in on save,
        // inserted at their canonical default spot rather than just appended.
        foreach ($defaults as $pos => $key) {
            if (in_array($key, $ordered, true) || ! in_array($key, $required, true)) {
                continue;
            }
            $insertAt = min($pos, count($ordered));
            array_splice($ordered, $insertAt, 0, [$key]);
        }
        foreach ($required as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }
        $this->visibleColumns = $ordered;

        \App\Domains\Order\Models\UserColumnPreference::updateOrCreate(
            ['membership_id' => $membership->id, 'view_key' => $this->trackingViewKey()],
            [
                'visible_columns' => $ordered,
                'table_style' => $this->tableStyle,
                'prefs_version' => 2,
            ],
        );
    }

    public function openTableSettings(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        $this->draftColumns = $this->visibleColumns;
        $this->draftStyle = $this->tableStyle;
        $this->showTableSettings = true;
    }

    public function discardTableSettings(): void
    {
        $this->showTableSettings = false;
        $this->draftColumns = [];
        $this->draftStyle = 'default';
    }

    public function saveTableSettings(): void
    {
        $validKeys = collect($this->trackingColumns())->pluck('key')->all();
        $this->visibleColumns = array_values(array_intersect($this->draftColumns, $validKeys));
        $this->tableStyle = in_array($this->draftStyle, ['default', 'status'], true) ? $this->draftStyle : 'default';

        $this->saveColumnPreferences();

        $this->showTableSettings = false;
        $this->draftColumns = [];
        $this->draftStyle = 'default';

        $this->loadTrackingTabPreferences();
        $this->loadShipments();

        $this->dispatch('swal:toast', ['icon' => 'success', 'title' => __('merchant_panel.settings_saved')]);
    }

    public function toggleDraftColumn(string $column): void
    {
        $col = collect($this->trackingColumns())->firstWhere('key', $column);

        if (! $col || ($col['required'] ?? false)) {
            return;
        }

        if (in_array($column, $this->draftColumns, true)) {
            $this->draftColumns = array_values(array_diff($this->draftColumns, [$column]));
        } else {
            $this->draftColumns[] = $column;
        }
    }

    public function moveDraftColumn(string $column, string $direction): void
    {
        $position = array_search($column, $this->draftColumns, true);
        if ($position === false) {
            return;
        }

        $target = $direction === 'up' ? $position - 1 : $position + 1;
        if ($target < 0 || $target >= count($this->draftColumns)) {
            return;
        }

        [$this->draftColumns[$position], $this->draftColumns[$target]] = [$this->draftColumns[$target], $this->draftColumns[$position]];
    }

    public function reorderDraftColumns(array $keys): void
    {
        $validKeys = collect($this->trackingColumns())->pluck('key')->all();
        $this->draftColumns = array_values(
            array_intersect(collect($keys)->unique()->values()->all(), $validKeys),
        );
    }

    public function resetColumns(): void
    {
        $this->draftColumns = $this->trackingDefaultOrder();
        $this->draftStyle = 'default';
    }

    // ——— Rider options — configured riders power the header 'rider' filter list. ———
    public function loadRiderOptions(): void
    {
        $this->allRiders = app(DeliveryRiderService::class)->listForStore(currentStoreId())
            ->map(fn (DeliveryRider $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'phone' => $r->phone,
                'vehicle_label' => $r->vehicle_label,
                'is_active' => (bool) $r->is_active,
            ])
            ->values()
            ->all();

        // Selectable carrier partners in the shared order edit form: only
        // active riders, in the same hint/kind shape the orders page uses.
        $this->riderOptions = app(DeliveryRiderService::class)
            ->listForStore(currentStoreId(), onlyActive: true)
            ->map(fn (DeliveryRider $r) => [
                'value' => (string) $r->id,
                'label' => $r->name,
                'hint' => (string) ($r->phone ?? ''),
                'kind' => 'rider',
            ])
            ->values()
            ->all();
    }
}