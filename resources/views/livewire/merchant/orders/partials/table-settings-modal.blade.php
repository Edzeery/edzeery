{{-- Table Settings Modal (P24.4): responsive popup for column visibility/order + row style --}}
@if ($showTableSettings)
    <div @edz-modal-closed.window="$wire.discardTableSettings()">
        <x-edz.modal :isOpen="true" size="lg" wire:key="order-table-settings">
            <div x-data="{ tab: 'columns' }" class="p-6">
                {{-- Header --}}
                <div class="mb-5">
                    <h3 class="text-lg font-bold text-ink">{{ __('merchant_panel.table_settings') }}</h3>
                </div>

                {{-- Tabs --}}
                <div
                    class="inline-flex w-full sm:w-auto items-center gap-1 p-1 bg-surface-secondary rounded-xl mb-5">
                    <button @click="tab = 'columns'" type="button"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                        :class="tab === 'columns' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                        <span class="inline-flex items-center gap-1.5">
                            <x-edz.icon name="view-columns" class="w-4 h-4" />
                            {{ __('merchant_panel.tab_columns') }}
                        </span>
                    </button>
                    <button @click="tab = 'style'" type="button"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-semibold rounded-lg transition"
                        :class="tab === 'style' ? 'bg-surface text-ink shadow-sm' : 'text-ink-muted hover:text-ink'">
                        <span class="inline-flex items-center gap-1.5">
                            <x-edz.icon name="color-palette" class="w-4 h-4" />
                            {{ __('merchant_panel.tab_style') }}
                        </span>
                    </button>
                </div>

                {{-- Tab: Columns --}}
                <div x-show="tab === 'columns'" x-cloak class="space-y-5">
                    @php
                        $settingsColumns = collect($this->orderColumns())
                            ->filter(fn($col) => $this->columnAllowedForUser($col))
                            ->values()
                            ->all();
                        $settingsAllKeys = collect($settingsColumns)->pluck('key')->all();
                        $settingsRequiredKeys = collect($settingsColumns)->where('required', true)->pluck('key')->all();
                        $settingsVisibleDraft = array_values(array_intersect($this->draftColumns, $settingsAllKeys));
                        $settingsSortedAll = array_merge(
                            $settingsVisibleDraft,
                            collect($settingsColumns)->pluck('key')->reject(fn($k) => in_array($k, $settingsVisibleDraft, true))->values()->all(),
                        );
                    @endphp

                    <div>
                        <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                            {{ __('merchant_panel.columns') }}</p>
                        <p class="text-xs text-ink-muted mb-2">{{ __('merchant_panel.primary_columns_hint') }}</p>
                        <p class="text-xs text-ink-muted mb-2">{{ __('merchant_panel.column_order_hint') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5"
                            x-data="orderColumnReorderDraft()"
                            @dragstart="onDragStart($event)"
                            @dragover="onDragOver($event)"
                            @drop="onDrop($event)"
                            @dragleave="onDragLeave($event)"
                            @dragend="onDragEnd()">
                            @foreach ($settingsSortedAll as $settingsKey)
                                @php
                                    $settingsCol = collect($settingsColumns)->firstWhere('key', $settingsKey);
                                    $settingsIsChecked = in_array($settingsKey, $settingsVisibleDraft, true);
                                    $settingsIndex = array_search($settingsKey, $settingsVisibleDraft, true);
                                    $settingsPos = $settingsIndex !== false ? $settingsIndex + 1 : null;
                                    $settingsCanUp = $settingsIndex !== false && $settingsIndex > 0;
                                    $settingsCanDown = $settingsIndex !== false && $settingsIndex < count($settingsVisibleDraft) - 1;
                                    $settingsIsRequired = in_array($settingsKey, $settingsRequiredKeys, true);
                                @endphp
                                <label
                                    class="flex items-center gap-2 px-2.5 py-2 rounded-lg border border-surface-border hover:bg-surface-secondary cursor-pointer text-sm {{ $settingsIsRequired ? 'bg-surface-secondary/60' : '' }}"
                                    data-col-key="{{ $settingsKey }}"
                                    data-col-row="true">
                                    <span class="cursor-grab text-ink-muted hover:text-ink shrink-0 opacity-70"
                                        draggable="true"
                                        title="{{ __('merchant_panel.drag_to_reorder') }}">
                                        <x-edz.icon name="bars-2" class="w-4 h-4" />
                                    </span>
                                    <x-edz.checkbox size="sm" wire:click="toggleDraftColumn('{{ $settingsKey }}')"
                                        :checked="$settingsIsChecked" :disabled="$settingsIsRequired"
                                        :class="$settingsIsRequired ? 'opacity-70' : ''" />
                                    @if ($settingsIsRequired)
                                        <x-edz.icon name="lock-closed" class="w-3.5 h-3.5 shrink-0 text-ink-muted"
                                            title="{{ __('merchant_panel.primary_columns') }}" />
                                    @endif
                                    <span class="flex-1 min-w-0 truncate">
                                        {{ __("merchant_panel.{$settingsCol['label_key']}") }}
                                        @if ($settingsIsRequired)
                                            <span class="text-[10px] text-ink-muted font-normal">
                                                ({{ __('merchant_panel.always_visible') }})</span>
                                        @endif
                                    </span>
                                    @if ($settingsIsChecked)
                                        <span class="text-[10px] text-ink-muted tabular-nums shrink-0" wire:key="col-order-{{ $settingsKey }}">
                                            {{ $settingsPos }}
                                        </span>
                                        <span class="flex items-center gap-0.5 shrink-0">
                                            <button type="button" title="Up"
                                                wire:click="moveDraftColumn('{{ $settingsKey }}', 'up')"
                                                @disabled(!$settingsCanUp)
                                                class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                <x-edz.icon name="arrow-up" class="w-3.5 h-3.5" />
                                            </button>
                                            <button type="button" title="Down"
                                                wire:click="moveDraftColumn('{{ $settingsKey }}', 'down')"
                                                @disabled(!$settingsCanDown)
                                                class="p-1 rounded text-ink-muted hover:text-ink hover:bg-surface-tertiary disabled:opacity-30 disabled:cursor-not-allowed">
                                                <x-edz.icon name="arrow-down" class="w-3.5 h-3.5" />
                                            </button>
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Tab: Style --}}
                <div x-show="tab === 'style'" x-cloak>
                    <p class="text-xs font-semibold text-ink-muted uppercase tracking-wide mb-2">
                        {{ __('merchant_panel.tab_style') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" wire:click="$set('draftStyle', 'default')"
                            class="flex items-start gap-3 text-start p-4 rounded-xl border transition {{ $this->draftStyle === 'default' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary' }}">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-ink">
                                    {{ __('merchant_panel.style_default') }}</p>
                                <div class="mt-2 flex items-center gap-1.5 text-[10px]">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1001</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1002</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold bg-surface-secondary text-ink-muted">#1003</span>
                                </div>
                            </div>
                            <x-edz.icon name="check"
                                class="w-4 h-4 mt-0.5 shrink-0 {{ $this->draftStyle === 'default' ? 'text-accent-600' : 'text-surface-border' }}" />
                        </button>

                        <button type="button" wire:click="$set('draftStyle', 'status')"
                            class="flex items-start gap-3 text-start p-4 rounded-xl border transition {{ $this->draftStyle === 'status' ? 'border-accent-500 ring-1 ring-accent-500 bg-accent-50/40' : 'border-surface-border hover:bg-surface-secondary' }}">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-ink">{{ __('merchant_panel.style_status') }}
                                </p>
                                <p class="mt-0.5 text-xs text-ink-muted">
                                    {{ __('merchant_panel.style_status_hint') }}</p>
                                <div
                                    class="mt-2 rounded-lg overflow-hidden border border-surface-border text-[10px]">
                                    <table class="w-full">
                                        <tbody>
                                            <tr class="edz-table-row--success">
                                                <td class="px-2.5 py-1.5 font-semibold">#1001</td>
                                                <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                </td>
                                            </tr>
                                            <tr class="edz-table-row--warning">
                                                <td class="px-2.5 py-1.5 font-semibold">#1002</td>
                                                <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                </td>
                                            </tr>
                                            <tr class="edz-table-row--danger">
                                                <td class="px-2.5 py-1.5 font-semibold">#1003</td>
                                                <td class="px-2.5 py-1.5">{{ __('merchant_panel.style_status') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <x-edz.icon name="check"
                                class="w-4 h-4 mt-0.5 shrink-0 {{ $this->draftStyle === 'status' ? 'text-accent-600' : 'text-surface-border' }}" />
                        </button>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="flex flex-wrap items-center justify-between gap-3 pt-5 mt-6 border-t border-surface-border">
                    <button type="button" wire:click="resetColumns"
                        class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.reset_columns') }}</button>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="discardTableSettings"
                            class="edz-btn edz-btn--ghost edz-btn--sm">{{ __('merchant_panel.cancel') }}</button>
                        <button wire:click="saveTableSettings" class="edz-btn edz-btn--primary edz-btn--sm"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none">
                            <span>{{ __('merchant_panel.save_settings') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-edz.modal>
    </div>
@endif