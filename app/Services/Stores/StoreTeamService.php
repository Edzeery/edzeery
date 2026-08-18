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

            $this->ensureUserIsNotPlatformStaff($data['email']);

            $member_user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );

            $member_user->update([
                'name'       => $data['name'],
                'country_id' => $data['country_id'] ?? $member_user->country_id,
                'state_id'   => $data['state_id'] ?? $member_user->state_id,
                'city_id'    => $data['city_id'] ?? $member_user->city_id,
                ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
            ]);

            if (
                StoreMembership::where('store_id', $store->id)
                ->where('user_id', $member_user->id)
                ->exists()
            ) {
                throw new \Exception(__('teams.member_already_exists'));
            }

            $member = StoreMembership::create([
                'store_id'  => $store->id,
                'user_id'   => $member_user->id,
                'invited_by' => user()->id,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $role = StoreRoleEnum::from($data['store_role']);

            $member_user->guard_name = 'merchant';
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

            $membership->update([
                'is_active' => $data['is_active'] ?? $membership->is_active,
            ]);

            if (! empty($data['store_role'])) {
                $role = StoreRoleEnum::from($data['store_role']);

                $user->guard_name = 'merchant';
                $user->syncRoles([]);
                $user->assignRole($role->value);
            }

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $user->guard_name = 'merchant';
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
            throw new \Exception(__('teams.cannot_add_platform_staff'));
        }
    }
}
