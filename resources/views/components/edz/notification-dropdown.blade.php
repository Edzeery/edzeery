@php
    $notifications = [
        [
            'id' => 1,
            'userName' => 'Terry Franci',
            'userImage' => '/images/user/user-02.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Nganter App',
            'type' => 'Project',
            'time' => '5 min ago',
            'status' => 'online',
        ],
        [
            'id' => 2,
            'userName' => 'Alex Johnson',
            'userImage' => '/images/user/user-03.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Nganter App',
            'type' => 'Project',
            'time' => '10 min ago',
            'status' => 'offline',
        ],
        [
            'id' => 3,
            'userName' => 'Sarah Williams',
            'userImage' => '/images/user/user-04.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Dashboard UI',
            'type' => 'Project',
            'time' => '15 min ago',
            'status' => 'online',
        ],
        [
            'id' => 4,
            'userName' => 'Mike Brown',
            'userImage' => '/images/user/user-05.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - E-commerce',
            'type' => 'Project',
            'time' => '20 min ago',
            'status' => 'online',
        ],
        [
            'id' => 5,
            'userName' => 'Emma Davis',
            'userImage' => '/images/user/user-06.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Mobile App',
            'type' => 'Project',
            'time' => '25 min ago',
            'status' => 'offline',
        ],
        [
            'id' => 6,
            'userName' => 'John Smith',
            'userImage' => '/images/user/user-07.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Landing Page',
            'type' => 'Project',
            'time' => '30 min ago',
            'status' => 'online',
        ],
        [
            'id' => 7,
            'userName' => 'Lisa Anderson',
            'userImage' => '/images/user/user-08.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - Blog System',
            'type' => 'Project',
            'time' => '35 min ago',
            'status' => 'online',
        ],
        [
            'id' => 8,
            'userName' => 'David Wilson',
            'userImage' => '/images/user/user-09.jpg',
            'action' => 'requests permission to change',
            'project' => 'Project - CRM Dashboard',
            'type' => 'Project',
            'time' => '40 min ago',
            'status' => 'online',
        ],
    ];
@endphp

<x-edz.dropdown align="right" width="350px">
    <x-slot name="trigger">
        <x-edz.icon name="bell" class="w-5 h-5" />
        <span class="edz-topbar__dot"></span>
    </x-slot>

    <div class="edz-dropdown__header">
        <h5 class="edz-dropdown__title">{{ __('menu.notifications') }}</h5>
    </div>

    <ul class="edz-notifications">
        @foreach ($notifications as $notification)
            <li class="edz-notifications__item">
                <a href="#" class="edz-notifications__link">
                    <span class="edz-notifications__avatar">
                        <img src="{{ $notification['userImage'] }}" alt="{{ $notification['userName'] }}" />
                        <span class="edz-notifications__status edz-notifications__status--{{ $notification['status'] }}"></span>
                    </span>
                    <span class="edz-notifications__content">
                        <span class="edz-notifications__text">
                            <span class="edz-notifications__user">{{ $notification['userName'] }}</span>
                            {{ $notification['action'] }}
                            <span class="edz-notifications__project">{{ $notification['project'] }}</span>
                        </span>
                        <span class="edz-notifications__meta">
                            <span>{{ $notification['type'] }}</span>
                            <span class="edz-notifications__separator"></span>
                            <span>{{ $notification['time'] }}</span>
                        </span>
                    </span>
                </a>
            </li>
        @endforeach
    </ul>

    <a href="#" class="edz-dropdown__footer">
        {{ __('buttons.view_all') }} {{ __('menu.notifications') }}
    </a>
</x-edz.dropdown>
