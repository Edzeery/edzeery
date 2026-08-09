<header x-data="{ open: false }" class="fixed w-full z-30 top-0 bg-neutral-surface dark:bg-dark-surface shadow-md">
    <div class="max-w-7xl mx-auto px-6 flex justify-between items-center h-16">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <x-application-logo class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            <span class="font-bold text-lg text-neutral-text dark:text-dark-text">
                {{ config('app.name', 'Edzeery') }}
            </span>
        </a>

        {{-- Desktop Navigation --}}
        <nav class="hidden md:flex gap-4 text-sm font-medium">
            <x-nav-link href="#services">{{ __('landing.services') }}</x-nav-link>
            <x-nav-link href="#pricing">{{ __('landing.pricing') }}</x-nav-link>
            <x-nav-link href="#contact">{{ __('landing.contact') }}</x-nav-link>
        </nav>

        {{-- Actions: Language, Dark Mode, Auth --}}
        <div class="flex items-center gap-4">

            {{-- Language Switcher --}}
            <x-lang-switcher />

            {{-- Dark Mode Toggle (Desktop only) --}}
            <div class="hidden md:block">
                <x-dark-toggle />
            </div>

            {{-- Auth Links (Desktop) --}}
            @guest
                <div class="hidden md:flex gap-2">
                    <x-nav-link href="{{ route('login') }}">{{ __('buttons.login') }}</x-nav-link>
                    <x-nav-link href="{{ route('register') }}">{{ __('buttons.register') }}</x-nav-link>
                </div>
            @else
                <div class="hidden md:flex gap-2">
                    <x-nav-link href="{{ getCurrentPanel() }}">
                        {{ __('titles.dashboard') }}
                    </x-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-400 transition px-3 py-1">
                            {{ __('buttons.logout') }}
                        </button>
                    </form>
                </div>
            @endguest

            {{-- Mobile Menu Button --}}
            <button @click="open = !open"
                class="md:hidden p-2 rounded-md hover:bg-neutral-secondary-soft dark:hover:bg-dark-secondary transition">
                <svg class="w-6 h-6 text-neutral-text dark:text-dark-text" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open" x-transition class="md:hidden mt-2">
        <nav class="flex flex-col gap-2 bg-neutral-surface dark:bg-dark-surface p-4 rounded-md shadow-md">
            {{-- Mobile Menu يظهر فقط Auth Links / Dashboard --}}
            @guest
                <x-nav-link href="{{ route('login') }}">{{ __('buttons.login') }}</x-nav-link>
                <x-nav-link href="{{ route('register') }}">{{ __('buttons.register') }}</x-nav-link>
            @else
                <x-nav-link href="{{ getCurrentPanel() }}">
                    {{ __('titles.dashboard') }}
                </x-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-400 transition px-3 py-1">
                        {{ __('buttons.logout') }}
                    </button>
                </form>
            @endguest

            <x-nav-link href="#services">{{ __('landing.services') }}</x-nav-link>
            <x-nav-link href="#pricing">{{ __('landing.pricing') }}</x-nav-link>
            <x-nav-link href="#contact">{{ __('landing.contact') }}</x-nav-link>

        </nav>
    </div>
</header>
