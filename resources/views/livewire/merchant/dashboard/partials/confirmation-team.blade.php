{{-- Confirmation team section (M5) — follows the global count/rate mode, no
     sub-tab. Every row is a slice of the same ConfirmationStatsQuery scan as the
     headline cards. Deleted orders always show as a red (n) badge with an
     explanatory tooltip and never enter the rate (D7); the without/with-trash
     toggle only decides which members are named. --}}
@php
    // D3: the same one-colour-per-group map as the cards, doughnut and trend.
    $groupDot = array_map(
        static fn (string $token): string => 'bg-'.$token,
        config('analytics.group_colors'),
    );

    // D3 (team cards): the four core groups always read; the rarer buckets only
    // appear on a row that actually carries them, so a card never lists a wall
    // of zero rows.
    $coreGroups = ['confirmed', 'canceled', 'pending', 'failed'];
    $extraGroups = ['duplicate', 'deferred', 'other'];
    // D2: a member card mirrors the headline groups (no delivery row — orders
    // that moved past confirmation stay inside `confirmed`), so a member line
    // adds up to the same headline the section sits under.

    // The service returns a trailing total row; the member cards answer it, so
    // it is dropped here instead of being rendered as a card of its own.
    $allRows = collect($confirmationTeam ?? [])
        ->reject(fn (array $row) => ($row['type'] ?? null) === 'total')
        ->values();
    $hasActivity = $allRows->whereIn('type', ['member', 'unattributed'])->sum('total') > 0;

    // PHASE 37-M (D1): the confirmation key is the assignment, so the sentinel
    // row reads «غير مُسنَدة» (no work), not «بلا رصيد» (no credit).
    $typeLabels = [
        'unattributed' => __('dashboard.team_unassigned'),
        'total' => __('dashboard.team_total'),
    ];

    // The search is a client-side refinement of the already-scanned rows: it
    // narrows the named members only.
    $search = trim(mb_strtolower((string) ($teamSearch ?? '')));
    $rows = $search === ''
        ? $allRows
        : $allRows->filter(function (array $row) use ($search, $typeLabels) {
            $name = (string) ($row['name'] ?? ($typeLabels[$row['type']] ?? $row['type']));

            return str_contains(mb_strtolower($name), $search);
        })->values();

    $noResults = $search !== '' && $rows->isEmpty();
@endphp

