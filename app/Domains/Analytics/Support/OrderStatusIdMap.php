<?php

namespace App\Domains\Analytics\Support;

use App\Enums\Store\OrderStatus;
use Illuminate\Support\Facades\DB;

/**
 * Per-request cache of the order status ids, keyed by status key.
 *
 * The dashboard renders five blocks that each need to resolve status keys to
 * ids, so this saves one query per block without caching anything across
 * requests: the container hands out a fresh instance per service, and a
 * long-lived worker therefore cannot serve ids from an earlier request.
 */
class OrderStatusIdMap
{
    /** @var array<string, string|null>|null key => id for type='order' statuses */
    private ?array $map = null;

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        if ($this->map === null) {
            $this->map = DB::table('statuses')
                ->where('type', 'order')
                ->pluck('id', 'key')
                ->all();
        }

        return $this->map;
    }

    public function id(OrderStatus $status): ?string
    {
        return $this->all()[$status->value] ?? null;
    }

    /**
     * Ids for the given statuses, skipping the ones this store has not defined.
     *
     * @param  array<int, OrderStatus>  $statuses
     * @return array<int, string>
     */
    public function ids(array $statuses): array
    {
        $map = $this->all();
        $ids = [];

        foreach ($statuses as $status) {
            $id = $map[$status->value] ?? null;

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @return callable(OrderStatus): ?string
     */
    public function resolver(): callable
    {
        return fn (OrderStatus $status) => $this->id($status);
    }
}
