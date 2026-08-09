@if (!empty($languages))
    <x-dropdown align="{{$algin}}" width="38"
        contentClasses="py-2 bg-neutral-bg dark:bg-dark-bg
        border border-gray-200 dark:border-gray-700">
        <x-slot name="trigger">

            <img src="{{ asset('images/icons/' . $lang . '.png') }}" alt="{{ __('general.language') }}"
                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 shadow-sm">
        </x-slot>

        <x-slot name="content">
            @foreach ($languages as $language)
                <button
                    class="w-full text-{{$algin}} px-4 py-2 text-sm rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800
                       {{ $lang === $language->code ? 'bg-primary/20 font-semibold' : '' }}"
                    @click.prevent="
                    fetch('{{ route('lang.switch', $language->code) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(() => location.reload())
                ">
                    <span class="flex gap-1"> <img src="{{ asset('images/icons/' . $language->code . '.png') }}"
                            alt="{{ __('general.' . $language->name) }}"
                            class="w-5 h-5 rounded-full object-cover border-2 border-gray-200 shadow-sm">
                        {{ __('general.' . Str::lower($language->name)) }}</span>
                </button>
            @endforeach
        </x-slot>
    </x-dropdown>
@endif
