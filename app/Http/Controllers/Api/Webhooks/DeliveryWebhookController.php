<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Services\NoestTrackingSyncService;
use App\Http\Controllers\Controller;
use App\Models\Orders\OrderTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Delivery tracking webhook. The carrier (or our future middleware) POSTs an
 * event against /webhooks/delivery/{provider}; {provider} is the carrier code
 * (shipping_providers.code, e.g. "noest") so every company gets one stable URL
 * per store domain. The store's provider is picked by its per-store secret:
 * sent as the X-Delivery-Token header with a ?token= query fallback. The
 * legacy form (the URL segment itself was the secret) is kept working
 * transiently. The payload is normalised into the same entry shape
 * NoestTrackingSyncService::apply consumes, so a pushed event and a scheduled
 * poll follow identical idempotent update rules.
 */
class DeliveryWebhookController extends Controller
{
    public function __invoke(Request $request, string $providerKey): Response
    {
        $provider = $this->resolveProvider($request, $providerKey);

        if (! $provider) {
            return response('Unknown webhook.', Response::HTTP_NOT_FOUND);
        }

        $provider->update(['webhook_last_seen_at' => now()]);

        $payload = $request->json();

        if (! $payload) {
            return response('Malformed payload.', Response::HTTP_BAD_REQUEST);
        }

        $data = $payload->all();
        $trackingNumber = $this->extractTrackingNumber($data);

        if (! $trackingNumber) {
            return response('Missing tracking number.', Response::HTTP_BAD_REQUEST);
        }

        $tracking = OrderTracking::query()
            ->where('store_id', $provider->store_id)
            ->where('tracking_number', $trackingNumber)
            ->first();

        // Ack so the carrier stops retrying; the row may simply not exist yet
        // (e.g. the event raced the shipment creation).
        if (! $tracking) {
            return response('OK', Response::HTTP_OK);
        }

        $entry = $this->normalizeEntry($data);

        if (empty($entry['activity']) && empty($entry['OrderInfo'])) {
            return response('No event data.', Response::HTTP_BAD_REQUEST);
        }

        try {
            app(NoestTrackingSyncService::class)->apply($tracking, $entry);
        } catch (\Throwable $e) {
            Log::warning("delivery webhook apply failed for [{$trackingNumber}] ({$provider->store_id}): {$e->getMessage()}");

            return response('Internal error.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('OK', Response::HTTP_OK);
    }

    /**
     * Canonical form: /webhooks/delivery/{code} + X-Delivery-Token header (or
     * ?token=) pins the provider by its per-store secret. Legacy form: no
     * header/query token means the URL segment itself is the secret.
     */
    private function resolveProvider(Request $request, string $providerKey): ?ShippingProvider
    {
        $token = trim((string) $request->header('X-Delivery-Token', ''));

        if ($token === '') {
            $token = trim((string) $request->query('token', ''));
        }

        $query = ShippingProvider::query()->where('is_active', true);

        if ($token !== '') {
            return $query
                ->where('code', $providerKey)
                ->where('webhook_token', $token)
                ->first();
        }

        return $query
            ->where('webhook_token', $providerKey)
            ->first();
    }

    /**
     * Accept both the normalised shape (OrderInfo/activity) and flat aliases so
     * carriers can POST the smallest sensible body.
     */
    private function normalizeEntry(array $data): array
    {
        $entry = [];

        if (isset($data['OrderInfo']) && is_array($data['OrderInfo'])) {
            $entry['OrderInfo'] = $data['OrderInfo'];
        } elseif (isset($data['order_info']) && is_array($data['order_info'])) {
            $entry['OrderInfo'] = $data['order_info'];
        }

        if (isset($data['activity']) && is_array($data['activity'])) {
            $entry['activity'] = array_values($data['activity']);
        } elseif (isset($data['events']) && is_array($data['events'])) {
            $entry['activity'] = array_values($data['events']);
        } elseif (isset($data['status']) || isset($data['event']) || isset($data['event_key'])) {
            $entry['activity'] = [$this->singleEvent($data)];
        }

        return $entry;
    }

    private function singleEvent(array $data): array
    {
        return [
            'date' => (string) ($data['date'] ?? $data['created_at'] ?? $data['updated_at'] ?? now()->toDateTimeString()),
            'event' => (string) ($data['event'] ?? $data['label'] ?? ''),
            'event_key' => (string) ($data['event_key'] ?? $data['status'] ?? ''),
        ];
    }

    private function extractTrackingNumber(array $data): ?string
    {
        foreach (['tracking_number', 'tracking', 'code'] as $key) {
            $value = $data[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        if (isset($data['data']['tracking_number']) && is_string($data['data']['tracking_number'])) {
            return $data['data']['tracking_number'];
        }

        return null;
    }
}