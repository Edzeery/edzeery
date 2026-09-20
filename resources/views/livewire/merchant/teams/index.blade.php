<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreTeamService;
use App\Support\PermissionGroupMeta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use function Livewire\Volt\computed;
use function Livewire\Volt\layout;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

layout('components.layouts.store');

state([
    'search' => '',
    'creating' => false,
    'editingId' => null,
    'name' => '',
    'email' => '',
    'password' => '',
    'country_id' => '',
    'state_id' => '',
    'city_id' => '',
    'store_role' => '',
    'supervisor_membership_id' => null,
    'isActive' => true,
    'permissions' => [],
    'activePermissionGroup' => null,
    'productScopeMembershipId' => null,
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::TEAM_VIEW->value) || canStore(StorePermissionEnum::TEAM_VIEW_OWN->value), 403);
    abort_unless(canManageTeam(), 403);
});

$members = computed(function () {
    $user = user();

    $actorMembershipId = $user?->storeMembership(currentStore())?->id;

    $query = StoreMembership::query()
        ->with('user')
        ->where('store_id', currentStoreId())
        ->where('user_id', '!=', $user->id)
        ->latest('created_at');

    if (isStoreOwner($user) || isStoreAdmin($user)) {
        // Owner & Admin see everyone
    } elseif (isStoreManager($user)) {
        $query->where(function ($q) use ($user, $actorMembershipId) {
            $q->where('user_id', $user->id)
                ->orWhere('supervisor_membership_id', $actorMembershipId);
        });
    } else {
        $query->where('user_id', $user->id);
    }

    if ($this->search !== '') {
        $query->where(function ($q) {
            $q->whereHas('user', function ($uq) {
                $uq->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        });
    }

    return $query->paginate(15);
});

$managers = computed(function (): array {
    return StoreMembership::where('store_id', currentStoreId())
        ->where('is_active', true)
        ->where('role', StoreRoleEnum::MANAGER->value)
        ->with('user:id,name')
        ->latest('created_at')
        ->get()
        ->map(fn (StoreMembership $m) => ['value' => $m->id, 'label' => $m->user?->name])
        ->prepend(['value' => '', 'label' => __('teams.no_supervisor')])
        ->values()
        ->all();
});

$canCreate = fn () => canManageTeam();
$canModify = fn (StoreMembership $membership) => canModifyMember($membership);
$canManageScope = fn (StoreMembership $membership) => canManageTeam() && $membership->isManager();
$memberRoleName = function (StoreMembership $membership): string {
    $role = $membership->membershipRole();
    return $role?->name ?? 'staff';
};

$openCreate = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->reset('editingId', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'supervisor_membership_id', 'isActive', 'permissions', 'activePermissionGroup');
    $this->creating = true;
};

$closeCreate = function (): void {
    $this->reset('creating', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'supervisor_membership_id', 'isActive', 'permissions', 'activePermissionGroup');
};

$openEdit = function (StoreMembership $membership): void {
    abort_unless($this->canModify($membership), 403);

    $user = $membership->user;
    $role = $membership->membershipRole();

    $this->editingId = $membership->id;
    $this->name = $user->name;
    $this->email = $user->email;
    $this->password = '';
    $this->country_id = $user->country_id ?? '';
    $this->state_id = $user->state_id ?? '';
    $this->city_id = $user->city_id ?? '';
    $this->store_role = $role?->name ?? '';
    $this->supervisor_membership_id = $membership->supervisor_membership_id;
    $this->isActive = (bool) $membership->is_active;
    $this->permissions = $membership->permissionNames()
        ?: \App\Support\StoreRoles::permissions(StoreRoleEnum::from($this->store_role));
    $this->activePermissionGroup = null;
    $this->creating = false;
};

$closeEdit = function (): void {
    $this->reset('editingId', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'supervisor_membership_id', 'isActive', 'permissions', 'activePermissionGroup');
};

$openProductScope = function (StoreMembership $membership): void {
    abort_unless($this->canManageScope($membership), 403);
    $this->productScopeMembershipId = $membership->id;
};

$closeProductScope = function (): void {
    $this->productScopeMembershipId = null;
};

$saveNew = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'password' => ['required', 'string', 'min:8'],
        'country_id' => ['required'],
        'state_id' => ['required'],
        'city_id' => ['required'],
        'store_role' => ['required', Rule::in(array_column(StoreRoleEnum::cases(), 'value'))],
    ]);

    try {
        app(StoreTeamService::class)->addMember(currentStore(), [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'store_role' => $this->store_role,
            'supervisor_membership_id' => $this->supervisor_membership_id,
            'is_active' => $this->isActive,
            'permissions' => $this->permissions,
        ]);

        $this->closeCreate();
        $this->dispatch('swal', type: 'success', title: __('messages.created_successfully'));
    } catch (\Exception $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$saveEdit = function (): void {
    $membership = StoreMembership::findOrFail($this->editingId);
    abort_unless($this->canModify($membership), 403);

    $this->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'password' => ['nullable', 'string', 'min:8'],
        'country_id' => ['required'],
        'state_id' => ['required'],
        'city_id' => ['required'],
        'store_role' => ['required', Rule::in(array_column(StoreRoleEnum::cases(), 'value'))],
    ]);

    try {
        app(StoreTeamService::class)->updateMember(currentStore(), $membership, [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'store_role' => $this->store_role,
            'supervisor_membership_id' => $this->supervisor_membership_id,
            'is_active' => $this->isActive,
            'permissions' => $this->permissions,
        ]);

        $this->closeEdit();
        $this->dispatch('swal', type: 'success', title: __('messages.updated_successfully'));
    } catch (\Exception $e) {
        $this->dispatch('swal', type: 'error', title: $e->getMessage());
    }
};

