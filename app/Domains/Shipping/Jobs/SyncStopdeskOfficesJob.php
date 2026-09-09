<?php

namespace App\Domains\Shipping\Jobs;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\StopdeskOfficeSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStopdeskOfficesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $storeId = null,
        public ?string $providerId = null,
        public bool $refresh = true,
    ) {
    }

    /**
     * Pulls stopdesk offices for carrier-backed shipping providers of a store
     * (or for one specific provider when dispatched right after saving it).
     * Exceptions per provider are reported and isolated so one carrier failure
     * never blocks the rest.
     */
    public function handle(?StopdeskOfficeSync $sync = null): void
    {
        $sync ??= app(StopdeskOfficeSync::class);

        $query = ShippingProvider::query()
            ->where('is_active', true);

        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->providerId) {
            $query->whereKey($this->providerId);
        }

        $query->chunkById(20, function ($providers) use ($sync): void {
            foreach ($providers as $provider) {
                try {
                    $sync->sync($provider, null, null, $this->refresh);
                } catch (\Throwable $e) {
                    Log::warning("stopdesk office sync failed for provider [{$provider->id}]: " . $e->getMessage());
                    report($e);
                }
            }
        });
    }
}