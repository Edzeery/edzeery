<?php

namespace App\Models\Stores;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $primaryKey = 'store_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'store_id',
        'currency',
        'currency_symbol',
        'language',
        'supported_languages',
        'timezone',
        'phone',
        'guest_checkout',
        'inventory_tracking',
        'show_out_of_stock',
        'allow_backorder',
        'allow_price_edit',
        'min_order_qty',
        'max_order_qty',
        'payment_methods',
        'contact_info',
        'distribution_overflow_enabled',
        'distribution_overflow_percentage',
        'finance_capture_started_at',
        'confirmation_excluded_status_keys',
        'confirmation_exclusions_locked_at',
    ];

    protected $casts = [
        'guest_checkout' => 'boolean',
        'inventory_tracking' => 'boolean',
        'show_out_of_stock' => 'boolean',
        'allow_backorder' => 'boolean',
        'allow_price_edit' => 'boolean',
        'min_order_qty' => 'integer',
        'max_order_qty' => 'integer',
        'payment_methods' => 'array',
        'contact_info' => 'array',
        'supported_languages' => 'array',
        'distribution_overflow_enabled' => 'boolean',
        'distribution_overflow_percentage' => 'integer',
        'finance_capture_started_at' => 'datetime',
        'confirmation_excluded_status_keys' => 'array',
        'confirmation_exclusions_locked_at' => 'datetime',
    ];

    /**
     * PHASE 37-N2 (D2): the exclusions are a one-shot choice. The first save
     * (even an empty one) stamps confirmation_exclusions_locked_at; from then on
     * any attempt to move either column is refused here — at the model, so no
     * Livewire action or stray request can slip past the UI.
     */
    protected static function booted(): void
    {
        static::updating(function (self $setting): void {
            $locked = $setting->getOriginal('confirmation_exclusions_locked_at') !== null;

            if ($locked && $setting->isDirty([
                'confirmation_excluded_status_keys',
                'confirmation_exclusions_locked_at',
            ])) {
                throw new RuntimeException(__('merchant_panel.confirmation_exclusions_locked'));
            }
        });
    }

    /**
     * The status keys the store keeps out of the confirmation rate. An unset
     * (null) setting means "exclude nothing".
     *
     * @return list<string>
     */
    public function excludedConfirmationStatusKeys(): array
    {
        return array_values(array_map('strval', $this->confirmation_excluded_status_keys ?? []));
    }

    /** Whether the one-shot exclusions choice has already been made. */
    public function confirmationExclusionsLocked(): bool
    {
        return $this->confirmation_exclusions_locked_at !== null;
    }

    /* ================= Relations ================= */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
