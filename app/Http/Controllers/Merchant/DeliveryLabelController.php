<?php

namespace App\Http\Controllers\Merchant;

use App\Domains\Shipping\Services\StopdeskOfficeSync;
use App\Enums\Store\StorePermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Orders\OrderTracking;
use App\Models\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Streams a carrier's printable label (PDF/ZPL) through the server so the
 * carrier bearer token never reaches the browser. The tracking index hands
 * this URL to a proxy-open in a new tab; the response is served inline so the
 * browser's print dialog can render it directly.
 */
class DeliveryLabelController extends Controller
{
    public function show(Store $store, Request $request, string $tracking): Response
    {
        abort_unless(canStore(StorePermissionEnum::ORDER_VIEW->value), 403);

        $row = OrderTracking::with('shippingProvider')
            ->where('store_id', $store->id)
            ->where('tracking_number', $tracking)
            ->first();

        abort_unless($row?->shippingProvider, 404);

        $adapter = (new StopdeskOfficeSync())->resolve($row->shippingProvider);

        abort_unless($adapter && method_exists($adapter, 'getLabel'), 404);

        $label = $adapter->getLabel($row->shippingProvider, $tracking);

        if (empty($label['ok']) || empty($label['url'])) {
            abort(404);
        }

        $token = (string) ($row->shippingProvider->credentials['api_token'] ?? '');

        $fetch = Http::timeout(60)
            ->withHeaders(['Authorization' => "Bearer {$token}"])
            ->get($label['url']);

        abort_if($fetch->failed(), 502);

        $contentType = $fetch->header('Content-Type') ?: 'application/octet-stream';

        return response($fetch->body(), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="label-' . $tracking . '"',
            'Cache-Control' => 'no-store',
        ]);
    }
}