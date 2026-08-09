<?php

namespace App\Services\Stores;

use App\Enums\Store\StoreRoleEnum;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreTeamService
{
    public function addMember(Store $store, array $data): StoreMembership
    {
        return DB::transaction(function () use ($store, $data) {

            // 1️⃣ منع ربط حسابات المنصة
            $this->ensureUserIsNotPlatformStaff($data['email']);

            // 2️⃣ إيجاد أو إنشاء المستخدم
            $member_user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' =>  Hash::make($data['password']),
                ]
            );
            // 2️⃣.1 تحديث بيانات المستخدم إذا كان موجودًا مسبقًا
            $updateData = [
                'name'       => $data['name'],
                'country_id' => $data['country_id'] ?? $member_user->country_id,
                'state_id'   => $data['state_id'] ?? $member_user->state_id,
                'city_id'    => $data['city_id'] ?? $member_user->city_id,
            ];

            // تحديث كلمة المرور فقط إذا تم إدخالها
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $member_user->update($updateData);
            // 3️⃣ منع التكرار
            if (
                StoreMembership::where('store_id', $store->id)
                ->where('user_id', $member_user->id)
                ->where('invited_by', user()->id)
                ->exists()
            ) {
                throw new \Exception('User already member of this store.');
            }

            // 4️⃣ إنشاء العضوية للمتجر
            $member = StoreMembership::create([
                'store_id'  => $store->id,
                'user_id'   => $member_user->id,
                'invited_by' => user()->id,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // 5️⃣ تحقق أمني: المستخدم لا يمكن أن يكون نفسه المتجر
            if ($member_user->id === $store->id) {
                throw new \Exception('User id and store id cannot be the same.');
            }

            $role = StoreRoleEnum::from($data['store_role']);

            $member_user->syncRoles([]);

            $member_user->assignRole($role->value);

            $permissions = $data['permissions'] ?? \App\Support\StoreRoles::permissions($role);
            $member_user->syncPermissions($permissions);

            return $member;
        });
    }

    public function updateMember(Store $store, StoreMembership $membership, array $data): StoreMembership
    {
        return DB::transaction(function () use ($store, $membership, $data) {

            $user = $membership->user;

            // 1️⃣ تحديث بيانات المستخدم
            $userData = [
                'name'       => $data['name'],
                'email'      => $data['email'],
                'country_id' => $data['country_id'] ?? $user->country_id,
                'state_id'   => $data['state_id'] ?? $user->state_id,
                'city_id'    => $data['city_id'] ?? $user->city_id,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $user->update($userData);


            // 2️⃣ تحديث حالة العضوية
            $membership->update([
                'is_active' => $data['is_active'] ?? $membership->is_active,
            ]);

            // 3️⃣ تحديث الدور
            if (! empty($data['store_role'])) {
                $role = StoreRoleEnum::from($data['store_role']);

                // مهم: إزالة الأدوار السابقة
                $user->syncRoles([]);

                // تعيين الدور
                $user->assignRole($role->value);
            }

            // 4️⃣ تحديث الصلاحيات (Checkboxes)
            if (
                isset($data['permissions']) &&
                is_array($data['permissions'])
            ) {
                $user->syncPermissions($data['permissions']);
            }

            return $membership->refresh();
        });
    }



    protected function ensureUserIsNotPlatformStaff(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return;
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'tech_support', 'support_agent'])) {
            throw new \Exception('This account cannot be added to a store.');
        }
    }
}
