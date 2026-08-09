<?php

namespace App\Enums\Concerns;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use LogicException;

trait HasFilamentPresentation
{
    protected function filamentGroup(): string
    {
        if (! defined('static::GROUP')) {
            throw new LogicException(static::class . ' must define GROUP constant.');
        }

        return static::GROUP;
    }

    protected function filamentConfig(): array
    {
        return config(
            'status-colors.' . $this->filamentGroup() . '.' . $this->value,
            []
        );
    }

    /* ============================
     |  Filament helpers
     |============================ */

    public function filamentColor(): string | array | bool | Closure
    {
        return $this->filamentConfig()['filament'] ?? 'primary';
    }

    public function icon(): string | BackedEnum | Htmlable | bool | Closure | null
    {
        return $this->filamentConfig()['icon'] ?? null;
    }

    public function css(bool $dark = false): string
    {
        return $this->filamentConfig()[$dark ? 'dark' : 'light'] ?? '';
    }

    public function hex(): string
    {
        return $this->filamentConfig()['hex'] ?? '#000000';
    }

    public function label($group = null): string
    {
        $group = $group ?? $this->filamentGroup();

        return __(
            $group . '.' . $this->value
        );
    }
}
