@props(['providers' => [], 'selectedProviderId' => null])

<aside class="edz-card edz-card--padded lg:sticky lg:top-4">
    <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted mb-3">
        {{ __('merchant_panel.select_company') }}
    </p>
    <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible">
        @foreach ($providers as $provider)
            <button type="button"
                aria-pressed="{{ $selectedProviderId === $provider['id'] ? 'true' : 'false' }}"
                wire:click="selectProvider('{{ $provider['id'] }}')"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-start transition-colors whitespace-nowrap lg:whitespace-normal
                {{ $selectedProviderId === $provider['id'] ? 'bg-brand-surface ring-1 ring-brand-ring' : 'hover:bg-surface-secondary' }}">
                <x-edz.icon name="truck"
                    class="w-4 h-4 shrink-0 {{ $selectedProviderId === $provider['id'] ? 'text-brand-500' : 'text-ink-muted' }}" />
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink truncate">{{ $provider['name'] }}</span>
                    @if ($provider['carrier'])
                        <span class="block text-xs text-ink-muted truncate">{{ $provider['carrier'] }}</span>
                    @endif
                </span>
                @if ($provider['is_integrated'])
                    <span title="{{ __('merchant_panel.stopdesk_sync_desc') }}"
                        class="shrink-0 {{ $selectedProviderId === $provider['id'] ? 'text-brand-500' : 'text-ink-muted' }}">
                        <x-edz.icon name="arrow-path" class="w-3.5 h-3.5" />
                    </span>
                @endif
                <span class="edz-badge {{ $selectedProviderId === $provider['id'] ? 'edz-badge--info' : 'edz-badge--neutral' }} shrink-0">
                    {{ $provider['points_count'] }}
                </span>
            </button>
        @endforeach
    </div>
</aside>