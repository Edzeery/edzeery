@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 p-3 rounded-xl bg-success-50 dark:bg-success-900/20 text-sm font-medium text-success-700 dark:text-success-300 flex items-center gap-2 border border-success-200 dark:border-success-800']) }}>
        <x-edz.icon name="check-circle" class="w-5 h-5 flex-shrink-0" aria-hidden="true" />
        {{ $status }}
    </div>
@endif
