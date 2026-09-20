<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Plan\Services\FeatureUsageService;
use App\Domains\Shipping\Models\DeliveryRate;
use App\Domains\Shipping\Models\DeliveryRateCity;
use App\Domains\Shipping\Models\ShippingProvider;
use App\Domains\Shipping\Models\ShippingRate;
use App\Domains\Shipping\Models\StopdeskPoint;
use App\Domains\Shipping\Services\ShippingCostCalculator;
use App\Models\Customer;
use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Products\ProductVariant;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 xl:max-w-6xl py-8 sm:py-12">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo e(__('storefront.checkout')); ?>

        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <?php echo e(__('storefront.complete_your_order')); ?>

        </p>
    </div>

    <?php
        $storeId = currentStoreId();
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems($storeId)->toArray();
        $cartCount = $cartService->getCount($storeId);
        $cartSubtotal = $cartService->getSubtotal($storeId);

        // Enrich cart items with images and slugs
        if (!empty($cartItems)) {
            $variantIds = array_column($cartItems, 'variant_id');
            $variants = \App\Models\Products\ProductVariant::with('product.images')
                ->whereIn('id', $variantIds)->get()->keyBy('id');
            foreach ($cartItems as &$ci) {
                $v = $variants[$ci['variant_id']] ?? null;
                $p = $v?->product;
                $img = $p?->images?->first()?->path;
                $ci['image'] = $img ? asset('storage/' . $img) : asset('img/icons/noimg.png');
                $ci['slug'] = $p?->slug ?? '';
            }
            unset($ci);
        }

        $providers = $this->availableProviders;
        $hasProviders = $providers->isNotEmpty();
        $isSingleProvider = $providers->count() === 1;
        $allProvidersDead = $this->hasActiveProviders && $providers->isEmpty();

        if ($isSingleProvider) {
            $this->selectedProvider = (string) $providers->first()->id;
        } elseif (! $this->selectedProvider && ($defaultProvider = $providers->firstWhere('is_default', true))) {
            // Several carriers: pre-select the marked default so the cascade
            // is ready immediately — the buyer can still switch it.
            $this->selectedProvider = (string) $defaultProvider->id;
        }
        $providerId = $this->selectedProvider
            ? (string) $this->selectedProvider
            : ($providers->first()?->id ? (string) $providers->first()->id : null);

        $providerFlat = $providers->firstWhere('id', $providerId)?->flat_rate ?? null;

        // Wilayas scoped to (carrier + delivery type): office-bearing states for
        // stopdesk, announced home coverage otherwise. A flat-rate carrier (or a
        // legacy store without carriers) covers every wilaya.
        $states = collect();
        if ($allProvidersDead) {
            $states = collect();
        } elseif (! $hasProviders || ! $providerId) {
            $states = State::active()->orderedByCode()->get();
        } elseif ($this->delivery_type === 'stopdesk') {
            $officeStateIds = DeliveryRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNotNull('office_cost')->orWhereNotNull('free_above'))
                ->distinct()
                ->pluck('state_id');
            $pointStateIds = StopdeskPoint::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->whereNotNull('state_id')
                ->distinct()
                ->pluck('state_id');
            $stateIds = $officeStateIds->merge($pointStateIds)->unique()->values();
            $states = State::whereIn('id', $stateIds)->active()->orderedByCode()->get();
        } else {
            $homeStateIds = DeliveryRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNotNull('home_cost')->orWhereNotNull('free_above'))
                ->distinct()
                ->pluck('state_id');
            $legacyStateIds = ShippingRate::where('store_id', $storeId)
                ->where('shipping_provider_id', $providerId)
                ->where('is_active', true)
                ->distinct()
                ->pluck('state_id');
            $stateIds = $homeStateIds->merge($legacyStateIds)->unique()->values();

            if ($stateIds->isEmpty() || $providerFlat !== null) {
                $states = State::active()->orderedByCode()->get();
            } else {
                $states = State::whereIn('id', $stateIds)->active()->orderedByCode()->get();
            }
        }

        // Communes scoped to (carrier + wilaya + delivery type). Stopdesk lists
        // only communes with an office; home falls back to every commune of the
        // wilaya when no per-commune pricing narrows it down.
        $cities = $this->citiesForSelection();

        // Offices: a single one auto-picks itself (shown as a compact card),
        // several become a picker, none keeps the explanatory note visible.
        $stopdesks = collect();
        if ($this->state_id && $this->delivery_type === 'stopdesk') {
            $stopdesks = $this->officesForSelection();
        }

        $officeOptions = $this->formatOfficeOptions($stopdesks);

        // Communes and offices are embedded inline, scoped to the current
        // wilaya / carrier: the lists arrive with the page so they can never
        // fail to appear (no lazy on-open fetch, no per-open round-trip).

        $shippingProductIds = ! empty($variants)
            ? $variants->pluck('product_id')->filter()->unique()->values()->all()
            : [];
        $shippingInfo = $this->quoteShipping($cartSubtotal, $shippingProductIds);
        if ($this->delivery_type === 'stopdesk' && ! ($shippingInfo['available'] ?? false)) {
            $shippingInfo = ['cost' => 0, 'is_free' => true, 'available' => true];
        }
        $paymentMethods = $this->paymentMethods;
    ?>

    
    <div class="mb-6">
        <a href="<?php echo e(route('storefront.home', ['store' => currentStore()?->slug ?? ''])); ?>"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'arrow-'.e(app()->getLocale() === 'ar' ? 'right' : 'left').'','class' => 'w-4 h-4 text-base']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-'.e(app()->getLocale() === 'ar' ? 'right' : 'left').'','class' => 'w-4 h-4 text-base']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
            <?php echo e(__('storefront.back_to_store')); ?>

        </a>
    </div>

    
    <div class="mb-8 flex items-center justify-between max-w-md">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">

                <?php if (isset($component)) { $__componentOriginal916418750eca0f0299436c8f1a00baec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal916418750eca0f0299436c8f1a00baec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-icon','data' => ['domain' => 'order','status' => 'completed','set' => 'bi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'order','status' => 'completed','set' => 'bi']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal916418750eca0f0299436c8f1a00baec)): ?>
