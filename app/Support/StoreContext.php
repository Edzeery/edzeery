<?php

namespace App\Support;

use App\Models\Stores\Store;
use Closure;

class StoreContext
{
    protected ?Store $store = null;

    public function set(Store $store): void
    {
        $this->store = $store;
    }

    public function get(): ?Store
    {
        return $this->store;
    }

    public function id(): ?string
    {
        return $this->store?->id;
    }

    /** Whether an explicit store context is currently active. */
    public function has(): bool
    {
        return $this->store !== null;
    }

    public function clear(): void
    {
        $this->store = null;
    }

    /**
     * Run a callback inside a store context and restore the previous context
     * afterwards (even on exception). The explicit escape hatch for jobs,
     * commands and webhooks that must operate on a store outside an HTTP
     * request. Pass `null` to run with no store context at all.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function runAs(?Store $store, Closure $callback): mixed
    {
        $context = app(static::class);

        $previous = $context->store;
        $context->store = $store;

        try {
            return $callback();
        } finally {
            $context->store = $previous;
        }
    }
}
