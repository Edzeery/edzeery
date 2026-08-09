<?php

namespace App\Enums\Concerns;

use Edzeery\MyStatusKit\DTO\StatusResult;
use Edzeery\MyStatusKit\Facades\Status;
use LogicException;

trait InteractsWithStatusKit
{
    protected function kitGroup(): string
    {
        if (defined('static::STATUS_KIT_GROUP')) {
            return static::STATUS_KIT_GROUP;
        }

        if (! defined('static::GROUP')) {
            throw new LogicException(static::class.' must define GROUP constant.');
        }

        return static::GROUP;
    }

    public function statusResult(): StatusResult
    {
        return Status::for($this->kitGroup(), $this->value);
    }

    public function kitVariant(): string
    {
        return $this->statusResult()->variant();
    }

    public function kitColor(bool $withDark = true, ?string $framework = null): string
    {
        return $this->statusResult()->color($withDark, $framework);
    }

    public function kitLightClass(): string
    {
        return $this->statusResult()->lightClass();
    }

    public function kitDarkClass(): string
    {
        return $this->statusResult()->darkClass();
    }

    public function kitHex(): string
    {
        return $this->statusResult()->hex();
    }

    public function kitLabel(?string $locale = null): string
    {
        return $this->statusResult()->label($locale);
    }

    public function kitIcon(?string $set = null, ?string $classes = null): string
    {
        return $this->statusResult()->icon($set, $classes);
    }

    public function kitBadge(?string $set = null, ?string $extraClasses = null, ?string $framework = null): string
    {
        return $this->statusResult()->badge($set, $extraClasses, $framework);
    }

    public function kitBadgeWithoutIcon(?string $extraClasses = null, ?string $framework = null): string
    {
        return $this->statusResult()->badgeWithoutIcon($extraClasses, $framework);
    }

    public function kitToArray(): array
    {
        return $this->statusResult()->toArray();
    }
}
