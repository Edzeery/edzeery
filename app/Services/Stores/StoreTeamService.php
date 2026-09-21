<?php

namespace App\Services\Stores;

use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Store\StoreRoleEnum;
use App\Mail\StoreMembershipCredentialsMail;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreTeamService
{
    public function addMember(Store $store, array $data): StoreMembership
    {
        $member = DB::transaction(function () use ($store, $data) {

            $this->ensureUserIsNotPlatformStaff($data['email']);
            $this->ensureStaffLimitNotExceeded($store);

            $member_user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            $updateData = [
                'name'       => $data['name'],
                'country_id' => $data['country_id'] ?? $member_user->country_id,
                'state_id'   => $data['state_id'] ?? $member_user->state_id,
                'city_id'    => $data['city_id'] ?? $member_user->city_id,
            ];

            if (! empty($data['password']) && ! $member_user->wasRecentlyCreated) {
                $updateData['password'] = Hash::make($data['password']);
            } elseif (! empty($data['password']) && $member_user->wasRecentlyCreated) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $member_user->update($updateData);

            if (
                StoreMembership::where('store_id', $store->id)
                ->where('user_id', $member_user->id)
                ->exists()
            ) {
                throw new \Exception(__('teams.member_already_exists'));
            }

            $role = StoreRoleEnum::from($data['store_role']);

            $member = StoreMembership::create([
                'store_id'                 => $store->id,
                'user_id'                  => $member_user->id,
                'invited_by'               => user()->id,
                'is_active'                => $data['is_active'] ?? true,
                'role'                     => $role->value,
                'supervisor_membership_id' => $this->resolveSupervisorId($store, $data),
            ]);

            // Decision #6 — hybrid: keep the global merchant role for platform
            // compatibility, but store the scoped role + custom permissions on
            // THIS membership so multiple stores stay isolated.
            $member_user->guard_name = 'merchant';
            $existingRoles = $member_user->getRoleNames('merchant');
            if ($existingRoles->isEmpty()) {
                $member_user->assignRole($role->value);
            }

            $permissions = $data['permissions'] ?? \App\Support\StoreRoles::permissions($role);
            $member->syncPermissions($permissions);

            $this->consumeStaffQuota($store);

            return $member;
        });

        // Phase 36.8 — send the one-time credentials email AFTER the transaction
        // commits, so a mail failure never rolls back the new member.
        $this->dispatchMemberCredentialsMail($store, $member, $data);

        return $member;
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
                $membership->update(['role' => $role->value]);

                // Decision #6 — hybrid: keep the global role synced for platform
                // compatibility, while the membership-level role/permissions hold
                // the authoritative, store-isolated scope.
                $user->guard_name = 'merchant';
                $user->syncRoles([$role->value]);
            }

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $user->guard_name = 'merchant';
                $user->syncPermissions($data['permissions']);
                $membership->syncPermissions($data['permissions']);
            }

            $this->applySupervisorOnUpdate($store, $membership, $data);

            return $membership->refresh();
        });
    }

    public function removeMember(StoreMembership $membership): void
    {
        DB::transaction(function () use ($membership): void {
            $user = $membership->user;

            // Decision #6 — drop the membership-scoped custom permissions first.
            $membership->syncPermissions([]);

            $membership->delete();

            if (! $user) {
                return;
            }

            if ($user->storeMemberships()->where('is_active', true)->exists()) {
                return;
            }

            $user->guard_name = 'merchant';
            $user->syncRoles([]);
            $user->syncPermissions([]);
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

    protected function ensureStaffLimitNotExceeded(Store $store): void
    {
        $subscription = $store->user->latestSubscription();

        if (! $subscription || ! $subscription->plan) {
            return;
        }

        $usageService = app(FeatureUsageService::class);

        if (! $usageService->canUse($subscription, 'staff_limit')) {
            throw new \Exception(__('teams.staff_limit_reached'));
        }
    }

    protected function consumeStaffQuota(Store $store): void
    {
        $subscription = $store->user->latestSubscription();

        if (! $subscription || ! $subscription->plan) {
            return;
        }

        app(FeatureUsageService::class)->consume($subscription, 'staff_limit');
    }

    protected function actorMembership(Store $store): ?StoreMembership
    {
        return StoreMembership::where('store_id', $store->id)
            ->where('user_id', user()->id)
            ->where('is_active', true)
            ->first();
    }

    protected function resolveSupervisorId(Store $store, array $data): ?string
    {
        $actor = $this->actorMembership($store);
        if (($data['store_role'] ?? null) !== StoreRoleEnum::STAFF->value) {
            return null;
        }
        $explicit = (string) ($data['supervisor_membership_id'] ?? '');
        if ($explicit === '') {
            return $actor?->isManager() ? $actor->id : null;
        }
        if (! $actor || (! $actor->isOwner() && ! $actor->isAdmin())) {
            return null;
        }
        $this->assertValidSupervisor($store, $explicit);

        return $explicit;
    }

    protected function assertValidSupervisor(Store $store, string $membershipId): void
    {
        $valid = StoreMembership::whereKey($membershipId)
            ->where('store_id', $store->id)
            ->where('is_active', true)
            ->where('role', StoreRoleEnum::MANAGER->value)
            ->exists();
        if (! $valid) {
            throw new \Exception(__('teams.invalid_supervisor'));
        }
    }

    protected function dispatchMemberCredentialsMail(Store $store, StoreMembership $member, array $data): void
    {
        try {
            Mail::to($data['email'])->send(new StoreMembershipCredentialsMail(
                storeName: $store->name,
                inviterName: user()->name,
                memberName: $data['name'],
                memberEmail: $data['email'],
                password: $data['password'],
                loginUrl: route('login'),
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to send team member credentials email.', [
                'store_membership_id' => $member->id,
                'store_id'            => $store->id,
                'member'              => $data['email'],
                'error'               => $e->getMessage(),
            ]);
        }
    }

    protected function applySupervisorOnUpdate(Store $store, StoreMembership $membership, array $data): void
    {
        if (($data['store_role'] ?? null) !== StoreRoleEnum::STAFF->value) {
            $membership->update(['supervisor_membership_id' => null]);
            return;
        }
        $actor = $this->actorMembership($store);
        if (! $actor || (! $actor->isOwner() && ! $actor->isAdmin())) {
            return;
        }
        $explicit = (string) ($data['supervisor_membership_id'] ?? '');
        if ($explicit === '') {
            $membership->update(['supervisor_membership_id' => null]);
            return;
        }
        $this->assertValidSupervisor($store, $explicit);
        $membership->update(['supervisor_membership_id' => $explicit]);
    }
}
