{{-- Admin/Guest Language Switcher (non-storefront) --}}
@if (!empty($languages))
    @php
        $currentLanguage = $languages->first(fn($l) => $l->code === $lang);
    @endphp
    {{-- Language Switcher — vanilla JS, no Alpine dependency --}}
    <div class="relative" id="lang-dropdown-{{ $lang }}">
        {{-- Trigger --}}
        <button onclick="document.getElementById('lang-dropdown-menu-{{ $lang }}').classList.toggle('hidden'); event.stopPropagation();"
                class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-gray-200 dark:border-gray-600
                       bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer">
            <img src="{{ asset('images/icons/' . $lang . '.png') }}" alt="{{ __('general.language') }}"
                 class="w-6 h-6 rounded-full object-cover">
        </button>

        {{-- Dropdown Menu --}}
        <div id="lang-dropdown-menu-{{ $lang }}"
             class="hidden absolute {{ ($algin ?? 'right') === 'left' ? 'left-0' : 'right-0' }} mt-2 w-44 rounded-xl
                    bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                    shadow-lg py-1 z-50
                    origin-top-{{ ($algin ?? 'right') === 'left' ? 'left' : 'right' }}
                    animate-[scale-in_0.15s_ease-out]">
            @foreach ($languages as $language)
                <button
                    class="w-full text-{{ ($algin ?? 'right') === 'left' ? 'left' : 'right' }} px-4 py-2.5 text-sm flex items-center gap-2.5
                           transition-colors duration-150
                           {{ $lang === $language->code
                              ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold'
                              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                    onclick="event.stopPropagation();
                        fetch('{{ route('lang.switch', $language->code) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        }).then(function(r) {
                            if (r.ok) { location.reload(); }
                            else { console.error('Language switch failed:', r.status); }
                        }).catch(function(e) {
                            console.error('Language switch error:', e);
                        })">
                    <img src="{{ asset('images/icons/' . $language->code . '.png') }}"
                         alt="{{ __('general.' . $language->name) }}"
                         class="w-5 h-5 rounded-full object-cover border border-gray-200 dark:border-gray-600">
                    <span>{{ __('general.' . Str::lower($language->name)) }}</span>
                    @if ($lang === $language->code)
                        <ion-icon name="checkmark-outline" class="text-indigo-500 text-sm ms-auto"></ion-icon>
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
