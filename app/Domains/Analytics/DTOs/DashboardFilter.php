<?php

namespace App\Domains\Analytics\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

final class DashboardFilter implements Arrayable
{
    public function __construct(
        public readonly string $period,
        public readonly ?CarbonImmutable $from,
        public readonly ?CarbonImmutable $to,
        public readonly ?CarbonImmutable $previousFrom,
        public readonly ?CarbonImmutable $previousTo,
        public readonly ?string $carrierId = null,
        public readonly ?string $memberId = null,
        public readonly ?string $memberDimension = null,
        public readonly ?array $allowedMembershipIds = null,
        public readonly string $storeId = '',
    ) {}

    public function granularity(): string
    {
        if ($this->from && $this->to) {
            $diff = $this->from->diffInDays($this->to);
            if ($diff <= 0) {
                return 'hour';
            }
            if ($diff <= 62) {
                return 'day';
            }

            return 'month';
        }

        if ($this->period === 'today' || $this->period === 'yesterday') {
            return 'hour';
        }

        return 'month';
    }

    public function hash(): string
    {
        $parts = [
            'period' => $this->period,
            'from' => $this->from?->format('Y-m-d H:i:s'),
            'to' => $this->to?->format('Y-m-d H:i:s'),
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
            'from' => $this->from?->toIso8601String(),
            'to' => $this->to?->toIso8601String(),
            'previousFrom' => $this->previousFrom?->toIso8601String(),
            'previousTo' => $this->previousTo?->toIso8601String(),
            'carrierId' => $this->carrierId,
            'memberId' => $this->memberId,
            'memberDimension' => $this->memberDimension,
            'storeId' => $this->storeId,
        ];
    }
}
