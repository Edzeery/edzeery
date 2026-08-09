<?php

namespace App\Domains\Plan\ValueObjects;

class FeatureValue
{
    public function __construct(
        public readonly mixed $value
    ) {}

    public function isUnlimited(): bool
    {
        return $this->value === 'unlimited' || $this->value === null;
    }

    public function isEnabled(): bool
    {
        return $this->value === true || $this->value === 'true' || $this->value === 1;
    }

    public function toInt(): ?int
    {
        return $this->isUnlimited() ? null : (int) $this->value;
    }
}
