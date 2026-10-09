{{-- Product Assignments Tab --}}
    @if($tab === 'products')
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_product_assignments_desc') }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <input type="text" wire:model.live.debounce.300ms="assignSearch"
                        placeholder="{{ __('merchant_panel.search_assignments') }}"
                        class="edz-input text-sm ps-8 pe-8">
                    <x-edz.icon name="search"
                        class="absolute start-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
                    @if ($this->assignSearch !== '')
                        <button wire:click="$set('assignSearch', '')" type="button"
                            class="absolute end-2 top-1/2 -translate-y-1/2 text-ink-muted hover:text-accent-500 transition"
                            aria-label="Clear search">
                            <x-edz.icon name="x-mark" class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
                <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm shrink-0">
                    <x-edz.icon name="check-circle" class="w-4 h-4" />
                    {{ __('merchant_panel.assign_products') }}
                </button>
            </div>
        </div>

        {{-- Role filter --}}
        <div class="flex flex-wrap items-center gap-2 mb-4" role="group" aria-label="{{ __('merchant_panel.role') }}">
            @foreach (['all' => __('merchant_panel.all_roles'), 'confirm' => __('merchant_panel.queue_tab_confirmation'), 'track' => __('merchant_panel.queue_tab_tracking')] as $key => $label)
                <button type="button" wire:click="$set('assignRoleFilter', '{{ $key }}')"
                    class="cursor-pointer {{ $this->assignRoleFilter === $key ? 'edz-badge edz-badge--brand' : 'edz-badge edz-badge--neutral' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Ownership setup gap: eligible members who own no product of a role --}}
        @foreach ($this->assignSetupGaps as $gap)
            @if ($gap['unowned'] > 0)
                <div class="mb-3 rounded-lg border border-warning-200 bg-warning-50/60 p-3" role="status"
                    wire:key="assign-gap-{{ $gap['role'] }}">
                    <p class="text-sm font-semibold text-warning-800">
                        {{ ($gap['role'] === 'track' ? __('merchant_panel.queue_tab_tracking') : __('merchant_panel.queue_tab_confirmation')) }} — {{ __('merchant_panel.setup_gap_title') }}
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-warning-700">
                        {{ __('merchant_panel.setup_gap_ownership', ['names' => implode(', ', $gap['names'])]) }}
                    </p>
                </div>
            @endif
        @endforeach

        @if(!empty($assignments))
            @php
                $visibleAssignments = $this->visibleAssignments();
                $grouped = collect($visibleAssignments)->groupBy(fn($a) => $a['membership_id'].'::'.($a['role_scope'] ?? 'confirm'));
            @endphp
            @if($grouped->isEmpty())
                <div class="edz-card p-8 text-center text-sm text-ink-muted">
                    {{ __('merchant_panel.no_search_results') }}
                </div>
            @else
            <div class="space-y-4">
                @foreach($grouped as $groupKey => $items)
                    @php
                        $first = $items->first();
                        $memberId = $first['membership_id'];
                        $roleScope = $first['role_scope'] ?? 'confirm';
                        $agentName = $first['membership']['user']['name'] ?? '—';
                    @endphp
                    <div class="edz-card overflow-hidden" wire:key="group-{{ $groupKey }}">
                        <div class="bg-surface-secondary border-b border-surface-border px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-surface flex items-center justify-center">
                                    <x-edz.icon name="user" class="w-4 h-4 text-brand-fg" />
                                </div>
                                <span class="font-semibold text-sm text-ink">{{ $agentName }}</span>
                                <span class="edz-badge {{ $roleScope === 'track' ? 'edz-badge--neutral' : 'edz-badge--brand' }}">
                                    {{ $roleScope === 'track' ? __('merchant_panel.queue_tab_tracking') : __('merchant_panel.queue_tab_confirmation') }}
                                </span>
                                <span class="edz-badge edz-badge--neutral">{{ $items->count() }} {{ __('merchant_panel.products') }}</span>
                            </div>
                            <button wire:click="openAssignModal('{{ $memberId }}', '{{ $roleScope }}')" class="edz-btn edz-btn--ghost edz-btn--sm">
                                <x-edz.icon name="edit" class="w-4 h-4" />
                                {{ __('merchant_panel.edit') }}
                            </button>
                        </div>
                        <div class="divide-y divide-surface-border">
                            @foreach($items as $a)
                                <div class="px-4 py-3 flex items-center justify-between hover:bg-surface-secondary" wire:key="assign-{{ $a['id'] }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-surface-secondary flex items-center justify-center">
                                            <x-edz.icon name="package" class="w-4 h-4 text-ink-muted" />
                                        </div>
                                        <span class="text-sm text-ink">{{ $a['product']['name'] ?? '—' }}</span>
                                    </div>
                                    <button type="button"
                                            class="edz-btn edz-btn--ghost edz-btn--sm text-danger-500"
                                            x-data
                                            x-on:click.prevent="(async () => { if (await EdzSwal.confirmDelete()) await $wire.removeAssignment('{{ $a['id'] }}') })()">
                                        <x-edz.icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        @else
            <div class="edz-card p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-surface-secondary flex items-center justify-center mx-auto mb-4">
                    <x-edz.icon name="package" class="w-8 h-8 text-ink-muted opacity-40" />
                </div>
                <p class="text-ink-muted mb-4">{{ __('merchant_panel.no_assignments_yet') }}</p>
                <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                    <x-edz.icon name="check-circle" class="w-4 h-4" />
                    {{ __('merchant_panel.assign_products') }}
                </button>
            </div>
        @endif
    @endif
