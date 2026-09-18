{{-- Shared queue table (P34.5) — one table for both tabs.
     $kind: 'confirm' (Order rows) | 'track' (OrderTracking rows). --}}
@php
    $queueRows = $kind === 'confirm' ? $confirmationQueue : $trackingQueue;
    $queueEmpty = $kind === 'confirm'
        ? __('merchant_panel.queue_empty_confirmation')
        : __('merchant_panel.queue_empty_tracking');
@endphp

@if (empty($queueRows))
    <div class="rounded-2xl border border-surface-border bg-white p-10 text-center">
        <x-edz.icon name="list-bullet" class="w-8 h-8 mx-auto mb-3 text-ink-muted" />
        <p class="text-sm text-ink">{{ $queueEmpty }}</p>
        <p class="text-xs text-ink-muted mt-1">{{ __('merchant_panel.queue_empty_hint') }}</p>
    </div>
@else
    <div class="overflow-hidden rounded-2xl border border-surface-border bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-ink-muted border-b border-surface-border">
                        <th class="px-4 py-3 font-medium">{{ __('merchant_panel.order_number') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('merchant_panel.customer') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('merchant_panel.status') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('merchant_panel.queue_current_assignee') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('general.created_at') }}</th>
                        <th class="px-4 py-3 font-medium text-end">{{ __('merchant_panel.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border">
                    @foreach ($queueRows as $queueRow)
                        <tr wire:key="queue-{{ $kind }}-{{ $queueRow['id'] }}" class="hover:bg-surface-secondary/50">
                            <td class="px-4 py-3 font-mono font-semibold text-ink">#{{ $queueRow['number'] }}</td>
                            <td class="px-4 py-3 text-ink">{{ $queueRow['customer'] }}</td>
                            <td class="px-4 py-3">
                                @if ($kind === 'confirm')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('general', $queueRow['status']['color'] ?? 'gray')->color() }}">
                                        {!! \Edzeery\MyStatusKit\Facades\Status::for('order', $queueRow['status']['key'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                        {{ \Edzeery\MyStatusKit\Facades\Status::for('order', $queueRow['status']['key'] ?? 'default')->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->color() }}">
                                        {!! \Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->icon(null, 'w-3 h-3 shrink-0') !!}
                                        {{ \Edzeery\MyStatusKit\Facades\Status::for('tracking', $queueRow['tracking_status'] ?? 'default')->label() }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="flex items-center gap-2">
                                    @if (! empty($queueRow['assigned_to']))
                                        <span class="inline-flex items-center gap-1 text-ink-muted">
                                            <x-edz.icon name="user" class="w-3 h-3 text-ink-muted" />
                                            {{ $queueRow['assigned_to'] }}
                                        </span>
                                    @else
                                        <span class="font-medium text-ink">{{ __('merchant_panel.queue_unassigned') }}</span>
                                    @endif
                                    @if (! empty($queueRow['over_capacity']))
                                        <span class="inline-flex items-center gap-1 rounded-full bg-warning-500/10 text-warning-700 text-[10px] font-semibold px-2 py-0.5">
                                            <x-edz.icon name="exclamation-triangle" class="w-3 h-3" />
                                            {{ __('merchant_panel.queue_over_capacity') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-muted whitespace-nowrap">{{ $queueRow['created_ago'] }}</td>
                            <td class="px-4 py-3 text-end">
                                <button type="button" wire:click="openReassignModal('{{ $queueRow['id'] }}')"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:text-brand-600">
                                    <x-edz.icon name="arrows-right-left" class="w-3.5 h-3.5" />
                                    {{ __('merchant_panel.reassign') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif