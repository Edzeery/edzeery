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

        // At or below this, the return rate reads as healthy (green).
        'return_rate' => (int) env('DASHBOARD_RETURN_RATE_TARGET', 10),
    ],

];