<div wire:key="dash-conf-team-{{ $filter->hash() }}-{{ $confirmationTab }}-{{ $teamTrash ? 'trash' : 'clean' }}"
     class="edz-card edz-card--padded mb-6">
    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="min-w-0 lg:flex-1">
            <h3 class="text-base font-semibold tracking-tight text-ink sm:text-lg">{{ __('dashboard.team_performance_title') }}</h3>
            <p class="mt-1 text-sm text-ink-muted">{{ __('dashboard.team_performance_confirmation') }}</p>
        </div>

        {{-- Search sits between the title and the toggle. --}}
        <div class="flex justify-center lg:flex-1">
            <div class="relative w-full max-w-xs">
                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-ink-muted">
                    <x-edz.icon name="magnifying-glass" class="h-4 w-4" />
                </span>
                <input type="search"
                       wire:model.live.debounce.300ms="teamSearch"
                       placeholder="{{ __('dashboard.search_member') }}"
                       aria-label="{{ __('dashboard.search_member') }}"
                       class="edz-input w-full ps-9 text-sm">
            </div>
        </div>

        {{-- Members-only trash toggle (D7): never turns off the order badge. --}}
        <div class="flex justify-center lg:flex-1 lg:justify-end">
            <div class="flex items-center gap-1 rounded-lg bg-surface-secondary/70 p-1" role="group">
                @foreach (['clean' => false, 'with' => true] as $key => $value)
                    <button type="button"
                        wire:click="setTeamTrash({{ $value ? 'true' : 'false' }})"
                        aria-pressed="{{ $teamTrash === $value ? 'true' : 'false' }}"
                        @class([
                            'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                            'bg-surface text-ink shadow-sm' => $teamTrash === $value,
                            'text-ink-muted hover:text-ink' => $teamTrash !== $value,
                        ])>
                        {{ __("dashboard.team_members_{$key}_trash") }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    @if (! $hasActivity)
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <x-edz.icon name="users" class="mb-2 h-10 w-10 text-ink-muted" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.team_no_activity') }}</p>
        </div>
    @elseif ($noResults)
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <x-edz.icon name="magnifying-glass" class="mb-2 h-10 w-10 text-ink-muted" />
            <p class="text-sm text-ink-muted">{{ __('dashboard.no_data') }}</p>
        </div>
    @else
        {{-- Responsive team grid: one column under 768, two at md, three at xl,
             so a sparse team never leaves an empty slot. --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($rows as $row)
                @php
                    $rowName = $row['name'] ?? ($typeLabels[$row['type']] ?? $row['type']);
                @endphp
                <div class="rounded-xl border border-surface-border p-4">
                    {{-- Status: who the row belongs to. --}}
                    <div class="flex items-center justify-center gap-2.5 min-w-0">
                        @if ($row['type'] === 'member')
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-500/10 text-sm font-bold text-accent-600">
                                {{ mb_strtoupper(mb_substr($rowName, 0, 1)) }}
                            </span>
                        @else
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-surface-secondary text-ink-muted">
                                <x-edz.icon name="user" class="h-4 w-4" />
                            </span>
                        @endif
                        <div class="min-w-0 text-start">
                            <p @class([
                                'truncate text-base',
                                'font-semibold text-ink' => $row['type'] === 'member',
                                'italic font-medium text-ink-muted' => $row['type'] === 'unattributed',
                            ])>
                                {{ $rowName }}
                                @if ($row['deleted_member'])
                                    <span class="text-sm font-normal text-ink-muted">({{ __('dashboard.team_member_deleted') }})</span>
                                @endif
                            </p>
                            @if ($filter->period !== 'all')
                                <p class="text-xs text-ink-muted">{{ $periodLabel }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($confirmationTab === 'count')
                        {{-- Count: the member's all-orders figure, centred, with the
                             trash count as a (n) badge at the end. --}}
                        <div class="mt-4 text-center">
                            <p class="text-sm font-medium uppercase tracking-wider text-ink-muted">{{ __('dashboard.team_all_orders') }}</p>
                            <p class="text-4xl font-bold leading-none tracking-tighter text-ink tabular-nums">{{ $row['total'] }}</p>
                        </div>
                        {{-- The trash chip is shown only when there is trash to
                             name; a zero row stays completely clean (D7, D3). --}}
                        @if ($row['trash'] > 0)
                            <div class="mt-4 flex items-center justify-center">
                                <x-edz.tooltip :label="__('dashboard.deleted_brackets', ['count' => $row['trash']])" side="top"
                                    class="inline-flex items-center gap-1 rounded-full bg-danger-surface px-2 py-0.5 text-sm font-semibold tabular-nums text-danger-fg-strong">
                                    <x-edz.icon name="trash" class="h-4 w-4" />
                                    <span>({{ $row['trash'] }})</span>
                                </x-edz.tooltip>
                            </div>
                        @endif
                    @else
                        {{-- Rate: the member's rate, then one status per line —
                             status, its order count, then the deleted (n) badge. --}}
                        <div class="mt-4 text-center">
                            <p class="text-xs font-medium uppercase tracking-wider text-ink-muted">{{ __(($row['kind'] ?? 'gross') === 'net' ? 'dashboard.confirmation_rate_net' : 'dashboard.confirmation_rate_gross') }}</p>
                            <p class="mt-1 text-4xl font-bold leading-none tracking-tighter text-ink tabular-nums">{{ $row['rate'] }}<span class="text-lg font-semibold text-ink-muted">%</span></p>
                            <p class="mt-1 text-sm text-ink-muted">{{ $row['confirmed'] }} / {{ $row['denominator'] }}</p>
                        </div>

                        <div class="mx-auto mt-3 h-1.5 w-full overflow-hidden rounded-full bg-surface-secondary">
                            <div class="h-1.5 rounded-full transition-all duration-700 ease-out-expo {{ $row['rate'] >= config('analytics.targets.confirmation_rate') ? 'bg-success-500' : 'bg-warning-500' }}"
                                 style="width: {{ $row['rate'] }}%"></div>
                        </div>

                        <div class="mt-4 divide-y divide-surface-border">
                            @php
                                // The four core groups always read; the rarer
                                // buckets join only when this row carries them.
                                $visibleGroups = $coreGroups;

                                foreach ($extraGroups as $extra) {
                                    if ((int) ($row['groups'][$extra]['total'] ?? 0) > 0) {
                                        $visibleGroups[] = $extra;
                                    }
                                }
                            @endphp
                            @foreach ($visibleGroups as $key)
                                @php
                                    $g = $row['groups'][$key] ?? ['total' => 0, 'deleted' => 0];
                                @endphp
                                <div class="flex items-center gap-3 py-2 first:pt-0 last:pb-0">
                                    <span class="flex min-w-0 flex-1 items-center gap-2">
                                        <span class="h-2 w-2 shrink-0 rounded-full {{ $groupDot[$key] }}"></span>
                                        <span class="truncate text-sm text-ink-muted">{{ __("dashboard.group_{$key}") }}</span>
                                    </span>
                                    <span class="w-10 shrink-0 text-center text-base font-semibold text-ink tabular-nums">{{ $g['total'] }}</span>
                                    <span class="flex w-16 shrink-0 items-center justify-end gap-1">
                                        @if ($g['deleted'] > 0)
                                            <span class="text-sm font-semibold tabular-nums text-danger-600">({{ $g['deleted'] }})</span>
                                            <x-edz.tooltip :label="__('dashboard.group_deleted_hint', ['count' => $g['deleted'], 'group' => __('dashboard.group_'.$key)])" side="top" maxWidth="240px"
                                                class="text-ink-muted transition-colors hover:text-ink">
                                                <x-edz.icon name="info-circle" class="h-3.5 w-3.5" />
                                            </x-edz.tooltip>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
