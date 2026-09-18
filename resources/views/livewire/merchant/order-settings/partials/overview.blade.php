{{-- Page Header --}}
    <div class="mb-6">
        <x-edz.page-header
            title="{{ __('merchant_panel.order_settings') }}"
            description="{{ __('merchant_panel.order_settings_desc') }}">
        </x-edz.page-header>
    </div>

    @php
        $now = \Carbon\Carbon::now($storeTimezone ?? config('app.timezone'));
    @endphp

    {{-- Store-time info bar --}}
    <div class="edz-card edz-card--padded mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-surface flex items-center justify-center">
                    <x-edz.icon name="clock" class="w-5 h-5 text-brand-500" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-ink">
                        {{ $now->translatedFormat('l, d M Y — H:i') }}
                    </p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.store_timezone') }}: {{ $storeTimezone }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-success-500"></span>
                <span class="text-sm text-ink">
                    {{ __('merchant_panel.agents_on_shift_now', ['count' => $onShiftNow]) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-surface flex items-center justify-center">
                    <x-edz.icon name="adjustments" class="w-5 h-5 text-brand-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($shifts) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.total_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-surface flex items-center justify-center">
                    <x-edz.icon name="check-circle" class="w-5 h-5 text-success-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->where('is_active', true)->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.active_shifts') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-warning-surface flex items-center justify-center">
                    <x-edz.icon name="package" class="w-5 h-5 text-warning-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ count($assignments) }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.product_rules') }}</p>
                </div>
            </div>
        </div>
        <div class="edz-card edz-card--padded">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info-surface flex items-center justify-center">
                    <x-edz.icon name="user" class="w-5 h-5 text-info-500" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink">{{ collect($shifts)->pluck('membership_id')->unique()->count() }}</p>
                    <p class="text-xs text-ink-muted">{{ __('merchant_panel.agents_with_shifts') }}</p>
                </div>
            </div>
        </div>
    </div>