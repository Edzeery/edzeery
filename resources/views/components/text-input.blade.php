@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge([
        'class' =>
            'border border-neutral-border dark:border-dark-border rounded-lg px-3 py-2 w-full text-neutral-text dark:text-dark-text bg-neutral-surface dark:bg-dark-surface focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand transition',
    ]) }} />
