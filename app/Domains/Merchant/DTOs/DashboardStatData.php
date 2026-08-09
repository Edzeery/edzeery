<?php

namespace App\Domains\Merchant\DTOs;

final class DashboardStatData
{
    public function __construct(
        public readonly string $title,
        public readonly int|float $count,
        public readonly ?string $desc = null,
        public readonly float $percentageResult = 0,
        public readonly string $trend = 'up',
        public readonly ?string $icon = null,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'count' => $this->count,
            'desc' => $this->desc,
            'percentage_result' => $this->percentageResult,
            'trend' => $this->trend,
            'icon' => $this->icon,
        ];
    }
}
