<header
    class="fixed top-0 right-0 left-0 lg:left-64 z-40
           h-16
           bg-neutral-surface/80 dark:bg-dark-surface/80
           backdrop-blur-xl
           border-b border-neutral-border dark:border-dark-border">

    <div class="h-full px-6 flex items-center justify-between">

        {{-- Left --}}
        <div class="flex items-center gap-3">
            {{-- Mobile Sidebar Toggle --}}

            <button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar"
                aria-controls="default-sidebar" type="button"
                class="text-heading bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-base ms-3 mt-3 text-sm p-2 focus:outline-none inline-flex sm:hidden">
                <span class="sr-only">Open sidebar</span>
                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10" />
                </svg>
            </button>

            <span
                class="hidden md:inline-block text-xs px-2 py-0.5 rounded-full
                         bg-primary-100 dark:bg-primary-900/30
                         text-primary-700 dark:text-primary-300">
                {{ __('titles.merchant') }}
            </span>
        </div>

        {{-- Right --}}
        <div class="flex items-center gap-3">

            {{-- Language Switcher --}}
            <x-lang-switcher />

            {{-- Dark Mode --}}
            <x-dark-toggle />

            {{-- User Menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open=!open"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg
                           hover:bg-neutral-secondary-soft dark:hover:bg-dark-secondary transition">
                    <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                    <span class="iconify text-sm opacity-60" data-icon="solar:alt-arrow-down-linear"></span>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" @click.outside="open=false" x-transition
                    class="absolute right-0 mt-2 w-48
                           bg-neutral-surface dark:bg-dark-surface
                           border border-neutral-border dark:border-dark-border
                           rounded-xl shadow-xl overflow-hidden">

                    <a href="{{ route('account.merchant.profile') }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm
                              hover:bg-neutral-secondary-soft dark:hover:bg-dark-secondary">
                        <span class="iconify" data-icon="solar:user-linear"></span>
                        {{ __('titles.profile') }}
                    </a>

                    <a href="{{ route('account.merchant.stores') }}"
                        class="flex items-center gap-2 px-4 py-2 text-sm
                              hover:bg-neutral-secondary-soft dark:hover:bg-dark-secondary">
                        <span class="iconify" data-icon="solar:shop-linear"></span>
                        {{ __('titles.my_stores') }}
                    </a>

                    <div class="border-t border-neutral-border dark:border-dark-border"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-500
                                   hover:bg-red-50 dark:hover:bg-red-500/10">
                            <span class="iconify" data-icon="solar:logout-2-linear"></span>
                            {{ __('buttons.logout') }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</header>
