<?php

return [
    'order' => [
        'dispatch' => [
            'carrier' => 'Hand an order over to a shipping company so the carrier collects and tracks it. This is the action that actually sends the order to the carrier\'s API. Distinct from order.dispatch_validate, which only checks the shipment against the carrier\'s handover record and sends nothing, and from order.dispatch.rider, which hands the parcel to a delivery rider rather than a company. Grant this one when a member should dispatch to carriers but not hold full order.manage.',
            'rider' => 'Hand an order over to a delivery rider, or take it back from one, so the rider collects and delivers it. Distinct from order.assign, which moves an order between your own team members — grant this one when a member should place parcels with riders but not reassign work inside the team.',
        ],
        'dispatch_validate' => 'Check a shipment against the carrier\'s handover record before or after dispatch — parcel count, weight and cash-on-delivery amount. This does not send the order to the carrier; sending is covered by order.dispatch.carrier or order.manage.',
        'edit' => [
            'geography' => 'Edit an order\'s delivery destination and carrier on any order — wilaya, commune, address, delivery type, shipping company, delivery rider, office and the ship-from-carrier-warehouse flag. Store-wide: it covers every order in the store. This is the narrow alternative to order.manage for fixing delivery details.',
            'identity' => 'Edit customer-identifying details on any order — customer name, phone numbers and internal notes. Store-wide: it covers every order in the store, not only the ones in your own visibility scope. This is the narrow alternative to order.manage for identity corrections.',
            'products' => 'Edit an order\'s products, quantities, weight, shipment type and discount on any order. Store-wide: it covers every order in the store. Price is still governed separately by order.edit.price plus the store\'s allow_price_edit setting — this permission does not by itself allow changing prices.',
        ],
        'manage' => 'Move any order through the full workflow — prepare, ship, deliver and return — and edit any field on any order, plus bulk actions, the order settings panel and the distribution queue. Store-wide: it covers every order in the store. For a narrower grant see order.status.manage.own, the order.edit.* permissions and the order.dispatch.* permissions.',
        'status' => [
            'manage' => [
                'own' => 'Move the orders you can see through the full workflow without store-wide order management. Covers every status transition on those orders — not only the shipping ones: confirming a new order, call outcomes (no answer, wrong number, out of stock), postponing, duplicating, cancelling, as well as preparing, shipping, delivering and returning. Strictly limited to your visibility scope: your own assigned orders, plus your supervised staff\'s if you supervise anyone. This is the narrow alternative to order.manage for staff who handle their own order flow end to end.',
            ],
        ],
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
