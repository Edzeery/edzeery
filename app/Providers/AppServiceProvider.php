<?php

namespace App\Providers;

use App\Models\Orders\Order;
use App\Models\billing\Subscription;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\StoreObserver;
use App\Observers\SubscriptionObserver;
use App\Models\Finance\DebtPayment;
use App\Observers\Finance\DebtPaymentObserver;
use App\Support\StoreContext;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StoreContext::class);
        $this->app->singleton(\App\Domains\Shipping\Services\ShippingCostCalculator::class);
        $this->app->singleton(\App\Domains\Cart\Services\CartService::class);
        $this->app->singleton(\App\Domains\Order\Services\OrderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('user', user());
            $store = currentStore();
            $view->with('store', $store);
            $view->with('currency', $store ? ($store->settings->currency ?? 'DZD') : 'DZD');
            $view->with('theme', user_setting('theme') ?? 'light');
            $view->with('lang',  getCurrentLocale());
            $view->with('languages',  getLanguages() ?? []);
            $view->with('isRtl',  isRtl());
            $view->with('dir',  setRTL());
            $view->with('algin',  algin());
        });

        // 🔤 إعداد اللغات
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(
                    getLanguageCodes()
                )
                ->labels(getLanguagesArray())
                ->flags(getLanguagesArrayFlags())
                ->circular()


            ;
        });
 
        Store::observe(StoreObserver::class);
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
        DebtPayment::observe(DebtPaymentObserver::class);

        Paginator::useBootstrapFive();

    }
}
