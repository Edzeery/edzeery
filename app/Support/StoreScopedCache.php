<?php

declare(strict_types=1);

namespace App\Support;

use App\Domains\Order\Services\OrderCompleteness;
use App\Domains\Order\Services\OrderService;
use App\Domains\Shipping\Services\DeliveryRiderService;
use App\Domains\Status\StatusResolver;

/**
 * PHASE 38-C — worker cache isolation.
 *
 * A long-lived process (queue worker, scheduler, Octane) carries class
 * statics and container bindings across jobs, so a store-scoped memo resolved
 * for one job would leak into the next. This orchestrator resets every
 * request-scoped/worker-scoped memo at the START and END of every queued job
 * actually processed from the queue (JobProcessing / JobProcessed / JobFailed
 * on a non-"sync" connection). Jobs dispatched inline via the sync driver
 * (dispatchSync, tests) deliberately share the enclosing request's context and
 * are skipped. Scheduled commands are isolated by the scheduler itself — each
 * runs as its own subprocess — so no console hooks are needed.
 *
 * It is wired ONCE in AppServiceProvider via global queue events — not through
 * a base class, so no job needs to opt in.
 */
final class StoreScopedCache
{
    /**
     * canStore() memo (app/Helpers/helpers.php). Moved out of the helper's
     * function-local static so the reset can reach it — a function-local
     * static could not be flushed externally.
     *
     * @var array<string, bool>
     */
    public static array $canStore = [];

    /** User id the current $canStore memo belongs to. */
    public static ?string $canStoreUser = null;

    /**
     * Reset every request-scoped/worker-scoped memo.
     */
    public static function flush(): void
    {
        app(StoreContext::class)->clear();

        self::$canStore = [];
        self::$canStoreUser = null;

        // The membership bound by EnsureStoreMembership for the previous
        // request/job must not survive into the next one.
        if (app()->bound('currentMembership')) {
            app()->forgetInstance('currentMembership');
        }

        OrderService::flushCaches();
        OrderCompleteness::flushCaches();
        DeliveryRiderService::flushCaches();
        StatusResolver::flush();
    }
}
