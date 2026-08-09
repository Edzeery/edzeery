<?php

namespace App\Policies;

use App\Enums\Store\StorePermissionEnum;
use App\Models\Stores\Team\StoreMembership;
use App\Models\User;

class StoreMembershipPolicy
{
    /* ================= Helpers ================= */

    protected function actor(): ?StoreMembership
    {
        return currentMembership();
    }

    protected function sameStore(StoreMembership $membership): bool
    {
        return currentStore()
            && $membership->store_id === currentStore()->id;
    }

    protected function canTouch(StoreMembership $target): bool
    {
        $actor = $this->actor();

        if (! $actor || ! $this->sameStore($target)) {
            return false;
        }

        // لا أحد يلمس OWNER
        if ($target->isOwner()) {
            return false;
        }

        // OWNER & ADMIN يديرون الجميع
        if ($actor->isOwner() || $actor->isAdmin()) {
            return true;
        }

        // MANAGER فقط فريقه
        return $actor->isManager()
            && $target->invited_by === $actor->user_id;
    }

    /* ================= Abilities ================= */

    public function viewAny(User $user): bool
    {
        return $this->actor()?->can(StorePermissionEnum::TEAM_VIEW) ?? false;
    }

    public function view(User $user, StoreMembership $membership): bool
    {
        return $this->sameStore($membership)
            && $this->actor()?->can(StorePermissionEnum::TEAM_VIEW);
    }

    public function create(User $user): bool
    {
        return $this->actor()?->can(StorePermissionEnum::TEAM_INVITE) ?? false;
    }

    public function update(User $user, StoreMembership $membership): bool
    {
        return $this->canTouch($membership)
            && $this->actor()?->can(StorePermissionEnum::STORE_TEAM_MANAGE);
    }

    public function delete(User $user, StoreMembership $membership): bool
    {
        return $this->canTouch($membership)
            && $this->actor()?->can(StorePermissionEnum::TEAM_REMOVE);
    }

    public function deactivate(User $user, StoreMembership $membership): bool
    {
        return $this->delete($user, $membership);
    }

    /**
     * ❗ حذف نهائي
     * OWNER فقط
     */
    public function forceDelete(User $user, StoreMembership $membership): bool
    {
        $actor = $this->actor();

        return $actor
            && $this->sameStore($membership)
            && $actor->isOwner()
            && ! $membership->isOwner();
    }
}
