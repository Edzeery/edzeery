<?php

namespace App\Http\Middleware\Merchant\Store;

use App\Enums\Store\StoreStatusEnum;
use Closure;
use Illuminate\Http\Request;

class EnsureStoreIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $store = currentStore();
        // جلب أحدث حالة للمتجر من جدول StoreStatusHistory
        $latestStatusHistory = $store->latestStatus(); // دالة في موديل Store


        // إذا كانت الحالة غير مسموح بها
        if (in_array($latestStatusHistory, [
            StoreStatusEnum::PENDING,
            StoreStatusEnum::CLOSED,
            StoreStatusEnum::SUSPENDED,
        ], true) ) {

            // إذا لم نكن في صفحة حالة المتجر، نعيد التوجيه
            if (! $request->routeIs('filament.merchant.settings.pages.status')) {
                return redirect()->route('filament.merchant.settings.pages.status', $store);
            }
        }

        return $next($request);
    }
}
