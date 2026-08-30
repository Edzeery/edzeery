<?php

namespace App\Enums\Store;

enum InventoryMovementType: string
{
    case SALE = 'sale';        // ledger-only (stock already reserved)
    case RETURN = 'return';      // + stock
    case PURCHASE = 'purchase';    // + stock
    case ADJUSTMENT = 'adjustment';  // +/- manual
    case RESERVE = 'reserve';     // - stock (order confirmed)
    case RELEASE = 'release';     // + stock (order cancelled before delivery)
    case LOSS = 'loss';            // - stock (stock write-off: lost items)
    case DAMAGE = 'damage';        // - stock (stock write-off: damaged items)

    /* ===============================
     | Presentation (UI)
     =============================== */

    public function label(): string
    {

        return match ($this) {
            self::SALE => status_label('inventorymovementtype', 'sale'),
            self::RETURN => status_label('inventorymovementtype', 'return'),
            self::PURCHASE => status_label('inventorymovementtype', 'purchase'),
            self::ADJUSTMENT => status_label('inventorymovementtype', 'adjustment'),
            self::RESERVE => status_label('inventorymovementtype', 'reserve'),
            self::RELEASE => status_label('inventorymovementtype', 'release'),
            self::LOSS => status_label('inventorymovementtype', 'loss'),
            self::DAMAGE => status_label('inventorymovementtype', 'damage'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SALE => status_color('inventorymovementtype', 'sale'),
            self::RETURN => status_color('inventorymovementtype', 'return'),
            self::PURCHASE => status_color('inventorymovementtype', 'purchase'),
            self::ADJUSTMENT => status_color('inventorymovementtype', 'adjustment'),
            self::RESERVE => status_color('inventorymovementtype', 'reserve'),
            self::RELEASE => status_color('inventorymovementtype', 'release'),
            self::LOSS => status_color('inventorymovementtype', 'loss'),
            self::DAMAGE => status_color('inventorymovementtype', 'damage'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SALE => status_icon('inventorymovementtype', 'sale'),
            self::RETURN => status_icon('inventorymovementtype', 'return'),
            self::PURCHASE => status_icon('inventorymovementtype', 'purchase'),
            self::ADJUSTMENT => status_icon('inventorymovementtype', 'adjustment'),
            self::RESERVE => status_icon('inventorymovementtype', 'reserve'),
            self::RELEASE => status_icon('inventorymovementtype', 'release'),
            self::LOSS => status_icon('inventorymovementtype', 'loss'),
            self::DAMAGE => status_icon('inventorymovementtype', 'damage'),
        };
    }

    /* ===============================
     | Core Logic
     =============================== */

    /**
     * Does this movement increase stock?
     */
    public function isIncrease(): bool
    {
        return in_array($this, [
            self::RETURN,
            self::PURCHASE,
            self::RELEASE,
        ], true);
    }

    /**
     * Does this movement decrease stock?
     */
    public function isDecrease(): bool
    {
        return in_array($this, [
            self::RESERVE,
            self::LOSS,
            self::DAMAGE,
        ], true);
    }

    /**
     * Affects physical stock count?
     */
    public function affectsStock(): bool
    {
        return in_array($this, [
            self::SALE,
            self::RETURN,
            self::PURCHASE,
            self::ADJUSTMENT,
            self::RESERVE,
            self::RELEASE,
            self::LOSS,
            self::DAMAGE,
        ], true);
    }

    /**
     * Signed direction for ledger math
     */
    public function direction(): int
    {
        return match ($this) {
            self::SALE => 0,  // ledger-only: stock already reserved at confirm
            self::RETURN,
            self::PURCHASE,
            self::RELEASE => 1,
            self::RESERVE,
            self::LOSS,
            self::DAMAGE => -1,
            self::ADJUSTMENT => 0,  // decided by caller
        };
    }

    /**
     * Can be triggered manually by user?
     */
    public function isManual(): bool
    {
        return in_array($this, [
            self::PURCHASE,
            self::RETURN,
            self::ADJUSTMENT,
            self::LOSS,
            self::DAMAGE,
        ], true);
    }

    /**
     * System-generated movements (orders, reservations)
     */
    public function isSystem(): bool
    {
        return ! $this->isManual();
    }

    /* ===============================
     | Form Helpers
     =============================== */

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    public static function manualOptions(): array
    {
        return collect(self::cases())
            ->filter(fn (self $case) => $case->isManual())
            ->mapWithKeys(fn (self $case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }
}
