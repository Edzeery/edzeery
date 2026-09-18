{{-- Product Assignments Tab --}}
    @if($tab === 'products')
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-ink-muted">{{ __('merchant_panel.tab_product_assignments_desc') }}</p>
            <button wire:click="openAssignModal" class="edz-btn edz-btn--primary edz-btn--sm">
                <x-edz.icon name="check-circle" class="w-4 h-4" />
                {{ __('merchant_panel.assign_products') }}
            </button>
        </div>

        @if(!empty($assignments))
            @php
                $grouped = collect($assignments)->groupBy(fn($a) => $a['membership_id']);
            @endphp
            <div class="space-y-4">
                @foreach($grouped as $memberId => $items)
                    @php
                        $agentName = $items->first()['membership']['user']['name'] ?? '—';
                    @endphp
                    <div class="edz-card overflow-hidden" wire:key="group-{{ $memberId }}">
                        <div class="bg-surface-secondary border-b border-surface-border px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-surface flex items-center justify-center">
                                    <x-edz.icon name="user" class="w-4 h-4 text-brand-fg" />
                                </div>
                                <span class="font-semibold text-sm text-ink">{{ $agentName }}</span>
                                <span class="edz-badge edz-badge--neutral">{{ $items->count() }} {{ __('merchant_panel.products') }}</span>
                            </div>
                            <button wire:click="openAssignModal('{{ $memberId }}')" class="edz-btn edz-btn--ghost edz-btn--sm">
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