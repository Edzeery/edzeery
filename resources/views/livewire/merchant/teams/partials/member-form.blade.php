{{-- Create / Edit member form (Phase 36.1 partial) — rendered from teams/index.blade.php, owns the Permission Hub section. --}}
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
                <x-edz.select
                    wire:model.live="store_role"
                    :options="collect(\App\Enums\Store\StoreRoleEnum::cases())->reject(fn ($r) => $r === \App\Enums\Store\StoreRoleEnum::OWNER)->map(fn ($r) => ['value' => $r->value, 'label' => $r->label()])->values()->all()"
                    placeholder="{{ __('teams.all_roles') }}"
                    :error="$errors->first('store_role')"
                />
            </div>
        </div>

        @if ($store_role === 'staff' && (isStoreOwner() || isStoreAdmin()))
            <div>
                <label class="mb-1 block text-sm font-medium text-ink" for="tm-supervisor">{{ __('teams.reports_to') }}</label>
                <x-edz.select
                    wire:model="supervisor_membership_id"
                    :options="$this->managers"
                    placeholder="{{ __('teams.no_supervisor') }}"
                />
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-ink" for="tm-country">{{ __('teams.country') }}</label>
                <x-edz.select
                    wire:model.live="country_id"
                    :options="countries()"
                    placeholder="{{ __('teams.select_country') }}"
                    :error="$errors->first('country_id')"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-ink" for="tm-state">{{ __('teams.state') }}</label>
                <x-edz.select
                    wire:model.live="state_id"
                    :options="$this->states"
                    option-value="id"
                    option-label="name"
                    option-code="state_code"
                    placeholder="{{ __('teams.select_state') }}"
                    :disabled="empty($this->country_id)"
                    :error="$errors->first('state_id')"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-ink" for="tm-city">{{ __('teams.city') }}</label>
                <x-edz.select
                    wire:model="city_id"
                    :options="$this->cities"
                    placeholder="{{ __('teams.select_city') }}"
                    :disabled="empty($this->state_id)"
                    :error="$errors->first('city_id')"
                />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                <x-edz.checkbox size="sm" wire:model="isActive" />
                {{ __('general.active') }}
            </label>
        </div>

        @if ($store_role && $this->allPermissions->isNotEmpty())
            @include('livewire.merchant.teams.partials.permission-hub')
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

    @if ($store_role && $this->allPermissions->isNotEmpty())
        @include('livewire.merchant.teams.partials.permission-group-modal')
    @endif
</div>