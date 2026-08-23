<?php

use App\Enums\Store\StorePermissionEnum;
use App\Enums\Store\StoreRoleEnum;
use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stores\Team\StoreMembership;
use App\Services\Stores\StoreTeamService;
use Illuminate\Support\Facades\DB;
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
    'isActive' => true,
    'permissions' => [],
]);

mount(function (): void {
    abort_unless(canStore(StorePermissionEnum::TEAM_VIEW->value), 403);
    abort_unless(canManageTeam(), 403);
});

$members = computed(function () {
    $user = user();

    $query = StoreMembership::query()
        ->with('user')
        ->where('store_id', currentStoreId())
        ->where('user_id', '!=', $user->id)
        ->latest('created_at');

    if (isStoreOwner($user) || isStoreAdmin($user)) {
        // Owner & Admin see everyone
    } elseif (isStoreManager($user)) {
        $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('invited_by', $user->id);
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

$canCreate = fn () => canManageTeam();

$canModify = fn (StoreMembership $membership) => canModifyMember($membership);

$memberRoleName = function (StoreMembership $membership): string {
    $role = $membership->membershipRole();
    return $role?->name ?? 'staff';
};

$openCreate = function (): void {
    abort_unless($this->canCreate(), 403);

    $this->reset('editingId', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'isActive', 'permissions');
    $this->creating = true;
};

$closeCreate = function (): void {
    $this->reset('creating', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'isActive', 'permissions');
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
    $this->isActive = (bool) $membership->is_active;
    $user->guard_name = 'merchant';
    $this->permissions = $user->getAllPermissions()->pluck('name')->toArray();
    $this->creating = false;
};

$closeEdit = function (): void {
    $this->reset('editingId', 'name', 'email', 'password', 'country_id', 'state_id', 'city_id', 'store_role', 'isActive', 'permissions');
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

$states = computed(fn () => $this->country_id
    ? State::where('country_id', $this->country_id)->pluck('name', 'id')
    : []);

$cities = computed(fn () => $this->state_id
    ? City::where('state_id', $this->state_id)->pluck('name', 'id')
    : []);

$allPermissions = computed(function () {
    if (! $this->store_role) {
        return collect();
    }

    try {
        $role = StoreRoleEnum::from($this->store_role);
    } catch (\ValueError) {
        return collect();
    }

    $all = \App\Support\StoreRoles::permissions($role);

    return collect($all)->groupBy(fn ($p) => explode('.', $p)[0]);
});
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
        <div class="edz-card mb-6">
            <div class="edz-card__header">
                <div>
                    <h2 class="edz-card__title">{{ $editingId ? __('teams.update_member') : __('teams.add_member') }}</h2>
                    <p class="text-sm text-ink-400">{{ $editingId ? __('teams.update_member') : __('teams.invite_member') }}</p>
                </div>
            </div>

            <form wire:submit="{{ $editingId ? 'saveEdit' : 'saveNew' }}" class="space-y-4 p-4" x-data="edzDirty()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-name">{{ __('teams.name') }}</label>
                        <input id="tm-name" type="text" class="edz-input @error('name') edz-input--error @enderror" wire:model="name" placeholder="{{ __('teams.name') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-email">{{ __('teams.email') }}</label>
                        <input id="tm-email" type="email" class="edz-input @error('email') edz-input--error @enderror" wire:model="email" placeholder="member@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-password">{{ __('table.password') }}{{ $editingId ? ' ('.__('teams.password_hint').')' : '' }}</label>
                        <input id="tm-password" type="password" class="edz-input @error('password') edz-input--error @enderror" wire:model="password" placeholder="{{ $editingId ? '••••••••' : __('teams.min_8_chars') }}">
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-role">{{ __('teams.role') }}</label>
                        <select id="tm-role" class="edz-select @error('store_role') edz-input--error @enderror" wire:model.live="store_role">
                            <option value="">{{ __('teams.all_roles') }}</option>
                            @foreach (collect(StoreRoleEnum::cases())->reject(fn ($r) => $r === StoreRoleEnum::OWNER) as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('store_role')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-country">{{ __('teams.country') }}</label>
                        <select id="tm-country" class="edz-select @error('country_id') edz-input--error @enderror" wire:model.live="country_id">
                            <option value="">{{ __('teams.select_country') }}</option>
                            @foreach (countries() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-state">{{ __('teams.state') }}</label>
                        <select id="tm-state" class="edz-select @error('state_id') edz-input--error @enderror" wire:model.live="state_id" {{ empty($this->country_id) ? 'disabled' : '' }}>
                            <option value="">{{ __('teams.select_state') }}</option>
                            @foreach ($this->states as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('state_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink" for="tm-city">{{ __('teams.city') }}</label>
                        <select id="tm-city" class="edz-select @error('city_id') edz-input--error @enderror" wire:model="city_id" {{ empty($this->state_id) ? 'disabled' : '' }}>
                            <option value="">{{ __('teams.select_city') }}</option>
                            @foreach ($this->cities as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-surface-border">
                        {{ __('general.active') }}
                    </label>
                </div>

                @if ($this->store_role && $this->store_role !== 'staff' && $this->allPermissions->isNotEmpty())
                    <div class="border-t border-surface-border pt-4">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="text-sm font-medium text-ink">{{ __('titles.permissions') }}</span>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                    wire:click="$set('permissions', {{ json_encode(\App\Support\StoreRoles::permissions(StoreRoleEnum::from($this->store_role))) }})">
                                {{ __('buttons.select_all') }}
                            </button>
                            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                    wire:click="$set('permissions', [])">
                                {{ __('buttons.unselect_all') }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($this->allPermissions as $group => $perms)
                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-ink-400">{{ ucfirst($group) }}</p>
                                    @foreach ($perms as $perm)
                                        <label class="flex items-center gap-2 py-0.5 text-sm text-ink">
                                            <input type="checkbox" wire:model="permissions" value="{{ $perm }}" class="h-3.5 w-3.5 rounded border-surface-border">
                                            {{ __("permissions.{$perm}") }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <button type="submit" class="edz-btn edz-btn--primary edz-btn--sm" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                        <span wire:loading.remove wire:target="saveNew,saveEdit">{{ __('buttons.save') }}</span>
                        <span wire:loading wire:target="saveNew,saveEdit">{{ __('buttons.processing') }}</span>
                    </button>
                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                            wire:click="{{ $editingId ? 'closeEdit' : 'closeCreate' }}">{{ __('buttons.cancel') }}</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Members Table --}}
    <div class="edz-card">
        <div class="edz-card__header">
            <div>
                <h2 class="edz-card__title">{{ __('teams.list_title') }}</h2>
                <p class="text-sm text-ink-400">{{ __('teams.list_subtitle') }}</p>
            </div>
        </div>

        <div class="border-b border-surface-border p-4">
            <input type="search" class="edz-input" placeholder="{{ __('teams.search_placeholder') }}"
                   wire:model.live.debounce.300ms="search">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-surface-border text-start text-xs uppercase tracking-wider text-ink-muted">
                        <th class="px-4 py-3 text-start font-semibold">{{ __('teams.name') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('teams.email') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('teams.role') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('table.address') }}</th>
                        <th class="px-4 py-3 text-start font-semibold">{{ __('teams.status') }}</th>
                        <th class="px-4 py-3 text-end font-semibold">{{ __('general.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->members as $membership)
                        @php
                            $roleName = $this->memberRoleName($membership);
                        @endphp
                        <tr class="border-b border-surface-border last:border-0 hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-medium text-ink">{{ $membership->user?->name }}</td>
                            <td class="px-4 py-3 text-ink-soft">{{ $membership->user?->email }}</td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="roles" :status="$roleName" />
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted">
                                {{ $membership->user?->city?->name }}, {{ $membership->user?->state?->name }}
                            </td>
                            <td class="px-4 py-3">
                                <x-merchant.status domain="general" :status="$membership->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($this->canModify($membership))
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="openEdit('{{ $membership->id }}')">{{ __('buttons.edit') }}</button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                                wire:click="toggleActive('{{ $membership->id }}')">
                                            {{ $membership->is_active ? __('buttons.deactivate') : __('buttons.activate') }}
                                        </button>
                                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                                x-data
                                                @click.prevent="if (await EdzSwal.confirmAction('{{ __('teams.remove_member') }}', '{{ __('messages.action_confirm_delete') }}')) $wire.remove('{{ $membership->id }}')"
                                                >{{ __('buttons.remove') }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <p class="text-sm font-medium text-ink-soft">{{ __('teams.no_members') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ __('teams.try_adjusting') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->members->hasPages())
            <div class="border-t border-surface-border px-4 py-3">
                {{ $this->members->links() }}
            </div>
        @endif
    </div>
</div>
