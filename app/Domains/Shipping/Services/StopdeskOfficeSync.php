<?php

namespace App\Domains\Shipping\Services;

use App\Domains\Shipping\Contracts\CarrierIntegrationContract;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Models\Locations\City;
use App\Models\Locations\State;

/**
 * Reconciles a carrier's remote offices into the local stopdesk_points
 * table so the office pickers stay fast and reuse the existing plumbing.
 */
class StopdeskOfficeSync
{
    /** @var array<string, State|null> memoized desk-code → wilaya lookups */
    private array $stateByCodeCache = [];

    /** @var array<string, string|null> memoized (stateId, commune) → cityId */
    private array $cityIdCache = [];

    public function resolve(ShippingProvider $provider): ?CarrierIntegrationContract
    {
        $code = $provider->carrier?->code;

        if (! $code) {
            return null;
        }

        $adapterClass = config(
            "delivery.carrier_integrations.{$code}",
            config('delivery.carrier_integrations.*'),
        );

        if (! $adapterClass || ! class_exists($adapterClass)) {
            return null;
        }

        return app($adapterClass);
    }

    /**
     * Reconciliation that reads the local stopdesk_points table first and only
     * touches the carrier API when our own rows are missing for the requested
     * scope. The office pickers thus stay DB-fast after the warm-up sync; the
     * explicit refresh button and the scheduled job (both refresh=true) are the
     * only paths that force a fresh pull.
     *
     * @return array{synced: bool, created: int, existing: int, total: int}
     */
    public function syncIfNeeded(ShippingProvider $provider, ?State $state = null, ?City $city = null): array
    {
        $query = StopdeskPoint::query()
            ->where('store_id', $provider->store_id)
            ->where('shipping_provider_id', $provider->id)
            ->where('is_active', true);

        if ($state) {
            $query->where('state_id', $state->id);
        }
        if ($city) {
            $query->where('city_id', $city->id);
        }

        if ($query->exists()) {
            return ['synced' => false, 'created' => 0, 'existing' => 0, 'total' => 0];
        }

        return $this->sync($provider, $state, $city);
    }

    /**
     * @return array{synced: bool, created: int, existing: int, total: int}
     */
    public function sync(ShippingProvider $provider, ?State $state = null, ?City $city = null, bool $refresh = false): array
    {
        $adapter = $this->resolve($provider);

        if (! $adapter) {
            return ['synced' => false, 'created' => 0, 'existing' => 0, 'total' => 0];
        }

        if ($refresh) {
            $adapter->forgetCache($provider);
        }

        // Single-use memo caches: repeated desk-code → wilaya and commune → city
        // resolutions inside one sync no longer re-query per office (the full
        // NOEST desk list shares wilaya codes like "34" / "34B").
        $this->stateByCodeCache = [];
        $this->cityIdCache = [];

        $offices = $adapter->offices($provider, $state, $city);

        $created = 0;
        $existing = 0;

        foreach ($offices as $office) {
            $externalCode = trim((string) ($office['external_code'] ?? ''));
            if ($externalCode === '' || trim((string) ($office['name'] ?? '')) === '') {
                continue;
            }

            // When a full-carrier sync runs without a state filter we still know
            // the wilaya: NOEST desk codes are the numeric wilaya code, and the
            // DB stores it zero-padded (char(2)). Assigning the state here keeps
            // every synced office reachable from the per-state management UI.
            $officeState = $state ?? $this->stateByDeskCode($externalCode);

            $point = StopdeskPoint::query()
                ->where('store_id', $provider->store_id)
                ->where('shipping_provider_id', $provider->id)
                ->where('external_code', $externalCode)
                ->first();

            $attributes = [
                'store_id' => $provider->store_id,
                'shipping_provider_id' => $provider->id,
                'external_code' => $externalCode,
                'name' => (string) $office['name'],
                'address' => isset($office['address']) ? (string) $office['address'] : null,
                'phone' => isset($office['phone']) ? (string) $office['phone'] : null,
                'is_active' => true,
            ];

            if ($officeState) {
                $attributes['state_id'] = $officeState->id;
            }

            $cityId = $office['city'] ?? null ? $this->resolveCityId((string) $office['city'], $officeState) : null;
            if ($cityId) {
                $attributes['city_id'] = $cityId;
            }

            if ($point) {
                $point->update($attributes);
                $existing++;
            } else {
                StopdeskPoint::create($attributes);
                $created++;
            }
        }

        return [
            'synced' => true,
            'created' => $created,
            'existing' => $existing,
            'total' => count($offices),
        ];
    }

    private function resolveCityId(string $commune, ?State $state): ?string
    {
        if (! $state) {
            return null;
        }

        $key = $state->id . '::' . mb_strtolower(trim($commune));
        if (array_key_exists($key, $this->cityIdCache)) {
            return $this->cityIdCache[$key];
        }

        $needle = mb_strtolower(trim($commune));

        $this->cityIdCache[$key] = City::query()
            ->where('state_id', $state->id)
            ->where(function ($query) use ($needle) {
                $query->whereRaw('LOWER(name) = ?', [$needle])
                    ->orWhereRaw("LOWER(COALESCE(arabic_name, '')) = ?", [$needle]);
            })
            ->value('id');

        return $this->cityIdCache[$key];
    }

    /**
     * Resolve the wilaya behind an office desk code (NOEST desk codes carry the
     * numeric wilaya code, e.g. "16", "31", or "34B" — the letter is a second
     * desk within the same wilaya). state_code is stored zero-padded char(2),
     * so the numeric part of the desk code is padded back to match it directly.
     */
    private function stateByDeskCode(string $deskCode): ?State
    {
        if (array_key_exists($deskCode, $this->stateByCodeCache)) {
            return $this->stateByCodeCache[$deskCode];
        }

        $numeric = (int) $deskCode;

        if ($numeric <= 0) {
            return $this->stateByCodeCache[$deskCode] = null;
        }

        return $this->stateByCodeCache[$deskCode] = State::query()
            ->where('state_code', str_pad((string) $numeric, 2, '0', STR_PAD_LEFT))
            ->first();
    }
}