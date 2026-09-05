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

        $offices = $adapter->offices($provider, $state, $city);

        $created = 0;
        $existing = 0;

        foreach ($offices as $office) {
            $externalCode = trim((string) ($office['external_code'] ?? ''));
            if ($externalCode === '' || trim((string) ($office['name'] ?? '')) === '') {
                continue;
            }

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

            if ($state) {
                $attributes['state_id'] = $state->id;
            }

            $cityId = $office['city'] ?? null ? $this->resolveCityId((string) $office['city'], $state) : null;
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

        $needle = mb_strtolower(trim($commune));

        return City::query()
            ->where('state_id', $state->id)
            ->where(function ($query) use ($needle) {
                $query->whereRaw('LOWER(name) = ?', [$needle])
                    ->orWhereRaw("LOWER(COALESCE(arabic_name, '')) = ?", [$needle]);
            })
            ->value('id');
    }
}