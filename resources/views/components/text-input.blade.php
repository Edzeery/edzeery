@props(['disabled' => false])

<input @disabled($disabled)
    {{ $attributes->merge([
        'class' =>
            'w-full px-4 py-2.5 rounded-xl border border-neutral-border dark:border-dark-border
             bg-neutral-surface dark:bg-dark-surface text-ink text-sm
             placeholder:text-ink-soft placeholder:font-normal
             focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500
             disabled:opacity-60 disabled:cursor-not-allowed
             transition-all duration-200',
    ]) }} />
