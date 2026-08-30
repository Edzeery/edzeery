<?php

namespace App\Enums\Concerns;

use App\Domains\Status\StatusResolver;
use App\Domains\Status\Support\ResolvedStatus;
use Closure;
use Edzeery\MyStatusKit\DTO\StatusResult;
use Edzeery\MyStatusKit\Facades\Status;
use Illuminate\Contracts\Support\Htmlable;
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

    public function statusDomain(): string
    {
        return $this->kitGroup();
    }

    public function statusResult(): StatusResult
    {
        return Status::for($this->kitGroup(), $this->value);
    }

    /**
     * نتيجة الحالة الموحّدة (DB أولاً ثم status-kit ثم fallback).
     */
    public function resolved(?string $storeId = null): ResolvedStatus
    {
        return StatusResolver::resolve($this->kitGroup(), $this->value, $storeId);
    }

    /* ================================
     | Label
     ================================ */

    public function label(?string $locale = null): string
    {
        if ($this->resolved()->source === 'db') {
            return $this->resolved()->label;
        }

        return $this->kitLabel($locale);
    }

    public function getLabel(): string
    {
        return $this->label();
    }

    /* ================================
     | Color
     ================================ */

    public function color(bool $dark = false, ?string $framework = 'tailwind'): string
    {
        return $this->kitColor($dark, $framework);
    }

    public function css(bool $dark = false): string
    {
        return $this->kitColor($dark, 'tailwind');
    }

    public function hex(): string
    {
        return $this->resolved()->hex;
    }

    public function filamentColor(): string|array|bool|Closure
    {
        return match ($this->kitVariant()) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            'info' => 'info',
            default => 'gray',
        };
    }

    /* ================================
     | Icon
     ================================ */

    /** مفتاح الأيقونة (بدون HTML). */
    public function iconKey(): string
    {
        return $this->resolved()->icon ?? 'default';
    }

    /** HTML الأيقونة عبر vendor status-kit. */
    public function icon(?string $set = null, ?string $classes = null): string
    {
        return $this->kitIcon($set, $classes);
    }

    /** اسم أيقونة Filament (heroicon-o-*). */
    public function filamentIcon(): string|BackedEnum|Htmlable|bool|Closure|null
    {
        return $this->resolved()->filamentIcon();
    }

    /* ================================
     | Collections
     ================================ */

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'color' => $this->css(),
            'hex' => $this->hex(),
            'icon' => $this->iconKey(),
        ];
    }

    public static function options(): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function api(): array
    {
        return array_map(fn ($case) => $case->toArray(), static::cases());
    }

    public static function values(): array
    {
        return array_column(static::cases(), 'value');
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