$toggleActive = function (StoreMembership $membership): void {
    abort_unless($this->canModify($membership), 403);
    $membership->update(['is_active' => ! $membership->is_active]);
};

$remove = function (StoreMembership $membership): void {
    abort_unless($this->canModify($membership), 403);
    app(StoreTeamService::class)->removeMember($membership);
    $this->dispatch('swal', type: 'success', title: __('messages.deleted_successfully'));
};

$updatedCountryId = function (?string $value): void {
    $this->state_id = '';
    $this->city_id = '';
};
$updatedStateId = function (?string $value): void {
    $this->city_id = '';
};
$states = computed(fn () => $this->country_id ? State::where('country_id', $this->country_id)->orderedByCode()->get(['id', 'name', 'state_code'])->toArray() : []);

$cities = computed(fn () => $this->state_id ? City::where('state_id', $this->state_id)->pluck('name', 'id') : collect());

$allPermissions = computed(function () {
    if (! $this->store_role) {
        return collect();
    }

    return collect(\App\Enums\Store\StorePermissionEnum::values())
        ->groupBy(fn ($p) => explode('.', $p)[0]);
});

$roleTemplatePermissions = computed(function (): array {
    if (! $this->store_role) {
        return [];
    }

    try {
        $role = StoreRoleEnum::from($this->store_role);
    } catch (\ValueError) {
        return [];
    }

    return \App\Support\StoreRoles::permissions($role);
});

$permissionGroups = computed(function (): array {
    $all = $this->allPermissions;
    $selected = array_flip($this->permissions ?? []);

    return collect(PermissionGroupMeta::order())
        ->map(fn (string $group) => [
            'group' => $group,
            'icon' => PermissionGroupMeta::icon($group),
            'title' => __("permission_groups.{$group}.title"),
            'description' => __("permission_groups.{$group}.description"),
            'total' => $all->get($group, collect())->count(),
            'checked' => $all->get($group, collect())
                ->filter(fn (string $p) => isset($selected[$p]))
                ->count(),
            'has_dangerous' => $all->get($group, collect())
                ->contains(fn (string $p) => PermissionGroupMeta::isDangerous($p)),
        ])
        ->values()
        ->all();
});