<?php $attributes = $__attributesOriginal916418750eca0f0299436c8f1a00baec; ?>
<?php unset($__attributesOriginal916418750eca0f0299436c8f1a00baec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal916418750eca0f0299436c8f1a00baec)): ?>
<?php $component = $__componentOriginal916418750eca0f0299436c8f1a00baec; ?>
<?php unset($__componentOriginal916418750eca0f0299436c8f1a00baec); ?>
<?php endif; ?>
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline"><?php echo e(__('storefront.cart')); ?></span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">
                <?php if (isset($component)) { $__componentOriginal916418750eca0f0299436c8f1a00baec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal916418750eca0f0299436c8f1a00baec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'status-kit::components.status-icon','data' => ['domain' => 'order','status' => 'confirmed','set' => 'bi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['domain' => 'order','status' => 'confirmed','set' => 'bi']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal916418750eca0f0299436c8f1a00baec)): ?>
<?php $attributes = $__attributesOriginal916418750eca0f0299436c8f1a00baec; ?>
<?php unset($__attributesOriginal916418750eca0f0299436c8f1a00baec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal916418750eca0f0299436c8f1a00baec)): ?>
<?php $component = $__componentOriginal916418750eca0f0299436c8f1a00baec; ?>
<?php unset($__componentOriginal916418750eca0f0299436c8f1a00baec); ?>
<?php endif; ?>
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline"><?php echo e(__('storefront.delivery')); ?></span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">3</div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline"><?php echo e(__('storefront.confirm')); ?></span>
        </div>
    </div>

    <form wire:submit="submitOrder" class="grid grid-cols-1 md:grid-cols-5 lg:grid-cols-3 gap-6 md:gap-8">

        
        <div class="md:col-span-3 lg:col-span-2 space-y-6">

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'user','class' => 'text-xl store-text-primary w-5 h-5 ']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'user','class' => 'text-xl store-text-primary w-5 h-5 ']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.customer_information')); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.who_is_receiving')); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.name')); ?> *</label>
                        <input type="text" wire:model="name"
                            placeholder="<?php echo e(__('storefront.full_name')); ?>"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.phone')); ?> *</label>
                        <input type="text" wire:model="phone" name="phone"
                            placeholder="0XXX XX XX XX"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.email')); ?></label>
                        <input type="email" wire:model="email"
                            placeholder="<?php echo e(__('storefront.email_optional')); ?>"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'text-xl store-text-primary w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'text-xl store-text-primary w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.delivery_information')); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.where_to_deliver')); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allProvidersDead): ?>
                        <div class="sm:col-span-2 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4" role="delivery-unavailable">
                            <div class="flex items-start gap-3">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'information-circle','class' => 'text-xl text-amber-500 w-5 h-5 shrink-0 mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'information-circle','class' => 'text-xl text-amber-500 w-5 h-5 shrink-0 mt-0.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300"><?php echo e(__('storefront.delivery_unavailable_title')); ?></p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5"><?php echo e(__('storefront.delivery_unavailable_body')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasProviders): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSingleProvider): ?>
                            <div class="sm:col-span-2 flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-3">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'truck','class' => 'text-xl store-text-primary w-5 h-5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'truck','class' => 'text-xl store-text-primary w-5 h-5 shrink-0']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 dark:text-gray-500"><?php echo e(__('storefront.shipping_via')); ?></p>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate"><?php echo e($providers->first()->name); ?></p>
                                </div>
                            </div>
                            <input type="hidden" wire:model.live="selectedProvider" value="<?php echo e($providers->first()->id); ?>" />
                        <?php else: ?>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.company')); ?> *</label>
                                <?php if (isset($component)) { $__componentOriginal1e47f77404551aa8f8b0c91590b60327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e47f77404551aa8f8b0c91590b60327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.storefront.select','data' => ['options' => $providers,'optionValue' => 'id','optionLabel' => 'name','wire:model.live' => 'selectedProvider','search' => true,'searchPlaceholder' => ''.e(__('storefront.search_company')).'','placeholder' => ''.e(__('storefront.select_company')).'','icon' => 'business','role' => 'company-select']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('storefront.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($providers),'option-value' => 'id','option-label' => 'name','wire:model.live' => 'selectedProvider','search' => true,'search-placeholder' => ''.e(__('storefront.search_company')).'','placeholder' => ''.e(__('storefront.select_company')).'','icon' => 'business','role' => 'company-select']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $attributes = $__attributesOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $component = $__componentOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__componentOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedProvider'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo e(__('storefront.delivery_type')); ?></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="home" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'home','class' => 'text-2xl text-gray-500 dark:text-gray-400 w-8 h-8 mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'home','class' => 'text-2xl text-gray-500 dark:text-gray-400 w-8 h-8 mx-auto']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('storefront.home_delivery')); ?></p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="stopdesk" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'map-pin','class' => 'text-2xl text-gray-500 dark:text-gray-400  w-8 h-8 mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'map-pin','class' => 'text-2xl text-gray-500 dark:text-gray-400  w-8 h-8 mx-auto']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('storefront.stop_desk')); ?></p>
                                </div>
                            </label>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.state')); ?> *</label>
                        <?php if (isset($component)) { $__componentOriginal1e47f77404551aa8f8b0c91590b60327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e47f77404551aa8f8b0c91590b60327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.storefront.select','data' => ['options' => $states,'optionValue' => 'id','optionLabel' => 'name','optionCode' => 'state_code','wire:model.live' => 'state_id','search' => true,'searchPlaceholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_state')).'','disabled' => ! $hasProviders || ! $providerId || $states->isEmpty(),'icon' => 'map','role' => 'state-select']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('storefront.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($states),'option-value' => 'id','option-label' => 'name','option-code' => 'state_code','wire:model.live' => 'state_id','search' => true,'search-placeholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_state')).'','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $hasProviders || ! $providerId || $states->isEmpty()),'icon' => 'map','role' => 'state-select']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $attributes = $__attributesOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $component = $__componentOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__componentOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasProviders && ! $providerId): ?>
                            <p class="text-amber-600 dark:text-amber-400 text-xs mt-1.5"><?php echo e(__('storefront.select_company_first')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->delivery_type === 'home'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.city')); ?></label>
                            <?php if (isset($component)) { $__componentOriginal1e47f77404551aa8f8b0c91590b60327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e47f77404551aa8f8b0c91590b60327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.storefront.select','data' => ['options' => $cities,'optionValue' => 'id','optionLabel' => 'name','wire:model.live' => 'city_id','search' => true,'searchPlaceholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_city')).'','disabled' => ! $this->state_id,'icon' => 'location','role' => 'city-select']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('storefront.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cities),'option-value' => 'id','option-label' => 'name','wire:model.live' => 'city_id','search' => true,'search-placeholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_city')).'','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(! $this->state_id),'icon' => 'location','role' => 'city-select']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $attributes = $__attributesOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $component = $__componentOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__componentOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['city_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->delivery_type === 'stopdesk'): ?>
                        <div class="sm:col-span-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stopdesks->count() === 1): ?>
                                <?php $singleOffice = $stopdesks->first(); ?>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.delivery_office')); ?></label>
                                <div class="rounded-xl border border-[color-mix(in_srgb,var(--store-primary)_30%,transparent)] bg-[color-mix(in_srgb,var(--store-primary)_8%,transparent)] p-4" role="office-card">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center shrink-0">
                                            <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'business','class' => 'text-xl store-text-primary w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'business','class' => 'text-xl store-text-primary w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($singleOffice->external_code)): ?>
                                                    <span class="inline-flex items-center justify-center min-w-6 px-1.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 text-[11px] font-semibold leading-none tabular-nums align-middle mr-1.5"><?php echo e($singleOffice->external_code); ?></span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php echo e($singleOffice->name); ?>

                                            </p>
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 shrink-0">
                                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'checkmark-circle','class' => 'w-4 h-4 text-base']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'checkmark-circle','class' => 'w-4 h-4 text-base']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                                <?php echo e(__('storefront.deliver_to_this_office')); ?>

                                            </span>
                                        </div>
                                            <div class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($singleOffice->city?->name): ?>
                                                    <p class="flex items-center gap-1.5"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'location','class' => 'text-sm w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','class' => 'text-sm w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?> <?php echo e($singleOffice->city->name); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($singleOffice->address): ?>
                                                    <p class="flex items-center gap-1.5"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'map','class' => 'text-sm w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'map','class' => 'text-sm w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?> <?php echo e($singleOffice->address); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($singleOffice->phone): ?>
                                                    <p class="flex items-center gap-1.5" dir="ltr"><?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'call','class' => 'text-sm w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'call','class' => 'text-sm w-3.5 h-3.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?> <?php echo e($singleOffice->phone); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" value="<?php echo e($singleOffice->id); ?>" data-role="selected-office" />
                            <?php elseif($stopdesks->count() > 1): ?>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.select_stopdesk_point')); ?></label>
                                <?php if (isset($component)) { $__componentOriginal1e47f77404551aa8f8b0c91590b60327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e47f77404551aa8f8b0c91590b60327 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.storefront.select','data' => ['options' => $officeOptions,'optionValue' => 'value','optionLabel' => 'label','optionCode' => 'code','optionExtra' => 'extra','wire:model.live' => 'selectedStopdesk','search' => true,'searchPlaceholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_stopdesk_point')).'','icon' => 'business','role' => 'office-select']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('storefront.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($officeOptions),'option-value' => 'value','option-label' => 'label','option-code' => 'code','option-extra' => 'extra','wire:model.live' => 'selectedStopdesk','search' => true,'search-placeholder' => ''.e(__('storefront.search')).'','placeholder' => ''.e(__('storefront.select_stopdesk_point')).'','icon' => 'business','role' => 'office-select']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $attributes = $__attributesOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__attributesOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e47f77404551aa8f8b0c91590b60327)): ?>
