<?php

use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Support\StoreResolver;

if (!function_exists('user')) {
    function user(): ?\App\Models\User
    {
        return Auth::check() ? auth()->user() : null;
    }
}

if (!function_exists('currentGuard')) {

    function currentGuard(): string
    {
        if (request()->is('merchant/*')) {
            return 'merchant';
        }

        return 'web';
    }
}


if (!function_exists('userHasStoreAccess')) {
    function userHasStoreAccess(User $user, Store $store): bool
    {
        return $user->stores()
            ->where('stores.id', $store->id)
            ->exists();
    }
}

// 🔹 هل المستخدم داخل متجر حاليًا؟
if (!function_exists('hasStoreContext')) {
    function hasStoreContext(): bool
    {
        return currentStore() !== null;
    }
}

// 🔹 هل المستخدم عضو في المتجر الحالي؟
if (!function_exists('isStoreMember')) {
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
if (!function_exists('hasStoreRole')) {
    function hasStoreRole(string | StoreRoleEnum $role, ?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        return $user->hasRole($role instanceof StoreRoleEnum ? $role->value : $role, 'merchant');
    }
}

if (!function_exists('isStoreOwner')) {
    function isStoreOwner(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::OWNER->value, $user);
    }
}

if (!function_exists('isStoreAdmin')) {
    function isStoreAdmin(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::ADMIN->value, $user);
    }
}

if (!function_exists('isStoreManager')) {
    function isStoreManager(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::MANAGER->value, $user);
    }
}

if (!function_exists('isStoreStaff')) {
    function isStoreStaff(?User $user = null): bool
    {
        return hasStoreRole(\App\Enums\Store\StoreRoleEnum::STAFF->value, $user);
    }
}

// 🔥 هل يملك صلاحية داخل المتجر الحالي؟
if (!function_exists('canStore')) {
    function canStore(string $permission, ?User $user = null): bool
    {
        $user ??= user();

        if (! $user || ! hasStoreContext()) {
            return false;
        }

        // Super Admin / Platform Admin bypass
        if ($user->hasAnyRoleForGuard(['super_admin', 'admin'], 'web')) {
            return true;
        }

        return $user->can($permission, 'merchant');
    }
}

if (!function_exists('storeCan')) {
    function storeCan(string $permission): bool
    {
        return canStore($permission);
    }
}
/**
 * 4️⃣ Helpers خاصة بالـ MANAGER (Scoped Team)
 * هنا نستغل invited_by كما ذكرت 👌
 */

// 🔹 هل يدير هذا العضو؟

if (!function_exists('managesMember')) {
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
            $targetMembership->invited_by === $actor->id
        ) {
            return true;
        }

        return false;
    }
}

/**
 * 5️⃣ Helpers جاهزة للـ Policies (تختصر الدنيا)
 */
//🔹 إدارة الفريق

if (!function_exists('canManageTeam')) {
    function canManageTeam(): bool
    {
        return
            isStoreOwner() ||
            isStoreAdmin() ||
            canStore(\App\Enums\Store\StorePermissionEnum::STORE_TEAM_MANAGE->value);
    }
}

//🔹 حذف / تعديل عضو
if (!function_exists('canModifyMember')) {
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

//🔹 عرض أو إخفاء Tabs / Actions
if (!function_exists('showIfStoreCan')) {
    function showIfStoreCan(string $permission): bool
    {
        return hasStoreContext() && canStore($permission);
    }
}


if (!function_exists('currentStore')) {
    function currentStore(): ?Store
    {
        return StoreResolver::resolve();
    }
}

if (!function_exists('currentStoreId')) {
    function currentStoreId(): ?string
    {
        return currentStore()?->id;
    }
}

if (!function_exists('currentMembership')) {
    function currentMembership(): ?StoreMembership
    {
        $store = currentStore();
        $user = auth()->user();
        return $user && $store ? $user->storeMembership($store) : null;
    }
}

if (!function_exists('membership')) {
    function membership(?\App\Models\User $user = null): ?StoreMembership
    {
        $user ??= Auth::user();
        return $user ? currentMembership() : null;
    }
}

if (!function_exists('currentMembershipStore')) {
    function currentMembershipStore(): ?Store
    {
        return currentMembership()?->store;
    }
}

if (!function_exists('generatecode')) {
    function generatecode($prefix = 'PRO-', $suffix = null): string
    {
        return $prefix . uniqid() . $suffix;
    }
}

if (!function_exists('uploadPath')) {
    function uploadPath($value): ?string
    {
        return is_array($value) ? ($value[0] ?? null) : $value;
    }
}

if (!function_exists('countries')) {
    function countries(string $name = 'name'): array
    {
        return Country::pluck($name, 'id')->toArray();
    }
}

if (!function_exists('country')) {
    function country($id)
    {
        return Country::findOrFail($id);
    }
}

if (!function_exists('states')) {
    function states(string $name = 'name'): array
    {
        if (isRTL()) {
            $name = 'ar_name';
        }
        return State::pluck($name, 'id')->toArray();
    }
}

if (!function_exists('state')) {
    function state($id)
    {
        return State::findOrFail($id);
    }
}

if (!function_exists('canDo')) {
    function canDo(string $permission): bool
    {
        return auth()->check() && auth()->user()->can($permission);
    }
}

if (!function_exists('userRole')) {
    function userRole(): ?string
    {
        $user = user();
        if (! $user) return null;

        // استخدام Spatie Roles
        $roles = $user->getRoleNames();

        // توحيد الأدوار الإدارية
        if ($roles->intersect(['super_admin', 'admin'])->isNotEmpty()) {
            return 'admin';
        }

        return $roles->first();
    }
}

if (!function_exists('generateBreadcrumb')) {
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
if (!function_exists('system_setting')) {
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





require __DIR__ . '/IconHelper.php';
require __DIR__ . '/Language_Translation.php';
require __DIR__ . '/subscription.php';
require __DIR__ . '/userHelper.php';
require __DIR__ . '/cart_notice.php';
