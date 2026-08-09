<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Models\Products\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductsController extends Controller
{
    /**
     * List the current store's products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['primaryImage', 'primaryCategory', 'brand'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search');

                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('is_active'), fn ($query) => $query->where('is_active', $request->boolean('is_active')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_dir', 'desc'))
            ->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Show a single product.
     */
    public function show(Product $product): ProductResource
    {
        $product->load(['primaryImage', 'primaryCategory', 'brand', 'variants']);

        return new ProductResource($product);
    }

    /**
     * Create a new product inside the current store.
     */
    public function store(StoreProductRequest $request): ProductResource
    {
        $storeId = currentStoreId();

        abort_unless($storeId, 422, 'A store context is required to create products.');

        $product = Product::create([
            ...$request->validated(),
            'store_id' => $storeId,
        ]);

        return new ProductResource($product->fresh());
    }

    /**
     * Update an existing product.
     */
    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->fresh());
    }

    /**
     * Soft-delete a product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
