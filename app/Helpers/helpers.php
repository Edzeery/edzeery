<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Setting;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Support\StoreResolver;
use App\Support\StoreScopedCache;
use Illuminate\Support\Facades\Auth;

if (! function_exists('user')) {
    function user(): ?\App\Models\User
    {
        return Auth::check() ? auth()->user() : null;
    }
}

if (! function_exists('currentGuard')) {

    function currentGuard(): string
    {
        if (request()->is('merchant/*')) {
            return 'merchant';
        }

        return 'web';
    }
}

if (! function_exists('userHasStoreAccess')) {
    function userHasStoreAccess(User $user, Store $store): bool
    {
        return $user->stores()
            ->where('stores.id', $store->id)
            ->exists();
    }
}

// 🔹 هل المستخدم داخل متجر حاليًا؟
if (! function_exists('hasStoreContext')) {
    function hasStoreContext(): bool
    {
        return currentStore() !== null;
    }
}

// 🔹 هل المستخدم عضو في المتجر الحالي؟
if (! function_exists('isStoreMember')) {
    function isStoreMember(?User $user = null): bool
    {
        $user ??= user();

        if (! $user) {
            return false;
        }

        return $user->storeMemberships()->where('user_id', $user->id)->exists();
    }
}
// 🔹 هل لديه دور متجر معيّن؟
if (! function_exists('hasStoreRole')) {
    function hasStoreRole(string|StoreRoleEnum $role, ?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        $value = $role instanceof StoreRoleEnum ? $role->value : $role;
        $membership = currentMembership();

        // Decision #6: membership-scoped role takes precedence (per-store isolation)
        if ($membership && $membership->role) {
            return $membership->role === $value;
        }

        return $user->hasRole($value, 'merchant');
    }
}

if (! function_exists('isStoreOwner')) {
    function isStoreOwner(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::OWNER->value, $user);
    }
}

if (! function_exists('isStoreAdmin')) {
    function isStoreAdmin(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::ADMIN->value, $user);
    }
}

if (! function_exists('isStoreManager')) {
    function isStoreManager(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::MANAGER->value, $user);
    }
}

if (! function_exists('isStoreStaff')) {
    function isStoreStaff(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::STAFF->value, $user);
    }
}

// 🔥 هل يملك صلاحية داخل المتجر الحالي؟
if (! function_exists('canStore')) {
    function canStore(string $permission, ?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        // Per-request memoization: permission checks run per cell/row/sidebar
        // item on grids, turning a simple check into hundreds of role +
        // membership + permission queries. Results are keyed by the resolved
        // user id AND the resolved store, so long-running processes
        // (Octane/queue) never leak a result between users or stores, and the
        // memo resets as soon as a different user or store is resolved on the
        // same worker — a user switching stores mid-request (decision #6)
        // must never reuse another store's permission result.
        //
        // The memo lives in StoreScopedCache so a queue worker can flush it at
        // job boundaries (PHASE 38-C) — a function-local static would be
        // unreachable from the orchestrator.
        if (StoreScopedCache::$canStoreUser !== (string) $user->getAuthIdentifier()) {
            StoreScopedCache::$canStoreUser = (string) $user->getAuthIdentifier();
            StoreScopedCache::$canStore = [];
        }

        // Store id is cheap here: StoreResolver is cached in the
        // request-scoped StoreContext after its first resolution.
        $storeKey = (string) currentStoreId();
        $key = $storeKey . '|' . $permission;

        if (array_key_exists($key, StoreScopedCache::$canStore)) {
            return StoreScopedCache::$canStore[$key];
        }

        // Super Admin / Platform Admin bypass (resolved once per user+request).
        if (! array_key_exists('__super_admin__', StoreScopedCache::$canStore)) {
            StoreScopedCache::$canStore['__super_admin__'] = $user->hasAnyRoleForGuard(['super_admin', 'admin'], 'web');
        }
        if (StoreScopedCache::$canStore['__super_admin__']) {
            return StoreScopedCache::$canStore[$key] = true;
        }

        $membership = currentMembership();

        // Decision #6: membership-scoped custom permissions take precedence.
        // If the membership has explicitly stored permissions (non-empty set),
        // only those apply — isolation across stores is guaranteed.
        if ($membership) {
            $stored = $membership->permissionNames();
            if (! empty($stored)) {
                return StoreScopedCache::$canStore[$key] = in_array($permission, $stored, true);
            }
        }

        return StoreScopedCache::$canStore[$key] = $user->can($permission, 'merchant');
    }
}

