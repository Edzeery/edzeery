<?php

namespace App\Models\Stores;

use App\Enums\Store\StoreStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Orders\Order;
use App\Models\billing\Payment;
use App\Models\billing\Subscription;
use App\Models\Plans\Plan;
use App\Models\Products\Product;
use App\Models\Stores\Team\StoreMembership;
use App\Models\Traits\HasStoreDefaults;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasUlids;
    use HasFactory;
    use HasStoreDefaults;
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'cover',
        'description',
        'landing_template',
        'status',
    ];

    protected $casts = [
        'status' => StoreStatusEnum::class,
        'landing_template' => \App\Enums\Store\LandingTemplateEnum::class,
    ];


    /* ================= Relations ================= */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_memberships');
    }

    public function members(): BelongsToMany
    {
        return $this->users();
    }

    public function membership(User $user): ?StoreMembership
    {

        return $this->membershipFor($user);
    }

    /**
     * جلب عضوية مستخدم محدد للمتجر
     */
    public function membershipFor(User $user): ?StoreMembership
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();
    }

    public function memberships() : HasMany
    {
        return $this->hasMany(StoreMembership::class)
            ->withoutGlobalScopes(); // مهم جداً;
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function settings()
    {
        return $this->hasOne(StoreSetting::class);
    }

    public function seo()
    {
        return $this->hasOne(StoreSeo::class);
    }

    public function theme()
    {
        return $this->hasOne(StoreThemeSetting::class);
    }



    public function payment()
    {
        return $this->hasOne(Payment::class)->latest('created_at')->orderByDesc('id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->latest('created_at')->orderByDesc('id');
    }



    public function latestPayment(): ?Payment
    {
        return $this->payments()
            ->latest('created_at') // تأكد من ترتيب الأحدث أولاً
            ->orderByDesc('id')
            ->first();
    }

    public function canCreateMultiStore(): bool
    {
        $subscription = $this->user->latestSubscription();

        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        return app(\App\Domains\Plan\Services\FeatureUsageService::class)
            ->canUse($subscription, 'stores_max');
    }


    public function numberAgents()
    {
        return ($this->memberships()?->where('user_id', '!=', $this->user_id)->count() ?? 0);
    }

    public function userRequests()
    {
        return $this->hasMany(StoreUserRequest::class);
    }

    /**
     * العلاقة مع جدول تاريخ الحالات.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(StoreStatusHistory::class);
    }

    /**
     * جلب أحدث حالة للمتجر (أحدث سجل حسب created_at).
     */
    public function latestStatus(): ?StoreStatusHistory
    {
        return $this->statusHistories()
            ->latest('created_at') // تأكد من ترتيب الأحدث أولاً
            ->first();
    }

    /**
     * الحصول على الحالة الحالية مباشرة (enum) من أحدث سجل أو العمود الافتراضي.
     */
    public function currentStatus(): StoreStatusEnum
    {
        return $this->status;
    }

    /**
     * سبب الإيقاف أو التعليق من أحدث سجل.
     */
    public function statusReason(): ?string
    {
        return $this->latestStatus()?->reason;
    }

    /**
     * الرابط العام للمتجر (واجهة العميل).
     */
    public function getPublicUrlAttribute(): string
    {
        $scheme = request()->secure() ? 'https' : 'http';
        $domain = config('app.domain');

        return "{$scheme}://{$this->slug}.{$domain}";
    }

    /**
     * هل المتجر نشط ومتاح للزيارة؟
     */
    public function isPubliclyActive(): bool
    {
        return $this->status === StoreStatusEnum::ACTIVE;
    }
}
