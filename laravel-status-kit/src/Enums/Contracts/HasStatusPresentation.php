<?php

namespace Edzeery\LaravelStatusKit\Enums\Contracts;

interface HasStatusPresentation
{
    public function label(): string;
    public function color(bool $dark = false): string;
    public function filamentColor(): string;
    public function icon(): ?string;
    public function hex(): string;
    public function toArray(): array;
    public static function options(): array;
}
