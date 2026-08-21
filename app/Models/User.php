<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Platform\UserRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements FilamentUser, HasTenants

{

    use HasUlids, HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_id',
        'state_id',
        'city_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted()
    {

        static::created(function (User $user) {

            // إنشاء اشتراك trial افتراضي
            $defaultPlan = Plan::where('is_default', true)->first();
            if ($defaultPlan) {
                $defaultPlanPrice = $defaultPlan->prices()->where('billing_period', 'monthly')->first();
                if ($defaultPlanPrice) {
                    $user->subscriptions()->create([

                        'user_id' => $user->id,
                        'plan_id' => $defaultPlan->id,
                        'plan_price_id' => $defaultPlanPrice->id,
                        'starts_at' => now(),
                        'ends_at' => now()->addDays($defaultPlan->trial_days),
                        'trial_ends_at' => now()->addDays($defaultPlan->trial_days),
                        'is_trial' => true,
                    ]);
                }
            }
        });
    }

    public function merchant()
    {
        $this->guard_name = 'merchant';
        return $this;
    }

    public function merchantRole(): BelongsToMany
    {
        return $this->roles()
            ->where('guard_name', 'merchant');
    }

    public function profile()
    {
        return $this->hasOne(\App\Domains\Account\Models\Profile::class, 'user_id');
    }

    public function settings()
    {
        return $this->hasOne(\App\Domains\Account\Models\UserSetting::class);
    }


    public function storeMemberships()
    {
        // كل عضوية هذا المستخدم
        return $this->hasMany(StoreMembership::class); // مهم جداً
    }

    // عضوية المستخدم في متجر محدد
    public function storeMembership(Store $store): ?StoreMembership
    {
        return $this->storeMemberships()
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->first();
    }

    // المتاجر التي يمتلكها
    public function storesOwned(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    // كل المتاجر التي ينتمي إليها المستخدم (نشطة فقط)
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_memberships')
            ->wherePivot('is_active', true);
    }

    /* ================= Filament Tenants ================= */

    public function getTenants($panel): array|Collection
    {
        return $this->stores;
    }

    public function canAccessTenant($tenant): bool
    {
        // dd($this->stores()->whereKey($tenant)->exists());
        // dd($this->stores->contains($tenant));

        return $this->stores()->whereKey($tenant)->exists();
    }


    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'super-admin' || $panel->getId() === 'admin') {
            return $this->hasAnyRole(['super_admin', 'admin']);
        }

        return $this->stores()->exists();
    }

    /* ================= Roles ================= */

    /**
     * تحقق إذا كان المستخدم يمتلك أي من الأدوار المحددة ضمن الـ guard المطلوب
     *
     * @param array $roles
     * @param string|null $guard
     * @return bool
     */
    public function hasAnyRoleForGuard(array $roles, ?string $guard = null): bool
    {
        $query = $this->roles()->whereIn('name', $roles);

        if ($guard) {
            $query->where('guard_name', $guard);
        }

        return $query->exists();
    }

    /**
     * تحقق إذا كان المستخدم يمتلك دور واحد فقط ضمن الـ guard المطلوب
     */
    public function hasRoleForGuard(string $role, ?string $guard = null): bool
    {
        return $this->hasAnyRoleForGuard([$role], $guard);
    }

    // تعتمد على Spatie Roles + teams
    public function isSuperAdmin(): bool
    {

        return $this->hasRole('super_admin', 'web');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin', 'web');
    }


    public function isMerchant(): bool
    {
        return $this->hasRole('merchant', 'web');
    }

    public function isUser(): bool
    {
        return $this->hasRole('user', 'web');
    }

    public function isPlatformStaff(): bool
    {
        return $this->hasAnyRoleForGuard([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::ADMIN->value,
            UserRoleEnum::SUPPORT_AGENT->value,
            UserRoleEnum::TECH_SUPPORT->value,
        ], 'web');
    }

    /* ================= Locations ================= */

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    //**
    /* Subscriptions
    /* */
    public function payments()
    {
        return $this->hasMany(Payment::class)
            ->latest('created_at')->orderByDesc('id');
    }



    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function billingAddress()
    {
        return $this->hasOne(\App\Models\Billing\BillingAddress::class);
    }

    // sub for merchant

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
    public function latestSubscriptions()
    {
        return $this->subscriptions()
            ->latest('updated_at');
    }

    public function onTrial(): bool
    {
        return $this->latestSubscriptions()?->first()?->is_trial
            && now()->lte($this->latestSubscriptions()?->first()->trial_ends_at);
    }


    public function latestSubscriptionRelation(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany('updated_at');
    }
    public function latestSubscription()
    {
        return $this->subscriptions()
            ->latest('updated_at')
            ->first();
    }

    public function canCreateMultiStore(): bool
    {
        $subscription = $this->latestSubscriptionRelation()->first();

        if (!$subscription || !$subscription->plan) {
            return false;
        }

        return app(\App\Domains\Plan\Services\FeatureUsageService::class)
            ->canUse($subscription, 'stores_max');
    }
}
