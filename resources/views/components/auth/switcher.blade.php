<div class="grid grid-cols-2 mb-6 bg-neutral-secondary dark:bg-dark-secondary rounded-xl p-1">

    @foreach([
        'login' => __('buttons.login'),
        'register' => __('buttons.register'),
    ] as $route => $label)

        <x-nav-link
            :href="route($route)"
            :active="request()->routeIs($route)"
            class="text-center py-2.5 rounded-lg text-sm font-semibold transition-all duration-200">
            {{ $label }}
        </x-nav-link>

    @endforeach

</div>
