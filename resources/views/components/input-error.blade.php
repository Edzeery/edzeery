@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs font-medium text-error-500 dark:text-error-400 mt-1.5 space-y-0.5', 'role' => 'alert', 'aria-live' => 'polite']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1">
                <ion-icon name="alert-circle-outline" class="text-sm mt-0.5 flex-shrink-0"></ion-icon>
                {{ $message }}
            </li>
        @endforeach
    </ul>
@endif
