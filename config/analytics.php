<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard targets
    |--------------------------------------------------------------------------
    |
    | The thresholds the dashboard KPIs colour themselves against. Both the
    | delivery and the confirmation views read these, so "what counts as a good
    | rate" is defined once instead of being hard-coded in two blades.
    |
    */

    'targets' => [
        // At or above this, the confirmation rate reads as healthy (green).
        'confirmation_rate' => (int) env('DASHBOARD_CONFIRMATION_RATE_TARGET', 70),

        // At or above this, the delivery rate reads as healthy (green).
        'delivery_rate' => (int) env('DASHBOARD_DELIVERY_RATE_TARGET', 80),

        // At or below this, the return rate reads as healthy (green).
        'return_rate' => (int) env('DASHBOARD_RETURN_RATE_TARGET', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Confirmation group colours (PHASE 37-M2, D3)
    |--------------------------------------------------------------------------
    |
    | One colour per confirmation group, defined once as a design-token family
    | (see resources/css/app.css). Every block reads it — the group cards, the
    | doughnut, the trend and the team rows — so a status keeps the same colour
    | everywhere and can never drift between blocks.
    |
    | Every token here clears 3:1 against the white light surface; the two muted
    | grays also clear 4.5:1 where they carry text. "duplicate" uses the accent
    | family (the same hue as violet-600) and "deferred" the info family (the
    | same hue as sky-600) because the design system exposes those two hues as
    | accent/info tokens rather than raw violet/sky families.
    |
    */

    'group_colors' => [
        'confirmed' => 'success-600',
        'pending' => 'warning-600',
        'failed' => 'danger-600',
        'canceled' => 'gray-700',
        'duplicate' => 'accent-600',
        'deferred' => 'info-500',
        'other' => 'gray-500',
    ],

    /*
    | The delivery status breakdown (PHASE 37-N8) reuses the one-colour idea over
    | the fulfilment buckets, so it lives beside the confirmation map rather than
    | inside it (the confirmation trend/legend iterates group_colors directly).
    | GroupColor::token() resolves both maps.
    */

    'delivery_group_colors' => [
        'delivered' => 'success-600',
        'returned' => 'danger-600',
        'in_progress' => 'info-500',
        'preparing' => 'accent-600',
        'canceled' => 'gray-700',
        'other' => 'gray-500',
    ],

];
