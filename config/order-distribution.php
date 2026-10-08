<?php

/*
|--------------------------------------------------------------------------
| Order distribution — status lifecycle classification
|--------------------------------------------------------------------------
|
| The shift-handover engine may only touch orders whose status still needs
| confirmation work (the confirmation bucket below). Fulfillment and closed
| orders keep whatever assignment they have — their work is someone else's
| workflow or settled history. Every system order status key is classified
| exactly once across the three buckets (asserted by a test against the
| seeder), so an engine pass can never miss or double-handle a status.
|
| This is deliberately NOT `statuses.stage`: that column (PHASE 38-C) is the
| compensation model's dashboard-KPI bucketing and its own documentation
| forbids reuse as a second domain list. Distribution ownership semantics
| (handover eligibility) are a separate concern and are listed here.
|
*/

return [

    // Still being confirmed: reachable by the handover sweep.
    'confirmation_statuses' => [
        'pending',
        'no_answer_1',
        'no_answer_2',
        'no_answer_3',
        'postponed',
        'on_hold',
    ],

    // Past confirmation, in the fulfillment pipeline: never reassigned.
    'fulfillment_statuses' => [
        'confirmed',
        'preparing',
        'shipped',
        'in_transit',
        'out_for_delivery',
        'unclaimed',
        'undeliverable',
    ],

    // Settled or dead-ended outcomes: never reassigned. `paid` sits here
    // too — a paid order is financially closed even when it still ships.
    'closed_statuses' => [
        'draft',
        'wrong_number',
        'duplicate',
        'out_of_stock',
        'cancelled',
        'canceled',
        'delivered',
        'returned',
        'completed',
        'refunded',
        'paid',
    ],

];
