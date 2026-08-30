<?php

use App\Enums\Store\InventoryMovementType;

it('treats loss and damage as decreasing, stock-affecting, manual movements', function () {
    expect(InventoryMovementType::LOSS->isDecrease())->toBeTrue()
        ->and(InventoryMovementType::DAMAGE->isDecrease())->toBeTrue()
        ->and(InventoryMovementType::LOSS->isIncrease())->toBeFalse()
        ->and(InventoryMovementType::DAMAGE->isIncrease())->toBeFalse()
        ->and(InventoryMovementType::LOSS->affectsStock())->toBeTrue()
        ->and(InventoryMovementType::DAMAGE->affectsStock())->toBeTrue()
        ->and(InventoryMovementType::LOSS->direction())->toBe(-1)
        ->and(InventoryMovementType::DAMAGE->direction())->toBe(-1)
        ->and(InventoryMovementType::LOSS->isManual())->toBeTrue()
        ->and(InventoryMovementType::DAMAGE->isManual())->toBeTrue()
        ->and(InventoryMovementType::LOSS->isSystem())->toBeFalse()
        ->and(InventoryMovementType::DAMAGE->isSystem())->toBeFalse();
});

it('keeps existing movement semantics unchanged', function () {
    expect(InventoryMovementType::RESERVE->isDecrease())->toBeTrue()
        ->and(InventoryMovementType::RESERVE->direction())->toBe(-1)
        ->and(InventoryMovementType::RELEASE->isDecrease())->toBeFalse()
        ->and(InventoryMovementType::RELEASE->direction())->toBe(1)
        ->and(InventoryMovementType::SALE->isDecrease())->toBeFalse()
        ->and(InventoryMovementType::ADJUSTMENT->isDecrease())->toBeFalse()
        ->and(InventoryMovementType::RETURN->isIncrease())->toBeTrue()
        ->and(InventoryMovementType::PURCHASE->isIncrease())->toBeTrue();
});

it('exposes loss and damage as manual options', function () {
    expect(InventoryMovementType::manualOptions())
        ->toHaveKey('loss')
        ->toHaveKey('damage');
});

it('resolves localised labels for loss and damage from the kit', function () {
    app()->setLocale('ar');

    expect(InventoryMovementType::LOSS->label())->toBe('ضياع')
        ->and(InventoryMovementType::DAMAGE->label())->toBe('تلف');

    app()->setLocale('en');

    expect(InventoryMovementType::LOSS->label())->toBe('Loss')
        ->and(InventoryMovementType::DAMAGE->label())->toBe('Damage');
});
