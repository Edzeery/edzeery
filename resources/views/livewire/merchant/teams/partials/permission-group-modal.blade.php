{{-- Permission Hub — per-group toggle modal (Phase 36.1). Lists every permission of the active group; enabling a permission auto-grants its prerequisites, disabling cascades them off. Sensitive permissions are flagged. --}}
<div class="contents" @edz-modal-closed.window="$wire.closePermissionGroup()">
    @if ($this->activeGroupMeta)
        <x-edz.modal :is-open="$this->activePermissionGroup !== null" size="md"
            show-close-button wire:key="permission-group-modal-{{ $this->activeGroupMeta['group'] }}">
            <div class="p-5">
                <div class="flex items-center gap-3 pe-10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-secondary text-ink-muted">
                        <x-edz.icon name="{{ $this->activeGroupMeta['icon'] }}" class="w-5 h-5" />
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-ink">{{ $this->activeGroupMeta['title'] }}</h3>
                        <p class="text-xs text-ink-muted">{{ $this->activeGroupMeta['description'] }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-2 flex-wrap">
                    <span class="text-xs text-ink-muted">{{ __('teams.dangerous_hint') }}</span>
                    <span class="flex items-center gap-1">
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm"
                                wire:click="selectGroupPermissions('{{ $this->activeGroupMeta['group'] }}')">
                            {{ __('teams.group_select_all') }}
                        </button>
                        <button type="button" class="edz-btn edz-btn--ghost edz-btn--sm text-danger-600 hover:text-danger-700"
                                wire:click="clearGroupPermissions('{{ $this->activeGroupMeta['group'] }}')">
                            {{ __('teams.group_clear') }}
                        </button>
                    </span>
                </div>

                <ul class="mt-3 divide-y divide-surface-border rounded-lg border border-surface-border">
                    @foreach ($this->activeGroupMeta['rows'] as $row)
                        <li class="flex items-start justify-between gap-3 px-3 py-2.5 {{ $row['coming_soon'] ? 'opacity-60' : '' }}">
                            <label class="flex min-w-0 items-start gap-2.5 {{ $row['coming_soon'] ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                <input type="checkbox"
                                    class="edz-checkbox mt-0.5 h-4 w-4 shrink-0"
                                    value="{{ $row['permission'] }}"
                                    @checked($row['checked'])
                                    @disabled($row['coming_soon'])
                                    wire:click="togglePermission('{{ $row['permission'] }}', {{ $row['checked'] ? 'false' : 'true' }})"
                                    wire:key="perm-cb-{{ $row['permission'] }}"
                                >
                                <span class="min-w-0">
                                    <span class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-sm font-medium text-ink">{{ $row['label'] }}</span>
                                        @if ($row['custom'])
                                            <span class="edz-badge edz-badge--neutral edz-badge--sm">{{ __('teams.custom_badge') }}</span>
                                        @endif
                                        @if ($row['dangerous'])
                                            <span class="edz-badge edz-badge--danger edz-badge--sm">{{ __('teams.dangerous_badge') }}</span>
                                        @endif
                                        @if ($row['coming_soon'])
                                            <span class="edz-badge edz-badge--neutral edz-badge--sm">{{ __('teams.soon_badge') }}</span>
                                        @endif
                                    </span>
                                    @if (! empty($row['requires']))
                                        <span class="mt-0.5 block text-xs text-ink-muted">
                                            {{ __('teams.requires') }}:
                                            {{ collect($row['requires'])->map(fn ($r) => \App\Support\PermissionGroupMeta::label($r))->implode(', ') }}
                                        </span>
                                    @endif
                                    @if (! empty($row['description']))
                                        <span class="mt-0.5 block text-xs text-ink-muted">{{ $row['description'] }}</span>
                                    @endif
                                </span>
                            </label>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 flex justify-end">
                    <button type="button" class="edz-btn edz-btn--primary edz-btn--sm" wire:click="savePermissionGroup"
                    wire:loading.attr="disabled" wire:target="savePermissionGroup">
                    {{ __('buttons.done') }}
                </button>
                </div>
            </div>
        </x-edz.modal>
    @endif
</div>