<?php

namespace App\Livewire\Concerns;

trait OrderDeliveryPartnerConcern
{
    public function changeFormState(string $stateId): void
    {
        $this->form['state_id'] = $stateId;
        $this->form['city_id'] = '';
        $this->form['stopdesk_point_id'] = '';
        $this->formOffices = [];
        $this->formHasOffices = false;

        $this->loadCities($stateId);
    }

    public function changeFormCity(string $cityId): void
    {
        $this->form['city_id'] = $cityId;

        $this->rebuildFormOffices();
    }

    public function switchFormPartner(string $encodedValue): void
    {
        if ($encodedValue === '') {
            return;
        }

        // A partner switch re-evaluates the carrier lanes: the refund request,
        // the can-open flag and the warehouse-shipment lane never survive a leg
        // change (the new partner may not offer them — the UI re-shows the toggles).
        $this->form['refund_request'] = false;
        $this->form['can_open'] = false;
        $this->form['send_from_carrier_warehouse'] = false;

        $kind = str_starts_with($encodedValue, 'r:') ? 'rider' : 'provider';
        $id = (string) substr($encodedValue, 2);
        $this->formPartnerType = $encodedValue;

        if ($kind === 'rider') {
            $this->form['delivery_rider_id'] = $id;
            $this->form['shipping_provider_id'] = '';
            $this->form['stopdesk_point_id'] = '';
            $this->formOffices = [];
            $this->formHasOffices = false;

            // A rider always carries to the address: the office toggle is
            // locked to home delivery and shipment is always "delivery".
            $this->form['delivery_type'] = 'home';
            $this->form['shipment_type'] = 'delivery';

            return;
        }

        $this->form['shipping_provider_id'] = $id;
        $this->form['delivery_rider_id'] = '';

        $this->applyProviderScope();
    }

    public function switchConfirmPartner(string $encodedValue): void
    {
        if ($encodedValue === '') {
            return;
        }

        $kind = str_starts_with($encodedValue, 'r:') ? 'rider' : 'provider';
        $id = (string) substr($encodedValue, 2);
        $this->confirmPartnerType = $encodedValue;

        if ($kind === 'rider') {
            $this->confirmProviderId = '';
            $this->confirmRiderId = $id;
        } else {
            $this->confirmRiderId = '';
            $this->confirmProviderId = $id;
        }
    }

    public function onFormOfficePicked(): void
    {
        if (($this->form['delivery_type'] ?? null) !== 'stopdesk') {
            return;
        }

        $officeId = (string) ($this->form['stopdesk_point_id'] ?? '');

        if ($officeId === '') {
            return;
        }

        $office = \App\Domains\Shipping\Models\StopdeskPoint::find($officeId);

        if ($office && $office->city_id !== null) {
            $this->form['city_id'] = (string) $office->city_id;
        }
    }
}