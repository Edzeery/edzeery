<?php

namespace App\Domains\Shipping\Models;

use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopdeskPoint extends Model
{
    use HasUlids;

    protected $fillable = [
        'store_id',
        'shipping_provider_id',
        'state_id',
        'city_id',
        'name',
        'address',
        'phone',
        'external_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class, 'shipping_provider_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /* =========================
     | Coverage helpers
     ========================= */

    /**
     * Communes (city_ids) actually served by the carrier's active points in a
     * given wilaya. Single shared source for the order form cascade and the
     * inline city editor, so both always scope the selectable communes to the
     * chosen shipping company.
     */
    public static function communitiesCoveredFor(string $storeId, string $providerId, string $stateId): array
    {
        return static::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->whereNotNull('city_id')
            ->distinct()
            ->pluck('city_id')
            ->all();
    }

    /**
     * Scoped office option list for a delivery destination (provider + wilaya +
     * optional commune). The commune's offices rank first, then the wilaya-wide
     * hubs (city_id NULL) that remain offered under every commune, with empty
     * external_codes sorted last. Offices of another commune of that wilaya are
     * excluded. Shared by the order form cascade (rebuildFormOffices), the
     * lazily loaded modal select (loadFormOfficesLazy) and the inline editor.
     *
     * @return \Illuminate\Support\Collection<int, array{value: string, label: string, code: ?string, hint: ?string}>
     */
    public static function scopedOfficeOptions(string $storeId, string $providerId, string $stateId, ?string $cityId): \Illuminate\Support\Collection
    {
        $query = static::query()
            ->where('store_id', $storeId)
            ->where('shipping_provider_id', $providerId)
            ->where('state_id', $stateId)
            ->where('is_active', true)
            ->with('city:id,name');

        if (! empty($cityId)) {
            $query->where(fn ($q) => $q->where('city_id', $cityId)->orWhereNull('city_id'));
            $query->orderByRaw('(city_id = ?) DESC, (city_id IS NULL) ASC, (external_code = \'\') ASC, external_code, name', [(int) $cityId]);
        } else {
            $query->orderByRaw('(external_code = \'\') ASC, external_code, name');
        }

        return $query->get()->map(function ($office) {
            $hint = trim(($office->city?->name ?? '') . ($office->address ? ' — ' . $office->address : ''), ' —');

            return [
                'value' => (string) $office->id,
                'label' => $office->name,
                'code' => $office->external_code !== null && $office->external_code !== ''
                    ? (string) $office->external_code
                    : null,
                'hint' => $hint !== '' ? $hint : null,
            ];
        });
    }
}
