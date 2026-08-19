@if (!empty($languages))
    @php
        $currentLanguage = $languages->first(fn($l) => $l->code === $lang);
    @endphp
    {{-- Language Switcher — vanilla JS, no Alpine dependency --}}
    <div class="relative" id="lang-dropdown-{{ $lang }}">
        {{-- Trigger --}}
        <button onclick="document.getElementById('lang-dropdown-menu-{{ $lang }}').classList.toggle('hidden'); event.stopPropagation();"
                class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-neutral-border dark:border-dark-border
                       bg-neutral-surface dark:bg-dark-surface shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer">
            <img src="{{ asset('images/icons/' . $lang . '.png') }}" alt="{{ __('general.language') }}"
                 class="w-6 h-6 rounded-full object-cover">
        </button>

        {{-- Dropdown Menu --}}
        <div id="lang-dropdown-menu-{{ $lang }}"
             class="hidden absolute {{ $algin === 'left' ? 'left-0' : 'right-0' }} mt-2 w-44 rounded-xl
                    bg-neutral-surface dark:bg-dark-surface border border-neutral-border dark:border-dark-border
                    shadow-lg py-1 z-50
                    origin-top-{{ $algin === 'left' ? 'left' : 'right' }}
                    animate-[scale-in_0.15s_ease-out]">
            @foreach ($languages as $language)
                <button
                    class="w-full text-{{ $algin }} px-4 py-2.5 text-sm flex items-center gap-2.5
                           transition-colors duration-150
                           {{ $lang === $language->code
                              ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300 font-semibold'
                               : 'text-ink hover:bg-neutral-secondary dark:hover:bg-dark-secondary/50' }}"
                    onclick="event.stopPropagation();
                        fetch('{{ route('lang.switch', $language->code) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        }).then(() => location.reload())">
                    <img src="{{ asset('images/icons/' . $language->code . '.png') }}"
                         alt="{{ __('general.' . $language->name) }}"
                         class="w-5 h-5 rounded-full object-cover border border-neutral-border dark:border-dark-border">
                    <span>{{ __('general.' . Str::lower($language->name)) }}</span>
                    @if ($lang === $language->code)
                        <ion-icon name="checkmark-outline" class="text-brand-500 text-sm ms-auto"></ion-icon>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('lang-dropdown-menu-{{ $lang }}');
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    </script>
@endif