$activeGroupMeta = computed(function (): ?array {
    if (! $this->activePermissionGroup) {
        return null;
    }

    $group = $this->activePermissionGroup;
    $all = $this->allPermissions;
    $selected = array_flip($this->permissions ?? []);
    $template = $this->roleTemplatePermissions;

    return [
        'group' => $group,
        'icon' => PermissionGroupMeta::icon($group),
        'title' => __("permission_groups.{$group}.title"),
        'description' => __("permission_groups.{$group}.description"),
        'rows' => $all->get($group, collect())
            ->map(fn (string $p) => [
                'permission' => $p,
                'label' => PermissionGroupMeta::label($p),
                'checked' => isset($selected[$p]),
                'custom' => ! in_array($p, $template, true),
                'dangerous' => PermissionGroupMeta::isDangerous($p),
                'requires' => PermissionGroupMeta::dependencies($p),
            ])
            ->values()
            ->all(),
    ];
});

$openPermissionGroup = function (string $group): void {
    if (in_array($group, PermissionGroupMeta::order(), true)) {
        $this->activePermissionGroup = $group;
    }
};

$closePermissionGroup = function (): void {
    $this->activePermissionGroup = null;
};

$savePermissionGroup = function (): void {
    $this->activePermissionGroup = null;
};

$togglePermission = function (string $permission, bool $checked): void {
    $this->permissions ??= [];

    if ($checked) {
        foreach (PermissionGroupMeta::dependencies($permission) as $required) {
            if (! in_array($required, $this->permissions, true)) {
                $this->permissions[] = $required;
            }
        }

        if (! in_array($permission, $this->permissions, true)) {
            $this->permissions[] = $permission;
        }
    } else {
        $removed = [$permission, ...PermissionGroupMeta::requiredBy($permission)];
        $this->permissions = array_values(array_diff($this->permissions, $removed));
    }
};

$selectGroupPermissions = function (string $group): void {
    $this->permissions ??= [];

    $this->allPermissions->get($group, collect())
        ->each(fn (string $p) => $this->togglePermission($p, true));

    $this->permissions = array_values(array_unique($this->permissions));
};

$clearGroupPermissions = function (string $group): void {
    $this->permissions ??= [];

    $this->permissions = array_values(array_diff(
        $this->permissions,
        $this->allPermissions->get($group, collect())->all()
    ));
};

$selectRoleTemplate = function (): void {
    if (! $this->store_role) {
        return;
    }

    try {
        $this->permissions = \App\Support\StoreRoles::permissions(StoreRoleEnum::from($this->store_role));
    } catch (\ValueError) {
        // leave the current selection untouched
    }
};

$clearAllPermissions = function (): void {
    $this->permissions = $this->permissions ?? [];
};
?>

<div>
    <div class="edz-page-head">
        <div>
            <h1 class="edz-page-head__title">{{ __('teams.title') }}</h1>
            <p class="edz-page-head__subtitle">{{ __('teams.subtitle', ['store' => currentStore()?->name]) }}</p>
        </div>
        @if ($this->canCreate())
            <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="openCreate">
                <x-edz.icon name="plus" class="w-4 h-4" /> {{ __('teams.add_member') }}
            </button>
        @endif
    </div>

    {{-- Create / Edit Form --}}
    @if ($creating || $editingId)
        @include('livewire.merchant.teams.partials.member-form')
    @endif

    {{-- Members Table --}}
    @include('livewire.merchant.teams.partials.members-table')

    @if ($this->productScopeMembershipId)
        <div @product-scope-closed.window="$wire.closeProductScope()">
            @livewire('merchant.teams.partials.product-scope-modal', ['membershipId' => $this->productScopeMembershipId], key('scope-' . $this->productScopeMembershipId))
        </div>
    @endif
</div>