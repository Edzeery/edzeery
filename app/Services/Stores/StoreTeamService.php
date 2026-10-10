<?php

namespace App\Services\Stores;

use App\Domains\Order\Jobs\ShiftHandoverJob;
use App\Domains\Plan\Services\FeatureUsageService;
use App\Enums\Store\StoreRoleEnum;
use App\Mail\StoreMembershipCredentialsMail;
use App\Models\Stores\Store;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;
use App\Services\Stores\Concerns\ResolvesSupervisorAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StoreTeamService
{
    use ResolvesSupervisorAssignment;

    public function addMember(Store $store, array $payload): StoreMembership
    {
        $newCredentialsProvided = false;

        $member = DB::transaction(function () use ($store, $payload, &$newCredentialsProvided) {

            $this->ensureUserIsNotPlatformStaff($payload['email']);
            $this->ensureStaffLimitNotExceeded($store);

            $member_user = User::firstOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // S-02: an account already linked to this email must NEVER have its
            // password rewritten from the add-member payload — that would be
            // account takeover. Existing accounts join with their credentials
            // intact; only a freshly-created account accepts a password.
            if (! empty($payload['password']) && ! $member_user->wasRecentlyCreated) {
                throw new \Exception(__('teams.cannot_set_password_on_existing_user'));
            }

            if ($member_user->wasRecentlyCreated && ! empty($payload['password'])) {
                $newCredentialsProvided = true;
            }

            $updateData = [
                'name' => $payload['name'],
                'country_id' => $payload['country_id'] ?? $member_user->country_id,
                'state_id' => $payload['state_id'] ?? $member_user->state_id,
                'city_id' => $payload['city_id'] ?? $member_user->city_id,
            ];

            if ($newCredentialsProvided) {
                $updateData['password'] = Hash::make($payload['password']);
            }

            $member_user->update($updateData);

            if (
                StoreMembership::where('store_id', $store->id)
                    ->where('user_id', $member_user->id)
                    ->exists()
            ) {
                throw new \Exception(__('teams.member_already_exists'));
            }

            $role = StoreRoleEnum::from($payload['store_role']);

            $member = StoreMembership::create([
                'store_id' => $store->id,
                'user_id' => $member_user->id,
                'invited_by' => user()->id,
                'is_active' => $payload['is_active'] ?? true,
                'role' => $role->value,
                'supervisor_membership_id' => $this->resolveSupervisorId($store, $payload),
            ]);

            // Decision #6 — hybrid: keep the global merchant role for platform
            // compatibility, but store the scoped role + custom permissions on
            // THIS membership so multiple stores stay isolated.
            $member_user->guard_name = 'merchant';
            $existingRoles = $member_user->getRoleNames('merchant');
            if ($existingRoles->isEmpty()) {
                $member_user->assignRole($role->value);
            }

            $permissions = $payload['permissions'] ?? \App\Support\StoreRoles::permissions($role);
            $member->syncPermissions($permissions);

            $this->consumeStaffQuota($store);

            return $member;
        });

        // Phase 36.8 — send the one-time credentials email AFTER the transaction
        // commits, so a mail failure never rolls back the new member. Only the
        // freshly-created account has a password to disclose.
        if ($newCredentialsProvided) {
            $this->dispatchMemberCredentialsMail($store, $member, $payload);
        }

        return $member;
    }

    public function updateMember(Store $store, StoreMembership $membership, array $payload): StoreMembership
    {
        $membership = DB::transaction(function () use ($store, $membership, $payload) {

            $user = $membership->user;

            $userData = [
                'name' => $payload['name'],
                'email' => $payload['email'],
                'country_id' => $payload['country_id'] ?? $user->country_id,
                'state_id' => $payload['state_id'] ?? $user->state_id,
                'city_id' => $payload['city_id'] ?? $user->city_id,
            ];

            // S-02: a password may only be set when the caller re-authenticates
            // with the acting member's current password — a silent reset from a
            // stale session is exactly the takeover this guard closes.
            if (! empty($payload['password'])) {
                if (blank($payload['current_password'] ?? null) || ! Hash::check($payload['current_password'], user()->getAuthPassword())) {
                    throw new \Exception(__('teams.current_password_required'));
                }

                $userData['password'] = Hash::make($payload['password']);
            }

            $user->update($userData);

            $membership->update([
                'is_active' => $payload['is_active'] ?? $membership->is_active,
            ]);

            if (! empty($payload['store_role'])) {
                $role = StoreRoleEnum::from($payload['store_role']);
                $membership->update(['role' => $role->value]);

                // Decision #6 — hybrid: keep the global role synced for platform
                // compatibility, while the membership-level role/permissions hold
                // the authoritative, store-isolated scope.
                $user->guard_name = 'merchant';
                $user->syncRoles([$role->value]);
            }

            if (isset($payload['permissions']) && is_array($payload['permissions'])) {
                $user->guard_name = 'merchant';
                $user->syncPermissions($payload['permissions']);
                $membership->syncPermissions($payload['permissions']);
            }

            $this->applySupervisorOnUpdate($store, $membership, $payload);

            return $membership->refresh();
        });

        // Roles/permissions/active state may have just changed: sweep the
        // store's open assignments now instead of waiting for the cron.
        ShiftHandoverJob::dispatch($store);

        return $membership;
    }

    public function removeMember(StoreMembership $membership): void
    {
        $store = $membership->store;

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

        // The removed member's assigned orders must be swept immediately.
        if ($store !== null) {
            ShiftHandoverJob::dispatch($store);
        }
    }

    protected function ensureUserIsNotPlatformStaff(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
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

    protected function dispatchMemberCredentialsMail(Store $store, StoreMembership $member, array $payload): void
    {
        try {
            Mail::to($payload['email'])->send(new StoreMembershipCredentialsMail(
                storeName: $store->name,
                inviterName: user()->name,
                memberName: $payload['name'],
                memberEmail: $payload['email'],
                password: $payload['password'],
                loginUrl: route('login'),
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to send team member credentials email.', [
                'store_membership_id' => $member->id,
                'store_id' => $store->id,
                'member' => $payload['email'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
