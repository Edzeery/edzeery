<?php

namespace App\Domains\Shipping\Adapters;

use App\Domains\Shipping\Contracts\CarrierIntegrationContract;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * NOEST (app.noest-dz.com) integration.
 *
 * API contract documented in docs/NOEST Order Popup v10.2.js:
 *   base            https://app.noest-dz.com/api/public
 *   auth            Authorization: Bearer {api_token}
 *   GET /desks      all stations ("desk.code" is the wilaya numeric code)
 *   POST /create/order  payload below, returns {success, tracking}
 *
 * Credentials live in shipping_providers.credentials:
 *   api_token, guid, api_base.
 */
class NoestIntegrationAdapter implements CarrierIntegrationContract
{
    public const DEFAULT_BASE = 'https://app.noest-dz.com/api/public';

    private const DESKS_CACHE_TTL_SECONDS = 60 * 60 * 24;

    public function carrierCode(): string
    {
        return 'noest';
    }

    public function offices(ShippingProvider $provider, ?State $state = null, ?City $city = null): array
    {
        $desks = $this->desks($provider);

        $offices = [];

        foreach ($desks as $desk) {
            $code = (string) ($desk['code'] ?? '');
            if ($code === '' || ! is_array($desk)) {
                continue;
            }

            // Desks are keyed by the numeric wilaya code in NOEST's model.
            if ($state && (string) (int) $code !== ltrim((string) $state->state_code, '0')) {
                continue;
            }

            $offices[] = [
                'external_code' => trim($code),
                'name' => (string) ($desk['name'] ?? ''),
                'city' => ($desk['commune'] ?? null) ? (string) $desk['commune'] : null,
                'address' => ($desk['address'] ?? null) ? (string) $desk['address'] : null,
                'phone' => $this->extractPhones($desk['phones'] ?? null),
            ];
        }

        // Prefer offices matching the selected commune when possible.
        if ($city && $offices !== []) {
            $needle = mb_strtolower(trim($city->name));
            $arabicNeedle = $city->arabic_name ? mb_strtolower(trim($city->arabic_name)) : null;

            usort($offices, function (array $a, array $b) use ($needle, $arabicNeedle): int {
                return (int) $this->communeScore($b['city'], $needle, $arabicNeedle)
                    <=> (int) $this->communeScore($a['city'], $needle, $arabicNeedle);
            });
        }

        return $offices;
    }

    public function createOrder(ShippingProvider $provider, Order $order): array
    {
        $token = (string) ($provider->credentials['api_token'] ?? '');
        $guid = (string) ($provider->credentials['guid'] ?? '');

        if ($token === '' || $guid === '') {
            throw new \RuntimeException('NOEST API credentials are missing (api_token / guid).');
        }

        $stateCode = $order->state?->state_code ? (string) (int) $order->state->state_code : null;
        $commune = $order->city?->name;

        $payload = [
            'user_guid' => $guid,
            'reference' => (string) $order->number,
            'client' => $order->customer?->name ?? '',
            'phone' => $order->customer?->phone ?? '',
            'adresse' => $order->address ?? ($order->stopdeskPoint?->name ?? ''),
            'wilaya_id' => $stateCode !== null ? (int) $stateCode : 0,
            'commune' => $commune ?? '',
            'montant' => round((float) $order->total_amount, 2),
            'produit' => $this->productSummary($order),
            'type_id' => 1,
            'poids' => (float) ($order->weight_kg ?? 0.5),
            'stop_desk' => $order->delivery_type === Order::DELIVERY_STOPDESK ? 1 : 0,
            'station_code' => $order->delivery_type === Order::DELIVERY_STOPDESK
                ? (string) ($order->stopdeskPoint?->external_code ?? '')
                : '',
            'can_open' => 1,
            'remboursement' => 0,
        ];

        if (! empty($order->phone_secondary)) {
            $payload['phone_2'] = (string) $order->phone_secondary;
        }
        if (! empty($order->notes)) {
            $payload['remarque'] = (string) $order->notes;
        }

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => "Bearer {$token}"])
            ->post(rtrim($this->baseUrl($provider), '/').'/create/order', $payload);

        $data = $response->json() ?? [];

        if ($response->failed() || empty($data['success']) || empty($data['tracking'])) {
            $message = (string) ($data['message'] ?? $data['error'] ?? "HTTP {$response->status()}");
            throw new \RuntimeException("NOEST rejected order {$order->number}: {$message}");
        }

        return [
            'tracking' => (string) $data['tracking'],
            'label_url' => $this->labelUrl($provider, (string) $data['tracking']),
            'raw' => $data,
        ];
    }

    public function forgetCache(ShippingProvider $provider): void
    {
        Cache::forget($this->desksCacheKey($provider));
    }

    /* ───────────────────────── Internal ───────────────────────── */

    protected function baseUrl(ShippingProvider $provider): string
    {
        $base = (string) ($provider->credentials['api_base'] ?? '');
        return $base !== '' ? rtrim($base, '/') : self::DEFAULT_BASE;
    }

    protected function labelUrl(ShippingProvider $provider, string $tracking): string
    {
        return $this->baseUrl($provider).'/get/order/label?tracking='.rawurlencode($tracking);
    }

    protected function desksCacheKey(ShippingProvider $provider): string
    {
        return "carrier:noest:desks:{$provider->store_id}:{$provider->id}";
    }

    /**
     * All desks for the provider, cached for a day (Cache::remember), and
     * returned normalized as a plain array of arrays.
     */
    protected function desks(ShippingProvider $provider): array
    {
        $token = (string) ($provider->credentials['api_token'] ?? '');
        if ($token === '') {
            return [];
        }

        $url = rtrim($this->baseUrl($provider), '/').'/desks';

        return Cache::remember($this->desksCacheKey($provider), self::DESKS_CACHE_TTL_SECONDS, function () use ($url, $token): array {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => "Bearer {$token}"])
                ->get($url);

            if ($response->failed()) {
                throw new \RuntimeException("NOEST desks request failed (HTTP {$response->status()})");
            }

            $data = $response->json();

            if (! is_array($data)) {
                return [];
            }

            // NOEST returns desks either keyed by code or as a plain list.
            return array_values(array_filter($data, 'is_array'));
        });
    }

    protected function extractPhones(mixed $phones): ?string
    {
        if (is_string($phones) && trim($phones) !== '') {
            return trim($phones);
        }
        if (! is_array($phones)) {
            return null;
        }

        $parts = [];
        array_walk_recursive($phones, static function ($value) use (&$parts): void {
            $value = trim((string) $value);
            if ($value !== '') {
                $parts[] = $value;
            }
        });

        return $parts === [] ? null : implode(' / ', array_unique($parts));
    }

    protected function productSummary(Order $order): string
    {
        $lines = $order->items->map(fn ($item) => trim((string) $item->product?->name).($item->quantity > 1 ? " x{$item->quantity}" : ''));

        $summary = $lines->filter()->implode(' + ');

        if ($summary === '' && $order->items->isNotEmpty()) {
            $summary = $order->items->count().' items';
        }

        return mb_substr($summary, 0, 120) ?: '—';
    }

    protected static function communeScore(?string $commune, string $needle, ?string $arabicNeedle): bool
    {
        if ($commune === null || $commune === '') {
            return false;
        }

        $commune = mb_strtolower(trim($commune));

        return $commune === $needle
            || ($arabicNeedle !== null && $commune === $arabicNeedle)
            || (str_contains($needle, $commune) || str_contains($commune, $needle));
    }
}