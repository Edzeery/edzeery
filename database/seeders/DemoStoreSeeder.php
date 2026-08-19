<?php

namespace Database\Seeders;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Products\Product;
use App\Models\Products\ProductVariant;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@edzeery.com'],
            [
                'name'     => 'Demo Merchant',
                'password' => Hash::make('password'),
            ]
        );

        if (! $user->hasRole(UserRoleEnum::MERCHANT)) {
            $user->assignRole(UserRoleEnum::MERCHANT);
        }

        $store = Store::firstOrCreate(
            ['slug' => 'default-store'],
            [
                'user_id'          => $user->id,
                'name'             => 'Edzeery Demo Store',
                'description'      => 'Welcome to the Edzeery demo store. Browse our curated collection to see how your storefront could look.',
                'landing_template' => LandingTemplateEnum::CATALOG,
                'status'           => StoreStatusEnum::ACTIVE,
            ]
        );

        if (! $store->settings) {
            $store->initializeStoreDefaults();
        }

        $store->theme()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'primary_color'     => '#6366f1',
                'secondary_color'   => '#8b5cf6',
                'font_family'       => 'Cairo',
                'homepage_sections' => ['hero', 'categories', 'social_proof'],
            ]
        );

        $store->settings()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'currency'        => 'DZD',
                'currency_symbol' => 'DA',
                'language'        => 'ar',
                'timezone'        => 'Africa/Algiers',
            ]
        );

        $membership = StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $user->id],
            [
                'invited_by' => $user->id,
                'is_active'  => true,
            ]
        );

        if (! $user->merchant()->hasRole(StoreRoleEnum::OWNER)) {
            $user->merchant()->assignRole(StoreRoleEnum::OWNER);
        }

        $this->seedBrands($store);
        $this->seedCategories($store);
        $this->seedProducts($store);
    }

    private function seedBrands(Store $store): void
    {
        $brands = [
            ['name' => 'TechVibe',   'slug' => 'techvibe'],
            ['name' => 'UrbanEdge',  'slug' => 'urbanedge'],
            ['name' => 'PureNature', 'slug' => 'purenature'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['store_id' => $store->id, 'slug' => $brand['slug']],
                ['name' => $brand['name'], 'is_active' => true]
            );
        }
    }

    private function seedCategories(Store $store): void
    {
        $categories = [
            ['name' => 'Electronics',  'slug' => 'demo-electronics'],
            ['name' => 'Clothing',     'slug' => 'demo-clothing'],
            ['name' => 'Accessories',  'slug' => 'demo-accessories'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['store_id' => $store->id, 'slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }
    }

    private function seedProducts(Store $store): void
    {
        $brands = Brand::where('store_id', $store->id)->get()->keyBy('slug');
        $cats   = Category::where('store_id', $store->id)->get()->keyBy('slug');

        $products = [
            [
                'name'              => 'Wireless Earbuds Pro',
                'slug'              => 'demo-wireless-earbuds-pro',
                'sku'               => 'DEMO-EAR-001',
                'type'              => 'variable',
                'short_description' => 'Premium wireless earbuds with active noise cancellation and 30-hour battery life.',
                'description'       => "Experience crystal-clear audio with our flagship wireless earbuds. Featuring active noise cancellation, IPX5 water resistance, and up to 30 hours of total battery life.\n\nKey Features:\n• Active Noise Cancellation\n• IPX5 Water Resistant\n• 30-hour battery life\n• Bluetooth 5.3\n• Touch controls",
                'price'             => 4500.00,
                'cost_price'        => 2200.00,
                'is_active'         => true,
                'is_featured'       => true,
                'brand_slug'        => 'techvibe',
                'category_slugs'    => ['demo-electronics', 'demo-accessories'],
                'variants'          => [
                    ['name' => 'Black', 'sku' => 'DEMO-EAR-001-BK', 'price' => 4500.00, 'stock' => 50],
                    ['name' => 'White', 'sku' => 'DEMO-EAR-001-WH', 'price' => 4500.00, 'stock' => 35],
                    ['name' => 'Blue',  'sku' => 'DEMO-EAR-001-BL', 'price' => 4800.00, 'stock' => 20],
                ],
            ],
            [
                'name'              => 'Smart Watch Ultra',
                'slug'              => 'demo-smart-watch-ultra',
                'sku'               => 'DEMO-WATCH-001',
                'type'              => 'variable',
                'short_description' => 'Feature-packed smartwatch with health monitoring and GPS tracking.',
                'description'       => "Stay connected and track your fitness with our advanced smartwatch.\n\nKey Features:\n• Heart Rate & SpO2 Monitoring\n• Built-in GPS\n• AMOLED Display\n• 7-day battery life\n• 5 ATM water resistance",
                'price'             => 8900.00,
                'cost_price'        => 4500.00,
                'is_active'         => true,
                'is_featured'       => true,
                'brand_slug'        => 'techvibe',
                'category_slugs'    => ['demo-electronics'],
                'variants'          => [
                    ['name' => '42mm - Silver', 'sku' => 'DEMO-WATCH-001-SV', 'price' => 8900.00, 'stock' => 15],
                    ['name' => '46mm - Black',  'sku' => 'DEMO-WATCH-001-BK', 'price' => 9500.00, 'stock' => 20],
                ],
            ],
            [
                'name'              => 'Classic Cotton T-Shirt',
                'slug'              => 'demo-classic-cotton-tshirt',
                'sku'               => 'DEMO-TSHIRT-001',
                'type'              => 'variable',
                'short_description' => 'Premium 100% organic cotton t-shirt with a modern relaxed fit.',
                'description'       => "Our signature organic cotton t-shirt is designed for everyday comfort.\n\nKey Features:\n• 100% organic cotton\n• Relaxed modern fit\n• Pre-shrunk fabric\n• Reinforced stitching\n• Eco-friendly dyes",
                'price'             => 1800.00,
                'cost_price'        => 600.00,
                'is_active'         => true,
                'is_featured'       => false,
                'brand_slug'        => 'urbanedge',
                'category_slugs'    => ['demo-clothing'],
                'variants'          => [
                    ['name' => 'Small - Black',  'sku' => 'DEMO-TSHIRT-001-SB', 'price' => 1800.00, 'stock' => 40],
                    ['name' => 'Medium - Black', 'sku' => 'DEMO-TSHIRT-001-MB', 'price' => 1800.00, 'stock' => 60],
                    ['name' => 'Large - Black',  'sku' => 'DEMO-TSHIRT-001-LB', 'price' => 1800.00, 'stock' => 50],
                    ['name' => 'Medium - White', 'sku' => 'DEMO-TSHIRT-001-MW', 'price' => 1800.00, 'stock' => 45],
                    ['name' => 'Large - White',  'sku' => 'DEMO-TSHIRT-001-LW', 'price' => 1800.00, 'stock' => 30],
                ],
            ],
            [
                'name'              => 'Leather Crossbody Bag',
                'slug'              => 'demo-leather-crossbody-bag',
                'sku'               => 'DEMO-BAG-001',
                'type'              => 'simple',
                'short_description' => 'Handcrafted genuine leather crossbody bag with adjustable strap.',
                'description'       => "A timeless crossbody bag handcrafted from premium genuine leather.\n\nKey Features:\n• Genuine leather\n• Adjustable shoulder strap\n• Multiple compartments\n• Magnetic closure\n• Dimensions: 25cm × 18cm × 8cm",
                'price'             => 5500.00,
                'cost_price'        => 2500.00,
                'is_active'         => true,
                'is_featured'       => true,
                'brand_slug'        => 'urbanedge',
                'category_slugs'    => ['demo-accessories'],
                'variants'          => [
                    ['name' => 'Default', 'sku' => 'DEMO-BAG-001-DF', 'price' => 5500.00, 'stock' => 25],
                ],
            ],
            [
                'name'              => 'Bamboo Sunglasses',
                'slug'              => 'demo-bamboo-sunglasses',
                'sku'               => 'DEMO-SUN-001',
                'type'              => 'simple',
                'short_description' => 'Eco-friendly bamboo frame sunglasses with UV400 protection.',
                'description'       => "Stylish and sustainable sunglasses with frames made from natural bamboo.\n\nKey Features:\n• Natural bamboo frames\n• Polarized UV400 lenses\n• Lightweight (28g)\n• Comes with bamboo case\n• Eco-friendly packaging",
                'price'             => 3200.00,
                'cost_price'        => 1200.00,
                'is_active'         => true,
                'is_featured'       => false,
                'brand_slug'        => 'purenature',
                'category_slugs'    => ['demo-accessories'],
                'variants'          => [
                    ['name' => 'Default', 'sku' => 'DEMO-SUN-001-DF', 'price' => 3200.00, 'stock' => 30],
                ],
            ],
            [
                'name'              => 'Portable Bluetooth Speaker',
                'slug'              => 'demo-portable-bt-speaker',
                'sku'               => 'DEMO-SPK-001',
                'type'              => 'simple',
                'short_description' => 'Waterproof portable speaker with 360-degree surround sound.',
                'description'       => "Take your music anywhere with this rugged portable speaker.\n\nKey Features:\n• 360-degree surround sound\n• IPX7 waterproof\n• 20-hour battery\n• Bluetooth 5.0\n• Built-in microphone",
                'price'             => 6200.00,
                'cost_price'        => 3000.00,
                'is_active'         => true,
                'is_featured'       => true,
                'brand_slug'        => 'techvibe',
                'category_slugs'    => ['demo-electronics'],
                'variants'          => [
                    ['name' => 'Default', 'sku' => 'DEMO-SPK-001-DF', 'price' => 6200.00, 'stock' => 18],
                ],
            ],
            [
                'name'              => 'Linen Summer Shirt',
                'slug'              => 'demo-linen-summer-shirt',
                'sku'               => 'DEMO-SHIRT-001',
                'type'              => 'variable',
                'short_description' => 'Lightweight linen blend shirt perfect for warm weather.',
                'description'       => "Stay cool and stylish with our linen blend summer shirt.\n\nKey Features:\n• Linen-cotton blend\n• Breathable and lightweight\n• Relaxed fit\n• Mother-of-pearl buttons\n• Machine washable",
                'price'             => 2800.00,
                'cost_price'        => 900.00,
                'is_active'         => true,
                'is_featured'       => false,
                'brand_slug'        => 'urbanedge',
                'category_slugs'    => ['demo-clothing'],
                'variants'          => [
                    ['name' => 'Medium - Beige',  'sku' => 'DEMO-SHIRT-001-MB', 'price' => 2800.00, 'stock' => 25],
                    ['name' => 'Large - Beige',   'sku' => 'DEMO-SHIRT-001-LB', 'price' => 2800.00, 'stock' => 20],
                    ['name' => 'Medium - Green',  'sku' => 'DEMO-SHIRT-001-MG', 'price' => 2800.00, 'stock' => 15],
                ],
            ],
            [
                'name'              => 'Organic Green Tea Set',
                'slug'              => 'demo-organic-green-tea-set',
                'sku'               => 'DEMO-TEA-001',
                'type'              => 'simple',
                'short_description' => 'Premium organic green tea collection with bamboo infuser.',
                'description'       => "A curated set of 6 premium organic green teas.\n\nSet includes:\n• Sencha (25g)\n• Matcha (30g)\n• Jasmine Pearls (25g)\n• Gunpowder (25g)\n• Mint Green (25g)\n• Bamboo infuser",
                'price'             => 3800.00,
                'cost_price'        => 1500.00,
                'is_active'         => true,
                'is_featured'       => false,
                'brand_slug'        => 'purenature',
                'category_slugs'    => ['demo-accessories'],
                'variants'          => [
                    ['name' => 'Default', 'sku' => 'DEMO-TEA-001-DF', 'price' => 3800.00, 'stock' => 40],
                ],
            ],
        ];

        foreach ($products as $data) {
            $brand = $brands->get($data['brand_slug'] ?? null);

            $product = Product::withoutGlobalScopes()->firstOrCreate(
                ['store_id' => $store->id, 'slug' => $data['slug']],
                [
                    'name'              => $data['name'],
                    'sku'               => $data['sku'],
                    'type'              => $data['type'],
                    'short_description' => $data['short_description'],
                    'description'       => $data['description'],
                    'price'             => $data['price'],
                    'cost_price'        => $data['cost_price'],
                    'is_active'         => $data['is_active'],
                    'is_featured'       => $data['is_featured'],
                    'brand_id'          => $brand?->id,
                ]
            );

            if (isset($data['category_slugs'])) {
                $catIds = $cats->filter(fn ($c) => in_array($c->slug, $data['category_slugs']))
                    ->pluck('id')
                    ->toArray();

                foreach ($catIds as $catId) {
                    $product->categories()->syncWithoutDetaching([
                        $catId => ['store_id' => $store->id],
                    ]);
                }
            }

            foreach ($data['variants'] as $i => $vData) {
                ProductVariant::firstOrCreate(
                    ['store_id' => $store->id, 'sku' => $vData['sku']],
                    [
                        'product_id' => $product->id,
                        'name'       => $vData['name'],
                        'price'      => $vData['price'],
                        'stock'      => $vData['stock'],
                        'is_active'  => true,
                        'is_default' => $i === 0,
                    ]
                );
            }

            if (! $product->images()->exists()) {
                $product->images()->create([
                    'path'       => "demo/{$product->slug}.jpg",
                    'store_id'   => $store->id,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }
        }
    }
}
