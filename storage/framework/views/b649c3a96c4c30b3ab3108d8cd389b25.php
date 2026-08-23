<?php

use App\Domains\Cart\Services\CartService;
use App\Domains\Order\Services\OrderAssignmentService;
use App\Domains\Plan\Services\FeatureUsageService;
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
use Illuminate\Support\Facades\Validator;

?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo e(__('storefront.checkout')); ?>

        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <?php echo e(__('storefront.complete_your_order')); ?>

        </p>
    </div>

    <?php
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems(currentStoreId())->toArray();
        $cartCount = $cartService->getCount(currentStoreId());
        $cartSubtotal = $cartService->getSubtotal(currentStoreId());

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

        $states = State::active()->orderBy('name')->get();
        $cities = $this->state_id ? City::where('state_id', $this->state_id)->active()->orderBy('name')->get() : collect();
        $stopdesks = ($this->state_id && $this->delivery_type === 'stopdesk')
            ? StopdeskPoint::where('store_id', currentStoreId())->where('state_id', $this->state_id)->where('is_active', true)->get()
            : collect();
        $calculator = app(ShippingCostCalculator::class);
        $shippingInfo = $calculator->calculate(currentStore(), $this->state_id ?: null, $this->city_id ?: null, $cartSubtotal);
        $paymentMethods = ['cod'];
    ?>

    
    <div class="mb-6">
        <a href="<?php echo e(route('storefront.home', ['store' => currentStore()?->slug ?? ''])); ?>"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
            <ion-icon name="arrow-back-outline" class="text-base"></ion-icon>
            <?php echo e(__('storefront.back_to_store')); ?>

        </a>
    </div>

    
    <div class="mb-8 flex items-center justify-between max-w-md">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">
                <ion-icon name="checkmark-outline" class="text-base"></ion-icon>
            </div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline"><?php echo e(__('storefront.cart')); ?></span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 store-bg-primary"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full store-bg-primary text-white flex items-center justify-center text-sm font-bold">2</div>
            <span class="ms-2 text-sm font-medium store-text-primary hidden sm:inline"><?php echo e(__('storefront.delivery')); ?></span>
        </div>
        <div class="flex-1 h-0.5 mx-2 sm:mx-3 bg-gray-200 dark:bg-gray-700"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
            <span class="ms-2 text-sm font-medium text-gray-400 dark:text-gray-500 hidden sm:inline"><?php echo e(__('storefront.confirm')); ?></span>
        </div>
    </div>

    <form wire:submit="submitOrder" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        
        <div class="lg:col-span-2 space-y-6">

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <ion-icon name="person-outline" class="text-xl store-text-primary"></ion-icon>
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
                        <input type="text" wire:model="phone"
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
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <ion-icon name="car-outline" class="text-xl store-text-primary"></ion-icon>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.delivery_information')); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.where_to_deliver')); ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.state')); ?> *</label>
                        <select wire:model.live="state_id"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                            <option value=""><?php echo e(__('storefront.select_state')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($state->id); ?>"><?php echo e($state->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['state_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.city')); ?></label>
                        <select wire:model.live="city_id"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                            <option value=""><?php echo e(__('storefront.select_city')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($city->id); ?>"><?php echo e($city->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"><?php echo e(__('storefront.delivery_type')); ?></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="home" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="home-outline" class="text-2xl text-gray-500 dark:text-gray-400"></ion-icon>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('storefront.home_delivery')); ?></p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="delivery_type" value="stopdesk" class="peer sr-only">
                                <div class="border-2 rounded-xl p-4 text-center peer-checked:border-[var(--store-primary)] peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_10%,transparent)] dark:peer-checked:bg-[color-mix(in_srgb,var(--store-primary)_20%,transparent)] border-gray-200 dark:border-gray-600 transition">
                                    <ion-icon name="location-outline" class="text-2xl text-gray-500 dark:text-gray-400"></ion-icon>
                                    <p class="text-sm mt-1 font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('storefront.stop_desk')); ?></p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->delivery_type === 'stopdesk' && $stopdesks->count()): ?>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5"><?php echo e(__('storefront.select_stopdesk_point')); ?> *</label>
                            <select wire:model="selectedStopdesk"
                            style="--tw-ring-color: color-mix(in srgb, var(--store-primary) 20%, transparent)"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600
                                   bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm
                                   shadow-sm focus:outline-none focus:ring-2 focus:border-[var(--store-primary)]
                                   transition-all duration-200">
                                <option value=""><?php echo e(__('storefront.select_point')); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stopdesks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($point->id); ?>"><?php echo e($point->name); ?> — <?php echo e($point->address); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedStopdesk'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 dark:text-red-400 text-xs mt-1.5"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->delivery_type === 'home'): ?>
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
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl store-bg-primary-soft flex items-center justify-center">
                        <ion-icon name="cash-outline" class="text-xl store-text-primary"></ion-icon>
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
                                <ion-icon name="<?php echo e($method === 'cod' ? 'cash-outline' : 'card-outline'); ?>" class="text-2xl text-gray-500 dark:text-gray-400"></ion-icon>
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

        
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('storefront.order_summary')); ?></h2>

                <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center gap-3">
                            <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['product_name']); ?>"
                                 class="w-10 h-10 rounded-lg object-cover bg-gray-100 dark:bg-gray-700 shrink-0"
                                 onerror="this.onerror=null;this.src='<?php echo e(asset('img/icons/noimg.png')); ?>'">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><?php echo e($item['product_name']); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['variant_name']): ?>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($item['variant_name']); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white tabular-nums"><?php echo e(currency($item['price'] * $item['quantity'])); ?></p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">× <?php echo e($item['quantity']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-4"><?php echo e(__('storefront.cart_is_empty')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.subtotal')); ?> (<?php echo e($cartCount); ?> <?php echo e(__('storefront.items')); ?>)</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e(currency($cartSubtotal)); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('storefront.shipping')); ?></span>
                        <span class="font-medium text-gray-900 dark:text-white">
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

                <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900 dark:text-white"><?php echo e(__('storefront.total')); ?></span>
                        <span class="text-xl font-bold store-text-primary">
                            <?php echo e(currency($cartSubtotal + ($shippingInfo['cost'] ?? 0))); ?>

                        </span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-6 w-full store-btn-primary text-white font-semibold py-3.5 px-4 rounded-xl transition disabled:opacity-50 flex items-center justify-center gap-2"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="submitOrder" class="flex items-center gap-2">
                        <ion-icon name="lock-closed-outline" class="text-lg"></ion-icon>
                        <?php echo e(__('storefront.place_order')); ?>

                    </span>
                    <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <?php echo e(__('storefront.placing')); ?>

                    </span>
                </button>

                <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <ion-icon name="shield-checkmark-outline" class="text-base"></ion-icon>
                    <span><?php echo e(__('storefront.secure_checkout')); ?></span>
                </div>
            </div>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\edzeery\resources\views\livewire\storefront\order-form.blade.php ENDPATH**/ ?>