if (! function_exists('storeCan')) {
    function storeCan(string $permission): bool
    {
        return canStore($permission);
    }
}

// إعادة التكليف: محجوزة عن الموظف (STAFF) مهما مُنحت له أو مباشرة عبر العضوية.
// المالك والأدمن مؤهلون دائمًا؛ أي دور آخر (مدير/عضوية مخصصة) عبر order.assign.
if (! function_exists('canReassignOrders')) {
    function canReassignOrders(?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        if (isStoreStaff($user)) {
            return false;
        }

        if (isStoreOwner($user) || isStoreAdmin($user)) {
            return true;
        }

        return canStore(\App\Enums\Store\StorePermissionEnum::ORDER_ASSIGN->value, $user);
    }
}

// الحذف النهائي: المالك دائمًا، أو من يملك صلاحية order.delete.final صريحة
// (لا يستمدها الأدمن/المدير/الموظف من دورهم).
if (! function_exists('canFinalDeleteOrders')) {
    function canFinalDeleteOrders(?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        if (isStoreOwner($user)) {
            return true;
        }

        return canStore(\App\Enums\Store\StorePermissionEnum::ORDER_DELETE_FINAL->value, $user);
    }
}
/**
 * 4️⃣ Helpers خاصة بالـ MANAGER (Scoped Team)
 * هنا نستغل invited_by كما ذكرت 👌
 */

// 🔹 هل يدير هذا العضو؟

if (! function_exists('managesMember')) {
    function managesMember(StoreMembership $targetMembership, ?User $actor = null): bool
    {
        $actor ??= user();

        if (! $actor) {
            return false;
        }

        $currentStoreId = currentStoreId();

        if (! $currentStoreId || $targetMembership->store_id !== $currentStoreId) {
            return false;
        }

        // OWNER & ADMIN يديرون الجميع
        if (isStoreOwner($actor) || isStoreAdmin($actor)) {
            return true;
        }

        // MANAGER فقط فريقه
        if (
            isStoreManager($actor) &&
            $targetMembership->supervisor_membership_id === $actor->storeMembership(currentStore())?->id
        ) {
            return true;
        }

        return false;
    }
}

/**
 * 5️⃣ Helpers جاهزة للـ Policies (تختصر الدنيا)
 */
// 🔹 إدارة الفريق

if (! function_exists('canManageTeam')) {
    function canManageTeam(): bool
    {
        return
            isStoreOwner() ||
            isStoreAdmin() ||
            canStore(\App\Enums\Store\StorePermissionEnum::STORE_TEAM_MANAGE->value) ||
            canStore(\App\Enums\Store\StorePermissionEnum::TEAM_MANAGE_OWN->value);
    }
}

// 🔹 رؤية معلومات الاشتراك/الفوترة لمتجر محدد (المالك أو من يملك إدارة الفوترة)
if (! function_exists('canViewStoreBilling')) {
    function canViewStoreBilling(Store $store, ?User $user = null): bool
    {
        $user ??= user();

        if (! $user) {
            return false;
        }

        if ($store->user_id === $user->id) {
            return true;
        }

        return (bool) $user->storeMembership($store)?->can(\App\Enums\Store\StorePermissionEnum::STORE_BILLING_MANAGE->value);
    }
}

// 🔹 حذف / تعديل عضو
if (! function_exists('canModifyMember')) {
    function canModifyMember(StoreMembership $membership): bool
    {
        // لا أحد يلمس OWNER
        if ($membership->isOwner()) {
            return false;
        }

        return managesMember($membership);
    }
}
/**
 * 6️⃣ Helpers للـ UI (Filament)
 */

// 🔹 عرض أو إخفاء Tabs / Actions
if (! function_exists('showIfStoreCan')) {
    function showIfStoreCan(string $permission): bool
    {
        return hasStoreContext() && canStore($permission);
    }
}

if (! function_exists('currentStore')) {
    function currentStore(): ?Store
    {
        return StoreResolver::resolve();
    }
}

if (! function_exists('currentStoreId')) {
    function currentStoreId(): ?string
    {
        return currentStore()?->id;
    }
}

if (! function_exists('currentMembership')) {
    function currentMembership(): ?StoreMembership
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $store = currentStore();
        if (! $store) {
            return null;
        }

        // Reuse the membership already resolved by EnsureStoreMembership
        // (bound to the container for store-scoped merchant routes) instead
        // of re-querying it on every permission check. Same active membership
        // is returned, so the per-instance permission cache kicks in.
        if (app()->bound('currentMembership')) {
            $bound = app('currentMembership');
            if (
                $bound instanceof StoreMembership
                && (string) $bound->store_id === (string) $store->id
                && (int) $bound->user_id === (int) $user->id
            ) {
                return $bound;
            }
        }

        return $user->storeMembership($store);
    }
}

