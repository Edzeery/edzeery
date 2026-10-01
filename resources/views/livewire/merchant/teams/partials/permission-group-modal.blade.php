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

                {{-- 45vh matches the other bordered, divided lists that scroll
                     inside a modal (orders-items-edit-modals). The modal panel
                     itself scrolls at 90vh with a browser-default bar, so the
                     long groups after Phase 36.12 pushed the header and the
                     Done button off with them. Scrolling the list instead keeps
                     those pinned and themes the bar with the edz convention. --}}
                <ul class="mt-3 max-h-[45vh] overflow-y-auto edz-scroll divide-y divide-surface-border rounded-lg border border-surface-border">
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
                                        {{-- The description used to sit here as a plain
                                             line under the label, which made every row a
                                             different height. It now lives in an inline
                                             tooltip on this same row, so rows stay uniform
                                             and the list reads as a clean set of switches.
                                             The trigger is a real <button> (the repo's
                                             convention for icon-only tooltip triggers) so
                                             the bubble opens on keyboard focus too, not
                                             just on hover; nesting a button in this <label>
                                             is safe because a label's activation
                                             behaviour does nothing on interactive
                                             descendants, so the checkbox never toggles
                                             from the info icon. --}}
                                        @if (! empty($row['description']))
                                            <x-edz.tooltip :label="$row['description']" side="top">
                                                <button type="button" aria-label="{{ $row['description'] }}"
                                                    class="shrink-0 cursor-help rounded p-0.5 text-ink-muted transition hover:text-accent-600">
                                                    <x-edz.icon name="info-circle" class="w-3.5 h-3.5 shrink-0" />
                                                </button>
                                            </x-edz.tooltip>
                                        @endif
                                    </span>
                                    @if (! empty($row['requires']))
                                        <span class="mt-0.5 block text-xs text-ink-muted">
                                            {{ __('teams.requires') }}:
                                            {{ collect($row['requires'])->map(fn ($r) => \App\Support\PermissionGroupMeta::label($r))->implode(', ') }}
                                        </span>
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