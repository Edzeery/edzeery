<?php

namespace App\Domains\Order\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfirmationShift extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'membership_id',
        'shift_type',
        'start_time',
        'end_time',
        'days_of_week',
        'is_active',
        'max_concurrent_orders',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'start_time' => 'string',
        'end_time' => 'string',
        'max_concurrent_orders' => 'integer',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stores\Team\StoreMembership::class, 'membership_id');
    }

    /**
     * Whether this shift overlaps an existing shift of the same member.
     * Renders both windows into per-day minute blocks (overnight spans two blocks).
     */
    public static function overlapsActiveShift(array $candidate, ?string $excludeId = null): bool
    {
        $existing = static::query()
            ->where('membership_id', $candidate['membership_id'])
            ->where('is_active', true)
            ->when($excludeId, fn (Builder $q, $id) => $q->where('id', '!=', $id))
            ->get(['days_of_week', 'start_time', 'end_time']);

        foreach ($existing as $shift) {
            if (static::blocksOverlap(
                $shift->days_of_week, $shift->start_time, $shift->end_time,
                $candidate['days_of_week'], $candidate['start_time'], $candidate['end_time'],
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert a shift window into (day, startMinute, endMinute) blocks.
     */
    private static function shiftBlocks(?array $days, ?string $start, ?string $end): array
    {
        $days = $days ?: range(1, 7);
        $s = static::toMinutes($start);
        $e = static::toMinutes($end);

        if ($s === $e) {
            return [];
        }

        $blocks = [];
        if ($s < $e) {
            foreach ($days as $day) {
                $blocks[] = [$day, $s, $e];
            }

            return $blocks;
        }

        // Overnight: [start, 24:00) + [00:00, end) on the following day
        foreach ($days as $day) {
            $blocks[] = [$day, $s, 1440];
            $blocks[] = [$day === 7 ? 1 : $day + 1, 0, $e];
        }

        return $blocks;
    }

    private static function blocksOverlap(
        ?array $daysA, ?string $startA, ?string $endA,
        ?array $daysB, ?string $startB, ?string $endB,
    ): bool {
        $blocksA = static::shiftBlocks($daysA, $startA, $endA);
        $blocksB = static::shiftBlocks($daysB, $startB, $endB);

        foreach ($blocksA as [$dayA, $startMinA, $endMinA]) {
            foreach ($blocksB as [$dayB, $startMinB, $endMinB]) {
                if ($dayA !== $dayB) {
                    continue;
                }
                if ($startMinA < $endMinB && $startMinB < $endMinA) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether this shift covers a given ISO day-of-week (1=Mon..7=Sun) and time of day (H:i).
     * Overnight shifts (start > end) naturally extend into the following day.
     */
    public function coversDayTime(int $dayOfWeek, string $time): bool
    {
        $days = $this->days_of_week ?: range(1, 7);
        $start = static::toMinutes($this->start_time);
        $end = static::toMinutes($this->end_time);
        $minute = static::toMinutes($time);

        if (! $this->is_active) {
            return false;
        }

        if ($start < $end) {
            // Normal (same-day) shift.
            return in_array($dayOfWeek, $days, true)
                && $minute >= $start && $minute < $end;
        }

        if ($start === $end) {
            return false;
        }

        // Overnight: [start, 24:00) on `day`, then [00:00, end) on `day + 1`.
        $onDay = in_array($dayOfWeek, $days, true) && $minute >= $start;
        $nextDay = $dayOfWeek === 1 ? 7 : $dayOfWeek - 1;
        $onNext = in_array($nextDay, $days, true) && $minute < $end;

        return $onDay || $onNext;
    }

    private static function toMinutes(?string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time ?? '00:00'));

        return ($h * 60) + $m;
    }
}
