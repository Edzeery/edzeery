<?php

use App\Domains\Shipping\Support\CarrierStatusDictionary;
use App\Enums\Store\OrderTrackingStatus;

it('documents the three integrated carriers', function () {
    expect(CarrierStatusDictionary::carriers())->toBe(['noest', 'ecotrack', 'yalidine']);

    $options = CarrierStatusDictionary::carrierOptions();

    expect($options)->toHaveCount(3)
        ->and(array_column($options, 'value'))->toBe(['noest', 'ecotrack', 'yalidine'])
        ->and(array_column($options, 'label'))->toContain('NOEST', 'Ecotrack', 'Yalidine');
});

it('maps raw NOEST event keys onto internal statuses', function () {
    expect(CarrierStatusDictionary::statusFor('noest', 'livre'))->toBe(OrderTrackingStatus::DELIVERED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'livred'))->toBe(OrderTrackingStatus::DELIVERED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'upload'))->toBe(OrderTrackingStatus::SHIPPED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'validation_collect_colis'))->toBe(OrderTrackingStatus::IN_TRANSIT)
        ->and(CarrierStatusDictionary::statusFor('noest', 'fdr_activated'))->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY)
        ->and(CarrierStatusDictionary::statusFor('noest', 'mise_a_jour'))->toBe(OrderTrackingStatus::FAILED_ATTEMPT)
        ->and(CarrierStatusDictionary::statusFor('noest', 'return_asked_by_customer'))->toBe(OrderTrackingStatus::RETURNING)
        ->and(CarrierStatusDictionary::statusFor('noest', 'return_dispatched_to_partenaire'))->toBe(OrderTrackingStatus::RETURNED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'colis_pickup_transmit_to_partner'))->toBe(OrderTrackingStatus::RETURNED);
});

it('maps the new NOEST on-hold and cancellation keys from the docs', function () {
    expect(CarrierStatusDictionary::statusFor('noest', 'colis_suspendu'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('noest', 'verssement_admin_cust_canceled'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('noest', 'verssement_hub_cust_canceled'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('noest', 'ask_to_delete_by_admin'))->toBe(OrderTrackingStatus::CANCELLED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'ask_to_delete_by_hub'))->toBe(OrderTrackingStatus::CANCELLED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'echange_valide'))->toBe(OrderTrackingStatus::DELIVERED)
        ->and(CarrierStatusDictionary::statusFor('noest', 'echange_valid_by_hub'))->toBe(OrderTrackingStatus::DELIVERED);
});

it('keeps administrative events unmapped so the previous status is untouched', function () {
    expect(CarrierStatusDictionary::statusFor('noest', 'edited_informations'))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('noest', 'edit_price'))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('noest', 'edit_wilaya'))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('noest', 'extra_fee'))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('noest', ''))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('noest', null))->toBeNull();
});

it('maps the Ecotrack activity and status vocabularies', function () {
    expect(CarrierStatusDictionary::statusFor('ecotrack', 'order_information_received_by_carrier'))->toBe(OrderTrackingStatus::SHIPPED)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'en_ramassage'))->toBe(OrderTrackingStatus::IN_TRANSIT)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'dispatched_to_driver'))->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'suspendu'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'attempt_delivery'))->toBe(OrderTrackingStatus::FAILED_ATTEMPT)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'return_asked'))->toBe(OrderTrackingStatus::RETURNING)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'Return_received'))->toBe(OrderTrackingStatus::RETURNED)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'livred'))->toBe(OrderTrackingStatus::DELIVERED)
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'all'))->toBeNull()
        ->and(CarrierStatusDictionary::statusFor('ecotrack', 'annule'))->toBe(OrderTrackingStatus::CANCELLED);
});

it('maps the Yalidine history statuses verbatim to internal statuses', function () {
    expect(CarrierStatusDictionary::statusFor('yalidine', 'Prêt à expédier'))->toBe(OrderTrackingStatus::SHIPPED)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'En transit'))->toBe(OrderTrackingStatus::IN_TRANSIT)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Sorti en livraison'))->toBe(OrderTrackingStatus::OUT_FOR_DELIVERY)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Bloqué'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'En alerte'))->toBe(OrderTrackingStatus::ON_HOLD)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Tentative échouée'))->toBe(OrderTrackingStatus::FAILED_ATTEMPT)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Retour vers centre'))->toBe(OrderTrackingStatus::RETURNING)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Retour non retiré'))->toBe(OrderTrackingStatus::RETURNED)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Colis abandonné'))->toBe(OrderTrackingStatus::LOST)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Annulé'))->toBe(OrderTrackingStatus::CANCELLED)
        ->and(CarrierStatusDictionary::statusFor('yalidine', 'Livré'))->toBe(OrderTrackingStatus::DELIVERED);
});

it('returns full raw lists and per-status key lookups', function () {
    $rows = CarrierStatusDictionary::list('noest');

    expect($rows)->not->toBeEmpty()
        ->and(collect($rows)->pluck('raw'))->toContain('livre', 'colis_suspendu', 'ask_to_delete_by_admin');

    foreach ($rows as $row) {
        expect($row['status'])->toBeInstanceOf(OrderTrackingStatus::class);
    }

    $deliveredKeys = CarrierStatusDictionary::keysFor('noest', OrderTrackingStatus::DELIVERED);

    expect($deliveredKeys)->toContain('livre', 'livred', 'echange_valide');

    $onHoldKeys = CarrierStatusDictionary::keysFor('noest', OrderTrackingStatus::ON_HOLD);

    expect($onHoldKeys)->toContain('colis_suspendu');

    expect(CarrierStatusDictionary::keysFor('noest', OrderTrackingStatus::DAMAGED))->toBe([]);
});
