<?php

return [
    'order' => [
        'manage' => 'Move orders through the full workflow: prepare, ship, deliver and return.',
    ],
    'store' => [
        'billing' => [
            'manage' => 'View and manage store subscriptions and payment methods.',
        ],
        'team' => [
            'manage' => 'Manage the entire store team — add, edit, remove and reassign any member, regardless of who supervises them. This is the store-wide, unscoped team-management permission; it is distinct from team.manage.own, which only covers the staff a manager supervises.',
        ],
        'settings' => [
            'sensitive' => 'Open the Store Settings department from the sidebar and manage the store\'s core settings (branding, shop info, commerce rules, inventory defaults and supported languages). Owner-only by default — hidden from admins and managers unless explicitly granted.',
        ],
    ],
];
