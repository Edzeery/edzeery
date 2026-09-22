{{-- Permission Hub — card grid (Phase 36.1). One card per StorePermissionEnum group; opening a card renders the per-group modal. Included from member-form.blade.php, only when a role is selected. --}}
<div class="border-t border-surface-border pt-4">
    <div class="mb-3 flex items-center justify-between gap-2 flex-wrap">
        <div>
            <span class="text-sm font-medium text-ink">{{ __('titles.permissions') }}</span>
            <span class="ms-2 text-xs text-ink-muted">{{ __('teams.hub_hint') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                    wire:click="selectRoleTemplate">
                {{ __('buttons.select_all') }}
            </button>
            <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                    wire:click="clearAllPermissions">
                {{ __('buttons.unselect_all') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->permissionGroups as $groupCard)
            <button type="button" wire:click="openPermissionGroup('{{ $groupCard['group'] }}')"
                    data-edz-loading="off"
                    class="group rounded-xl border border-surface-border bg-surface-secondary p-4 text-start transition hover:shadow-card focus:outline-none">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-secondary text-ink-muted">
                        <x-edz.icon name="{{ $groupCard['icon'] }}" class="w-5 h-5" />
                    </span>
                    <span class="edz-badge {{ $groupCard['checked'] === $groupCard['total'] && $groupCard['total'] > 0 ? 'edz-badge--success' : 'edz-badge--neutral' }}">
                        {{ __('teams.granted_count', ['selected' => $groupCard['checked'], 'total' => $groupCard['total']]) }}
                    </span>
                </div>
                <span class="mt-3 block text-sm font-semibold text-ink">{{ $groupCard['title'] }}</span>
                <span class="mt-1 block text-xs leading-relaxed text-ink-muted">{{ $groupCard['description'] }}</span>
                @if ($groupCard['has_dangerous'])
                    <span class="edz-badge edz-badge--danger edz-badge--sm mt-2">{{ __('teams.dangerous_badge') }}</span>
                @endif
            </button>
        @endforeach
    </div>
</div>
