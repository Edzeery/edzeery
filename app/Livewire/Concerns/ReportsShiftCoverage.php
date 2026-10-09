<?php

namespace App\Livewire\Concerns;

use App\Domains\Order\Models\ConfirmationShift;
use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;

/**
 * Shifts-tab presentation state for merchant/order-settings: the role filter
 * over state['shifts'] and the per-role setup-gap report (eligible members
 * with no active shift of that role, plus how many shifts cover right now).
 * State (shifts, shiftRoleFilter, storeTimezone, …) stays in the component.
 */
trait ReportsShiftCoverage
{
    public function setShiftRoleFilter(string $role): void
    {
        $this->shiftRoleFilter = in_array($role, ['all', 'confirm', 'track'], true) ? $role : 'all';
    }

    /**
     * Shift rows after the shifts-tab role filter.
     */
    public function visibleShifts(): array
    {
        $shifts = collect($this->shifts);

        if ($this->shiftRoleFilter !== 'all') {
            $shifts = $shifts->filter(fn (array $shift) => ($shift['role_scope'] ?? 'confirm') === $this->shiftRoleFilter);
        }

        $search = trim($this->shiftSearch ?? '');
        if ($search !== '') {
            $needle = mb_strtolower($search);

            $shifts = $shifts->filter(function (array $shift) use ($needle) {
                $role = $shift['role_scope'] ?? 'confirm';

                return str_contains(mb_strtolower($shift['membership']['user']['name'] ?? ''), $needle)
                    || str_contains(mb_strtolower($role), $needle)
                    || str_contains(mb_strtolower((string) ($shift['shift_type'] ?? '')), $needle);
            });
        }

        return $shifts->values()->all();
    }

    /**
     * Per-role setup gap for the notice above the shifts table: how many
     * eligible members hold no active shift of that role (they can never be
     * auto-assigned) and how many of the existing shifts cover right now.
     */
    public function setupGapDetails(): array
    {
        $now = Carbon::now($this->storeTimezone);

        $members = StoreMembership::where('store_id', currentStoreId())
            ->where('is_active', true)
            ->with(['user:id,name', 'permissions', 'confirmationShifts', 'productAssignments'])
            ->get();

        $details = [];

        foreach (['confirm', 'track'] as $roleScope) {
            $permission = $roleScope === 'track'
                ? StorePermissionEnum::CRM_ORDER_TRACKING->value
                : StorePermissionEnum::ORDER_CONFIRM->value;

            $eligible = $members->filter(fn (StoreMembership $m) => $m->can($permission));

            $roleShifts = fn (StoreMembership $m) => $m->confirmationShifts->filter(
                fn (ConfirmationShift $s) => $s->is_active && ($s->role_scope ?? 'confirm') === $roleScope
            );

            $missing = $eligible->reject(fn (StoreMembership $m) => $roleShifts($m)->isNotEmpty());

            $onShiftNow = $eligible->filter(fn (StoreMembership $m) => $roleShifts($m)->contains(
                fn (ConfirmationShift $s) => $s->coversDayTime($now->dayOfWeekIso, $now->format('H:i'))
            ));

            // Ownership coverage for this role: eligible members who own no
            // product of the role. Under strict routing they form the general
            // pool, so the owner should see who is unassigned.
            $ownsRole = fn (StoreMembership $m) => $m->productAssignments
                ->contains(fn ($a) => ($a->role_scope ?? 'confirm') === $roleScope);
            $unowned = $eligible->reject($ownsRole);

            $details[$roleScope] = [
                'role' => $roleScope,
                'missing' => $missing->map(fn (StoreMembership $m) => $m->user?->name ?: '—')->values()->all(),
                'holders' => $eligible->count() - $missing->count(),
                'on_shift_now' => $onShiftNow->count(),
                'unowned' => $unowned->count(),
                'unowned_names' => $unowned->map(fn (StoreMembership $m) => $m->user?->name ?: '—')->values()->all(),
            ];
        }

        return $details;
    }
}
