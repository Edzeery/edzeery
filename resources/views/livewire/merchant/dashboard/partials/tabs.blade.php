{{-- View tabs (PHASE 37-N5): the dashboard is split into Confirmation, Delivery
     and Top views. They lead the page — before the filters — as a centred
     segmented control so the user picks the lens first, then narrows it. A view
     the user cannot read is never offered; a single permitted view renders as a
     centred heading. Arrow/Home/End move focus with a roving tabindex,
     RTL-aware, matching the inner count/rate control. --}}
@php
    $tabIcons = [
        'confirmation' => 'check-circle',
        'delivery' => 'truck',
        'top' => 'trending-up',
    ];
    $tabHintKeys = [
        'confirmation' => 'tab_confirmation_hint',
        'delivery' => 'tab_delivery_hint',
        'top' => 'tab_top_hint',
    ];
@endphp

@if (count($tabs) >= 2)
    <div class="mb-6 flex justify-center">
        <div class="flex w-full items-center gap-1 rounded-2xl border border-surface-border bg-surface-secondary/70 p-1 shadow-sm sm:inline-flex sm:w-auto"
             role="tablist"
             aria-label="{{ __('dashboard.tabs_label') }}"
             x-data="{
                 move(dir) {
                     const tabs = Array.from($el.querySelectorAll('[role=tab]'));
                     if (!tabs.length) return;
                     const rtl = document.documentElement.dir === 'rtl';
                     const step = rtl ? -dir : dir;
                     const current = tabs.indexOf(document.activeElement);
                     const next = (current < 0 ? 0 : (current + step + tabs.length) % tabs.length);
                     tabs[next].focus();
                     tabs[next].click();
                 },
                 edge(last) {
                     const tabs = Array.from($el.querySelectorAll('[role=tab]'));
                     if (!tabs.length) return;
                     const target = last ? tabs[tabs.length - 1] : tabs[0];
                     target.focus();
                     target.click();
                 }
             }"
             x-on:keydown.arrow-right.prevent="move(1)"
             x-on:keydown.arrow-left.prevent="move(-1)"
             x-on:keydown.home.prevent="edge(false)"
             x-on:keydown.end.prevent="edge(true)">
            @foreach ($tabs as $key)
                <button type="button"
                    id="dashboard-tab-{{ $key }}"
                    wire:click="setTab('{{ $key }}')"
                    role="tab"
                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                    aria-controls="dashboard-panel-{{ $key }}"
                    tabindex="{{ $activeTab === $key ? '0' : '-1' }}"
                    @class([
                        'group flex flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition-all duration-200 sm:flex-none sm:px-5 sm:py-2.5 sm:text-sm',
                        'bg-surface text-ink shadow-sm' => $activeTab === $key,
                        'text-ink-muted hover:bg-surface/60 hover:text-ink' => $activeTab !== $key,
                    ])>
                    <x-edz.icon :name="$tabIcons[$key] ?? 'grid'"
                        class="h-4 w-4 shrink-0 transition-colors {{ $activeTab === $key ? 'text-accent-600' : 'text-ink-muted group-hover:text-ink' }}" />
                    <span class="truncate">{{ __("dashboard.tab_{$key}") }}</span>
                </button>
            @endforeach
        </div>
    </div>
@elseif (count($tabs) === 1)
    <div class="mb-6 flex justify-center">
        <h1 class="flex items-center gap-2 text-base font-semibold tracking-tight text-ink sm:text-lg">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-accent-500/10 text-accent-600">
                <x-edz.icon :name="$tabIcons[$tabs[0]] ?? 'grid'" class="h-5 w-5" />
            </span>
            {{ __("dashboard.tab_{$tabs[0]}") }}
        </h1>
    </div>
@endif
