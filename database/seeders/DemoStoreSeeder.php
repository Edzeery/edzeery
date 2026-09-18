<?php

namespace Database\Seeders;

use App\Domains\Order\Models\ConfirmationProductAssignment;
use App\Domains\Order\Models\ConfirmationShift;
use App\Domains\Shipping\Models\Carrier;
use App\Domains\Shipping\Models\DeliveryRider;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\LandingTemplateEnum;
use App\Enums\Store\OrderTrackingStatus;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Enums\Store\StoreStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderEvent;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Orders\OrderTracking;
use App\Models\Orders\OrderTrackingHistory;
use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Products\ProductOptionValue;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoStoreSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@edzeery.com'],
            [
                'name'              => 'Demo Merchant',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // The demo account may already exist (e.g. created in an earlier
        // session without email_verified_at) — force-verify so the merchant
        // 'verified' middleware never bounces the tester to an inbox.
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        if (! $user->hasRole(UserRoleEnum::MERCHANT)) {
            $user->assignRole(UserRoleEnum::MERCHANT);
        }

        $store = Store::firstOrCreate(
            ['slug' => 'demo'],
            [
                'user_id'          => $user->id,
                'name'             => 'Edzeery Demo Store',
                'description'      => 'Welcome to the Edzeery demo store. Browse our curated collection to see how your storefront could look.',
                'landing_template' => LandingTemplateEnum::CATALOG,
                'status'           => StoreStatusEnum::ACTIVE,
            ]
        );

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
                'role'       => StoreRoleEnum::OWNER->value,
            ]
        );

        // Decision #6 — make sure the owner's membership carries its scoped
        // permissions so per-store isolation works from the start (mirrors
        // StoreRolesAndPermissionsSeeder::ensureMembership).
        if (! $user->merchant()->hasRole(StoreRoleEnum::OWNER)) {
            $user->merchant()->assignRole(StoreRoleEnum::OWNER);
        }
        $membership->syncPermissions(StoreRoles::permissions(StoreRoleEnum::OWNER));

        // Demo team members — one per role on THIS demo store, so a tester can
        // log in as each role and see the scoped/matrix behaviour. Separate
        // demo.* emails keep them isolated from the default-store members
        // created by StoreRolesAndPermissionsSeeder (admin@edzeery.com, ...).
        $demoMembers = [
            ['email' => 'demo.admin@edzeery.com',   'name' => 'Demo Admin',   'role' => StoreRoleEnum::ADMIN],
            ['email' => 'demo.manager@edzeery.com', 'name' => 'Demo Manager', 'role' => StoreRoleEnum::MANAGER],
            ['email' => 'demo.staff@edzeery.com',   'name' => 'Demo Staff',   'role' => StoreRoleEnum::STAFF],
        ];

        foreach ($demoMembers as $member) {
            $memberUser = $this->createUser($member['email'], $member['name'], UserRoleEnum::MERCHANT, $member['role']);

            $memberMembership = StoreMembership::firstOrCreate(
                ['store_id' => $store->id, 'user_id' => $memberUser->id],
                [
                    'invited_by' => $user->id,
                    'is_active'  => true,
                    'role'       => $member['role']->value,
                ]
            );

            $memberMembership->syncPermissions(StoreRoles::permissions($member['role']));
        }

        $this->seedDemoCustomer($user);

        // Phase 34.4 demo: three permission-scoped staff members (confirm-only,
        // track-only, confirm+track dual) plus the rotation rules (shift caps,
        // product specialist) and overflow/price-edit toggles that make the
        // assignment, reassign and confirmation demos behave realistically.
        $this->seedPermissionScopedTeam($store);
        $this->enableFeatureSettings($store);
        $this->seedConfirmationShifts($store);
        $this->seedShippingProviders($store);

        $this->seedBrands($store);
        $this->seedCategories($store);
        $this->seedProducts($store);
        $this->seedSpecialistAssignment($store);
        $this->seedRiders($store);

        $this->seedDemoCustomers($store);
        $this->seedDemoOrders($store);
    }

    private function createUser(string $email, string $name, UserRoleEnum $platformRole, ?StoreRoleEnum $storeRole = null): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Force-verify existing accounts too (see owner note above).
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        $platformRoleObj = Role::findByName($platformRole->value, 'web');
        if ($platformRoleObj && ! $user->hasRole($platformRoleObj)) {
            $user->assignRole($platformRoleObj);
        }

        $storeRoleObj = $storeRole ? Role::findByName($storeRole->value, 'merchant') : null;
        if ($storeRoleObj && ! $user->hasRole($storeRoleObj)) {
            $user->assignRole($storeRoleObj);
        }

        return $user;
    }

    private function seedDemoCustomer(User $owner): void
    {
        // A public storefront shopper so checkout can be tested end-to-end.
        $customer = User::firstOrCreate(
            ['email' => 'customer@edzeery.com'],
            [
                'name'              => 'Demo Customer',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $roleObj = Role::findByName(UserRoleEnum::USER->value, 'web');
        if ($roleObj && ! $customer->hasRole($roleObj)) {
            $customer->assignRole($roleObj);
        }
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
                    ['name' => 'Black', 'sku' => 'DEMO-EAR-001-BK', 'price' => 4500.00, 'stock' => 50, 'option_values' => [['Color', 'Black']]],
                    ['name' => 'White', 'sku' => 'DEMO-EAR-001-WH', 'price' => 4500.00, 'stock' => 35, 'option_values' => [['Color', 'White']]],
                    ['name' => 'Blue',  'sku' => 'DEMO-EAR-001-BL', 'price' => 4800.00, 'stock' => 20, 'option_values' => [['Color', 'Blue']]],
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
                    ['name' => '42mm - Silver', 'sku' => 'DEMO-WATCH-001-SV', 'price' => 8900.00, 'stock' => 15, 'option_values' => [['Size', '42mm'], ['Color', 'Silver']]],
                    ['name' => '46mm - Black',  'sku' => 'DEMO-WATCH-001-BK', 'price' => 9500.00, 'stock' => 20, 'option_values' => [['Size', '46mm'], ['Color', 'Black']]],
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
                    ['name' => 'Small - Black',  'sku' => 'DEMO-TSHIRT-001-SB', 'price' => 1800.00, 'stock' => 40, 'option_values' => [['Size', 'Small'], ['Color', 'Black']]],
                    ['name' => 'Medium - Black', 'sku' => 'DEMO-TSHIRT-001-MB', 'price' => 1800.00, 'stock' => 60, 'option_values' => [['Size', 'Medium'], ['Color', 'Black']]],
                    ['name' => 'Large - Black',  'sku' => 'DEMO-TSHIRT-001-LB', 'price' => 1800.00, 'stock' => 50, 'option_values' => [['Size', 'Large'], ['Color', 'Black']]],
                    ['name' => 'Medium - White', 'sku' => 'DEMO-TSHIRT-001-MW', 'price' => 1800.00, 'stock' => 45, 'option_values' => [['Size', 'Medium'], ['Color', 'White']]],
                    ['name' => 'Large - White',  'sku' => 'DEMO-TSHIRT-001-LW', 'price' => 1800.00, 'stock' => 30, 'option_values' => [['Size', 'Large'], ['Color', 'White']]],
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
                    ['name' => 'Medium - Beige',  'sku' => 'DEMO-SHIRT-001-MB', 'price' => 2800.00, 'stock' => 25, 'option_values' => [['Size', 'Medium'], ['Color', 'Beige']]],
                    ['name' => 'Large - Beige',   'sku' => 'DEMO-SHIRT-001-LB', 'price' => 2800.00, 'stock' => 20, 'option_values' => [['Size', 'Large'], ['Color', 'Beige']]],
                    ['name' => 'Medium - Green',  'sku' => 'DEMO-SHIRT-001-MG', 'price' => 2800.00, 'stock' => 15, 'option_values' => [['Size', 'Medium'], ['Color', 'Green']]],
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
                $variant = ProductVariant::firstOrCreate(
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

                if ($data['type'] === 'variable' && isset($vData['option_values'])) {
                    foreach ($vData['option_values'] as [$optionName, $optionValue]) {
                        $option = ProductOption::firstOrCreate(
                            ['store_id' => $store->id, 'name' => $optionName],
                            ['type' => 'select', 'sort_order' => 0]
                        );

                        $optValue = ProductOptionValue::firstOrCreate(
                            ['store_id' => $store->id, 'product_option_id' => $option->id, 'value' => $optionValue],
                            ['sort_order' => 0]
                        );

                        $variant->optionValues()->syncWithoutDetaching([
                            $optValue->id => ['product_option_id' => $option->id],
                        ]);
                    }
                }
            }

            if (! $product->images()->exists()) {
                $product->images()->create([
                    'path'       => 'img/icons/noimg.png',
                    'store_id'   => $store->id,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }
        }
    }

    private function seedRiders(Store $store): void
    {
        $riders = [
            ['name' => 'Yacine Belkacem', 'phone' => '0550100011', 'email' => 'yacine.b@demo.edzeery.com', 'vehicle_type' => DeliveryRider::VEHICLE_MOTORCYCLE, 'is_active' => true, 'notes' => 'Covers the city centre, available mornings.'],
            ['name' => 'Ahmed Cherif',    'phone' => '0660200022', 'email' => 'ahmed.c@demo.edzeery.com', 'vehicle_type' => DeliveryRider::VEHICLE_CAR,       'is_active' => true, 'notes' => 'Large orders and bulk deliveries.'],
            ['name' => 'Sofiane Hamdi',   'phone' => '0770300033', 'email' => 'sofiane.h@demo.edzeery.com', 'vehicle_type' => DeliveryRider::VEHICLE_BICYCLE,   'is_active' => false, 'notes' => 'Short-distance courier, currently on leave.'],
            ['name' => 'Khaled Meziane',    'phone' => '0550400044', 'email' => 'khaled.m@demo.edzeery.com', 'vehicle_type' => DeliveryRider::VEHICLE_VAN,       'is_active' => true, 'notes' => 'Furniture and heavy items, suburb routes.'],
        ];

        foreach ($riders as $rider) {
            DeliveryRider::firstOrCreate(
                ['store_id' => $store->id, 'phone' => $rider['phone']],
                $rider
            );
        }
    }

    /* =========================================================================
     | Permission-scoped team + rotation rules (Phase 34.4 demo data)
     | ========================================================================= */

    private function seedPermissionScopedTeam(Store $store): void
    {
        // Three members whose stored permissions are scoped to a single
        // workflow — the exact confirm/track/dual scenarios the reassign
        // modals and the capacity-rotation rules exercise:
        //   • demo.confirmer@edzeery.com  → confirm only
        //   • demo.tracker@edzeery.com   → tracking only
        //   • demo.dual@edzeery.com      → confirm + track (dual-role member)
        $scopedMembers = [
            'demo.confirmer@edzeery.com' => [
                'name'  => 'Demo Confirmer',
                'perms' => [
                    StorePermissionEnum::ORDER_VIEW->value,
                    StorePermissionEnum::ORDER_CONFIRM->value,
                    StorePermissionEnum::CRM_ORDER_CONFIRMATION->value,
                    StorePermissionEnum::RETURNS_VERIFY_BARCODE->value,
                    StorePermissionEnum::RETURNS_PROCESS->value,
                    StorePermissionEnum::STATS_CONFIRMATION->value,
                    StorePermissionEnum::INVENTORY_VIEW->value,
                ],
            ],
            'demo.tracker@edzeery.com' => [
                'name'  => 'Demo Tracker',
                'perms' => [
                    StorePermissionEnum::ORDER_VIEW->value,
                    StorePermissionEnum::CRM_ORDER_TRACKING->value,
                    StorePermissionEnum::DELIVERY_RIDERS_VIEW->value,
                    StorePermissionEnum::STATS_DELIVERY->value,
                    StorePermissionEnum::STATS_TOP_KPIS->value,
                    StorePermissionEnum::INVENTORY_VIEW->value,
                ],
            ],
            'demo.dual@edzeery.com' => [
                'name'  => 'Demo Confirm + Track',
                'perms' => [
                    StorePermissionEnum::ORDER_VIEW->value,
                    StorePermissionEnum::ORDER_CONFIRM->value,
                    StorePermissionEnum::CRM_ORDER_TRACKING->value,
                    StorePermissionEnum::CRM_ORDER_CONFIRMATION->value,
                    StorePermissionEnum::RETURNS_VERIFY_BARCODE->value,
                    StorePermissionEnum::STATS_CONFIRMATION->value,
                    StorePermissionEnum::STATS_DELIVERY->value,
                    StorePermissionEnum::INVENTORY_VIEW->value,
                ],
            ],
        ];

        foreach ($scopedMembers as $email => $member) {
            $memberUser = $this->createUser($email, $member['name'], UserRoleEnum::MERCHANT);

            $membership = StoreMembership::firstOrCreate(
                ['store_id' => $store->id, 'user_id' => $memberUser->id],
                [
                    'invited_by' => $store->user_id,
                    'is_active'  => true,
                    'role'       => StoreRoleEnum::STAFF->value,
                ]
            );

            // Decision #6 — the stored rows are authoritative, so each member
            // carries only its workflow scope (no ORDER_MANAGE, no unrelated
            // confirm/track permission leaking through the global STAFF role).
            $membership->syncPermissions($member['perms']);
        }
    }

    private function enableFeatureSettings(Store $store): void
    {
        // Toggles that make the overflow fallback, the "over capacity" badge
        // and the in-table price editor demo-able out of the box.
        $store->settings()->updateOrCreate([], [
            'distribution_overflow_enabled'    => true,
            'distribution_overflow_percentage' => 20,
            'allow_price_edit'                 => true,
        ]);
    }

    private function seedConfirmationShifts(Store $store): void
    {
        // Rotation windows for the confirm/track workflows. Algeria works
        // Sun–Thu (Fri/Sat weekend), so days_of_week = [7, 1, 2, 3, 4] ISO.
        $members = StoreMembership::where('store_id', $store->id)->with('user')->get()
            ->keyBy(fn (StoreMembership $m) => strtolower($m->user->email));

        $shifts = [
            ['email' => 'demo.confirmer@edzeery.com', 'scope' => 'confirm', 'start' => '08:00', 'end' => '17:00', 'cap' => 15],
            ['email' => 'demo.tracker@edzeery.com',   'scope' => 'track',   'start' => '09:00', 'end' => '18:00', 'cap' => 20],
            ['email' => 'demo.dual@edzeery.com',      'scope' => 'confirm', 'start' => '08:00', 'end' => '17:00', 'cap' => 25],
            ['email' => 'demo.dual@edzeery.com',      'scope' => 'track',   'start' => '08:00', 'end' => '17:00', 'cap' => 25],
        ];

        foreach ($shifts as $shift) {
            $membership = $members->get($shift['email']);
            if (! $membership) {
                continue;
            }

            ConfirmationShift::firstOrCreate(
                [
                    'store_id'      => $store->id,
                    'membership_id' => $membership->id,
                    'role_scope'    => $shift['scope'],
                    'shift_type'    => 'morning',
                ],
                [
                    'start_time'            => $shift['start'],
                    'end_time'              => $shift['end'],
                    'days_of_week'          => [7, 1, 2, 3, 4],
                    'is_active'             => true,
                    'max_concurrent_orders' => $shift['cap'],
                ]
            );
        }
    }

    private function seedShippingProviders(Store $store): void
    {
        // Store-scoped providers backed by the global carrier catalog so the
        // tracking table shows carrier logos/colours and the send flow works.
        $carriers = Carrier::whereIn('code', ['ecotrack', 'zrexpress_v2'])->get()->keyBy('code');

        $providers = [
            ['code' => 'ecotrack',      'name' => 'Ecotrack',       'flat_rate' => 450.00, 'max_weight_kg' => 30, 'is_default' => true],
            ['code' => 'zrexpress_v2',  'name' => 'ZR Express v2',  'flat_rate' => 400.00, 'max_weight_kg' => 20, 'is_default' => false],
        ];

        foreach ($providers as $provider) {
            $carrier = $carriers->get($provider['code']);

            ShippingProvider::updateOrCreate(
                ['store_id' => $store->id, 'code' => $provider['code']],
                [
                    'name'                   => $provider['name'],
                    'carrier_platform_id'    => $carrier?->platform_id,
                    'carrier_id'             => $carrier?->id,
                    'credentials'            => [],
                    'shipment_types_enabled' => ['delivery'],
                    'is_active'              => true,
                    'is_default'             => $provider['is_default'],
                    'max_weight_kg'          => $provider['max_weight_kg'],
                    'flat_rate'              => $provider['flat_rate'],
                ]
            );
        }
    }

    private function seedSpecialistAssignment(Store $store): void
    {
        // A confirmation specialist: the dual member is preferred on this
        // product (the resolver favours specialists in the candidate list).
        $product = Product::withoutGlobalScopes()
            ->where('store_id', $store->id)
            ->where('slug', 'demo-wireless-earbuds-pro')
            ->first();

        $dual = StoreMembership::where('store_id', $store->id)
            ->whereHas('user', fn ($q) => $q->where('email', 'demo.dual@edzeery.com'))
            ->first();

        if (! $product || ! $dual) {
            return;
        }

        ConfirmationProductAssignment::firstOrCreate(
            [
                'store_id'      => $store->id,
                'membership_id' => $dual->id,
                'product_id'    => $product->id,
            ]
        );
    }

    /* =========================================================================
     | Semi-realistic customers + orders (exercises every recent phase)
     | ========================================================================= */

    private function algeriaLocation(string $stateCode, string $cityName, string $postCode): array
    {
        $country = Country::where('code', 'DZ')->first()
            ?? Country::create([
                'name'             => 'Algeria',
                'arabic_name'      => 'الجزائر',
                'code'             => 'DZ',
                'is_active'        => true,
                'is_cod_available' => true,
            ]);

        $stateNames = ['16' => 'Algiers', '31' => 'Oran', '25' => 'Constantine', '09' => 'Blida'];

        $state = State::where('country_id', $country->id)->where('state_code', $stateCode)->first()
            ?? State::create([
                'country_id'       => $country->id,
                'name'             => $stateNames[$stateCode] ?? $stateCode,
                'state_code'       => $stateCode,
                'is_active'        => true,
                'is_cod_available' => true,
            ]);

        $city = City::where('state_id', $state->id)->where('name', $cityName)->first()
            ?? City::create([
                'state_id'         => $state->id,
                'name'             => $cityName,
                'post_code'        => $postCode,
                'is_active'        => true,
                'is_cod_available' => true,
            ]);

        return compact('country', 'state', 'city');
    }

    private function seedDemoCustomers(Store $store): void
    {
        $customers = [
            ['name' => 'Amine Bensaïd',   'phone' => '0550123456', 'state' => '16', 'city' => 'Bab Ezzouar', 'post' => '16062', 'email' => 'amine.bensaid@example.dz',    'address' => 'Cité 200 logements, Bab Ezzouar, Alger'],
            ['name' => 'Meriem Cherif',   'phone' => '0770987654', 'state' => '31', 'city' => 'Bir El Djir', 'post' => '31000', 'email' => 'meriem.cherif@example.dz',    'address' => 'Résidence les Palmiers, Bir El Djir, Oran'],
            ['name' => 'Rania Bouzid',    'phone' => '0661234567', 'state' => '25', 'city' => 'El Khroub',   'post' => '25100', 'email' => 'rania.bouzid@example.dz',     'address' => 'Zone urbaine est, El Khroub, Constantine'],
            ['name' => 'Yacine Haddad',   'phone' => '0555555555', 'state' => '09', 'city' => 'Boufarik',    'post' => '09000', 'email' => 'yacine.haddad@example.dz',    'address' => 'Route de Boufarik centre, Blida'],
            ['name' => 'Sofiane Belkadi', 'phone' => '0771112233', 'state' => '16', 'city' => 'Hussein Dey', 'post' => '16045', 'email' => 'sofiane.belkadi@example.dz', 'address' => 'Rue des Frères Mansouri, Hussein Dey, Alger'],
        ];

        foreach ($customers as $data) {
            $loc = $this->algeriaLocation($data['state'], $data['city'], $data['post']);

            Customer::firstOrCreate(
                ['store_id' => $store->id, 'phone' => $data['phone']],
                [
                    'name'       => $data['name'],
                    'email'      => $data['email'],
                    'address'    => $data['address'],
                    'country_id' => $loc['country']->id,
                    'state_id'   => $loc['state']->id,
                    'city_id'    => $loc['city']->id,
                    'status'     => true,
                ]
            );
        }
    }

    private function seedDemoOrders(Store $store): void
    {
        $statuses  = Status::system()->forType('order')->get()->keyBy('key');
        $members   = StoreMembership::where('store_id', $store->id)->with('user')->get()
            ->keyBy(fn (StoreMembership $m) => strtolower($m->user->email));
        $providers = ShippingProvider::where('store_id', $store->id)->get()->keyBy('code');
        $riders    = DeliveryRider::where('store_id', $store->id)->get()->keyBy('phone');
        $customers = Customer::where('store_id', $store->id)->get()->keyBy('phone');
        $variants  = ProductVariant::withoutGlobalScopes()->where('store_id', $store->id)->get()->keyBy('sku');

        $ctx = [
            'statuses'  => $statuses,
            'members'   => $members,
            'providers' => $providers,
            'riders'    => $riders,
            'customers' => $customers,
            'variants'  => $variants,
            'owner'     => $members->get('demo@edzeery.com'),
            'confirmer' => $members->get('demo.confirmer@edzeery.com'),
            'tracker'   => $members->get('demo.tracker@edzeery.com'),
            'dual'      => $members->get('demo.dual@edzeery.com'),
        ];

        $specs = [
            [ // 21001 — brand new, nothing done yet
                'number' => '21001', 'customer' => '0550123456', 'status' => 'pending', 'days_ago' => 0, 'create_hour' => 9,
                'items' => [['DEMO-EAR-001-BK', 1]],
                'notes' => 'New order from the demo storefront — waiting for the first confirmation call.',
            ],
            [ // 21002 — pending, manually assigned to the confirmer (reassign demo)
                'number' => '21002', 'customer' => '0770987654', 'status' => 'pending', 'days_ago' => 0, 'create_hour' => 10,
                'items' => [['DEMO-WATCH-001-BK', 1]],
                'assign_to' => 'demo.confirmer@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'manual',
                'events' => [
                    ['type' => 'reassigned', 'hours' => 1, 'by' => 'demo@edzeery.com',
                     'message' => __('order_flow.event_reassigned', [], 'ar'), 'payload' => ['to' => 'Demo Confirmer']],
                ],
            ],
            [ // 21003 — pending, auto-assigned to the dual member
                'number' => '21003', 'customer' => '0661234567', 'status' => 'pending', 'days_ago' => 1, 'create_hour' => 14,
                'items' => [['DEMO-TSHIRT-001-MB', 2]],
                'assign_to' => 'demo.dual@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
            ],
            [ // 21004 — no_answer_1 with contact attempt + secondary phone
                'number' => '21004', 'customer' => '0555555555', 'status' => 'no_answer_1', 'days_ago' => 2, 'create_hour' => 9,
                'items' => [['DEMO-SUN-001-DF', 1]],
                'notes' => 'Client did not answer the first call — try again tomorrow.',
                'phone_secondary' => '0661998877',
                'attempts' => 1, 'last_contact_hours' => 26,
                'history' => [['no_answer_1', 'demo.staff@edzeery.com', 'First confirmation attempt — no answer', 26]],
                'events' => [
                    ['type' => 'contact', 'hours' => 26, 'by' => 'demo.staff@edzeery.com',
                     'message' => __('order_flow.event_contact', ['outcome' => 'no answer'], 'ar'), 'payload' => ['outcome' => 'no answer']],
                ],
            ],
            [ // 21005 — postponed on request
                'number' => '21005', 'customer' => '0771112233', 'status' => 'postponed', 'days_ago' => 4, 'create_hour' => 11,
                'items' => [['DEMO-SPK-001-DF', 1]],
                'notes' => 'Customer asked to call back next week.',
                'history' => [['postponed', 'demo.staff@edzeery.com', 'Customer requested a callback next week', 34]],
            ],
            [ // 21006 — confirmed, company selected, ready to send
                'number' => '21006', 'customer' => '0550123456', 'status' => 'confirmed', 'days_ago' => 1, 'create_hour' => 10,
                'items' => [['DEMO-TSHIRT-001-MB', 2]],
                'provider' => 'ecotrack',
                'assign_to' => 'demo.dual@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'manual',
                'history' => [['confirmed', 'demo.confirmer@edzeery.com', 'Customer confirmed details by phone', 14]],
                'events' => [
                    ['type' => 'contact', 'hours' => 14, 'by' => 'demo.confirmer@edzeery.com',
                     'message' => __('order_flow.event_contact', ['outcome' => 'answered'], 'ar'), 'payload' => ['outcome' => 'answered']],
                ],
            ],
            [ // 21007 — confirmed, auto-assigned to the confirmer
                'number' => '21007', 'customer' => '0770987654', 'status' => 'confirmed', 'days_ago' => 2, 'create_hour' => 9,
                'items' => [['DEMO-WATCH-001-BK', 1]],
                'provider' => 'ecotrack',
                'assign_to' => 'demo.confirmer@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'history' => [['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed via WhatsApp', 16]],
            ],
            [ // 21008 — preparing (warehouse)
                'number' => '21008', 'customer' => '0661234567', 'status' => 'preparing', 'days_ago' => 3, 'create_hour' => 10,
                'items' => [['DEMO-SHIRT-001-MB', 2]],
                'assign_to' => 'demo.dual@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'history' => [
                    ['confirmed', 'demo.dual@edzeery.com', 'Confirmed — customer verified the order', 18],
                    ['preparing', 'demo@edzeery.com', 'Handed to warehouse for packing', 40],
                ],
            ],
            [ // 21009 — shipped via Ecotrack (carrier tab)
                'number' => '21009', 'customer' => '0555555555', 'status' => 'shipped', 'days_ago' => 5, 'create_hour' => 9,
                'items' => [['DEMO-SPK-001-DF', 1]],
                'provider' => 'ecotrack',
                'assign_to' => 'demo.tracker@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'history' => [
                    ['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed by phone', 10],
                    ['shipped', 'demo.tracker@edzeery.com', 'Packed and picked up by Ecotrack', 30],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-402731', 'status' => 'shipped', 'shipped_hours' => 30,
                    'timeline' => [['shipped', 'Handed to courier at the Ecotrack Algiers hub', 30]],
                ],
            ],
            [ // 21010 — out_for_delivery, carrier-validated + over-capacity flag
                'number' => '21010', 'customer' => '0771112233', 'status' => 'out_for_delivery', 'days_ago' => 7, 'create_hour' => 9,
                'items' => [['DEMO-WATCH-001-BK', 1]],
                'provider' => 'ecotrack',
                'assign_to' => 'demo.tracker@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'over_capacity' => true,
                'history' => [
                    ['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed', 8],
                    ['shipped', 'demo.tracker@edzeery.com', 'Ecotrack pickup', 26],
                    ['out_for_delivery', 'demo.tracker@edzeery.com', 'Carrier validated — envelope out for delivery', 150],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-402732', 'status' => 'out_for_delivery', 'shipped_hours' => 26,
                    'validated' => true, 'validated_hours' => 150,
                    'timeline' => [
                        ['shipped', 'Picked up by Ecotrack', 26],
                        ['out_for_delivery', 'Carrier validated — out for delivery', 150],
                    ],
                ],
            ],
            [ // 21011 — delivered, full lifecycle via ZR Express
                'number' => '21011', 'customer' => '0550123456', 'status' => 'delivered', 'days_ago' => 9, 'create_hour' => 9,
                'items' => [['DEMO-EAR-001-BK', 1], ['DEMO-BAG-001-DF', 1]],
                'provider' => 'zrexpress_v2',
                'assign_to' => 'demo.tracker@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'notes' => 'Delivered to the concierge at the residence; COD collected.',
                'history' => [
                    ['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed', 6],
                    ['shipped', 'demo.tracker@edzeery.com', 'Picked by ZR Express', 24],
                    ['in_transit', 'demo.tracker@edzeery.com', 'Transit update from the carrier', 60],
                    ['out_for_delivery', 'demo.tracker@edzeery.com', 'Out for delivery', 156],
                    ['delivered', 'demo.tracker@edzeery.com', 'Delivered — COD collected', 160],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-402733', 'status' => 'delivered', 'shipped_hours' => 24, 'delivered_hours' => 160,
                    'timeline' => [
                        ['shipped', 'Picked by ZR Express', 24],
                        ['in_transit', 'Arrived at the Oran distribution centre', 72],
                        ['out_for_delivery', 'Courier en route', 154],
                        ['delivered', 'Delivered and COD collected', 160],
                    ],
                ],
                'events' => [
                    ['type' => 'note', 'hours' => 162, 'by' => 'demo.tracker@edzeery.com',
                     'message' => 'COD amount collected and marked as paid.', 'payload' => []],
                ],
            ],
            [ // 21012 — returned, barcode verified + processed (returns scan demo)
                'number' => '21012', 'customer' => '0661234567', 'status' => 'returned', 'days_ago' => 11, 'create_hour' => 9,
                'items' => [['DEMO-SHIRT-001-LB', 1]],
                'provider' => 'ecotrack',
                'assign_to' => 'demo.confirmer@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'notes' => 'Customer refused the parcel — return verified and processed.',
                'history' => [
                    ['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed', 8],
                    ['shipped', 'demo.tracker@edzeery.com', 'Picked by Ecotrack', 30],
                    ['returned', 'demo.confirmer@edzeery.com', 'Customer refused — return barcode verified & processed', 210],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-402735', 'status' => 'returned', 'shipped_hours' => 30, 'returned_hours' => 210,
                    'barcode' => 'DEMO-RET-001', 'barcode_processed' => true,
                    'inspection' => 'good', 'inspection_notes' => 'Blister opened, otherwise undamaged.',
                    'timeline' => [
                        ['shipped', 'Picked by Ecotrack', 30],
                        ['returned', 'Returned to warehouse — verified & processed', 210],
                    ],
                ],
            ],
            [ // 21013 — cancelled at the customer's request
                'number' => '21013', 'customer' => '0555555555', 'status' => 'cancelled', 'days_ago' => 12, 'create_hour' => 9,
                'items' => [['DEMO-SUN-001-DF', 1]],
                'history' => [
                    ['confirmed', 'demo.confirmer@edzeery.com', 'Confirmed by mistake — customer asked to cancel', 12],
                    ['cancelled', 'demo@edzeery.com', 'Cancelled at customer request', 20],
                ],
            ],
            [ // 21014 — shipped via rider hand-off (rider tab)
                'number' => '21014', 'customer' => '0550123456', 'status' => 'shipped', 'days_ago' => 3, 'create_hour' => 9,
                'items' => [['DEMO-BAG-001-DF', 1]],
                'rider' => '0550100011',
                'assign_to' => 'demo.tracker@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'history' => [
                    ['confirmed', 'demo.dual@edzeery.com', 'Confirmed — COD via rider', 10],
                    ['shipped', 'demo.tracker@edzeery.com', 'Handed to rider Yacine', 28],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-903118', 'status' => 'shipped', 'shipped_hours' => 28,
                    'timeline' => [['shipped', 'Handed to rider Yacine Belkacem', 28]],
                ],
            ],
            [ // 21015 — out_for_delivery via rider (rider tab)
                'number' => '21015', 'customer' => '0770987654', 'status' => 'out_for_delivery', 'days_ago' => 6, 'create_hour' => 9,
                'items' => [['DEMO-TSHIRT-001-MB', 1]],
                'rider' => '0660200022',
                'assign_to' => 'demo.dual@edzeery.com', 'assign_by' => 'demo@edzeery.com', 'assign_method' => 'auto',
                'history' => [
                    ['confirmed', 'demo.dual@edzeery.com', 'Confirmed', 8],
                    ['shipped', 'demo.tracker@edzeery.com', 'Handed to rider Ahmed', 26],
                    ['out_for_delivery', 'demo.dual@edzeery.com', 'Rider en route — ETA within the hour', 120],
                ],
                'tracking' => [
                    'number' => 'DEM-HM-903119', 'status' => 'out_for_delivery', 'shipped_hours' => 26,
                    'timeline' => [
                        ['shipped', 'Handed to rider Ahmed Cherif', 26],
                        ['out_for_delivery', 'Rider en route — ETA within the hour', 120],
                    ],
                ],
            ],
        ];

        foreach ($specs as $spec) {
            $this->seedOrder($store, $spec, $ctx);
        }
    }

    private function seedOrder(Store $store, array $spec, array $ctx): void
    {
        // Always-fresh demo rows: if a previous run left a 210xx order behind
        // (or the seeder died halfway), remove it with its children first so
        // re-seeding reproduces the exact same, consistent dataset. The range
        // is reserved for the demo store, so real merchant orders never match.
        $existing = Order::withTrashed()
            ->where('store_id', $store->id)
            ->where('number', $spec['number'])
            ->first();

        if ($existing) {
            $existing->statusHistories()->forceDelete();
            $existing->events()->forceDelete();
            foreach ($existing->trackings()->get() as $tracking) {
                $tracking->histories()->delete();
                $tracking->delete();
            }
            $existing->items()->forceDelete();
            $existing->forceDelete();
        }

        $createdAt = now()->startOfDay()->subDays((int) $spec['days_ago'])->addHours((int) ($spec['create_hour'] ?? 9));
        if ($createdAt->isFuture()) {
            $createdAt = now()->subHours(2);
        }

        $customer = $ctx['customers']->get($spec['customer']);
        $status = $ctx['statuses']->get($spec['status']);
        if (! $customer || ! $status) {
            return;
        }

        $provider = ! empty($spec['provider']) ? $ctx['providers']->get($spec['provider']) : null;
        $rider = ! empty($spec['rider']) ? $ctx['riders']->get($spec['rider']) : null;
        $assignTo = ! empty($spec['assign_to']) ? $ctx['members']->get($spec['assign_to']) : null;
        $assignBy = ! empty($spec['assign_by']) ? $ctx['members']->get($spec['assign_by']) : null;

        // Items + totals (weight-free local home deliveries).
        $itemsData = [];
        $subtotal = 0.0;
        foreach ($spec['items'] as [$sku, $qty]) {
            $variant = $ctx['variants']->get($sku);
            if (! $variant) {
                continue;
            }
            $price = (float) $variant->price;
            $itemsData[] = [$variant, $qty, $price];
            $subtotal += $price * $qty;
        }

        $assignedAt = $assignTo ? $createdAt->copy()->addMinutes(25) : null;

        $order = new Order();
        $order->store_id = $store->id;
        $order->customer_id = $customer->id;
        $order->status_id = $status->id;
        $order->number = $spec['number'];
        $order->created_by_membership_id = $ctx['owner']?->id;
        $order->total_amount = round($subtotal + (float) ($spec['shipping'] ?? 0), 2);
        $order->shipping_cost = $spec['shipping'] ?? 0;
        $order->state_id = $customer->state_id;
        $order->city_id = $customer->city_id;
        $order->address = $customer->address;
        $order->delivery_type = Order::DELIVERY_HOME;
        $order->payment_method = 'cod';
        $order->shipment_type = 'delivery';
        $order->shipping_provider_id = $provider?->id;
        $order->delivery_rider_id = $rider?->id;
        $order->assigned_to_membership_id = $assignTo?->id;
        $order->assigned_by_membership_id = $assignBy?->id;
        $order->assigned_at = $assignedAt;
        $order->assignment_method = $spec['assign_method'] ?? null;
        $order->over_capacity = (bool) ($spec['over_capacity'] ?? false);
        $order->notes = $spec['notes'] ?? null;
        $order->phone_secondary = $spec['phone_secondary'] ?? null;
        $order->confirmation_attempts = (int) ($spec['attempts'] ?? 0);
        $order->last_contact_at = (($spec['last_contact_hours'] ?? null) !== null)
            ? $createdAt->copy()->addHours((int) $spec['last_contact_hours'])
            : null;
        $order->created_at = $createdAt;
        $order->updated_at = $createdAt;
        $order->save();

        // OrderObserver::created logs a NOW-based, app-locale "created" audit
        // event automatically. The demo timeline below is authoritative (Arabic,
        // dated to the seeded created_at), so drop the observer's synthetic row.
        $order->events()->where('event_type', 'created')->delete();

        foreach ($itemsData as [$variant, $qty, $price]) {
            OrderItem::create([
                'store_id'           => $store->id,
                'order_id'           => $order->id,
                'product_variant_id' => $variant->id,
                'product_id'         => $variant->product_id,
                'quantity'           => $qty,
                'price'              => $price,
                'subtotal'           => $price * $qty,
            ]);
        }

        // Order-level timeline: created + every status transition + extras.
        $this->addAuditEvent(
            $order,
            'created',
            __('order_flow.event_created', ['number' => $order->number], 'ar'),
            ['number' => $order->number, 'total_amount' => $order->total_amount],
            $ctx['owner']?->id,
            $createdAt,
        );

        $prevKey = 'pending';
        foreach ($spec['history'] ?? [] as [$key, $byEmail, $reason, $hours]) {
            $byId = ! empty($byEmail) ? $ctx['members']->get($byEmail)?->id : null;
            $at = $createdAt->copy()->addHours((int) $hours);
            $this->addStatusHistory($order, $ctx['statuses']->get($key), $byId, $reason, $at);
            $this->addAuditEvent(
                $order,
                'status',
                __('order_flow.event_status', ['to' => status_label('order', $key)], 'ar'),
                ['from' => $prevKey, 'to' => $key, 'reason' => $reason],
                $byId,
                $at,
            );
            $prevKey = $key;
        }

        foreach ($spec['events'] ?? [] as $event) {
            $byId = ! empty($event['by']) ? $ctx['members']->get($event['by'])?->id : null;
            $this->addAuditEvent(
                $order,
                $event['type'],
                $event['message'],
                $event['payload'],
                $byId,
                $createdAt->copy()->addHours((int) $event['hours']),
            );
        }

        if (! empty($spec['tracking'])) {
            $this->seedOrderTracking($store, $order, $spec, $ctx, $createdAt, $provider, $assignTo);
        }
    }

    private function seedOrderTracking(Store $store, Order $order, array $spec, array $ctx, Carbon $createdAt, ?ShippingProvider $provider, ?StoreMembership $assignTo): void
    {
        $t = $spec['tracking'];
        $hoursOf = fn (string $key, $default) => (int) ($t[$key] ?? $default);

        $tracking = new OrderTracking();
        $tracking->store_id = $store->id;
        $tracking->order_id = $order->id;
        $tracking->shipping_provider_id = $provider?->id;
        $tracking->tracking_number = $t['number'];
        $tracking->tracking_status = $t['status'];
        $tracking->shipped_at = $createdAt->copy()->addHours($hoursOf('shipped_hours', 24));
        $tracking->webhook_token = Str::random(40);
        $tracking->assigned_to_membership_id = $assignTo?->id;
        $tracking->assigned_by_membership_id = $ctx['owner']?->id;
        $tracking->assigned_at = $tracking->shipped_at;
        $tracking->assignment_method = $order->assignment_method;
        $tracking->over_capacity = $order->over_capacity;
        $tracking->carrier_raw = ['carrier' => $provider?->code, 'last_check' => 'seeded'];
        $tracking->notes = $t['notes'] ?? null;

        if ($t['status'] === OrderTrackingStatus::DELIVERED->value) {
            $tracking->delivered_at = $createdAt->copy()->addHours($hoursOf('delivered_hours', 160));
        }
        if (isset($t['returned_hours'])) {
            $tracking->returned_at = $createdAt->copy()->addHours((int) $t['returned_hours']);
        }
        if (($t['validated'] ?? false)) {
            $tracking->carrier_validated_at = $createdAt->copy()->addHours($hoursOf('validated_hours', 150));
            $tracking->carrier_validated_by_membership_id = $ctx['tracker']?->id;
        }
        if (($t['barcode'] ?? null) !== null) {
            $tracking->verification_barcode = $t['barcode'];
            $tracking->verified_at = $createdAt->copy()->addHours($hoursOf('barcode_hours', 206));
            $tracking->verified_by_membership_id = $ctx['confirmer']?->id;
            $tracking->inspection_result = $t['inspection'] ?? 'good';
            $tracking->inspection_notes = $t['inspection_notes'] ?? null;
            if (($t['barcode_processed'] ?? false)) {
                $tracking->processed_at = $tracking->verified_at->copy()->addMinutes(6);
                $tracking->processed_by_membership_id = $ctx['confirmer']?->id;
            }
        }

        $tracking->created_at = $createdAt;
        $tracking->updated_at = $createdAt;
        $tracking->save();

        foreach ($t['timeline'] ?? [] as [$tsKey, $tsNotes, $tsHours]) {
            $byId = $ctx['tracker']?->id ?? $ctx['owner']?->id;
            $at = $createdAt->copy()->addHours((int) $tsHours);
            $this->addTrackingHistory($order, $tracking, $tsKey, $byId, $tsNotes, $at);
            $this->addAuditEvent(
                $order,
                'tracking',
                __('order_flow.event_tracking', ['status' => status_label('tracking', $tsKey)], 'ar'),
                ['tracking_status' => $tsKey, 'tracking_number' => $tracking->tracking_number],
                $byId,
                $at,
            );
        }

        if ($provider) {
            $this->addAuditEvent(
                $order,
                'sent_to_carrier',
                __('order_flow.event_sent_to_carrier', [], 'ar'),
                ['provider' => $provider->name, 'tracking_number' => $tracking->tracking_number],
                $ctx['tracker']?->id ?? $ctx['owner']?->id,
                $tracking->shipped_at,
            );
        }

        if (($t['validated'] ?? false)) {
            $this->addAuditEvent(
                $order,
                'carrier_validated',
                __('order_flow.event_carrier_validated', [], 'ar'),
                ['provider' => $provider?->name, 'tracking_number' => $tracking->tracking_number],
                $ctx['tracker']?->id,
                $createdAt->copy()->addHours($hoursOf('validated_hours', 150)),
            );
        }
    }

    private function addStatusHistory(Order $order, ?Status $status, ?string $byId, ?string $reason, Carbon $at): void
    {
        if (! $status) {
            return;
        }

        $history = new OrderStatusHistory([
            'order_id'                  => $order->id,
            'status_id'                 => $status->id,
            'changed_by_membership_id'  => $byId,
            'reason'                    => $reason,
        ]);
        $history->created_at = $at;
        $history->updated_at = $at;
        $history->save();
    }

    private function addTrackingHistory(Order $order, OrderTracking $tracking, string $status, ?string $byId, ?string $notes, Carbon $at): void
    {
        OrderTrackingHistory::create([
            'store_id'                => $order->store_id,
            'order_id'                => $order->id,
            'order_tracking_id'       => $tracking->id,
            'status'                  => $status,
            'changed_by_membership_id' => $byId,
            'notes'                   => $notes,
            'created_at'              => $at,
        ]);
    }

    private function addAuditEvent(Order $order, string $eventType, string $message, array $payload, ?string $byId, Carbon $at): void
    {
        OrderEvent::create([
            'store_id'             => $order->store_id,
            'order_id'             => $order->id,
            'actor_membership_id'  => $byId,
            'actor_type'           => $byId ? OrderEvent::ACTOR_MEMBERSHIP : OrderEvent::ACTOR_SYSTEM,
            'event_type'           => $eventType,
            'message'              => $message,
            'payload'              => $payload,
            'occurred_at'          => $at,
        ]);
    }
}