<?php $component = $__componentOriginal1e47f77404551aa8f8b0c91590b60327; ?>
<?php unset($__componentOriginal1e47f77404551aa8f8b0c91590b60327); ?>
<?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedStopdesk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                
                                <div class="rounded-xl border px-4 py-3 text-sm
                                            bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700
                                            text-amber-700 dark:text-amber-400">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->state_id): ?>
                                        <?php echo e(__('storefront.select_state_for_desks')); ?>

                                    <?php else: ?>
                                        <?php echo e(__('storefront.no_desks_in_state')); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.address')); ?> *</label>
                            <textarea wire:model="address" rows="2"
                                placeholder="<?php echo e(__('storefront.address_placeholder')); ?>"
                                style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                       bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                       placeholder:text-gray-400 dark:placeholder:text-gray-500
                                       shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                       transition-all duration-200 resize-none"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.notes')); ?></label>
                            <textarea wire:model="notes" rows="2"
                            placeholder="<?php echo e(__('storefront.order_notes_optional')); ?>"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200 resize-none"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'banknotes','class' => 'text-xl store-text-primary w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'banknotes','class' => 'text-xl store-text-primary w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.payment_method')); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.payment_method_desc')); ?></p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payment_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mb-3"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="payment_method" value="<?php echo e($method); ?>" class="peer sr-only">
                            <div class="border-2 rounded-xl p-4 flex items-center gap-3 peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => $method === 'cod' ? 'banknotes' : 'credit-card','class' => 'text-2xl text-gray-500 dark:text-gray-400 w-8 h-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($method === 'cod' ? 'banknotes' : 'credit-card'),'class' => 'text-2xl text-gray-500 dark:text-gray-400 w-8 h-8']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <?php echo e($method === 'cod' ? __('storefront.payment_on_delivery') : ucfirst($method)); ?>

                                    </p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($method === 'cod'): ?>
                                        <p class="text-xs text-gray-400 dark:text-gray-500"><?php echo e(__('storefront.pay_on_delivery')); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="md:col-span-2 lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 sm:p-6 shadow-sm md:sticky md:top-24">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center shrink-0">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'bag','class' => 'text-xl store-text-primary w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bag','class' => 'text-xl store-text-primary w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white leading-tight"><?php echo e(__('storefront.order_summary')); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?php echo e($cartCount); ?> <?php echo e(__('storefront.items')); ?></p>
                    </div>
                    <span class="shrink-0 inline-flex items-center justify-center min-w-6 h-6 px-2 rounded-full store-bg-primary text-white text-xs font-semibold tabular-nums">
                        <?php echo e($cartCount); ?>

                    </span>
                </div>

                <div class="mb-4 -me-1 max-h-64 overflow-y-auto sf-scroll ps-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3 py-2.5 <?php echo e(! $loop->last ? 'border-b border-gray-100 dark:border-gray-700/60' : ''); ?>">
                            <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['product_name']); ?>"
                                 class="w-10 h-10 rounded-lg object-cover border border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 shrink-0"
                                 onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['product_name']); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['variant_name']): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e($item['variant_name']); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="flex flex-col items-end text-right shrink-0 ps-2">
                                <p class="text-sm font-medium text-gray-900 dark:text-white tabular-nums"><?php echo e(currency($item['price'] * $item['quantity'])); ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 tabular-nums">&times; <?php echo e($item['quantity']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4"><?php echo e(__('storefront.cart_is_empty')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.subtotal')); ?></span>
                        <span class="font-medium text-gray-900 dark:text-white tabular-nums"><?php echo e(currency($cartSubtotal)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.shipping')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shippingInfo['provider_name'] ?? null): ?>
                                <span class="text-gray-400 dark:text-gray-500">· <?php echo e($shippingInfo['provider_name']); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                        <span class="font-medium text-gray-900 dark:text-white tabular-nums">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shippingInfo['is_free'] ?? false): ?>
                                <span class="text-emerald-600 dark:text-emerald-400"><?php echo e(__('storefront.free')); ?></span>
                            <?php elseif(($shippingInfo['available'] ?? true)): ?>
                                <?php echo e(currency($shippingInfo['cost'] ?? 0)); ?>

                            <?php else: ?>
                                <span class="text-red-500 dark:text-red-400"><?php echo e(__('storefront.not_available')); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="rounded-xl store-bg-primary-soft border border-[color-mix(in_srgb,var(--store-primary)_25%,transparent)] mt-4 px-4 py-3.5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.total')); ?></span>
                        <span class="text-xl font-bold store-text-primary tabular-nums leading-none">
                            <?php echo e(currency($cartSubtotal + ($shippingInfo['cost'] ?? 0))); ?>

                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 w-full store-btn-primary text-white font-semibold py-3.5 px-4 rounded-xl transition disabled:opacity-50 flex items-center justify-center gap-2"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submitOrder" class="flex items-center gap-2 text-white">
                        <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'lock-closed','class' => 'text-lg  w-5 h-5  mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock-closed','class' => 'text-lg  w-5 h-5  mx-auto']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                        <?php echo e(__('storefront.place_order')); ?>

                    </span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <?php echo e(__('storefront.placing')); ?>

                    </span>
                </button>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <?php if (isset($component)) { $__componentOriginal78f5a7347bd00ba3623a459cd340078c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78f5a7347bd00ba3623a459cd340078c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.edz.icon','data' => ['name' => 'shield-check','class' => 'text-base w-5 h-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('edz.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield-check','class' => 'text-base w-5 h-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $attributes = $__attributesOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__attributesOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78f5a7347bd00ba3623a459cd340078c)): ?>
<?php $component = $__componentOriginal78f5a7347bd00ba3623a459cd340078c; ?>
<?php unset($__componentOriginal78f5a7347bd00ba3623a459cd340078c); ?>
<?php endif; ?>
                    <span><?php echo e(__('storefront.secure_checkout')); ?></span>
                </div>
            </div>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\order-form.blade.php ENDPATH**/ ?>