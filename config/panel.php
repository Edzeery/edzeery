<?php

/*
|--------------------------------------------------------------------------
| Custom panel configuration
|--------------------------------------------------------------------------
| The navigation tree for the new Volt dashboard lives here so it can be
| consumed by any sidebar (Livewire/Volt or Blade) and extended per module.
*/

return [

    'menu' => [
        [
            'group' => 'overview',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'panel.dashboard', 'icon' => 'grid'],
            ],
        ],
        [
            'group' => 'commerce',
            'items' => [
                ['label' => 'Products', 'route' => 'panel.products', 'icon' => 'package'],
                ['label' => 'Orders', 'route' => 'panel.orders', 'icon' => 'cart'],
            ],
        ],
        [
            'group' => 'system',
            'items' => [
                ['label' => 'Settings', 'route' => 'panel.settings', 'icon' => 'settings'],
            ],
        ],
    ],
];
