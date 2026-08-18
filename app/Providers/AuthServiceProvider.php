<?php

namespace App\Providers;

use App\Models\Products\Product;
use App\Models\Products\ProductOption;
use App\Models\Orders\Order;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Policies\ImportPolicy;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Filament\Actions\Imports\Models\Import;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Store::class => \App\Policies\StorePolicy::class,
        StoreMembership::class => \App\Policies\StoreMembershipPolicy::class,
        Product::class => \App\Policies\ProductPolicy::class,
        Order::class => \App\Policies\OrderPolicy::class,
        ProductOption::class => \App\Policies\ProductOptionPolicy::class,
        Import::class => ImportPolicy::class,
    ];
    protected $listen = [
        \App\Domains\Billing\Events\SubscriptionActivated::class => [
            \App\Domains\Billing\Listeners\HandleSubscriptionActivation::class,
        ],

        \App\Domains\Billing\Events\SubscriptionCreated::class => [
            \App\Domains\Billing\Listeners\HandleSubscriptionCreated::class,
        ],

        \App\Domains\Billing\Events\SubscriptionExpired::class => [
            \App\Domains\Billing\Listeners\HandleSubscriptionExpired::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/storefront.php'));
    }
}