if (! function_exists('membership')) {
    function membership(?\App\Models\User $user = null): ?StoreMembership
    {
        $user ??= Auth::user();

        return $user ? currentMembership() : null;
    }
}

if (! function_exists('currentMembershipStore')) {
    function currentMembershipStore(): ?Store
    {
        return currentMembership()?->store;
    }
}

if (! function_exists('generatecode')) {
    function generatecode($prefix = 'PRO-', $suffix = null): string
    {
        return $prefix . uniqid() . $suffix;
    }
}

if (! function_exists('uploadPath')) {
    function uploadPath($value): ?string
    {
        return is_array($value) ? ($value[0] ?? null) : $value;
    }
}

if (! function_exists('countries')) {
    function countries(string $name = 'name'): array
    {
        return Country::pluck($name, 'id')->toArray();
    }
}

if (! function_exists('country')) {
    function country($id)
    {
        return Country::findOrFail($id);
    }
}

if (! function_exists('states')) {
    function states(string $name = 'name'): array
    {
        if (isRTL()) {
            $name = 'ar_name';
        }

        return State::pluck($name, 'id')->toArray();
    }
}

if (! function_exists('state')) {
    function state($id)
    {
        return State::findOrFail($id);
    }
}

if (! function_exists('canDo')) {
    function canDo(string $permission): bool
    {
        return auth()->check() && auth()->user()->can($permission);
    }
}

if (! function_exists('userRole')) {
    function userRole(): ?string
    {
        $user = user();
        if (! $user) {
            return null;
        }

        // استخدام Spatie Roles
        $roles = $user->getRoleNames();

        // توحيد الأدوار الإدارية
        if ($roles->intersect(['super_admin', 'admin'])->isNotEmpty()) {
            return 'admin';
        }

        return $roles->first();
    }
}

if (! function_exists('generateBreadcrumb')) {
    function generateBreadcrumb(): array
    {
        $segments = request()->segments();
        $breadcrumbs = [];

        if (isset($segments[0]) && $segments[0] === 'merchant') {
            if (count($segments) === 1 || (count($segments) === 2 && $segments[1] === 'dashboard')) {
                return [];
            }

            $total = count($segments);
            for ($i = 1; $i < $total; $i++) {
                $parts = array_slice($segments, 1, $i + 1);
                $candidateKey = implode('.', $parts);

                $label = __("breadcrumbs.$candidateKey");
                if ($label === "breadcrumbs.$candidateKey") {
                    $seg = $segments[$i];
                    $label = __("breadcrumbs.$seg");
                    if ($label === "breadcrumbs.$seg") {
                        $label = ucfirst(str_replace(['-', '_'], ' ', $seg));
                    }
                }

                $urlParts = array_slice($segments, 0, $i + 1);
                $url = url(implode('/', $urlParts));

                $breadcrumbs[] = ['label' => $label, 'url' => $url];
            }
        }

        return $breadcrumbs;
    }
}
if (! function_exists('system_setting')) {
    function system_setting($key = null, $default = null)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Setting::pluck('value', 'key')->toArray();
        }

        if (is_null($key)) {
            return $cache;
        }

        return $cache[$key] ?? $default;
    }
}

if (! function_exists('lowercase')) {
    function lowercase(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }
}

if (! function_exists('uppercase')) {
    function uppercase(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}

if (! function_exists('FirstLetterUppercase')) {
    function FirstLetterUppercase(string $value): string
    {
        return mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($value, 1, null, 'UTF-8');
    }
}


if (! function_exists('formatCurrency')) {
    function formatCurrency(float $amount, string $currency = 'DZD'): string
    {
        return number_format($amount, 2, '.', ',') . ' ' .  __('currency.' . $currency);
    }
}

if (! function_exists('formatCurrencyWithoutSymbol')) {
    function formatCurrencyWithoutSymbol(float $amount): string
    {
        return number_format($amount, 2, '.', ',');
    }

}


require __DIR__ . '/Language_Translation.php';
require __DIR__ . '/subscription.php';
require __DIR__ . '/userHelper.php';
require __DIR__ . '/cart_notice.php';
require __DIR__ . '/store_helper.php';
require __DIR__ . '/order_helper.php';
