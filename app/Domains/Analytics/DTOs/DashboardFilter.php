<?php

namespace App\Domains\Analytics\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

final class DashboardFilter implements Arrayable
{
    /**
     * Sentinel value of the member pick for the unattributed cohort
     * (confirmed_by IS NULL, «بلا رصيد»). It is never a real ULID, so it can
     * never collide with a store membership id.
     */
    public const UNATTRIBUTED = '__unattributed__';

    private bool $previousResolved = false;

    private ?self $previousFilter = null;

    /**
     * @param  string  $timezone  Store timezone the period is expressed in.
     * @param  int  $utcOffsetSeconds  Offset of $timezone at the end of the window.
     * @param  ?array<int, string>|null  $memberScopeIds  null = every member.
     * @param  CarbonImmutable|null  $from  Window start, already converted to UTC
     *                                      because orders.created_at is stored in UTC.
     *                                      Use localFrom()/localTo() for anything shown.
     * @param  CarbonImmutable|null  $to  Window end in UTC, null for period "all".
     */
    public function __construct(
        public readonly string $period,
        public readonly ?CarbonImmutable $from,
        public readonly ?CarbonImmutable $to,
        public readonly string $timezone,
        public readonly int $utcOffsetSeconds,
        public readonly ?string $carrierId = null,
        public readonly ?string $memberId = null,
        public readonly ?string $memberDimension = null,
        public readonly ?array $memberScopeIds = null,
        public readonly string $storeId = '',
        public readonly bool $memberLocked = false,
        public readonly bool $memberUnattributedOnly = false,
    ) {}

    /**
     * The immediately preceding window of identical length, or null when the
     * period is unbounded ("all") and therefore has nothing to compare with.
     */
    public function previous(): ?self
    {
        if ($this->previousResolved) {
            return $this->previousFilter;
        }

        $this->previousResolved = true;
        $this->previousFilter = $this->buildPrevious();

        return $this->previousFilter;
    }

    public function localFrom(): ?CarbonImmutable
    {
        return $this->from?->setTimezone($this->timezone);
    }

    public function localTo(): ?CarbonImmutable
    {
        return $this->to?->setTimezone($this->timezone);
    }

    public function hash(): string
    {
        $parts = [
            'period' => $this->period,
            'from' => $this->from?->format('Y-m-d H:i:s'),
            'to' => $this->to?->format('Y-m-d H:i:s'),
            'tz' => $this->timezone,
            'offset' => $this->utcOffsetSeconds,
            'carrier' => $this->carrierId,
            'member' => $this->memberId,
            'dim' => $this->memberDimension,
        ];

        return 'f-'.substr(md5(json_encode($parts)), 0, 12);
    }

    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'timezone' => $this->timezone,
            'utcOffsetSeconds' => $this->utcOffsetSeconds,
            'from' => $this->from?->toIso8601String(),
            'to' => $this->to?->toIso8601String(),
            'carrierId' => $this->carrierId,
            'memberId' => $this->memberId,
            'memberDimension' => $this->memberDimension,
            'memberScopeIds' => $this->memberScopeIds,
            'storeId' => $this->storeId,
            'memberLocked' => $this->memberLocked,
            'memberUnattributedOnly' => $this->memberUnattributedOnly,
        ];
    }

    private function buildPrevious(): ?self
    {
        if ($this->from === null || $this->to === null) {
            return null;
        }

        // [from - length, from - 1s]: exactly as many seconds as the current
        // window, immediately before it.
        $length = $this->from->diffInSeconds($this->to) + 1;

        return new self(
            period: $this->period,
            from: $this->from->subSeconds($length),
            to: $this->from->subSecond(),
            timezone: $this->timezone,
            utcOffsetSeconds: $this->utcOffsetSeconds,
            carrierId: $this->carrierId,
            memberId: $this->memberId,
            memberDimension: $this->memberDimension,
            memberScopeIds: $this->memberScopeIds,
            storeId: $this->storeId,
            memberLocked: $this->memberLocked,
            memberUnattributedOnly: $this->memberUnattributedOnly,
        );
    }
}
