<?php

namespace Edzeery\LaravelStatusKit\Traits;

use Edzeery\LaravelStatusKit\Support\StatusColorManager;

trait HasStatusTrait
{
    public function statusLabel(): string
    {
        return __(
            "status-kit::status.{$this->status}"
        );
    }

    public function statusColor(bool $dark = false): string
    {
        return StatusColorManager::get($this->statusGroup(), $this->status, $dark);
    }

    abstract protected function statusGroup(): string;
}
