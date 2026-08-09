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
use Illuminate\Database\Eloquent\SoftDeletes;
use phpDocumentor\Reflection\Types\String_;

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

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasRole(String| StoreRoleEnum $role) :bool
    {
        return hasStoreRole($role, $this->user);
    }

    public function membershipRole()
    {
        return $this->user->merchantRole->first();
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
     * تحقق من صلاحية معينة باستخدام Spatie
     */
    public function can(string|StorePermissionEnum $permission): bool
    {
        return $this->user->hasPermissionTo(
            $permission instanceof StorePermissionEnum ? $permission->value : $permission,
            'merchant'
        );
    }
}
