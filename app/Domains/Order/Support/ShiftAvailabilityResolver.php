<?php

namespace App\Domains\Order\Support;

use App\Domains\Order\Models\ConfirmationShift;
use App\Models\Stores\Team\StoreMembership;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Time-aware availability snapshot for one role scope at one instant. For each
 * candidate membership it answers the two questions the assignment engine and
 * the manual reassignment modals both ask:
 *
 *  - on_shift: does an active shift of this role scope cover the instant?
 *  - cap:      what capacity applies at that instant (null = uncapped)?
 *
 * Cap semantics are time-aware: only shifts COVERING the instant count, so a
 * member capped on another day is not capped now, and a member covered by any
 * uncapped shift is uncapped for this instant no matter how many capped shifts
 * they own (the highest covering cap wins otherwise). Members with no covering
 * shift are off-shift — they carry no entry, which consumers read as
 * ['on_shift' => false, 'cap' => null].
 *
 * One query per snapshot (one per assignment call) replaces the per-member
 * shift queries the pipelines used to make, and because both pipelines and
 * AssignmentCandidateResolver route through here, the reassign modal always
 * shows exactly what the engine enforces.
 */
class ShiftAvailabilityResolver
{
    /**
     * @param  Collection<int, StoreMembership>  $candidates  Permission-filtered member pool.
     * @param  ?Carbon  $at  Instant to evaluate; defaults to "now" in the store timezone.
     * @return array<string, array{on_shift: bool, cap: int|null}> Keyed by membership id; absent = off-shift.
     */
    public function resolve(string $storeId, string $roleScope, Collection $candidates, ?Carbon $at = null): array
    {
        if ($candidates->isEmpty()) {
            return [];
        }

        $memberIds = $candidates->pluck('id')->all();

        $timezone = $candidates->first()->storeWithTimezone?->settings?->timezone ?? config('app.timezone');
        $at ??= now($timezone);

        $dayOfWeek = $at->dayOfWeekIso;
        $time = $at->format('H:i');

        $shifts = ConfirmationShift::query()
            ->where('store_id', $storeId)
            ->whereIn('membership_id', $memberIds)
            ->where('is_active', true)
            ->when($roleScope === 'track', fn ($q) => $q->track(), fn ($q) => $q->confirm())
            ->get(['membership_id', 'days_of_week', 'start_time', 'end_time', 'is_active', 'max_concurrent_orders']);

        $covering = [];

        foreach ($shifts as $shift) {
            if (! $shift->coversDayTime($dayOfWeek, $time)) {
                continue;
            }

            $memberId = $shift->membership_id;
            $state = $covering[$memberId] ?? ['uncapped' => false, 'max_cap' => null];

            if ($shift->max_concurrent_orders === null) {
                $state['uncapped'] = true;
            } else {
                $state['max_cap'] = max($state['max_cap'] ?? 0, (int) $shift->max_concurrent_orders);
            }

            $covering[$memberId] = $state;
        }

        $availability = [];

        foreach ($covering as $memberId => $state) {
            $availability[$memberId] = [
                'on_shift' => true,
                'cap' => $state['uncapped'] ? null : $state['max_cap'],
            ];
        }

        return $availability;
    }
}
