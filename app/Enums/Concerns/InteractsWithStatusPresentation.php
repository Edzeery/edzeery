<?php

namespace App\Enums\Concerns;

use Closure;
use LogicException;

trait InteractsWithStatusPresentation
{

    protected function group(): string
    {
        if (!defined('static::GROUP')) {
            throw new LogicException(static::class . ' must define GROUP constant.');
        }

        return static::GROUP;
    }

    protected function config(): array
    {
        return config("status-colors.{$this->group()}.{$this->value}", []);
    }


    public function label(): string
    {
        return __("status.{$this->value}");
    }

    public function color(bool $dark = false): string
    {
        return $this->config()[$dark ? 'dark' : 'light']
            ?? config('status-colors.general.gray.light');
    }

    public function filamentColor(): string | array | bool | Closure
    {
        return $this->config()['filament'] ?? 'primary';
    }

    public function icon(): ?string
    {
        return $this->config()['icon'] ?? null;
    }

    public function hex(): string
    {
        return $this->config()['hex'] ?? '#000000';
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'color' => $this->color(),
            'hex'   => $this->hex(),
            'icon'  => $this->icon(),
        ];
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    public static function api(): array
    {
        return array_map(
            fn($case) => $case->toArray(),
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
