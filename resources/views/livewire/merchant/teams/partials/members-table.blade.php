{{-- Team members table (Phase 36.1 partial) — rendered from teams/index.blade.php. --}}
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
                            <x-merchant.status domain="role" :status="$roleName" icon />
                        </td>
                        <td class="px-4 py-3 text-xs text-ink-muted">

                            {{ $membership->user?->state ? $membership->user?->state?->name . " , " . $membership->user?->city?->name : __('teams.no_address') }}
                        </td>
                        <td class="px-4 py-3">
                            <x-merchant.status domain="general" :status="$membership->is_active ? 'active' : 'inactive'" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                @if ($this->canManageScope($membership))
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" data-edz-loading="off"
                                            wire:click="openProductScope('{{ $membership->id }}')">{{ __('teams.product_scope') }}</button>
                                @endif
                                @if ($this->canModify($membership))
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm" data-edz-loading="off"
                                            wire:click="openEdit('{{ $membership->id }}')">{{ __('buttons.edit') }}</button>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                            wire:click="toggleActive('{{ $membership->id }}')">
                                        {{ $membership->is_active ? __('buttons.deactivate') : __('buttons.activate') }}
                                    </button>
                                    <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                            x-data
                                            data-confirm-title="{{ __('teams.remove_member') }}"
                                            data-confirm-text="{{ __('messages.action_confirm_delete') }}"
                                            data-delete-id="{{ $membership->id }}"
                                            @click.prevent="(async () => { if (await EdzSwal.confirmAction($el.dataset.confirmTitle, $el.dataset.confirmText)) await $wire.remove($el.dataset.deleteId) })()"
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
