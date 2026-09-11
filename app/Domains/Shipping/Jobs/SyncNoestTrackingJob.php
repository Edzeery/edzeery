<?php

namespace App\Domains\Shipping\Jobs;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
use App\Domains\Shipping\Services\StopdeskOfficeSync;
use App\Models\Orders\OrderTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Polls NOEST's /get/trackings/info for every open (or not-yet-synced) tracking
 * record of a store's NOEST-backed providers and advances the local
 * order_trackings statuses. Idempotent: terminal records are only refreshed,
 * never regressed, and a History row is appended per status transition.
 */
class SyncNoestTrackingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(public string $storeId) {}

    public function handle(?StopdeskOfficeSync $resolver = null): void
    {
        $resolver ??= app(StopdeskOfficeSync::class);
        $sync = app(NoestTrackingSyncService::class);

        $providers = ShippingProvider::query()
            ->with('carrier')
            ->where('store_id', $this->storeId)
            ->where('is_active', true)
            // Providers with a webhook token push statuses on their own; polling
            // is only for carriers that have NOT enabled a delivery webhook.
            ->whereNull('webhook_token')
            ->whereHas('carrier', fn ($query) => $query->where('code', 'noest'))
            ->get();

        foreach ($providers as $provider) {
            $this->syncProvider($resolver, $sync, $provider);
        }
    }

    private function syncProvider(StopdeskOfficeSync $resolver, NoestTrackingSyncService $sync, ShippingProvider $provider): void
    {
        $adapter = $resolver->resolve($provider);

        if (! $adapter || ! method_exists($adapter, 'trackingsInfo')) {
            return;
        }

        $base = $this->dueTrackings($provider);
        if ($base->isEmpty()) {
            return;
        }

        foreach ($base->chunk(20) as $chunk) {
            try {
                $data = $adapter->trackingsInfo($provider, $chunk->pluck('tracking_number')->all());
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            if (! is_array($data) || $data === []) {
                $this->touchSyncedAt($chunk);
                continue;
            }

            $byNumber = $chunk->keyBy('tracking_number');

            foreach ($data as $trackingNumber => $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $tracking = $byNumber->get((string) $trackingNumber);
                if (! $tracking) {
                    continue;
                }

                $sync->apply($tracking, $entry);
            }

            $this->touchSyncedAt($byNumber);
        }
    }

    /**
     * Rows that should be refreshed: not yet in a terminal state, or never
     * synced, or synced more than RESYNC_MINUTES ago.
     */
    private function dueTrackings(ShippingProvider $provider)
    {
        return OrderTracking::query()
            ->where('shipping_provider_id', $provider->id)
            ->whereNotNull('tracking_number')
            ->where('tracking_number', '!=', '')
            ->where(function ($query) {
                $query->whereNull('delivered_at')
                    ->whereNull('returned_at')
                    ->orWhereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', now()->subMinutes(15));
            })
            ->get();
    }

    private function touchSyncedAt($trackings): void
    {
        if (! $trackings instanceof \Illuminate\Support\Collection || $trackings->isEmpty()) {
            return;
        }

        OrderTracking::query()
            ->whereIn('id', $trackings->pluck('id')->all())
            ->update(['last_synced_at' => now()]);

        $trackings->each(fn ($tracking) => $tracking->last_synced_at = now());
    }
}