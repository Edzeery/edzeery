<?php

namespace App\Domains\Order\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Serialises the auto-assignment critical section — candidate pool build,
 * capacity/ownership-ranked selection and the write — per store + role scope,
 * so two concurrent dispatchers (scheduler sweep vs. shift handover vs. an
 * event-driven dispatch) cannot both count the same member as free and
 * double-book them past their cap.
 *
 * The lock waits up to 5s (250ms poll) with a 10s lease; a contended lock is
 * skipped, never thrown: the caller's row keeps its previous state and the
 * next scheduled sweep retries it.
 */
trait GuardsDistributionLock
{
    /**
     * Run $callback while holding the store/role-scope distribution lock.
     * Returns the callback's result, or null when the lock timed out.
     *
     * The wait deadline uses microtime() rather than Lock::block(), which
     * measures with now() and therefore never expires under the frozen test
     * clock (Carbon::setTestNow) this engine's test suite relies on.
     */
    protected function withDistributionLock(string $storeId, string $roleScope, Closure $callback): mixed
    {
        $lock = Cache::lock("distribution:{$storeId}:{$roleScope}", 10);
        $deadline = microtime(true) + 5;

        while (! $lock->get()) {
            if (microtime(true) >= $deadline) {
                Log::warning('Distribution lock contended: assignment pass skipped', [
                    'store_id' => $storeId,
                    'role_scope' => $roleScope,
                ]);

                return null;
            }

            usleep(250_000);
        }

        try {
            return $callback();
        } finally {
            $lock->release();
        }
    }
}
