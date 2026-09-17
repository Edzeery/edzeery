<?php

namespace App\Domains\Shipping\Models;

use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only per-run metrics for the carrier tracking polling flow (pilot
 * observability). One row per syncProvider() run; consumed by the
 * carrier-sync:report command. Never a source of truth for domain state.
 */
class CarrierSyncRun extends Model
{
    use HasUlids;

    protected $table = 'carrier_sync_runs';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'attempted' => 'integer',
        'updated' => 'integer',
        'unknown' => 'integer',
        'failed' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shippingProvider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class);
    }
}