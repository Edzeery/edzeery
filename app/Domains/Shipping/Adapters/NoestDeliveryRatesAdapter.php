<?php

namespace App\Domains\Shipping\Adapters;

use App\Domains\Shipping\Contracts\ShippingProviderAdapterContract;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\State;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Real NOEST delivery prices.
 *
 * GET /fees (docs/توثيق_واجهة_برمجة_تطبيقات_NOEST_v2.3.md §13):
 *   { "tarifs": { "return": { <wilaya>: { tarif, tarif_stopdesk } },
 *                 "delivery": { <wilaya>: { tarif, tarif_stopdesk } } } }
 * - tarif            → home_cost
 * - tarif_stopdesk   → office_cost
 *
 * The fees payload is cached per provider (24h, same TTL/pattern as the desks
 * cache) under carrier:noest:fees:{store_id}:{provider_id}.
 *
 * A missing wilaya, missing credentials, or any HTTP/parse failure yields an
 * all-null quote so DeliveryRatesManager's sync loop keeps existing manual
 * values instead of aborting on a single bad state.
 */
class NoestDeliveryRatesAdapter implements ShippingProviderAdapterContract
{
    public const DEFAULT_BASE = 'https://app.noest-dz.com/api/public';

    private const FEES_CACHE_TTL_SECONDS = 60 * 60 * 24;

    public static function carrierCode(): string
    {
        return 'noest';
    }

    public function quote(ShippingProvider $provider, State $state, array $context = []): array
    {
        try {
            $fees = $this->fees($provider);
        } catch (\Throwable) {
            return [
                'office_cost' => null,
                'home_cost' => null,
                'free_above' => null,
                'fetched_at' => null,
            ];
        }

        $delivery = $fees['tarifs']['delivery'] ?? null;

        if (! is_array($delivery)) {
            return $this->emptyQuote();
        }

        $row = $this->findRow($delivery, (string) (int) $state->state_code);

        if (! is_array($row)) {
            return $this->emptyQuote();
        }

        $cost = static fn ($value) => ($value === null || $value === '') ? null : (float) $value;

        return [
            'office_cost' => $cost($row['tarif_stopdesk'] ?? null),
            'home_cost' => $cost($row['tarif'] ?? null),
            'free_above' => null,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /* ───────────────────────── Internal ───────────────────────── */

    protected function fees(ShippingProvider $provider): array
    {
        $token = (string) ($provider->credentials['api_token'] ?? '');

        if ($token === '') {
            throw new \RuntimeException('NOEST API credentials are missing (api_token).');
        }

        $url = rtrim($this->baseUrl($provider), '/').'/fees';

        return Cache::remember($this->feesCacheKey($provider), self::FEES_CACHE_TTL_SECONDS, function () use ($url, $token): array {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$token}"])
                ->get($url);

            if ($response->failed()) {
                throw new \RuntimeException("NOEST fees request failed (HTTP {$response->status()})");
            }

            $data = $response->json();

            if (! is_array($data) || ! isset($data['tarifs']) || ! is_array($data['tarifs'])) {
                return [];
            }

            return $data;
        });
    }

    protected function baseUrl(ShippingProvider $provider): string
    {
        $base = (string) ($provider->credentials['api_base'] ?? '');

        return $base !== '' ? rtrim($base, '/') : self::DEFAULT_BASE;
    }

    protected function feesCacheKey(ShippingProvider $provider): string
    {
        return "carrier:noest:fees:{$provider->store_id}:{$provider->id}";
    }

    /**
     * NOEST keys a wilaya by its numeric id (with or without a leading zero).
     */
    protected function findRow(array $delivery, string $wilayaId): ?array
    {
        if (array_key_exists($wilayaId, $delivery)) {
            return $delivery[$wilayaId];
        }

        $padded = str_pad($wilayaId, 2, '0', STR_PAD_LEFT);

        if (array_key_exists($padded, $delivery)) {
            return $delivery[$padded];
        }

        return null;
    }

    protected function emptyQuote(): array
    {
        return [
            'office_cost' => null,
            'home_cost' => null,
            'free_above' => null,
            'fetched_at' => null,
        ];
    }
}
