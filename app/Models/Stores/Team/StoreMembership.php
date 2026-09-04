<?php

namespace App\Models\Stores\Team;

use App\Enums\Platform\UserRoleEnum;
use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreMembership extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'invited_by',
        'invited_at',
        'accepted_at',
        'is_active',
        'role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];


    public function store() : BelongsTo
    {
        return $this->belongsTo(Store::class)
            ->withoutGlobalScopes(); // مهم إذا كان لديك global scopes على المتجر
    }

    public function storeWithTimezone(): BelongsTo
    {
        return $this->belongsTo(Store::class)
            ->withoutGlobalScopes()
            ->with('settings');
    }

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasRole(String| StoreRoleEnum $role) :bool
    {
        $value = $role instanceof StoreRoleEnum ? $role->value : $role;

        // Decision #6: the role stored on this membership is authoritative.
        if ($this->role) {
            return $this->role === $value;
        }

        return hasStoreRole($role, $this->user);
    }

    public function membershipRole()
    {
        return $this->user?->merchantRole()->first();
    }

    public function isOwner(): bool
    {
        return $this->hasRole(StoreRoleEnum::OWNER);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(StoreRoleEnum::ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(StoreRoleEnum::MANAGER);
    }
    /**
     * تحقق من صلاحية معينة داخل هذه العضوية (Decision #6).
     * الصلاحيات المخصصة المخزنة على العضوية لها الأولوية.
     */
    public function can(string|StorePermissionEnum $permission): bool
    {
        $permission = $permission instanceof StorePermissionEnum ? $permission->value : $permission;

        $stored = $this->permissionNames();
        if (! empty($stored)) {
            return in_array($permission, $stored, true);
        }

        return $this->user->hasPermissionTo($permission, 'merchant');
    }

    /**
     * الصلاحيات المخصصة المخزنة على هذه العضوية (داخل متجر محدد).
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(StoreMembershipPermission::class, 'membership_id');
    }

    public function permissionNames(): array
    {
        return $this->permissions()->pluck('permission')->all();
    }

    public function hasPermission(string|StorePermissionEnum $permission): bool
    {
        $permission = $permission instanceof StorePermissionEnum ? $permission->value : $permission;

        return $this->permissions()->where('permission', $permission)->exists();
    }

    /**
     * مزامنة الصلاحيات المخصصة على هذه العضوية (استبدال كامل).
     */
    public function syncPermissions(iterable $permissions): self
    {
        $this->permissions()->delete();
        foreach ($permissions as $permission) {
            $p = $permission instanceof StorePermissionEnum ? $permission->value : $permission;
            if ($p) {
                $this->permissions()->create(['permission' => $p]);
            }
        }

        return $this;
    }

    public function confirmationShifts(): HasMany
    {
        return $this->hasMany(\App\Domains\Order\Models\ConfirmationShift::class, 'membership_id');
    }

    public function productAssignments(): HasMany
    {
        return $this->hasMany(\App\Domains\Order\Models\ConfirmationProductAssignment::class, 'membership_id');
    }

    public function isOnActiveShift(?\Carbon\Carbon $at = null): bool
    {
        // Use store's timezone for shift calculations (avoids N+1 by using storeWithTimezone when eager loaded)
        $store = $this->storeWithTimezone()->first() ?? $this->store;
        $timezone = $store?->settings?->timezone ?? config('app.timezone');
        $at = $at ?? now($timezone);

        $dayOfWeek = $at->dayOfWeekIso;
        $time = $at->format('H:i');

        return $this->confirmationShifts()
            ->where('is_active', true)
            ->get(['days_of_week', 'start_time', 'end_time', 'is_active'])
            ->contains(fn ($shift) => $shift->coversDayTime($dayOfWeek, $time));
    }
}
