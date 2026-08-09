<?php

namespace App\Scopes;

use App\Enums\Platform\UserRoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {


        // السماح لسوبر أدمن أو أدمن برؤية كل البيانات
        if (user() && (user()->hasRole(UserRoleEnum::SUPER_ADMIN->value) || user()->hasRole(UserRoleEnum::ADMIN->value))) {
            return; // لا نطبق Scope
        }
        $store = currentStore();
        // تطبيق Store Scope للمستخدم العادي
        // هنا نفترض أن الـ Store model مرتبط بالـ User عبر user_id
        if ($store) {
            $builder->where($model->getTable() . '.store_id', $store->id);
        }
    }
}
