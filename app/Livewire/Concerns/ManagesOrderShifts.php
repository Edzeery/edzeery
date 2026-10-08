<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Role-scoped shift management for the merchant order-settings page: the
 * permission-filtered agent picker (confirmation vs tracking), shift CRUD with
 * role-aware overlap and permission validation, the shifts-tab role filter and
 * the setup-gap notice that surfaces a role with eligible members but no
 * active shift. Extracted from merchant/order-settings.blade.php so the Volt
 * file stays under the line ceiling; state (shiftForm, shiftRoleFilter,
 * shifts, storeTimezone, …) remains declared in the component.
 */
trait ManagesOrderShifts
{
    /**
     * Agent options for the shift modal partitioned by the role's required
     * permission. One query per render with the permissions relation
     * eager-loaded, so StoreMembership::can() never falls back to a
     * per-member pivot query.
     *
     * @return array<string, array<int, array<string, mixed>>> Keys: 'confirm', 'track'.
     */
    public function eligibleShiftMembers(): array
    {
        $members = StoreMembership::where('store_id', currentStoreId())
            ->with(['user:id,name', 'permissions'])
            ->get();

        return [
            'confirm' => $this->shiftMemberOptions($members, StorePermissionEnum::ORDER_CONFIRM->value),
            'track' => $this->shiftMemberOptions($members, StorePermissionEnum::CRM_ORDER_TRACKING->value),
        ];
    }

    /**
     * Swap the modal's role scope. A picked agent that does not hold the new
     * role's permission is cleared so a stale selection can never be saved
     * into the other role's shift.
     */
    public function setShiftRole(string $roleScope): void
    {
        if (! in_array($roleScope, ['confirm', 'track'], true)) {
            return;
        }

        $this->shiftForm['role_scope'] = $roleScope;

        $eligibleIds = array_column($this->eligibleShiftMembers()[$roleScope], 'id');

        if (! in_array($this->shiftForm['membership_id'], $eligibleIds, true)) {
            $this->shiftForm['membership_id'] = '';
        }
    }

    // ——— Shift Type auto-fill times ———

    public function onShiftTypeChange(): void
    {
        $shiftTimes = [
            'morning' => ['start_time' => '08:00', 'end_time' => '12:00'],
            'afternoon' => ['start_time' => '12:00', 'end_time' => '17:00'],
            'evening' => ['start_time' => '17:00', 'end_time' => '22:00'],
            'full_day' => ['start_time' => '08:00', 'end_time' => '22:00'],
            'custom' => ['start_time' => '08:00', 'end_time' => '17:00'],
        ];
        $times = $shiftTimes[$this->shiftForm['shift_type']] ?? ['start_time' => '08:00', 'end_time' => '17:00'];
        $this->shiftForm['start_time'] = $times['start_time'];
        $this->shiftForm['end_time'] = $times['end_time'];

        if ($this->shiftForm['shift_type'] === 'full_day') {
            $this->shiftForm['days_of_week'] = [1, 2, 3, 4, 5, 6, 7];
        }
    }

    // ——— Shifts CRUD ———

    public function openShiftModal(?string $shiftId = null): void
    {
        if ($shiftId) {
            $shift = ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId);
            $this->editingShiftId = $shift->id;
            $this->shiftForm = [
                'role_scope' => $shift->role_scope ?? 'confirm',
                'membership_id' => $shift->membership_id,
                'shift_type' => $shift->shift_type,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'days_of_week' => $shift->days_of_week ?? range(1, 7),
                'is_active' => $shift->is_active,
                'max_concurrent_orders' => $shift->max_concurrent_orders,
            ];
        } else {
            $this->editingShiftId = null;
            $this->shiftForm = [
                'role_scope' => 'confirm',
                'membership_id' => '',
                'shift_type' => 'morning',
                'start_time' => '08:00',
                'end_time' => '12:00',
                'days_of_week' => [1, 2, 3, 4, 5],
                'is_active' => true,
                'max_concurrent_orders' => null,
            ];
        }
        $this->showShiftModal = true;
    }

    public function toggleShiftDay(int $day): void
    {
        $current = $this->shiftForm['days_of_week'] ?? [];
        if (in_array($day, $current)) {
            $this->shiftForm['days_of_week'] = array_values(array_diff($current, [$day]));
        } else {
            $this->shiftForm['days_of_week'][] = $day;
            sort($this->shiftForm['days_of_week']);
        }
    }

    public function saveShift(): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);

        $validated = Validator::make($this->shiftForm, [
            'role_scope' => 'required|string|in:confirm,track',
            'membership_id' => 'required|exists:store_memberships,id',
            'shift_type' => 'required|string|in:morning,afternoon,evening,full_day,custom',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|min:1|max:7',
            'is_active' => 'boolean',
            'max_concurrent_orders' => 'nullable|integer|min:1|max:9999',
        ])->validate();

        $storeId = currentStoreId();
        $roleScope = $validated['role_scope'];

        // Overnight shifts are allowed (start > end); empty end means invalid.
        $days = array_values(array_unique($validated['days_of_week']));

        // The chosen agent must hold the selected role's permission, otherwise
        // the shift would describe work they can never be assigned.
        $member = StoreMembership::where('store_id', $storeId)
            ->where('id', $validated['membership_id'])
            ->with('permissions')
            ->first();

        if (! $member || ! $member->can($this->rolePermission($roleScope))) {
            $this->addError('shiftForm.membership_id', __('merchant_panel.shift_role_mismatch'));

            return;
        }

        // Prevent overlapping active shifts of the same member in the same
        // role scope — identical windows across roles are allowed.
        $candidate = $validated;
        $candidate['days_of_week'] = $days;

        if (ConfirmationShift::overlapsActiveShift($candidate, $this->editingShiftId)) {
            $this->addError('shiftForm.start_time', __('merchant_panel.shift_overlap'));

            return;
        }

        $data = $validated;
        $data['store_id'] = $storeId;
        $data['days_of_week'] = $days;

        if (! empty($this->editingShiftId)) {
            ConfirmationShift::where('store_id', $storeId)
                ->findOrFail($this->editingShiftId)
                ->update($data);
        } else {
            ConfirmationShift::create($data);
        }

        $this->dispatch('swal', type: 'success', title: __('merchant_panel.shift_saved'));
        $this->showShiftModal = false;
        $this->loadData();
    }

    public function deleteShift(string $shiftId): void
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_MANAGE->value), 403);
        ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId)->delete();
        $this->loadData();
        $this->dispatch('swal', type: 'success', title: __('merchant_panel.shift_deleted'));
    }

    public function toggleShiftActive(string $shiftId): void
    {
        $shift = ConfirmationShift::where('store_id', currentStoreId())->findOrFail($shiftId);
        $shift->update(['is_active' => ! $shift->is_active]);
        $this->loadData();
    }

    /**
     * Store membership ids holding a role's permission, in the x-edz.select
     * option shape ({id, user.name, is_active}).
     *
     * @return array<int, array<string, mixed>>
     */
    private function shiftMemberOptions(Collection $members, string $permission): array
    {
        return $members
            ->filter(fn (StoreMembership $m) => $m->can($permission))
            ->map(fn (StoreMembership $m) => [
                'id' => $m->id,
                'user' => ['name' => $m->user?->name ?: '—'],
                'is_active' => $m->is_active,
            ])
            ->values()
            ->all();
    }

    private function rolePermission(string $roleScope): string
    {
        return $roleScope === 'track'
            ? StorePermissionEnum::CRM_ORDER_TRACKING->value
            : StorePermissionEnum::ORDER_CONFIRM->value;
    }
}
