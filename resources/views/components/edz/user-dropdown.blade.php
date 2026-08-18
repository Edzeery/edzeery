@php
    $menuItems = [
        [
            'text' => __('buttons.profile'),
            'icon' => 'user',
            'path' => route('account.profile'),
        ],
        [
            'text' => __('buttons.settings'),
            'icon' => 'settings',
            'path' => route('merchant.stores.index'),
        ],
        [
            'text' => __('buttons.support'),
            'icon' => 'help-circle',
            'path' => '#',
        ],
    ];
@endphp

<x-edz.dropdown align="right" width="260px">
    <x-slot name="trigger">
        <span class="edz-dropdown__avatar">
            @if (auth()->user()->profile?->profile_picture)
                <img src="{{ asset('/storage/img/users/profiles/' . auth()->user()->profile->profile_picture) }}" alt="{{ auth()->user()->name }}" />
            @else
                <img src="{{ asset('img/icons/noimg.png') }}" alt="{{ auth()->user()->name }}" />
            @endif
        </span>
        <span class="edz-dropdown__label">{{ auth()->user()->name }}</span>
        <x-edz.icon name="chevron-down" class="w-4 h-4" />
    </x-slot>

    <div class="edz-dropdown__header">
        <span class="edz-dropdown__user-name">{{ auth()->user()->name }}</span>
        <span class="edz-dropdown__user-email">{{ auth()->user()->email }}</span>
    </div>

    <ul class="edz-dropdown__menu">
        @foreach ($menuItems as $item)
            <li>
                <a href="{{ $item['path'] }}" class="edz-dropdown__item">
                    <x-edz.icon :name="$item['icon']" class="w-5 h-5" />
                    {{ $item['text'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="edz-dropdown__item edz-dropdown__item--danger">
            <x-edz.icon name="log-out" class="w-5 h-5" />
            {{ __('buttons.logout') }}
        </button>
    </form>
</x-edz.dropdown>
