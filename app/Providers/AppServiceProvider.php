<?php

namespace App\Providers;

use App\Domains\Billing\Contracts\PaymentGatewayContract;
use App\Domains\Billing\Events\PaymentSucceeded;
use App\Domains\Billing\Gateways\ChargilyGateway;
use App\Domains\Billing\Gateways\MockGateway;
use App\Domains\Billing\Listeners\ActivateSubscriptionOnPaymentSucceeded;
use App\Models\billing\Subscription;
use App\Models\Finance\DebtPayment;
use App\Models\Orders\Order;
use App\Models\Products\Product;
use App\Models\Stores\Store;
use App\Observers\Finance\DebtPaymentObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\StoreObserver;
use App\Observers\SubscriptionObserver;
use App\Support\StoreContext;
use App\Support\StoreScopedCache;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\URL;
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

        $this->app->bind(PaymentGatewayContract::class, function () {
            return match (config('billing.gateway', 'mock')) {
                'chargily' => new ChargilyGateway(
                    apiKey: config('services.chargily.api_key', ''),
                    secretKey: config('services.chargily.secret_key', ''),
                    mode: config('services.chargily.mode', 'test'),
                ),
                default => new MockGateway,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when behind a TLS-terminating proxy (Cloudflare, load balancer, etc.)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Event-Listener bindings
        $this->app->events->listen(PaymentSucceeded::class, ActivateSubscriptionOnPaymentSucceeded::class);

        foreach ([JobProcessing::class, JobProcessed::class, JobFailed::class] as $event) {
            $this->app->events->listen($event, function (object $payload) {
                /*
                 * Only isolate at real queue-worker boundaries. Jobs dispatched
                 * inline on the sync driver (dispatchSync / tests) share the
                 * enclosing request's context by design and must not wipe the
                 * active StoreContext. Scheduled commands run as their own
                 * subprocess, so their statics start fresh naturally.
                 */
                if (strtolower((string) ($payload->connectionName ?? 'sync')) === 'sync') {
                    return;
                }

                StoreScopedCache::flush();
            });
        }

        View::composer('*', function ($view) {
            $view->with('user', user());
            $store = currentStore();
            $view->with('store', $store);
            $view->with('currency', $store?->settings?->currency ?? 'DZD');
            $view->with('theme', user_setting('theme') ?? 'light');
            $view->with('lang', getCurrentLocale());
            $view->with('languages', getLanguages() ?? []);
            $view->with('isRtl', isRtl());
            $view->with('alignment', isRTL() ? 'left-0' : 'right-0');
            $view->with('iconPosition', isRTL() ? 'left-4' : 'right-4');
            $view->with('dir', setRTL());
            $view->with('algin', algin());
        });

        // 🔤 إعداد اللغات
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(
                    getLanguageCodes()
                )
                ->labels(getLanguagesArray())
                ->flags(getLanguagesArrayFlags())
                ->circular();
        });

        Store::observe(StoreObserver::class);
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
        DebtPayment::observe(DebtPaymentObserver::class);
        Subscription::observe(SubscriptionObserver::class);

        Paginator::useBootstrapFive();
    }
}
