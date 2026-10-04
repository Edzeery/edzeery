<div class="mb-6 flex flex-col gap-3">
    <div class="flex flex-wrap items-center gap-2 overflow-x-auto sm:overflow-visible" style="scrollbar-width:none;-ms-overflow-style:none">
        @php
            $periods = [
                'all' => __('dashboard.period_all'),
                'today' => __('dashboard.period_today'),
                'yesterday' => __('dashboard.period_yesterday'),
                'week' => __('dashboard.period_week'),
                'month' => __('dashboard.period_month'),
                'custom' => __('dashboard.period_custom'),
            ];
        @endphp
        @foreach($periods as $key => $label)
            <button type="button"
                wire:click="$set('period','{{ $key }}')"
                class="px-3 py-1.5 text-xs sm:text-sm rounded-full border transition-colors {{ $filter->period === $key ? 'bg-primary text-white border-primary' : 'border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 text-ink' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if($filter->period === 'custom')
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-ink/70">{{ __('dashboard.date_from') }}</label>
                <input type="text" x-data="{}" x-init="flatpickr($el,{dateFormat:'Y-m-d'})" wire:model.blur="dateFrom" class="form-input text-sm px-2 py-1.5 rounded-md border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800" placeholder="YYYY-MM-DD">
            </div>
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-ink/70">{{ __('dashboard.date_to') }}</label>
                <input type="text" x-data="{}" x-init="flatpickr($el,{dateFormat:'Y-m-d'})" wire:model.blur="dateTo" class="form-input text-sm px-2 py-1.5 rounded-md border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800" placeholder="YYYY-MM-DD">
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
        @php
            $carriers = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)->carriers(currentStoreId());
            $members = app(\App\Domains\Analytics\Support\DashboardFilterOptions::class)->members(auth()->user()?->storeMemberships()->where('store_id', currentStoreId())->first());
        @endphp
        <div class="flex flex-col gap-1 w-full sm:w-auto">
            <label class="text-xs text-ink/70">{{ __('dashboard.filter_carrier') }}</label>
            <select wire:model="carrierId" class="form-select text-sm px-2 py-1.5 rounded-md border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800">
                <option value="">{{ __('dashboard.all_carriers') }}</option>
                @foreach($carriers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}@if(!$c->is_active) (inactive)@endif</option>
                @endforeach
            </select>
        </div>

        @if($members->count() > 0)
            <div class="flex flex-col gap-1 w-full sm:w-auto">
                <label class="text-xs text-ink/70">{{ __('dashboard.filter_member') }}</label>
                <select wire:model="memberId" class="form-select text-sm px-2 py-1.5 rounded-md border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800">
                    <option value="">{{ __('dashboard.all_members') }}</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($filter->memberId)
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label class="text-xs text-ink/70">&nbsp;</label>
                    <div class="flex items-center gap-2 px-2 py-1.5 rounded-md border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800">
                        <label class="flex items-center gap-1 text-xs">
                            <input type="radio" value="confirmation" wire:model="memberDimension" class="rounded">
                            <span>{{ __('dashboard.dimension_confirmation') }}</span>
                        </label>
                        <label class="flex items-center gap-1 text-xs">
                            <input type="radio" value="delivery" wire:model="memberDimension" class="rounded">
                            <span>{{ __('dashboard.dimension_delivery') }}</span>
                        </label>
                    </div>
                </div>
            @endif
        @endif

        <button type="button" wire:click="resetFilters" class="px-3 py-1.5 text-xs sm:text-sm rounded-md border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 hover:bg-surface-100 dark:hover:bg-surface-700 transition-colors mt-4 sm:mt-5">
            {{ __('dashboard.reset_filters') }}
        </button>
    </div>

    <div wire:loading.class="opacity-60" wire:target="period,dateFrom,dateTo,carrierId,memberId,memberDimension"></div>
</div>
