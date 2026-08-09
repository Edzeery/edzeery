<?php

namespace App\Enums\Concerns;

use Closure;

interface HasStatusPresentation
{
    public function label(): string;

    public function color(bool $dark = false): string;

    public function filamentColor(): string | array | bool | Closure;

    public function icon(): ?string;

    public function hex(): string;

    public function toArray(): array;

    public static function options(): array;

    public static function api(): array;

    public static function values(): array;
}
