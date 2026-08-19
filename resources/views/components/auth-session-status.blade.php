@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 p-3 rounded-xl bg-success-50 dark:bg-success-900/20 text-sm font-medium text-success-700 dark:text-success-300 flex items-center gap-2 border border-success-200 dark:border-success-800']) }}>
        <ion-icon name="checkmark-circle-outline" class="text-lg flex-shrink-0"></ion-icon>
        {{ $status }}
    </div>
@endif
