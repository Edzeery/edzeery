<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart
    ) {}

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|string',
            'quantity'   => 'required|integer|min:1',
        ]);

        $storeId = currentStoreId();
        $cart = $this->cart->addItem($storeId, $request->variant_id, $request->integer('quantity', 1));

        return response()->json([
            'success' => true,
            'count'   => $this->cart->getCount($storeId),
            'subtotal' => $this->cart->getSubtotal($storeId),
            'cart'    => $this->cart->toArray($storeId),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|string',
            'quantity'   => 'required|integer|min:0',
        ]);

        $storeId = currentStoreId();
        $this->cart->updateQuantity($storeId, $request->variant_id, $request->integer('quantity'));

        return response()->json([
            'success' => true,
            'count'   => $this->cart->getCount($storeId),
            'subtotal' => $this->cart->getSubtotal($storeId),
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|string',
        ]);

        $storeId = currentStoreId();
        $this->cart->removeItem($storeId, $request->variant_id);

        return response()->json([
            'success' => true,
            'count'   => $this->cart->getCount($storeId),
            'subtotal' => $this->cart->getSubtotal($storeId),
        ]);
    }

    public function count(): JsonResponse
    {
        $storeId = currentStoreId();

        return response()->json([
            'count' => $this->cart->getCount($storeId),
        ]);
    }
}
