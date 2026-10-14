{{-- Top members (PHASE 37-N5, D6): the executive board's two small rankings.
     Each list reuses the section it links to — the confirmation team scan and
     the delivery team scan — sliced to the leading five. No new query and no
     new tiebreak: the same rows, re-ordered by the metric each list leads with
     and cut to five. A list with nothing to rank shows the shared empty state. --}}
@php
    $pc = collect($confirmationTeam ?? [])
        ->filter(fn (array $row): bool => ($row['type'] ?? null) === 'member')
        ->sortBy([['confirmed', 'desc'], ['name', 'asc']])
        ->take(5)
        ->values();

    $pd = collect($teamPerformance ?? [])
        ->filter(fn (array $row): bool => ($row['type'] ?? null) === 'member')
        ->sortBy([['delivered', 'desc'], ['name', 'asc']])
        ->take(5)
        ->values();

    $lists = [];
    if ($showTopConfirmers ?? false) {
        $lists[] = [
            'title' => __('dashboard.top_confirmers'),
            'metric' => __('dashboard.col_confirmed'),
            'metricKey' => 'confirmed',
            'target' => 'confirmation',
            'rows' => $pc,
        ];
    }
    if ($showTopDeliverers ?? false) {
        $lists[] = [
            'title' => __('dashboard.top_deliverers'),
            'metric' => __('dashboard.col_delivered'),
            'metricKey' => 'delivered',
            'target' => 'delivery',
            'rows' => $pd,
        ];
    }
@endphp

@if (count($lists) > 0)
    <div class="edz-stagger grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        @foreach ($lists as $list)
            <div class="edz-card edz-card--padded">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold tracking-tight text-ink">{{ $list['title'] }}</h3>
                    <button type="button" wire:click="setTab('{{ $list['target'] }}')"
                            class="text-xs font-medium text-accent-600 transition-colors hover:text-accent-500">
                        {{ __('buttons.view_all') }}
                    </button>
                </div>
                @if ($list['rows']->isNotEmpty())
                    <ol class="divide-y divide-surface-border">
                        @foreach ($list['rows'] as $index => $row)
                            <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-surface-secondary text-xs font-bold tabular-nums text-ink-muted">{{ $index + 1 }}</span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-500/10 text-sm font-bold text-accent-600">
                                    {{ mb_strtoupper(mb_substr((string) $row['name'], 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-ink">{{ $row['name'] }}</p>
                                </div>
                                <div class="shrink-0 text-end">
                                    <p class="text-sm font-bold tracking-tight tabular-nums text-ink">{{ (int) $row[$list['metricKey']] }}</p>
                                    <p class="text-xs text-ink-muted">{{ $list['metric'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <x-edz.icon name="users" class="mb-2 h-10 w-10 text-ink-muted" />
                        <p class="text-sm text-ink-muted">{{ __('dashboard.team_no_activity') }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif